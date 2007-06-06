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

$news = new nouvelle($site->db,$site->dbrw);

if ( isset($_REQUEST["id_nouvelle"]) )
{
  $news->load_by_id($_REQUEST["id_nouvelle"]);
  
  if ( !$news->is_valid() || ($news->id_asso != $site->asso->id) )
  {
    header("Location: index.php");
    exit();
  }

  $can_edit = $site->user->is_in_group("moderateur_site") || ($news->id_utilisateur == $site->user->id) || $site->asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);

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
    $_REQUEST["content"] = str_replace("((@affiche|","((dfile://".$file->id."/preview|",$_REQUEST["content"]);
    $_REQUEST["content"] = str_replace("|@affiche]","|dfile://".$file->id."]",$_REQUEST["content"]);
    
    if ( !ereg("\(\(dfile\:\/\/([0-9]*)\/preview\|(.*)\)\)",$_REQUEST["content"]) )
    {
      $_REQUEST["content"] .= "\n\n~((dfile://".$file->id."/preview|Affiche))\n\n[Version HD de l'affiche|dfile://".$file->id."]";
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

  $news->add_news($site->user->id,
                  $site->asso->id,
                  $_REQUEST['title'],
                  $_REQUEST['resume'],
                  $_REQUEST['content'],
                  $_REQUEST['type'],
                  !isset($_REQUEST['non_asso_seule']));

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
  $cts = new contents ("Accueil nouvelles");
  $cts->add (new wikicontents ("aide",$site->get_textbox ("news_help")));
  $site->add_contents ($cts);
}




$frm = new form ("addnews_frm","news.php",false,"POST","Proposition d'une nouvelle");

if ( $news_error )
  $frm->error($news_error);

$type = $_REQUEST["type"];
if ( !$type )
  $type=1;

$sfrm = new form("type",null,null,null,"Nouvelle sur un concours, un appel &agrave; canditure : longue dur&eacute;e");
$sfrm->add_datetime_field("t3_debut","Date et heure de d&eacute;but",time());
$sfrm->add_datetime_field("t3_fin","Date et heure de fin",GetRequestParam('t3_fin',-1));
$frm->add($sfrm,false,true, $type==3 ,3 ,false,true);

$sfrm = new form("type",null,null,null,"Nouvelle sur un &eacute;v&eacute;nement ponctuel associ&eacute; &agrave; une date");
$sfrm->add_datetime_field("t1_debut","Date et heure de d&eacute;but",GetRequestParam('t1_debut',-1));
$sfrm->add_datetime_field("t1_fin","Date et heure de fin",GetRequestParam('t1_fin',-1));
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
  $ssfrm->add_datetime_field("t2_debut","Date et heure de d&eacute;but",GetRequestParam('t2_debut',-1));
  $ssfrm->add_datetime_field("t2_fin","Date et heure de fin",GetRequestParam('t2_fin',-1));
  $ssfrm->add_datetime_field("t2_until","... jusqu'au",GetRequestParam('t2_until',-1));
  $sfrm->add($ssfrm,false,true, false , 2 ,false,true);
}
else
{
  $sfrm->add_datetime_field("t2_debut","Date et heure de d&eacute;but",GetRequestParam('t2_debut',-1));
  $sfrm->add_datetime_field("t2_fin","Date et heure de fin",GetRequestParam('t2_fin',-1));
  $sfrm->add_datetime_field("t2_until","... jusqu'au",GetRequestParam('t2_until',-1));
}
$frm->add($sfrm,false,true, $type==2 ,2 ,false,true);

$sfrm = new form("type",null,null,null,"Information, resultat d'&eacute;lection - sans date");
$frm->add($sfrm,false,true, $type==0 ,0 ,false,true);

$frm->add_checkbox ( "non_asso_seule", "Publier aussi sur le site de l'AE (sera soumis à modération)", true);
$frm->add_text_field("title", "Titre de la nouvelle",$_REQUEST["title"],true);
$frm->add_text_area ("resume","Resum&eacute;",$_REQUEST["resume"]);
$frm->add_text_area ("content", "Contenu",$_REQUEST["content"],80,10,true);

if ( $file->id > 0 )
{
  $frm->add_info("Affiche enregistr&eacute;e : ".classlink($file).".");
  $frm->add_hidden("id_file",$file->id);
}
else
$frm->add_file_field("affiche_file","Affiche");

$frm->add_info("Pour ins&eacute;rer l'affiche, utilisez la syntaxe suivante: ((@affiche|Affiche)).");
$frm->add_info("Pour ins&eacute;rer un lien, utilisez la syntaxe suivante: [Version HD de l'affiche|@affiche].");

$frm->add_submit ("preview","Pr&eacute;visualiser");

if ( $suitable )
  $frm->add_submit ("submit","Proposer la nouvelle");

$site->add_contents ($frm);

$site->add_contents (new wikihelp());

$site->end_page ();


?>