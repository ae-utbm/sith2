<?php
/* Copyright 2005,2006,2007
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 
/** @file
 * Gestion des nouvelles
 *
 */

/**
 * @defgroup newstype Type de nouvelles
 * @{
 */
/** Nouvelle informative (resultat éléction, resultat concours) associé à aucune date */
define("NEWS_TYPE_NOTICE",0);
/** Nouvelle sur un evenement ponctuel associé à une date (avec une durée <= 5 jours) */
define("NEWS_TYPE_EVENT",1);
/** Nouvelle sur une activitée hebdomadaire associé à de nombreuses dates */
define("NEWS_TYPE_HEBDO",2);
/** Nouvelle sur un appel à candidature, recherche de bénévoles, concours... associé à une date avec une durée trés longue */
define("NEWS_TYPE_APPEL",3);
/**
 * @}
 */

/**
 * Nouvelle du site
 */
class nouvelle extends stdentity
{
  /** Auteur de la nouvelle */
  var $id_utilisateur;
  
  /** Association/club concerné */
  var $id_asso;
  
  /** Titre */
  var $titre;
  
  /** Résumé */
  var $resume;
  
  /** Contenu */
  var $contenu;
  
  /** Date d'ajout */
  var $date;
  
  /** Etat de modération: true modéré, false non modéré */
  var $modere;
  
  /** Utilisateur ayant modéré la nouvelle */
  var $id_utilisateur_moderateur;
  
  /** Afficher seulement dans AECMS : true sur AECMS seulement, false sur le site général et AECMS */
  var $asso_seule;
  
  /** Lieu concerné par la nouvelle */
  var $id_lieu;
  
  /** Charge une nouvelle en fonction de son id
   * $this->id est égal à null en cas d'erreur
   * @param $id id de la fonction
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `nvl_nouvelles`
				WHERE `id_nouvelle` = '" .
		       mysql_real_escape_string($id) . "'
				LIMIT 1");

    if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
  }

  /*
   * fonction de chargement (privee)
   *
   * @param row tableau associatif
   * contenant les informations sur la nouvelle.
   *
   */
  function _load ( $row )
  {
    $this->id			= $row['id_nouvelle'];
    $this->id_utilisateur	= $row['id_utilisateur'];
    $this->id_asso		= $row['id_asso'];
    $this->titre			= $row['titre_nvl'];
    $this->resume		= $row['resume_nvl'];
    $this->contenu		= $row['contenu_nvl'];
    $this->date			= strtotime($row['date_nvl']);
    $this->modere		= $row['modere_nvl'];
    $this->id_utilisateur_moderateur	= $row['id_utilisateur_moderateur'];
    $this->type	= $row['type_nvl'];
    $this->asso_seule = $row['asso_seule_nvl'];
    $this->id_lieu = $row['id_lieu'];
  }

  /** Construit un stdcontents avec le contenu de la nouvelle
   */
  function get_contents ()
  {
    global $wwwtopdir,$topdir;
    
    $asso = new asso($this->db);
    $asso->load_by_id($this->id_asso);
    
    $cts = new contents($this->titre);
    
    if ( $asso->is_valid() )
    {
      if ( !file_exists($topdir."var/img/logos/".$asso->nom_unix.".small.png") )
        $img =  $wwwtopdir."images/default/news.small.png";
      else
        $img = "/var/img/logos/".$asso->nom_unix.".small.png";
      
      $cts->add(new image($asso->nom, $img, "newsimg"));
    }
    else
      $cts->add(new image("Nouvelle",  $wwwtopdir."images/default/news.small.png", "newsimg"));      
    
    $cts->add(new wikicontents(false,$this->contenu));
  
    $req = new requete ( $this->db,
        "SELECT * FROM nvl_dates ".
        "WHERE id_nouvelle='".$this->id."' ORDER BY date_debut_eve");
  
    if ( $req->lines == 1 )
    {
      $row = $req->get_row();
      $cts->add_paragraph("Date : le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
    }
    elseif ( $req->lines > 1 )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      while ( $row = $req->get_row() )
        $lst->add("Le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
      $cts->add($lst);
    }
      
    if ( !is_null($this->id_lieu) && class_exists("lieu") )
    {
      $lieu = new lieu($this->db);
      $lieu->load_by_id($this->id_lieu);
      $cts->add_paragraph("Lieu : ".$lieu->get_html_link());
    }
      
    if ( $asso->is_valid() )
    {
      $cts->puts("<div class=\"clearboth\"></div>");
      $cts->add_title(2,"");
      $cts->add_paragraph(classlink($asso));
      if ( $asso->is_mailing_allowed() && !is_null($asso->id_parent) )
        $cts->add_paragraph("Inscrivez vous pour recevoir les nouvelles de ".$asso->nom." par e-mail et participer aux discussions, c'est simple et rapide : <a href=\"".$wwwtopdir."asso.php?id_asso=".$asso->id."&amp;action=selfenroll\">cliquez ici</a>");      
    }

    return $cts;
  }
  
  /** Construit un stdcontents avec le contenu de la nouvelle
   */
  function get_contents_nobrand_flow ()
  {
    global $wwwtopdir,$topdir;

    $cts = new contents($this->titre);
    
    $cts->add(new image("Nouvelle",  $wwwtopdir."images/default/news.small.png", "newsimg"));      
    
    $cts->add(new wikicontents(false,$this->contenu));
  
    $req = new requete ( $this->db,
        "SELECT * FROM nvl_dates ".
        "WHERE id_nouvelle='".$this->id."' ORDER BY date_debut_eve");
        
    $cts->add_paragraph("Posté le ".date("d/m/Y",$this->date));
            
    if ( $req->lines == 1 )
    {
      $row = $req->get_row();
      $cts->add_paragraph("Date : le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
    }
    elseif ( $req->lines > 1 )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      while ( $row = $req->get_row() )
        $lst->add("Le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
      $cts->add($lst);
    }
    if ( !is_null($this->id_lieu) && class_exists("lieu") )
    {
      $lieu = new lieu($this->db);
      $lieu->load_by_id($this->id_lieu);
      $cts->add_paragraph("Lieu : ".$lieu->get_html_link());
    }

    return $cts;
  }
  
  /** Supprime la nouvelle
   */
  function delete ()
  {
    if ( !$this->dbrw ) return;
    
    $this->set_tags_array(array());
    
    new delete($this->dbrw,"nvl_nouvelles",array("id_nouvelle"=>$this->id));
    new delete($this->dbrw,"nvl_dates",array("id_nouvelle"=>$this->id));
    new delete($this->dbrw,"nvl_nouvelles_files",array("id_nouvelle"=>$this->id));
    $this->id = null;
  }

  /** Valide la nouvelle
   */
  function validate($id_utilisateur_moderateur)
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"nvl_nouvelles",array("modere_nvl"=>1,"id_utilisateur_moderateur"=>$id_utilisateur_moderateur),array("id_nouvelle"=>$this->id));
    $this->modere_nvl = 1;
    $this->id_utilisateur_moderateur = $id_utilisateur_moderateur;
  }

  /** Invalide la nouvelle
   */
  function unvalidate()
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"nvl_nouvelles",array("modere_nvl"=>0),array("id_nouvelle"=>$this->id));
    $this->modere_nvl = 0;
  }


  /** @brief Ajoute une nouvelle
   *
   * @param id_utilisateur l'identifiant de l'utilisateur
   * @param id_asso (facultatif) l'identifiant de l'association
   * @param titre titre de la nouvelle
   * @param resume un resume de la nouvelle
   * @param contenu le contenu (format wiki2xhtml)
   *
   * @return true ou false en fonction du resultat
   */
  function add_news($id_utilisateur,
		    $id_asso = null,
		    $titre,
		    $resume,
		    $contenu,
		    $type=NEWS_TYPE_EVENT,
		    $asso_seule=false,
		    $id_lieu=NULL)
  {
    if (!$this->dbrw)
      return false;
      
    $this->asso_seule = $asso_seule;
    $this->id_lieu = $id_lieu;

    $req = new insert ($this->dbrw,
		       "nvl_nouvelles",
		       array ("id_utilisateur" => $id_utilisateur,
			      "id_asso" => $id_asso,
			      "titre_nvl" => $titre,
			      "resume_nvl" => $resume,
			      "contenu_nvl" => $contenu,
			      "date_nvl" => date("Y-m-d H:i:s"),
			      "modere_nvl" =>  false,
			      "id_utilisateur_moderateur"=>null,
			      "type_nvl"=>$type,
			      "asso_seule_nvl" => $this->asso_seule,
			      "id_lieu"=>$this->id_lieu));
			      
		if ( $req )
			$this->id = $req->get_id();
		else
			$this->id = null;
			
    $this->update_references($this->resume."\n".$this->contenu);
          
    return ($req != false);
  }
  
  /**
   * Associe la nouvelle à une date
   * @param $debut Timestamp de début
   * @param $fin Timestamp de fin
   */
	function add_date($debut,$fin)
	{
		$req = new insert ($this->dbrw,
				"nvl_dates",
				array ("id_nouvelle" => $this->id,
						"date_debut_eve" => date("Y-m-d H:i:s",$debut),
						"date_fin_eve" => date("Y-m-d H:i:s",$fin)
					));
	}
	
  /**
   * Desassocie la nouvelle à une date
   * @param $id_date Numéro de date
   */
	function delete_date ( $id_date )
	{
		$req = new delete ($this->dbrw,
				"nvl_dates",
				array ("id_nouvelle" => $this->id,
						"id_dates_nvl" => $id_date,
					));
	}
	
	/**
	 * Modifie la nouvelle
	 *
	 */
	function save_news(
		    $id_asso = null,
		    $titre,
		    $resume,
		    $contenu,
		    $modere,
		    $id_utilisateur_moderateur,
		    $type=NEWS_TYPE_EVENT,
		    $asso_seule=0,
		    $id_lieu=NULL)
	{
		if (!$this->dbrw)
			return false;

		$this->titre = $titre;
		$this->resume = $resume;
		$this->contenu = $contenu;
		$this->modere = $modere;
		$this->id_asso = $id_asso;
		$this->type = $type;
		$this->id_utilisateur_moderateur = $id_utilisateur_moderateur;
    $this->asso_seule = $asso_seule;
    $this->id_lieu = $id_lieu;

		$req = new update ($this->dbrw,
		       "nvl_nouvelles",
		       array (
			      "id_asso" => $id_asso,
			      "titre_nvl" => $titre,
			      "resume_nvl" => $resume,
			      "contenu_nvl" => $contenu,
			      "modere_nvl" =>  $modere,
			      "id_utilisateur_moderateur"=>$id_utilisateur_moderateur,
			      "type_nvl"=>$type,
			      "asso_seule_nvl" => $this->asso_seule,
			      "id_lieu"=>$this->id_lieu),
			   array(
			   	"id_nouvelle"=>$this->id
			   	));
    $this->update_references($this->resume."\n".$this->contenu);
	}
	
  function update_references($contents)
  {
    new requete($this->dbrw,
      "DELETE FROM nvl_nouvelles_files ".
      "WHERE `id_nouvelle` = '" . mysql_real_escape_string($this->id) . "'");

    $this->_ref_cache=array();

    $this->_update_references($contents,"#\[\[([^\]]+?)\]\]#i");
    $this->_update_references($contents,"#\{\{([^\}]+?)\}\}#i",true);
  }

  function add_rel_file ( $id_file )
  {
    if ( !isset($this->_ref_cache[$id_file]) ) 
    {
      new insert($this->dbrw,"nvl_nouvelles_files",array("id_nouvelle"=>$this->id,"id_file"=>$id_file));
      $this->_ref_cache[$id_file]=1;
    }
  }
  
  function _update_references( $contents, $regexp, $media=false )
  {
    if ( !preg_match_all ( $regexp, $contents, $matches ) ) return;
    foreach( $matches[1] as $link )
    {
      $link = trim($link);
      
      list($link,$dummy) = explode("|",$link,2);
      list($link,$dummy) = explode("#",$link,2);
      if ( $media )
        list($link,$dummy) = explode("?",$link,2);
      if( preg_match('/^([a-zA-Z]+):\/\//',$link) )
      {
        if ( preg_match("#^(dfile:\/\/|.*d\.php\?id_file=)([0-9]*)(.*)$#i",$link,$match) )
          $this->add_rel_file($match[2]);
      }
    }
  }
	
}


?>
