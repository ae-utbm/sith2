<?
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit
 * - Mathieu Briand < briandmathieu at hyprua dot org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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

$topdir = "./";
require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

require_once($topdir . "include/entities/affiche.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/page.inc.php");

$site = new site();

$affiche = new affiche($site->db, $site->dbrw);

$can_edit = false;

if ( isset($_REQUEST["id_affiche"]) )
{
  $affiche->load_by_id($_REQUEST["id_affiche"]);
  if ( $affiche->id < 1 )
  {
    $site->error_not_found();
    exit();
  }

  $asso = new asso($site->db);
  $asso->load_by_id($affiche->id_asso);

  $can_edit = $site->user->is_in_group("moderateur_site") || ($affiche->id_utilisateur == $site->user->id);

  if ( $asso->id > 0 )
    $can_edit = $can_edit || $asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);

}

if ( ($_REQUEST["action"] == "delete") && $can_edit )
{
  if ( $site->is_sure("accueil","Supprimer l'affiche ?","delaff".$affiche->id) )
    {
      $affiche->delete();
      $cts_success = new contents("Suppression d'affiche",
                                  "<p>Votre affiche a &eacute;t&eacute; supprim&eacute;e ".
                                  "avec succ&egrave;s</p>");
    }
}
elseif ( ($_REQUEST["action"] == "save") && $can_edit )
{
  $modere = false;
  $lieu->load_by_id($_REQUEST["id_lieu"]);


  if ( $_REQUEST["title"] && $_REQUEST["content"] )
  {
    $affiche->save_affiche(
                     $_REQUEST['id_asso'],
                     $_REQUEST['title'],
                     $_REQUEST['debut'],
                     $_REQUEST['fin']);
  }
}

if ( $_REQUEST["page"]  == "edit" && $can_edit )
{
  $site->start_page ("services", "Affiche : ".$affiche->titre);

  $frm = new form ("editaffiche","affiche.php",false,"POST","Edition d'une affiche");
  $frm->add_hidden("action","save");
  $frm->add_hidden("id_affiche",$affiche->id);
  $frm->add_info("<b>ATTENTION</b> L'affiche sera soumise &agrave; nouveau &agrave; mod&eacute;ration");

  $frm->add_text_field("title", "Titre",$affiche->titre,true);
  $frm->add_entity_select("id_asso", "Association concern&eacute;e", $site->db, "asso",$affiche->id_asso,true);

  $frm->add_datetime_field("debut","Date et heure de d&eacute;but");
  $frm->add_datetime_field("fin","Date et heure de fin");

  $frm->add_submit("valid","Enregistrer");

  $site->add_contents ($frm);

  $site->end_page ();
  exit();
}
if ( $_REQUEST["page"]  == "list" )
{
  $cts = $affiche->get_html_list($site->user);
  $site->add_contents ($cts);
  $site->end_page ();
  exit();
}


if ( $affiche->id > 0 )
{

  $site->start_page ("accueil", "Affiche : ".$affiche->titre);
  $cts = $affiche->get_contents();

  $cts->puts("<div class=\"clearboth\"></div>");

  if ( $site->user->is_in_group("gestion_ae"))
  {
    $user1 = new utilisateur($site->db);
    $user2 = new utilisateur($site->db);
    $user1->load_by_id($affiche->id_utilisateur);
    $user2->load_by_id($affiche->id_utilisateur_moderateur);

    $cts->add_title(2,"");
    $cts->add_paragraph("Post&eacute; par : ".$user1->get_html_link());

    if ( $user2->is_valid() )
      $cts->add_paragraph("Valid&eacute; par : ".$user2->get_html_link());
  }

  $site->add_contents ($cts);

  if ( $can_edit )
  {
    $cts = new contents("Edition");
    $cts->add_paragraph("<a href=\"affiche.php?page=edit&amp;id_affiche=".$affiche->id."\">Modifier</a> (l'affiche sera de nouveau soumise &agrave; mod&eacute;ration)");
    $cts->add_paragraph("<a href=\"affiche.php?action=delete&amp;id_affiche=".$affiche->id."\">Supprimer</a>");
    $site->add_contents($cts);
  }

  $site->end_page ();
  exit();
}

if ( !$site->user->is_valid() )
{
  header("Location: 403.php?reason=session");
  exit();
}

require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");

$file = new dfile($site->db, $site->dbrw);

$site->start_page ("none", "Accueil affiches");

$suitable = false;

if ( isset($_REQUEST["submit"]) )
{
  if ( isset($_FILES['affiche_file']) && $_FILES['affiche_file']['error'] == 0 )
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_REQUEST["id_asso"]);
    $folder= new dfolder ($site->db, $site->dbrw);
    $folder->create_or_load ( "Affiches", $asso->id );
    if ( $folder->is_valid() )
    {
      $file->herit($folder);
      $file->id_utilisateur = $site->user->id;
      $file->add_file ( $_FILES["affiche_file"], $_REQUEST["title"], $folder->id, "Affiche : ".$_REQUEST["title"], $asso->id );
    }
    else
      $affiche_error = "Erreur interne lors de la creation du dossier \"Affiches\".";
  }
  elseif ( $_FILES['affiche_file']['error'] != UPLOAD_ERR_NO_FILE )
    $affiche_error = "Erreur lors du transfert de l'affiche.";

  elseif ( isset($_REQUEST["id_file"]) )
    $file->load_by_id($_REQUEST["id_file"]);

  if ( !$_REQUEST["title"] || !$_REQUEST["content"] )
    $affiche_error = "Le champ titre n'a pas &eacute;t&eacute; remplis";
  elseif ( $_REQUEST["debut"] >= $_REQUEST["fin"] )
    $affiche_error = "Date de debut et date de fin erron&eacute;s";
  elseif ( !$file->is_valid() )
    $affiche_error = "Fichier invalide";
  else
    $suitable = true;
}


if ( $suitable && isset($_REQUEST["submit"]) )
{
  $affiche->add_affiche($site->user->id,
                  $_REQUEST['id_asso'],
                  $_REQUEST['title'],
                  $file->id,
                  $_REQUEST['debut'],
                  $_REQUEST['fin']);

  unset($_REQUEST["debut"]);
  unset($_REQUEST["fin"]);
  unset($_REQUEST["id_asso"]);
  unset($_REQUEST["title"]);
  $site->add_contents(new contents("Ajout d'affiches",
                              "<p>Votre affiche a &eacute;t&eacute; ajout&eacute;e ".
                              "avec succ&egrave;s</p>"));
}

$frm = new form ("editaffiche","affiche.php",false,"POST","Proposition d'une affiche");

if ( $affiche_error )
  $frm->error($affiche_error);

$frm->add_text_field("title", "Titre de l'affiche",$_REQUEST["title"],true);

$frm->add_datetime_field("debut","Date et heure de d&eacute;but");
$frm->add_datetime_field("fin","Date et heure de fin");

$frm->add_entity_select("id_asso", "Association concern&eacute;e", $site->db, "asso",$_REQUEST["id_asso"],true);

if ( $file->id > 0 )
{
  $frm->add_info("Affiche enregistr&eacute;e : ".$file->get_html_link().".");
  $frm->add_hidden("id_file",$file->id);
}
else
$frm->add_file_field("affiche_file","Affiche");

$frm->add_submit ("submit","Proposer l'affiche");

$site->add_contents ($frm);

$site->end_page ();

?>
