<?
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit
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

$topdir = "./";
require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/newsflow.inc.php");
require_once($topdir . "include/news.inc.php");
require_once($topdir . "include/assoclub.inc.php");
$site = new site();

$news = new nouvelle ($site->db, $site->dbrw);
$can_edit = false;

$site->add_box("lastnews",new newslist ( "Deni&egrave;res nouvelles", $site->db ) );
$site->set_side_boxes("left",array("calendrier","lastnews","alerts","connexion"),"news_left");

if ( isset($_REQUEST["id_nouvelle"]) )
{
  $news->load_by_id($_REQUEST["id_nouvelle"]);
  if ( $news->id < 1 )
    {
      header("Location: 404.php");
      exit();
    }

  $asso = new asso($site->db);
  $asso->load_by_id($news->id_asso);

  $can_edit = $site->user->is_in_group("moderateur_site") || ($news->id_utilisateur == $site->user->id);

  if ( $asso->id > 0 )
    $can_edit = $can_edit || $asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);

}

if ( ($_REQUEST["action"] == "adddate") && $can_edit )
{
  if ( $_REQUEST["debut"] && ( $_REQUEST["debut"]  < $_REQUEST["fin"] ) )
    $news->add_date($_REQUEST["debut"],$_REQUEST["fin"]);

}
else    if ( ($_REQUEST["action"] == "delete") && isset($_REQUEST["id_dates_nvl"]) && $can_edit )
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

  if ( $_REQUEST["title"] && $_REQUEST["content"] )
    $news->save_news(
                     $_REQUEST['id_asso'],
                     $_REQUEST['title'],
                     $_REQUEST['resume'],
                     $_REQUEST['content'],
                     false,null,$_REQUEST["type"]);
}

if ( $_REQUEST["page"]  == "edit" && $can_edit )
{
  $site->start_page ("services", $news->titre);
  $cts = new contents("Editer");

  $frm = new form ("editnews","news.php",false,"POST","Edition d'une nouvelle");
  $frm->add_hidden("action","save");
  $frm->add_hidden("id_nouvelle",$news->id);
  $frm->add_info("<b>ATTENTION</b> La nouvelle sera soumise &agrave; nouveau &agrave; mod&eacute;ration");

  $frm->add_select_field ("type",
                          "Type de nouvelle",
                          array(NEWS_TYPE_APPEL => "Appel/concours",
                                NEWS_TYPE_EVENT => utf8_encode("�v�nement ponctuel"),
                                NEWS_TYPE_HEBDO => utf8_encode("S�ance hebdomadaire"),
                                NEWS_TYPE_NOTICE => "Info/resultat")
                          ,$news->type);

  $frm->add_text_field("title", "Titre",$news->titre,true);
  $frm->add_entity_select("id_asso", "Association concern&eacute;e", $site->db, "asso",$news->id_asso,true);
  $frm->add_text_area ("resume","Resume",$news->resume);
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



if ( $news->id > 0 )
{

  $site->start_page ("accueil", $news->titre);

  $cts = new contents();
  $img = "var/img/logos/".$asso->nom_unix.".small.png";
  if ( !file_exists($img) )
    $img = "images/default/news.small.png";

  $cts->add_title(1,$news->titre);
  $cts->add(new image($asso->nom, $img, "newsimg"));
  $cts->add(new wikicontents(false,$news->contenu));

  $req = new requete ( $site->db,"SELECT * FROM nvl_dates WHERE id_nouvelle='".$news->id."' ORDER BY date_debut_eve");

  if ( $req->lines == 1 )
    {
      $row = $req->get_row();
      $cts->add_paragraph("Date : le ".textual_plage_horraire(strtotime($row['date_debut_eve']),strtotime($row['date_fin_eve'])));
    }
  elseif ( $req->lines > 1 )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      while ( $row = $req->get_row() )
        $lst->add("Le ".textual_plage_horraire(strtotime($row['date_debut_eve']),strtotime($row['date_fin_eve'])));
      $cts->add($lst);
    }

  if ( $asso->id > 0 )
    {
      $cts->puts("<div class=\"clearboth\"></div>");
      $cts->add_title(2,"");
      $cts->add_paragraph(classlink($asso));
    }

  if ( $site->user->is_in_group("gestion_ae"))
    {
      $user1 = new utilisateur($site->db);
      $user2 = new utilisateur($site->db);
      $user1->load_by_id($news->id_utilisateur);
      $user2->load_by_id($news->id_utilisateur_moderateur);

      $cts->puts("<div class=\"clearboth\"></div>");
      $cts->add_title(2,"");
      $cts->add_paragraph("Post&eacute; par : ".classlink($user1));
      if ( $user2->id > 0 )
        $cts->add_paragraph("Valid&eacute; par : ".classlink($user2));

    }

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

if ( $site->user->id < 1 )
{
  header("Location: 403.php?reason=session");
  exit();
}

require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");

$file = new dfile($site->db, $site->dbrw);

$site->start_page ("none", "Accueil Nouvelles");

$suitable = false;

if ( isset($_REQUEST["preview"]) || isset($_REQUEST["submit"]) )
{
  if ( isset($_FILES['affiche_file']) && $_FILES['affiche_file']['error'] == 0 )
    {


      $asso = new asso($site->db);
      $asso->load_by_id($_REQUEST["id_asso"]);
      if ( $asso->id < 1 )
        $asso->id = null;

      $folder1= new dfolder     ($site->db, $site->dbrw);
      $folder2= new dfolder     ($site->db, $site->dbrw);

      $folder1->load_root_by_asso($asso->id);
      if ( $folder1->id < 1 ) // L'association n'a pas encore de dossier racine
        {
          $folder1->id_groupe_admin = $asso->id + 20000; // asso-bureau
          $folder1->id_groupe = $asso->id + 30000; // asso-membres
          $folder1->droits_acces = 0xDDD;
          $folder1->id_utilisateur = null;
          $folder1->add_folder ( "Fichiers", null, null, $asso->id );
        }

      if ( $folder1->id > 0 )
        {
          $folder2->load_by_titre($folder1->id,"Affiches");
          if ( $folder2->id < 1 ) // L'association n'a pas encore de dossier "Affiches"
            {
              $folder2->herit($folder1);
              $folder2->add_folder ( "Affiches", $folder1->id, "Affiches utilis�s dans les nouvelles", $asso->id );
            }
        }

      if ( $folder2->id > 0 )
        {
          $file->herit($folder2);
          $file->id_utilisateur = $site->user->id;
          $file->add_file ( $_FILES["affiche_file"], $_REQUEST["title"], $folder2->id, "Affiche de ".$_REQUEST["title"], $asso->id );
        }
      else
        $news_error = "Erreur interne lors de la creation du dossier \"Affiches\".";
    }
  elseif ( $_FILES['affiche_file']['error'] != UPLOAD_ERR_NO_FILE )
    {
      $news_error = "Erreur lors du transfert de l'affiche.";
    }
  elseif ( isset($_REQUEST["id_file"]) )
    {
      $file->load_by_id($_REQUEST["id_file"]);
    }

  if ( $file->id > 0 )
    {
      $_REQUEST["content"] = str_replace("((@affiche|","((dfile://".$file->id."/preview|",$_REQUEST["content"]);
      $_REQUEST["content"] = str_replace("|@affiche]","|dfile://".$file->id."]",$_REQUEST["content"]);
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
                  $_REQUEST['id_asso'],
                  $_REQUEST['title'],
                  $_REQUEST['resume'],
                  $_REQUEST['content'],
                  $_REQUEST['type']);

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
  $cts_success = new contents("Ajout de nouvelles",
                              "<p>Votre nouvelle a &eacute;t&eacute; ajout&eacute;e ".
                              "avec succ&egrave;s</p>");
}



if (isset($cts_success))
  $site->add_contents($cts_success);

if ( $suitable && isset($_REQUEST["preview"]) )
{
  $asso = new asso($site->db);
  $asso->load_by_id($_REQUEST["id_asso"]);

  $cts = new contents($_REQUEST["title"]);

  $img = "var/img/logos/".$asso->nom_unix.".small.png";
  if ( !file_exists($img) )
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

  if ( $asso->id > 0 )
    {
      $cts->add_title(2,"");
      $cts->add_paragraph(classlink($asso));
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

$frm->add_text_field("title", "Titre de la nouvelle",$_REQUEST["title"],true);
$frm->add_entity_select("id_asso", "Association concern&eacute;e", $site->db, "asso",$_REQUEST["id_asso"],true);
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
