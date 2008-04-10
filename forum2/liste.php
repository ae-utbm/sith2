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
if( $_REQUEST["id_forum"] && !is_null($_REQUEST["id_forum"]) ){
  $forum->load_by_id( $_REQUEST["id_forum"] );
}
if( $_REQUEST["id_sujet"] && !is_null($_REQUEST["id_sujet"]) ){
  $sujet->load_by_id( $_REQUEST["id_sujet"] );
}



/* nouveau forum */
if( $_REQUEST["action"]=="new")
{

  $values_forum = array(null=>"(Aucun)");
  $sql = "SELECT id_forum, titre_forum FROM frm_forum ORDER BY titre_forum";
  $req = new requete($site->db, $sql);
  while( list($value,$name) = $req->get_row()){
    $values_forum[$value] = $name;
  }

  $cts->add_title(2,"Nouveau forum");

  $frm = new form("nvforum","liste.php",false,"POST","Nouveau forum ");
  $frm->add_text_field("titre_forum", "Titre","");
  $frm->add_text_area("description_forum", "Description","");
  $frm->add_select_field("categorie_forum",
                         "Categorie",
                         array( null => "Aucune",
                               "0" => "0",
                               "1" => "1"),
                         null, "", true);

  $frm->add_select_field("id_forum_parent",
                         "Forum parent",
                         $values_forum,
                         $forum->id_forum_parent,"", true);

  $frm->add_entity_select("id_asso",
                          "Association concern&eacute;e",
                          $site->db,
                          "asso", $forum->id_asso, true);
  $frm->add_submit("nvforum","Ajouter");
  $cts->add($frm);


/* modification d'un sujet */
}elseif($_REQUEST["action"]=="edit" && isset($_REQUEST["id_sujet"]))
{

  $values_forum = array();
  $sql = "SELECT id_forum, titre_forum FROM frm_forum ORDER BY titre_forum";
  $req = new requete($site->db, $sql);
  while( list($value,$name) = $req->get_row()){
    $values_forum[$value] = $name;
  }


  $cts->add_title(2,"Edition d'un sujet");

  $frm = new form("editsujet","liste.php",true,"POST","Edition sujet ");
  $frm->add_hidden("id_sujet",$sujet->id);
  $frm->add_text_field("titre_sujet", "Titre",$sujet->titre);
  $frm->add_select_field("id_forum",
                         "Forum concerné",
                         $values_forum,
                         $sujet->id_forum,"", true);
  $frm->add_entity_smartselect("id_utilisateur","Utilisateur modérateur", new utilisateur($site->db));

/* update d'un sujet */
}elseif(isset($_REQUEST["editsujet"]) && 
        isset($_REQUEST["id_sujet"]) &&
        isset($_REQUEST["titre_sujet"]) &&
        isset($_REQUEST["id_forum"]) &&
        isset($_REQUEST["id_utilisateur"]) &&
        !is_null($sujet->id) )
{
	$sujet->id_forum = $_REQUEST["id_forum"];

  $sujet->update($_REQUEST["titre_sujet"],$sujet->soustitre,$sujet->type,$sujet->icon,$sujet->date_fin_annonce,$sujet->id_nouvelle,$sujet->id_catph,$sujet->id_sondage);

/* modification d'un forum */
}elseif($_REQUEST["action"]=="edit" && isset($_REQUEST["id_forum"]))
{

  $values_forum = array(null=>"(Aucun)");
  $sql = "SELECT id_forum, titre_forum FROM frm_forum ORDER BY titre_forum";
  $req = new requete($site->db, $sql);
  while( list($value,$name) = $req->get_row()){
    $values_forum[$value] = $name;
  }


  $cts->add_title(2,"Edition du forum");

  $frm = new form("editforum","liste.php",true,"POST","Edition forum ");
  $frm->add_hidden("id_forum",$forum->id);
  $frm->add_text_field("titre_forum", "Titre",$forum->titre);
  $frm->add_text_area("description_forum", "Description",$forum->description);
  $frm->add_select_field("categorie_forum",
                         "Categorie",
                         array(null => "Aucune",
                               "0" => "0",
                               "1" => "1"),
                         $forum->categorie, "", true);

  $frm->add_select_field("id_forum_parent",
                         "Forum parent",
                         $values_forum,
                         $forum->id_forum_parent,"", true);

  $frm->add_entity_select("id_asso",
                          "Association concern&eacute;e",
                          $site->db,
                          "asso", $forum->id_asso, true);


  $frm->add_submit("editforum","Enregistrer");
  $cts->add($frm);
/* update d'un forum */
}elseif(isset($_REQUEST["editforum"]) && 
        isset($_REQUEST["id_forum"]) &&
        isset($_REQUEST["titre_forum"]) &&
        isset($_REQUEST["description_forum"]) &&
        isset($_REQUEST["id_forum_parent"]) &&
        isset($_REQUEST["id_asso"]) &&
        isset($_REQUEST["categorie_forum"]) &&
        !is_null($forum->id) )
{

  $forum->update($_REQUEST["titre_forum"],$_REQUEST["description_forum"],$_REQUEST["categorie_forum"],$_REQUEST["id_forum_parent"],$_REQUEST["id_asso"],$forum->ordre);

/* suppresion d'un forum */
}elseif(!is_null($forum->id) && $_REQUEST["action"]=="delete")
{
  $cts->add_title(2,"Suppression du forum");
  $cts->add_paragraph("Alerte : la suppression du forum n'est pas autoris&eacute;.");
  $cts->add_paragraph("Veuillez supprimer tous les liens en rapport avec lui (sujet, sous forum, etc.)");


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
    "liste.php", 
    "id_forum", 
    array("titre_forum"=>"Titre","description_forum"=>"Description","categorie_forum"=>"Catégorie","titre_forum_parent"=>"Forum parent","nom_asso"=>"Association concernée"), 
    array("edit"=>"Editer","delete"=>"Supprimer"),
    array(),
    array()
    );

$cts->add($tbl,true);

}


$site->add_contents($cts);
$site->end_page();

?>
