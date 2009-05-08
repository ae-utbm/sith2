<?php
/* Copyright 2009
 * - Simon Lopez < simon dot lopez at ayolo dot org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 */

/**
 * @file
 */
require_once($topdir."include/cts/cached.inc.php");
require_once($topdir."include/entities/basedb.inc.php");

/**
 * Blog pour les aecms.
 *
 * @author Simon Lopez
 */
class blog extends basedb
{
  public    $id      = null;
  protected $id_asso = null;
  private   $sub_id  = null;
  private   $cats    = null;

  /**
   * Charge un blog par id
   * @param $id id du blog
   * @return boolean success
   */
  public function load_by_id ( $id )
  {
    $req = new requete($this->db,
                       "SELECT * ".
                       "FROM `aecms_blog` ".
                       "WHERE `id_blog`='".intval($id)."' ");
   if($req->lines!=1)
     return false;

   $this->_load($req->get_row());
   return true;
  }

  /**
   * Charge le blog
   * @return boolean success
   */
  public function load(&$asso,$subid=false)
  {
    if(!$asso->is_valid())
      return false;
    if(!$subid || is_null($subid))
      $subid='';
    $req = new requete($this->db,
                       "SELECT * ".
                       "FROM `aecms_blog` ".
                       "WHERE `id_asso`='".$asso->id."' ".
                       "AND `sub_id`='".mysql_real_escape_string($subid)."'");
    if($req->lines!=1)
      return false;

    $this->_load($req->get_row());
    return true;
  }

  /**
   * Charge les données du blog
   */
  public function _load ( $row )
  {
    $this->id      = $row['id_blog'];
    $this->id_asso = $row['id_asso'];
    $this->sub_id  = $row['sub_id'];
  }

  /**
   * Crée un blog
   * @param $asso un objet asso valide
   * @param $subid string de l'id secondaire du aecms
   * @return boolean success
   */
  public function create (&$asso,$subid='')
  {
    if(!$asso->is_valid())
      return false;
    if( $this->load($asso,$subid) )
      return true;
    $req = new insert($this->dbrw,
                      'aecms_blog',
                      array('id_asso'=>$asso->id,
                            'sub_id'=>$subid));
    if(!$req->is_success())
      return false;
    $this->id     = $req->get_id();
    $this->asso   = &$asso;
    $this->sub_id = mysql_real_escape_string($subid);
    return true;
  }

  /**
   * Supprime entièrement un blog
   * @return boolean success
   */
  public function delete()
  {
    if(!$this->is_valid())
      return false;
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."'");
    if($req->lines>0)
    {
      while(list($id)=$req->get_row())
      {
        $cache = new cachedcontents("aecmsblog".$this->id.intval($id));
        $cache->expire();
      }
      //suppression des messages
      new delete($this->dbrw,
                 "aecms_blog_entries",
                 array('id_blog'=>$this->id));
    }
    //suppression des auteurs
    new delete($this->dbrw,
               "aecms_blog_writers",
               array('id_blog'=>$this->id));
    //suppression des catégories
    new delete($this->dbrw,
               "aecms_blog_cat",
               array('id_blog'=>$this->id));
    //supression du blog
    new delete($this->dbrw,
               "aecms_blog",
               array('id_blog'=>$this->id));
    $this->id      = null;
    $this->cats    = null;
    $this->id_asso = null;
    return true;
  }

  /**
   * Charge les catégories du blog
   * @return boolean success
   */
  private function load_cats()
  {
    if(!$this->is_valid())
      return false;
    if(is_array($this->cats))
      return true;
    $req = new requete($this->db,
                       "SELECT `id_cat`, `cat_name` ".
                       "FROM `aecms_blog_cat` ".
                       "WHERE `id_blog`='".$this->id."'".
                       "ORDER BY `cat_name` ASC");
    $this->cats=array();
    while(list($id,$name)=$req->get_row())
      $this->cats[$id]=$name;
    return true;
  }

  /**
   * indique si une catégorie existe
   * @param $id identifiant de la catégorie
   * @return vrai si oui, sinon faux
   */
  public function is_cat($id)
  {
    if ( !$this->is_valid() )
      return array();
    $this->load_cats();
    return isset($this->cats[$id]);
  }

  /**
   * Retourne la liste des catégories
   * @return array(id=>nom)
   */
  public function get_cats()
  {
    if ( !$this->is_valid() )
      return array();
    $this->load_cats();
    return $this->cats;
  }

  /**
   * Retourne les catégories sous forme de sqltable
   * @param $page Page qui va être la cible des actions
   * @param $actions actions sur chaque objet (envoyé à %page%?action=%action%&%id_utilisateur%=[id])
   * @param $batch_actions actions possibles sur plusieurs objets (envoyé à page, les id sont le tableau %id_utilisateur%s)
   * @return contents
   */
  public function get_cats_cts($page,$actions=array(),$batch_actions=array())
  {
    if ( !$this->is_valid() )
      return new contents("Catégories");
    $this->load_cats();
    if( empty($this->cats) )
      return new contents("Catégories");
    global $topdir;
    require_once($topdir. "include/cts/sqltable.inc.php");
    $cats = array();
    foreach($this->cats as $id => $cat)
      $cats[]=array('id_cat'=>$id,'cat'=>$cat);
    $tbl = new sqltable(
         'listcatsblog',
          'Catégories',
          $cats,
          $page,
          'id_cat',
          array("cat"=>"Catégorie"),
          $actions,
          $batch_actions);
    return $tbl;
  }

  /**
   * ajoute une catégorie
   * @param $cat string nom de la catégorie
   * @return boolean success
   */
  public function add_cat($name)
  {
    if ( !$this->is_valid() )
      return false;
    $this->load_cats();
    if (in_array($name,$this->cats) )
      return true;
    $req = new insert($this->dbrw,
                      "aecms_blog_cat",
                      array('id_blog'=>$this->id,
                            'cat_name'=>mysql_real_escape_string($name)));
    if ( !$req->is_success() )
      return false;
    $this->cats[$req->get_id()]=$name;
    return true;
  }

  /**
   * supprime une catégorie
   * @param $id id de la catégorie
   * @return boolean success
   */
  public function del_cat($id)
  {
    if ( !$this->is_valid() )
      return false;
    $this->load_cats();
    if( !isset($this->cats[$id]) )
      return false;
    new update($this->dbrw,
               "aecms_blog_entries",
               array('id_cat'=>null),
               array('id_blog'=>$this->id,
                     'id_cat'=>intval($id)));
    $req = new delete($this->dbrw,
                      "aecms_blog_cat",
                      array('id_blog'=>$this->id,
                            'id_cat'=>intval($id)));
    if ( ! $req->is_success() )
      return false;
    unset($this->cats[$id]);
    return true;
  }

  /**
   * Détermine si un utilisateur est administrateur du blog
   * @param $user un objet de type utilisateur
   * @return vrai si admin sinon faux
   */
  public function is_admin ( &$user )
  {
    if ( !$this->is_valid() )
      return false;
    global $site;
    return $site->is_user_admin();
  }

  /**
   * Détermine si un utilisateur est blogueur
   * @param $user un objet de type utilisateur
   * @return vrai si blogueur ou admin sinon faux
   */
  public function is_writer ( &$user )
  {
    if( !$user->is_valid() )
      return false;
    if ( $this->is_admin($user) )
      return true;
    $req = requete($this->db,
                   "SELECT `id_utilisateur` ".
                   "FROM `aecms_blog_writers` ".
                   "WHERE `id_blog`='".$this->id."' ".
                   "AND `id_utilisateur`='".$user->id."' ");
    if($req->lines==1)
      return true;
    return false;
  }

  /**
   * ajoute un blogueur
   * @param $user un objet de type utilisateur
   * @return boolean success
   */
  public function add_writer(&$user)
  {
    if ( !$this->is_valid() )
      return false;
    if ( !$user->is_valid() )
      return false;
    if ( $this->is_admin($user) )
      return true;
    $req = new insert($this->dbrw,
                      "aecms_blog_writers",
                      array('id_blog'=>$this->id,
                            'id_utilisateur'=>$user->id));
    return $req->is_success();
  }

  /**
   * supprime un blogueur
   * @param $user un objet de type utilisateur
   * @return boolean success
   */
  public function del_writer(&$user)
  {
    if ( !$this->is_valid() )
      return false;
    if ( !$user->is_valid() )
      return false;
    $req = new delete($this->dbrw,
                      "aecms_blog_writers",
                      array('id_blog'=>$this->id,
                            'id_utilisateur'=>$user->id));
    return $req->is_success();
  }

  /**
   * Retourne la liste des blogueurs sous forme de sqltable
   * @param $page Page qui va être la cible des actions
   * @param $actions actions sur chaque objet (envoyé à %page%?action=%action%&%id_utilisateur%=[id])
   * @param $batch_actions actions possibles sur plusieurs objets (envoyé à page, les id sont le tableau %id_utilisateur%s)
   * @return contents
   */
  public function get_writers_cts($page,$actions=array(),$batch_actions=array())
  {
    global $topdir;
    require_once($topdir. "include/cts/sqltable.inc.php");
    $req = new requete($this->db,
                       "SELECT `aecms_blog_writers`.`id_utilisateur` ".
                       ", CONCAT( `utilisateurs`.`prenom_utl` ".
                       "         ,' ' ".
                       "         ,`utilisateurs`.`nom_utl`) ".
                       "    AS `nom_utilisateur` ".
                       "FROM `aecms_blog_writers` ".
                       "INNER JOIN `utilisateurs` USING(`id_utilisateur`) ".
                       "WHERE `id_blog`='".$this->id."'");
    $tbl = new sqltable(
          'listwritersblog',
          'Blogueurs',
          $req,
          $page,
          'id_utilisateur',
          array("nom_utilisateur"=>"Utilisateur"),
          $actions,
          $batch_actions);
    return $tbl;
  }

  /**
   * ajoute un billet
   * @param $user un objet de type utilisateur (le posteur)
   * @param $titre le titre
   * @param $intro une introduction
   * @param $content le contenu
   * @param $pub boolean publié? (défault vrai)
   * @param $idcat id de la catégorie (défault null)
   * @param $date timestamp de publication (défault time())
   * @return boolean success
   */
  public function add_entry(&$user,
                            $titre,
                            $intro,
                            $content,
                            $pub=true,
                            $idcat=null,
                            $date=null)
  {
    if( !$this->is_valid() )
      return false;
    if( !$user->is_valid() )
      return false;
    if( !is_null($idcat) && !isset($this->cats[$idcat]) )
      $idcat=null;
    if( is_null($date) )
      $date=time();
    $date=date("Y-m-d H:i:s",$date);
    if($pub)
      $pub='y';
    else
      $pub='n';
    $req = new insert($this->dbrw,
                      "aecms_blog_entries",
                      array('id_blog'=>$this->id,
                            'id_utilisateur'=>$user->id,
                            'id_cat'=>intval($idcat),
                            'date'=>$date,
                            'pub'=>$pub,
                            'titre'=>$titre,
                            'intro'=>$intro,
                            'contenu'=>$contenu),1);
    if( !$req->is_success() )
      return false;
    return $req->get_id();
  }

  /**
   * modifie un billet
   * @param $id identifiant du billet
   * @param $titre le titre
   * @param $intro une introduction
   * @param $content le contenu
   * @param $pub boolean publié?
   * @param $idcat id de la catégorie
   * @param $date timestamp de publication
   * @return boolean success
   */
  public function update_entry($id,
                               $titre,
                               $intro,
                               $content,
                               $pub,
                               $idcat,
                               $date)
  {
    if( !$this->is_valid() )
      return false;
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `id_entry`='".intval($id)."'");
    if( $req->lines!=1 )
      return false;
    if( is_null($date) )
      $date=time();
    $date=date("Y-m-d H:i:s",$date);
    if($pub)
      $pub='y';
    else
      $pub='n';
    $req = new update($this->dbrw,
                      "aecms_blog_entries",
                      array('id_cat'=>intval($idcat),
                            'date'=>$date,
                            'pub'=>$pub,
                            'titre'=>$titre,
                            'intro'=>$intro,
                            'contenu'=>$contenu),
                      array('id_blog'=>$this->id,
                            'id_entry'=>intval($id)));
    $cache = new cachedcontents("aecmsblog".$this->id.intval($id));
    $cache->expire();
    return $req->is_success();
  }

  /**
   * supprime un billet
   * @param $id identifiant du billet
   * @return boolean success
   */
  public function delete_entry($id)
  {
    if( !$this->is_valid() )
      return false;
    $req = new delete($this->dbrw,
                      "aecms_blog_entries",
                      array('id_blog'=>$this->id,
                            'id_entry'=>intval($id)));
    $cache = new cachedcontents("aecmsblog".$this->id.intval($id));
    $cache->expire();
    return $req->is_success();
  }

  /**
   * publie un billet
   * @param $id identifiant du billet
   * @return boolean success
   */
  public function publish_entry($id)
  {
    if( !$this->is_valid() )
      return false;
    $req = new update($this->dbrw,
                      "aecms_blog_entries",
                      array('pub'=>'y'),
                      array('id_blog'=>$this->id,
                            'id_entry'=>intval($id)));
    $cache = new cachedcontents("aecmsblog".$this->id.intval($id));
    $cache->expire();
    return $req->is_success();
  }

  /**
   * Retourne un contents d'un billet
   * @param $id identifiant du billet
   * @return array
   */
  public function get_entry_row($id)
  {
    $req = new requete($this->db,
                       "SELECT * ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `id_entry`='".intval($id)."'");
    if($req->lines==0)
      return false;
    return $req->get_row();
  }

  /**
   * Retourne un contents d'un billet
   * @param $id identifiant du billet
   * @param $user utilisateur souhaitant accéder au billet
   * @return contents
   */
  public function get_cts_entry($id,&$user)
  {
    $lim = "AND `pub`='y' ";
    if( $this->is_writer($user) )
      $lim = '';
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       ",`id_utilisateur` ".
                       ",`date` ".
                       ",`titre` ".
                       ",`intro` ".
                       ",`content` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `id_entry`='".intval($id)."' ".
                       $lim);
    if($req->lines==0)
      return new contents('Billet non trouvé');

    $cache = new cachedcontents("aecmsblog".$this->id.intval($id));
    if ( $cache->is_cached() )
      return $cache->get_cache();
    $cts = new blogentry($row);
    $cache->set_contents($cts);
    return $cache;
  }

  /**
   * Retourne les billets en attente sous forme de sqltable
   * @param $page Page qui va être la cible des actions
   * @param $actions actions sur chaque objet (envoyé à %page%?action=%action%&%id_utilisateur%=[id])
   * @param $batch_actions actions possibles sur plusieurs objets (envoyé à page, les id sont le tableau %id_utilisateur%s)
   * @return contents
   */
  public function get_cts_waiting_entries($page,$actions=array(),$batch_actions=array())
  {
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       ",`id_utilisateur` ".
                       ",`date` ".
                       ",`titre` ".
                       ", CONCAT( `prenom_utl` ".
                       "         ,' ' ".
                       "         ,`nom_utl`) ".
                       "    AS `nom_utilisateur` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `pub`='n'".
                       "ORDER BY `titre` ASC");
    $tbl = new sqltable(
          'listwaitingentriesblog',
          'Billets en attente de publication',
          $req,
          $page,
          'id_entry',
          array("titre"=>"Titre",
                "date"=>"Date",
                "nom_utilisateur"=>"Blogguer"),
          $actions,
          $batch_actions);
    return $tbl;
  }

  /**
   * Retourne un contents des billets d'une cattégorie
   * @param $id identifiant du billet
   * @param $page numéro de la page à afficher
   * @return contents
   */
  public function get_cts_cat($id,$page=0)
  {
    $begin = 10*intval($page);
    $end   = 10+10*intval($page);
    $this->load_cats();
    if (!isset($this->cats[$id]) )
      return $this->get_cts();
    $cts = new contents("Catégorie ".$this->cats[$id],'<div class="blog">');
    $req = new requete($this->db,
                       "SELECT COUNT(*) ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `id_cat`='".intval($id)."' ".
                       "AND `pub`='y' ");
    list($total)=$req->get_row();
    if($total==0)
    {
      $cts->add_paragraph("Il n'y a aucun billet dans cette catégorie.","blogempty");
      $cts->puts('</div>');
      return $cts;
    }
    if( $begin>=$total )
    {
      $page=0;
      $begin=0;
    }
    $end = $begin+10;
    if($begin>0)
      $cts->puts("<div class='blogprevious blognavtop'><a href='?id_cat=".
                 $id."&id_page=".($page-1)."'>Billets précédents</a>");
    if($end<$total)
      $cts->puts("<div class='blognext blognavtop'><a href='?id_cat=".$id.
                 "&id_page=".($page+1)."'>Billets suivants</a>");
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       ",`id_utilisateur` ".
                       ",`date` ".
                       ",`titre` ".
                       ",`intro` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `id_cat`='".intval($id)."' ".
                       "AND `pub`='y' ".
                       "ORDER BY `date` DESC ".
                       "LIMIT ".$begin.",".$end."");
    $user = new utilisateur($this->db);
    while(list($id,$utl,$date,$titre,$intro)=$req->get_row())
    {
      $cache = new cachedcontents("aecmsblog".$this->id.intval($id)."preview");
      if ( !$cache->is_cached() )
      {
        if( !$user->load_by_id($utl) )
          $auteur = 'Annonyme';
        else
          $auteur = $user->get_display_name();
        $cache->set_contents(new blogentry($id,$auteur,$date,$titre,$intro));
      }
      $cts->add($cache->get_cache(),true);
    }
    if($begin>0)
      $cts->add_paragraph("<div class='blogprevious blognavbottom'><a href='?id_cat=".
                          $id."&id_page=".($page-1)."'>Billets précédents</a>");
    if($end<$total)
      $cts->add_paragraph("<div class='blognext blognavbottom'><a href='?id_cat=".
                          $id."&id_page=".($page+1)."'>Billets suivants</a>");
    $cts->puts('</div>');
    return $cts;
  }

  /**
   * Retourne un contents de tous les billets
   * @param $page numéro de la page à afficher
   * @return contents
   */
  public function get_cts($page=0)
  {
    $begin = 10*intval($page);
    $end   = 10+10*intval($page);
    $cts = new contents(false,'<div class="blog">');
    $req = new requete($this->db,
                       "SELECT COUNT(*) ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `pub`='y' ");
    list($total)=$req->get_row();
    if($total==0)
    {
      $cts->add_paragraph("Il n'y a pas encore billet.",'blogempty');
      $cts->puts('</div>');
      return $cts;
    }
    if( $begin>=$total )
    {
      $page=0;
      $begin=0;
    }
    $end = $begin+10;
    if($begin>0)
      $cts->puts("<div class='blogprevious blognavtop'><a href='?id_cat=".
                 $id."&id_page=".($page-1)."'>Billets précédents</a>");
    if($end<$total)
      $cts->puts("<div class='blognext blognavtop'><a href='?id_cat=".$id.
                 "&id_page=".($page+1)."'>Billets suivants</a>");
    $req = new requete($this->db,
                       "SELECT `id_entry` ".
                       ",`id_utilisateur` ".
                       ",`date` ".
                       ",`titre` ".
                       ",`intro` ".
                       "FROM `aecms_blog_entries` ".
                       "WHERE `id_blog`='".$this->id."' ".
                       "AND `pub`='y' ".
                       "ORDER BY `date` DESC ".
                       "LIMIT ".$begin.",".$end."");
    $user = new utilisateur($this->db);
    while(list($id,$utl,$date,$titre,$intro)=$req->get_row())
    {
      $cache = new cachedcontents("aecmsblog".$this->id.intval($id)."preview");
      if ( !$cache->is_cached() )
      {
        if( !$user->load_by_id($utl) )
          $auteur = 'Annonyme';
        else
          $auteur = $user->get_display_name();
        $cache->set_contents(new blogentry($id,$auteur,$date,$titre,$intro));
      }
      $cts->add($cache->get_cache(),true);
    }
    if($begin>0)
      $cts->puts("<div class='blogprevious blognavbottom'><a href='?id_cat=".
                 $id."&id_page=".($page-1)."'>Billets précédents</a>");
    if($end<$total)
      $cts->puts("<div class='blognext blognavbottom'><a href='?id_cat=".
                 $id."&id_page=".($page+1)."'>Billets suivants</a>");
    $cts->puts('</div>');
    return $cts;
  }
}

/** Conteneur de texte structuré
 * @ingroup display_cts
 */
class blogentrycts extends contents
{
  var $contents;
  var $wiki;

  /** Crée un stdcontents d'une entrée de wiki
   * @param $row
   * @param $contents  Texte structuré
   */
  function blogentrycts($id,$auteur,$date,$titre,$intro,$content=false)
  {
    $this->title   = $titre;
    $this->date    = $date;
    $this->auteur  = $auteur;
    $this->intro   = $intro;
    $this->content = $content;
  }

  function html_render()
  {
    setlocale(LC_TIME, "fr_FR", "fr_FR@euro", "fr", "FR", "fra_fra", "fra");
    $this->buffer = '<div class="blogentrypubdate">Le '.
                    strftime("%A %d %B %Y à %Hh%M", strtotime($date)).
                    '</div>'."\n";
    $this->buffer = '<div class="blogentrypubdate">Par '.
                    $this->auteur.
                    '</div>'."\n";
    $this->buffer.= '<div class="blogentryintro">'.doku2xhtml($this->intro).'</div>'."\n";
    if( !$this->contents )
      $this->buffer = '<div class"blogentryreadmore"><a href="?id_entry='.$id.'>Lire la suite</a></div>'."\n";
    else
      $this->buffer.= '<div class="blogentrycontent">'.doku2xhtml($this->contents).'</div>'."\n";
    return $this->buffer;

  }
}

?>
