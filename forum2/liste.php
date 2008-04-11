<?php

/* Copyright 2008
 * - Remy BURNEY < rburney <point> utbm <at> gmail <dot> com >
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
 * 02111-1307, USA.
 */

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/user.inc.php");
require_once($topdir . "include/entities/utilisateur.inc.php");
require_once($topdir . "include/entities/forum.inc.php");
require_once($topdir . "include/entities/sujet.inc.php");
require_once($topdir . "include/cts/forum.inc.php");


$site = new site ();
$cts=new contents();
$site->start_page("none","Administration des forum");

$can_admin=( $site->user->is_in_group("root") || $site->user->is_in_group("moderateur_forum") );

if ( !$site->user->is_in_group("moderateur_forum") )
  $site->error_forbidden("none","group",39);


$forum = new forum($site->db,$site->dbrw);
$sujet = new sujet($site->db,$site->dbrw);
$message = new message($site->db,$site->dbrw);

if ( isset($_REQUEST["id_message"]) )
{
  $message->load_by_id($_REQUEST["id_message"]);
  if ( $message->is_valid() )
  {
    $sujet->load_by_id($message->id_sujet); 
    $forum->load_by_id($sujet->id_forum); 
  }
}
elseif ( isset($_REQUEST["id_sujet"]) )
{
  $sujet->load_by_id($_REQUEST["id_sujet"]); 
  if ( $sujet->is_valid() )
  {
    $forum->load_by_id($sujet->id_forum); 
  }
}
elseif ( isset($_REQUEST["id_forum"]) )
{
  $forum->load_by_id($_REQUEST["id_forum"]); 
}



if( $_REQUEST["page"]=="new" && 
    isset($_REQUEST["type"]) &&
    isset($_REQUEST["action"]) &&
    $_REQUEST["action"] == "new")
{
   // On enregistre le sujet
  if ( isset($_REQUEST["type"]) && $_REQUEST["type"]="sbj" ) 
  {

    $message = new message($site->db,$site->dbrw);
		$sujet = new sujet($site->db,$site->dbrw);
    $forum->load_by_id($_REQUEST["id_forum"]);
   
    $type = $_REQUEST["subj_type"];
    if ( $type == SUJET_ANNONCE )
      $date_fin_annonce=$_REQUEST["date_fin_announce"];
    elseif ( $type == SUJET_ANNONCESITE )
      $date_fin_annonce=$_REQUEST["date_fin_announce_site"];

	$sujet->create($forum,$site->user,
                 $_REQUEST["titre_sujet"],
                 $_REQUEST["soustitre_sujet"],
                 $type,
                 null,
                 $date_fin_annonce);

  $message->create($forum,
                   $sujet,
                   $site->user,
                   $_REQUEST["titre_sujet"],
                   $_REQUEST["subjtext"],
                   $_REQUEST["synengine"]);

  }

  // On enregistre le forum
  if ( isset($_REQUEST["type"]) && $_REQUEST["type"]="frm" ) 
  {

    $forum = new forum($site->db,$site->dbrw);
		$forum->create($_REQUEST["titre"],
                   $_REQUEST["decription"],
                   $_REQUEST["categorie"],
                   $_REQUEST["id_forum_parent"],
                   $_REQUEST["id_asso"],
                   $_REQUEST["ordre"]);

    $cts->add_paragraph("L' ajout du forum".$_REQUEST["titre"].
                        " &agrave bien été prise en compte.");
  }



/* nouveau forum ou sujet (formulaire) */
}elseif( $_REQUEST["page"]=="new")
{

  $site->allow_only_logged_users("forum");

  // On créer le sujet  
  if ( isset($_REQUEST["type"]) && $_REQUEST["type"]="sbj" )
  {

    $frm = new form("newsbj","?page=new&type=sbj",true);
    $frm->add_hidden("action","new");  
    $frm->allow_only_one_usage();
  
    if ( isset($Erreur) )
      $frm->error($Erreur);

    $type=SUJET_NORMAL;
    $sfrm = new form("subj_type",null,null,null,"Sujet normal");
    $frm->add($sfrm,false,true, $type==SUJET_NORMAL ,SUJET_NORMAL ,false,true);
    
    $sfrm = new form("subj_type",null,null,null,"Sujet épinglé, il sera toujours affiché en haut");
    $frm->add($sfrm,false,true, $type==SUJET_STICK ,SUJET_STICK ,false,true);
    
    $sfrm = new form("subj_type",null,null,null,"Annonce, le message sera affiché en haut dans un cadre séparé");
    $sfrm->add_datetime_field('date_fin_announce', 
           'Date de fin de l\'annonce',
           time()+(7*24*60*60));
    $frm->add($sfrm,false,true, $type==SUJET_ANNONCE ,SUJET_ANNONCE ,false,true);
    $sfrm = new form("subj_type",null,null,null,"Annonce du site, le message sera affiché en haut sur la première page du forum");
    $sfrm->add_datetime_field('date_fin_announce_site', 
                              'Date de fin de l\'annonce',
                              time()+(7*24*60*60));
    $frm->add($sfrm,false,true, $type==SUJET_ANNONCESITE ,SUJET_ANNONCESITE ,false,true);
    }

    $values_forum = array();
    $sql = "SELECT id_forum, titre_forum FROM frm_forum ORDER BY titre_forum";
    $req = new requete($site->db, $sql);
    while( list($value,$name) = $req->get_row()){
      $values_forum[$value] = $name;
    }

    /* titre du sujet */
    $frm->add_text_field("titre_sujet", 
                         "Titre du message : ",
                         "",true,80);
    /* sous-titre du sujet */
    $frm->add_text_field("soustitre_sujet", 
                         "Sous-titre du message (optionel) : ",
                         "",false,80);

		/* forum associé au sujet */
		$frm->add_select_field('id_forum',
                           'Forum associé',
                           $values_forum,
                           '1');

    /* moteur de rendu */
    $frm->add_select_field('synengine',
                           'Moteur de rendu : ',
                            array('bbcode' => 'bbcode (type phpBB)',
                                  'doku' => 'Doku Wiki (recommandé)'),
                           'doku');
  
    /* texte du message initiateur */
    $frm->add_dokuwiki_toolbar('subjtext',"");
    $frm->add_text_area("subjtext", "Texte du message : ","",80,20);
    $frm->add_checkbox ( "star", "Ajouter à mes sujets favoris.", false );   
    $frm->add_submit("subjsubmit", "Ajouter");
    $frm->puts("<div class=\"formrow\"><div class=\"formlabel\"></div><div class=\"formfield\"><input type=\"button\" id=\"preview\" name=\"preview\" value=\"Prévisualiser\" class=\"isubmit\" onClick=\"javascript:make_preview();\" /></div></div>\n");
    $cts->add_paragraph("<script language=\"javascript\">
      function make_preview()
      {
        title = document.newsbj.titre_sujet.value;
        content = document.newsbj.subjtext.value;
        user = ".$site->user->id.";
        syntaxengine = document.newsbj.synengine.value;
        
        openInContents('msg_preview', './index.php', 'get_preview&title='+encodeURIComponent(title)+'&content='+encodeURIComponent(content)+'&user='+user+'&syntaxengine='+syntaxengine);
      }
      </script>\n");
  $cts->add($frm);
  $cts->puts("<div id=\"msg_preview\"></div>");
	exit();
  } // fin formulaire création sujet 

    // On créer le forum
  if ( isset($_REQUEST["type"]) && $_REQUEST["type"]="frm" )
  {

    $site->error_forbidden("forum","group");
 
    $values_forum = array(null=>"(Aucun)");
    $sql = "SELECT id_forum, titre_forum FROM frm_forum ORDER BY titre_forum";
    $req = new requete($site->db, $sql);
    while( list($value,$name) = $req->get_row()){
      $values_forum[$value] = $name;
    }

    $cts->add_title(2,"Nouveau forum");
    $frm = new form("newfrm","?page=new&type=frm",true);
    $frm->add_hidden("action","new");
    $frm->add_text_field("titre","Titre","");
    $frm->add_text_field("ordre","Numéro d'ordre",0);
    $frm->add_select_field("id_forum_parent",
                         "Forum parent",
                         $values_forum,
                         "","", true);
    $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$news->id_asso,true);
    $frm->add_checkbox ( "categorie", "Catégorie", false );
    $frm->add_text_area("description","Description","");
    $frm->add_rights_field($forum,false,$forum->is_admin($site->user));
    $frm->add_submit("newfrm","Ajouter");
    $cts->add($frm);
  
    $site->add_contents($cts);
    $site->end_page();  
    exit();
  } // fin formulaire création forum


}else{

$cts->add_title(2,"Administration du forum");
$lst = new itemlist();
$lst->add("<a href=\"liste.php?action=new\">Ajouter un sous forum</a>");
$lst->add("<a href=\"liste_ban.php\">Afficher les utilisateurs bannis du forum</a>");
$lst->add("<a href=\"liste.php\">Afficher les forums</a>");

$cts->add($lst);

$req = new requete($site->db,
    "SELECT f1.titre_forum as titre_forum, ".
    "f1.id_forum as id_forum ,".
    "f1.description_forum as description_forum, ".
    "f1.categorie_forum as categorie_forum, ".
    "f2.titre_forum as titre_forum_parent, ".
    "`asso`.nom_asso as nom_asso ".
    "FROM `frm_forum` f1,`frm_forum` f2, `asso`  ".
    "WHERE f1.id_forum_parent=f2.id_forum ".
    "AND `asso`.id_asso = f1.id_asso ".
    "ORDER BY f1.id_forum ");
		
  $tbl = new sqltable(
    "listforum", 
    "Liste des forums",
    $req,
    "index.php", 
    "id_forum", 
    array("titre_forum"=>"Titre","description_forum"=>"Description","categorie_forum"=>"Catégorie","titre_forum_parent"=>"Forum parent","nom_asso"=>"Association concernée"), 
    array("edit"=>"Editer","delete"=>"Supprimer"),
    array(),
    array()
    );

$cts->add($tbl,true);

$site->add_contents($cts);
$site->end_page();
exit();

}

$site->add_contents($cts);
$site->end_page();

?>
