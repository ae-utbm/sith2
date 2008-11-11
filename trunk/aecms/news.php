<?php
/*
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *
 * Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

require_once("include/site.inc.php");
require_once($topdir."include/entities/news.inc.php");
require_once($topdir."include/entities/lieu.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/entities/page.inc.php");

$news = new nouvelle($site->db,$site->dbrw);
$lieu = new lieu($site->db);

if ( isset($_REQUEST["id_nouvelle"]) )
{
  $news->load_by_id($_REQUEST["id_nouvelle"]);

  if ( !$news->is_valid() || $news->id_asso != $site->asso->id )
  {
    header("Location: index.php");
    exit();
  }

  $can_edit = $site->user->is_in_group("moderateur_site") || ($news->id_utilisateur == $site->user->id) || $site->asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);

}

if ( ($_REQUEST["action"] == "adddate") && $can_edit )
{
  if ( $_REQUEST["debut"] && ( $_REQUEST["debut"]  < $_REQUEST["fin"] ) )
    $news->add_date($_REQUEST["debut"],$_REQUEST["fin"]);

}
elseif ( ($_REQUEST["action"] == "delete") && isset($_REQUEST["id_dates_nvl"]) && $can_edit )
{
  $news->delete_date($_REQUEST["id_dates_nvl"]);
}
elseif ( ($_REQUEST["action"] == "delete") && !isset($_REQUEST["id_dates_nvl"]) && $can_edit )
{
  if ( $site->is_sure("accueil","Supprimer la nouvelle ?","delnws".$news->id) )
    {
      $news->delete();
      $cts_success = new contents("Suppression de nouvelles",
                                  "<p>Votre nouvelle a &eacute;t&eacute; supprim&eacute;e ".
                                  "avec succ&egrave;s</p>");
    }
}
elseif ( ($_REQUEST["action"] == "save") && $can_edit )
{
  $modere = false;
  $lieu->load_by_id($_REQUEST["id_lieu"]);


  if ( $_REQUEST["title"] && $_REQUEST["content"] )
  {
    $news->save_news($site->asso->id,
                     $_REQUEST['title'],
                     $_REQUEST['resume'],
                     $_REQUEST['content'],
                     false,
                     null,
                     $_REQUEST["type"],
                     $lieu->id,
                     !isset($_REQUEST['non_asso_seule']) ? NEWS_CANAL_AECMS : NEWS_CANAL_SITE);
    $news->set_tags($_REQUEST["tags"]);
  }
}

if ( $_REQUEST["page"]  == "edit" && $can_edit )
{
  $site->start_page (CMS_PREFIX."accueil", $news->titre);
  $cts = new contents("Editer");

  $frm = new form ("editnews","news.php",false,"POST","Edition d'une nouvelle");
  $frm->add_hidden("action","save");
  $frm->add_hidden("id_nouvelle",$news->id);
  $frm->add_info("<b>ATTENTION</b> La nouvelle sera soumise &agrave; nouveau &agrave; mod&eacute;ration");

  $frm->add_select_field ("type",
                          "Type de nouvelle",
                          array(NEWS_TYPE_APPEL => "Appel/concours",
                                NEWS_TYPE_EVENT => "Événement ponctuel",
                                NEWS_TYPE_HEBDO => "Séance hebdomadaire",
                                NEWS_TYPE_NOTICE => "Info/resultat")
                          ,$news->type);

  $frm->add_text_field("title", "Titre",$news->titre,true);
  $frm->add_checkbox ( "non_asso_seule", "Publier aussi sur le site de l'AE (sera soumis à modération)", $news->id_canal==NEWS_CANAL_SITE);
  $frm->add_entity_select("id_lieu", "Lieu", $site->db, "lieu",$news->id_lieu,true);
  $frm->add_text_field("tags", "Tags",$news->get_tags());
  $frm->add_text_area ("resume","Resume",$news->resume);
  $frm->add_dokuwiki_toolbar('content');
  $frm->add_text_area ("content", "Contenu",$news->contenu,80,10,true);

  $frm->add_submit("valid","Enregistrer");

  $site->add_contents ($frm);


  $req = new requete ( $site->db,"SELECT * FROM nvl_dates WHERE id_nouvelle='".$news->id."' ORDER BY date_debut_eve");

  $cts = new contents("Dates");

  if ( $req->lines > 0 )
    {
      $tbl = new sqltable(
                          "listsalles",
                          "Liste actuelle", $req, "news.php?page=edit&id_nouvelle=".$news->id,
                          "id_dates_nvl",
                          array("date_debut_eve"=>"De","date_fin_eve"=>"Au"),
                          array("delete"=>"Supprimer"), array(),array()
                          );
      $cts->add($tbl,true);
    }

  $frm = new form("selectdateresa","news.php?page=edit&id_nouvelle=".$news->id,false,"POST","Associer une date");
  $frm->add_hidden("action","adddate");
  $frm->add_datetime_field("debut","Date et heure de d&eacute;but");
  $frm->add_datetime_field("fin","Date et heure de fin");
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);

  $site->add_contents($cts);

  $site->add_contents (new wikihelp());
  $site->end_page ();
  exit();
}

if ( $news->is_valid() )
{



  $site->start_page (CMS_PREFIX."accueil", $news->titre);

  $cts = $news->get_contents();

  $site->add_contents ($cts);

  if ( $can_edit )
  {
    $cts = new contents("Edition");
    $cts->add_paragraph("<a href=\"news.php?page=edit&amp;id_nouvelle=".$news->id."\">Modifier</a> (la nouvelle sera de nouveau soumise &agrave; mod&eacute;ration)");
    $cts->add_paragraph("<a href=\"news.php?action=delete&amp;id_nouvelle=".$news->id."\">Supprimer</a>");
    $site->add_contents($cts);
  }

  $site->end_page ();
  exit();
}

if ( !$site->user->is_valid() || !$site->is_user_admin() )
{
  header("Location: index.php");
  exit();
}


require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");

$file = new dfile($site->db, $site->dbrw);

$site->start_page ("none", "Ajouter une nouvelle");

$suitable = false;

if ( isset($_REQUEST["preview"]) || isset($_REQUEST["submit"]) )
{
  if ( isset($_FILES['affiche_file']) && $_FILES['affiche_file']['error'] == 0 )
  {
    $folder= new dfolder ($site->db, $site->dbrw);
    $folder->create_or_load ( "Affiches", $site->asso->id );
    if ( $folder->is_valid() )
    {
      $file->herit($folder);
      $file->id_utilisateur = $site->user->id;
      $file->add_file ( $_FILES["affiche_file"], $_REQUEST["title"], $folder->id, "Affiche de ".$_REQUEST["title"], $site->asso->id );
    }
    else
      $news_error = "Erreur interne lors de la creation du dossier \"Affiches\".";
  }
  elseif ( $_FILES['affiche_file']['error'] != UPLOAD_ERR_NO_FILE )
    $news_error = "Erreur lors du transfert de l'affiche.";

  elseif ( isset($_REQUEST["id_file"]) )
    $file->load_by_id($_REQUEST["id_file"]);

  if ( $file->is_valid() )
  {
    $_REQUEST["content"] = str_replace("{{@affiche|","{{dfile://".$file->id."/preview|",$_REQUEST["content"]);
    $_REQUEST["content"] = str_replace("[[@affiche|","[[dfile://".$file->id."]",$_REQUEST["content"]);

    if ( !ereg("\{\{dfile\:\/\/([0-9]*)\/preview\|(.*)\}\}",$_REQUEST["content"]) )
    {
      $_REQUEST["content"] .= "\n\n{{dfile://".$file->id."/preview|Affiche}}\n\n[[dfile://".$file->id."|Version HD de l'affiche]]";
    }
  }

  if ( !$_REQUEST["title"] || !$_REQUEST["content"] )
    $news_error = "Un ou plusieurs champs obligatoires n'ont pas &eacute;t&eacute; remplis";

  elseif ( $_REQUEST["type"] == 3 &&
           (!$_REQUEST["t3_debut"] || !$_REQUEST["t3_fin"]) )
    $news_error = "Un ou plusieurs champs obligatoires n'ont pas &eacute;t&eacute; remplis";

  elseif ( $_REQUEST["type"] == 1 &&
           (!$_REQUEST["t1_debut"] || !$_REQUEST["t1_fin"]) )
    $news_error = "Un ou plusieurs champs obligatoires n'ont pas &eacute;t&eacute; remplis";

  elseif ( $_REQUEST["type"] == 2 &&
           (!$_REQUEST["t2_debut"] || !$_REQUEST["t2_fin"] || !$_REQUEST["t2_until"]) )
    $news_error = "Un ou plusieurs champs obligatoires n'ont pas &eacute;t&eacute; remplis";

  elseif ( $_REQUEST["type"] == 3 && ( $_REQUEST["t3_debut"]  >= $_REQUEST["t3_fin"] ) )
    $news_error = "Date de debut et date de fin erron&eacute;s";
  elseif ( $_REQUEST["type"] == 1 && ( $_REQUEST["t1_debut"]  >= $_REQUEST["t1_fin"] ) )
    $news_error = "Date de debut et date de fin erron&eacute;s";
  elseif ( $_REQUEST["type"] == 2 && ( $_REQUEST["t2_debut"]  >= $_REQUEST["t2_fin"] ) )
    $news_error = "Date de debut et date de fin erron&eacute;s";
  elseif ( $_REQUEST["type"] == 2 && ( $_REQUEST["t2_fin"] >= $_REQUEST["t2_until"] ) )
    $news_error = "Dates invalides";
  elseif ( $_REQUEST["type"] == 2 && $_REQUEST["seldates"] != 1 )
    {
      $h = intval(date("H",$_REQUEST["t2_debut"]));
      for($debut=$_REQUEST["t2_debut"];$debut<$_REQUEST["t2_until"];$debut+=60*60*24*7)
        {
          $debut += ($h-intval(date("H",$debut)))*(60*60);
          $fin = $debut+($_REQUEST["t2_fin"]-$_REQUEST["t2_debut"]);
          $_REQUEST["t2_dates"]["$debut:$fin"] = true;
        }
    }
  else
    $suitable = true;


}


if ( $suitable && isset($_REQUEST["submit"]) )
{
  $lieu->load_by_id($_REQUEST["id_lieu"]);

  $news->add_news($site->user->id,
                  $site->asso->id,
                  $_REQUEST['title'],
                  $_REQUEST['resume'],
                  $_REQUEST['content'],
                  $_REQUEST['type'],
                  $lieu->id,
                  !isset($_REQUEST['non_asso_seule']) ? NEWS_CANAL_AECMS : NEWS_CANAL_SITE );

  $news->set_tags($_REQUEST["tags"]);


  if ( !isset($_REQUEST['non_asso_seule']) ) // Auto-modération si affiché seulement dans le CMS
    $news->validate($site->user->id);

  if ( $_REQUEST["type"] == 3  )
    $news->add_date($_REQUEST["t3_debut"],$_REQUEST["t3_fin"]);
  elseif ( $_REQUEST["type"] == 1  )
    $news->add_date($_REQUEST["t1_debut"],$_REQUEST["t1_fin"]);
  elseif ( $_REQUEST["type"] == 2 )
    {
      foreach ( $_REQUEST["t2_dates"] as $seq => $on )
        {
          list($debut,$fin)=explode(":",$seq);
          $news->add_date($debut,$fin);
        }
    }
  unset($_REQUEST["dates"]);
  unset($_REQUEST["debut"]);
  unset($_REQUEST["fin"]);
  unset($_REQUEST["id_asso"]);
  unset($_REQUEST["title"]);
  unset($_REQUEST["resume"]);
  unset($_REQUEST["content"]);
  unset($_REQUEST["type"]);
  $site->add_contents(new contents("Ajout de nouvelles",
                              "<p>Votre nouvelle a &eacute;t&eacute; ajout&eacute;e ".
                              "avec succ&egrave;s</p>"));
}


if ( $suitable && isset($_REQUEST["preview"]) )
{


  $cts = new contents($_REQUEST["title"]);


  $img = "images/default/news.small.png";

  $cts->add(new image($asso->nom, $img, "newsimg"));
  $cts->add(new wikicontents(false,$_REQUEST["content"]));

  if ( isset($_REQUEST["dates"]) )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      foreach ( $_REQUEST["dates"] as $seq => $on )
        {
          list($debut,$fin)=explode(":",$seq);
          $lst->add("Le ".textual_plage_horraire($debut,$fin));
        }
      $cts->add($lst);
    }
  elseif ( $_REQUEST["debut"] && $_REQUEST["fin"] )
    {
      $cts->add_paragraph("Date : le ".textual_plage_horraire($_REQUEST["debut"],$_REQUEST["fin"]));
    }


  $site->add_contents ($cts);

}
elseif ( !isset($_REQUEST["preview"]) )
{
  $page = new page ($site->db);
  $page->load_by_pagename("info:news");
  if ( $page->is_valid() )
    $site->add_contents($page->get_contents());
}




$frm = new form ("addnews_frm","news.php",false,"POST","Proposition d'une nouvelle");

if ( $news_error )
  $frm->error($news_error);

if ( isset($_REQUEST["type"]) )
  $type = $_REQUEST["type"];
else
  $type=1;

$sfrm = new form("type",null,null,null,"Nouvelle sur un concours, un appel &agrave; canditure : longue dur&eacute;e");
$sfrm->add_datetime_field("t3_debut","Date et heure de d&eacute;but",time());
$sfrm->add_datetime_field("t3_fin","Date et heure de fin",$_REQUEST['t3_fin']);
$frm->add($sfrm,false,true, $type==3 ,3 ,false,true);

$sfrm = new form("type",null,null,null,"Nouvelle sur un &eacute;v&eacute;nement ponctuel associ&eacute; &agrave; une date");
$sfrm->add_datetime_field("t1_debut","Date et heure de d&eacute;but",$_REQUEST['t1_debut']);
$sfrm->add_datetime_field("t1_fin","Date et heure de fin",$_REQUEST['t1_fin']);
$frm->add($sfrm,false,true, $type==1 ,1 ,false,true);

$sfrm = new form("type",null,null,null,"Nouvelle sur une s&eacute;ance ou une r&eacute;union hebdomadaire");
if ( isset($_REQUEST["t2_dates"]) )
{
  $ssfrm = new form("seldates",null,null,null,"Veuillez selectionner les dates r&eacute;elles");
  foreach ( $_REQUEST["t2_dates"] as $seq => $on )
    {
      list($debut,$fin)=explode(":",$seq);
      $ssfrm->add_checkbox("t2_dates|$debut:$fin","Le ".textual_plage_horraire($debut,$fin),true);
    }
  $sfrm->add($ssfrm,false,true, true , 1 ,false,true);

  $ssfrm = new form("seldates",null,null,null,"ou changer la p&eacute;riode");
  $ssfrm->add_datetime_field("t2_debut","Date et heure de d&eacute;but",$_REQUEST['t2_debut']);
  $ssfrm->add_datetime_field("t2_fin","Date et heure de fin",$_REQUEST['t2_fin']);
  $ssfrm->add_datetime_field("t2_until","... jusqu'au",$_REQUEST['t2_until']);
  $sfrm->add($ssfrm,false,true, false , 2 ,false,true);
}
else
{
  $sfrm->add_datetime_field("t2_debut","Date et heure de d&eacute;but",$_REQUEST['t2_debut']);
  $sfrm->add_datetime_field("t2_fin","Date et heure de fin",$_REQUEST['t2_fin']);
  $sfrm->add_datetime_field("t2_until","... jusqu'au",$_REQUEST['t2_until']);
}
$frm->add($sfrm,false,true, $type==2 ,2 ,false,true);

$sfrm = new form("type",null,null,null,"Information, resultat d'&eacute;lection - sans date");
$frm->add($sfrm,false,true, $type==0 ,0 ,false,true);

$frm->add_text_field("title", "Titre de la nouvelle",$_REQUEST["title"],true);
$frm->add_checkbox ( "non_asso_seule", "Publier aussi sur le site de l'AE (sera soumis à modération)", true);
$frm->add_entity_select("id_lieu", "Lieu", $site->db, "lieu",false,true);
$frm->add_text_field("tags", "Tags",$_REQUEST["tags"]);
$frm->add_text_area ("resume","Resum&eacute;",$_REQUEST["resume"]);
$frm->add_text_area ("content", "Contenu",$_REQUEST["content"],80,10,true);

if ( $file->id > 0 )
{
  $frm->add_info("Affiche enregistr&eacute;e : ".$file->get_html_link().".");
  $frm->add_hidden("id_file",$file->id);
}
else
$frm->add_file_field("affiche_file","Affiche");

$frm->add_info("L'affiche sera automatiquement ajoutée en bas de la news.");

$frm->add_submit ("preview","Pr&eacute;visualiser");

if ( $suitable )
  $frm->add_submit ("submit","Proposer la nouvelle");

$site->add_contents ($frm);

$site->add_contents (new wikihelp());

$site->end_page ();

?>
