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
require_once($topdir."include/cts/sqltable.inc.php");

if ( !$site->is_user_admin() )
{
  exit();
}

if ( !is_null($site->asso->id_parent) )
{
  $GLOBALS['ROLEASSO'][ROLEASSO_PRESIDENT] = "Responsable";
  $GLOBALS['ROLEASSO'][ROLEASSO_VICEPRESIDENT] = "Vice-responsable";
}
else
{
  $GLOBALS['ROLEASSO'][ROLEASSO_PRESIDENT] = "Président";
  $GLOBALS['ROLEASSO'][ROLEASSO_VICEPRESIDENT] = "Vice-président";
}

$req = new requete($site->db, "SELECT nom_page,titre_page FROM `pages` WHERE `nom_page` LIKE '" . mysql_real_escape_string(CMS_PREFIX) . "%' AND `nom_page` NOT LIKE '" . mysql_real_escape_string(CMS_PREFIX) . "boxes:%'");	
$pages = array();
while ( $row = $req->get_row() )
  $pages[substr($row['nom_page'],strlen(CMS_PREFIX))] = $row['titre_page'];
  
if ( !isset($pages["home"]) )
  $pages["home"] = "Accueil";
  
if ( $_REQUEST["action"] == "addonglet" )
{
  if ( $_REQUEST["typepage"] == "article" )
  {
    $lien = "index.php?name=".$_REQUEST["nom_page"];
    $name = $_REQUEST["nom_page"];
    
    $page = new page ($site->db,$site->dbrw);
    $page->load_by_name(CMS_PREFIX.$_REQUEST["nom_page"]);
    $page->save($page->title, $page->texte, CMS_PREFIX.$name );
  }
  elseif ( $_REQUEST["typepage"] == "aedrive" )
  {
    $lien = "d.php";
    $name = "fichiers";
  }
  elseif ( $_REQUEST["typepage"] == "sas2" )
  {
    $lien = "photos.php";
    $name = "sas";
  }
  elseif ( $_REQUEST["typepage"] == "contact" )
  {
    $lien = "contact.php";
    $name = "contact";
  }  
  elseif ( $_REQUEST["typepage"] == "membres" )
  {
    $lien = "membres.php";
    $name = "membres";
  }
  else
  {
    $lien = "index.php";
    $name = "accueil";
  }

  $site->tab_array[] = array(CMS_PREFIX.$name,$lien,$_REQUEST["title"]);
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "addbox" )
{
  if ( empty($site->config["boxes.names"]) )
    $boxes = array();
  else
    $boxes = explode(",",$site->config["boxes.names"]);
  
  if ( $_REQUEST["typebox"] == "custom" )
  {
    $name = $_REQUEST["name"];
    $page = new page ($site->db,$site->dbrw);
    $page->load_by_name(CMS_PREFIX."boxes:".$name);
    if ( !$page->is_valid() )
    {
      $page->id_utilisateur = $site->user->id;
      $page->id_groupe = $site->asso->get_membres_group_id();
      $page->id_groupe_admin = $site->asso->get_bureau_group_id();
      $page->droits_acces = 0x311;      
      $page->add(CMS_PREFIX."boxes:".$name, $_REQUEST["title"], "", CMS_PREFIX."accueil");
    }
    else
      $page->save($_REQUEST["title"], $page->texte, CMS_PREFIX."accueil" );

  }
  else//if ( $_REQUEST["typebox"] == "calendrier" )
    $name = "calendrier";
  
  if ( !in_array($name,$boxes) )
  $boxes[] = $name;
  
  $site->config["boxes.names"] = implode(",",$boxes);
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "setconfig" )
{
  $site->config["membres.upto"] = intval($_REQUEST["membres_upto"]);
  $site->config["membres.allowjoinus"] = isset($_REQUEST["membres_allowjoinus"])?1:0;
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "delete" && isset($_REQUEST["nom_onglet"]) )
{
  if ( $_REQUEST["nom_onglet"] != CMS_PREFIX."accueil" )
  {
  
    foreach ( $site->tab_array as $key => $row )
    {
      if ( $_REQUEST["nom_onglet"] == $row[0] )
        unset($site->tab_array[$key]);
    }
    $site->save_conf();
  }
}
elseif ( $_REQUEST["action"] == "delete" && isset($_REQUEST["box_name"]) )
{
  if ( empty($site->config["boxes.names"]) )
    $boxes = array();
  else
    $boxes = explode(",",$site->config["boxes.names"]);
  
  foreach ( $boxes as $key => $name )
  {
    if ( $_REQUEST["box_name"] == $name )
      unset($boxes[$key]);
  }
  
  $site->config["boxes.names"] = implode(",",$boxes);
  $site->save_conf();
  
}
elseif ( $_REQUEST["action"] == "setboxsections"  )
{
  $sections = array(); 
  
  foreach( $_REQUEST["sections"]  as $nom => $set )
    $sections[]=$nom;  
    
  $site->config["boxes.sections"] = implode(",",$sections);
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "up" && isset($_REQUEST["nom_onglet"]) )
{
  $prevkey=null;
  
  foreach ( $site->tab_array as $key => $row )
  {
    if ( $_REQUEST["nom_onglet"] == $row[0] )
    {
      if ( !is_null($prevkey) )
      {
        $tmp = $site->tab_array[$key];
        $site->tab_array[$key] = $site->tab_array[$prevkey];
        $site->tab_array[$prevkey] = $tmp;
      }
    }
    $prevkey = $key;
  }
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "down" && isset($_REQUEST["nom_onglet"]) )
{
  $prevkey=null;
  foreach ( $site->tab_array as $key => $row )
  {
    if ( $_REQUEST["nom_onglet"] == $row[0] )
      $prevkey = $key;
    elseif ( !is_null($prevkey) )
    {
      $tmp = $site->tab_array[$key];
      $site->tab_array[$key] = $site->tab_array[$prevkey];
      $site->tab_array[$prevkey] = $tmp;
      $prevkey=null;
    }
  }
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "up" && isset($_REQUEST["box_name"]) )
{
  if ( empty($site->config["boxes.names"]) )
    $boxes = array();
  else
    $boxes = explode(",",$site->config["boxes.names"]);

  $prevkey=null;
  
  foreach ( $boxes as $key => $name )
  {
    if ( $_REQUEST["box_name"] == $name )
    {
      if ( !is_null($prevkey) )
      {
        $tmp = $boxes[$key];
        $boxes[$key] = $boxes[$prevkey];
        $boxes[$prevkey] = $tmp;
      }
    }
    $prevkey = $key;
  }
  
  $site->config["boxes.names"] = implode(",",$boxes);
  $site->save_conf();  
}
elseif ( $_REQUEST["action"] == "down" && isset($_REQUEST["box_name"]) )
{
  if ( empty($site->config["boxes.names"]) )
    $boxes = array();
  else
    $boxes = explode(",",$site->config["boxes.names"]);  
  
  $prevkey=null;
  foreach ( $boxes as $key => $name )
  {
    if ( $_REQUEST["box_name"] == $name )
      $prevkey = $key;
    elseif ( !is_null($prevkey) )
    {
      $tmp = $boxes[$key];
      $boxes[$key] = $boxes[$prevkey];
      $boxes[$prevkey] = $tmp;
    }
  }
  
  $site->config["boxes.names"] = implode(",",$boxes);
  $site->save_conf();  
}
elseif ( $_REQUEST["action"] == "edit" )
{
  $page = new page ($site->db,$site->dbrw);
  $page->load_by_name(CMS_PREFIX."boxes:".$_REQUEST["box_name"]);
  if ($page->is_valid() )
  {
    $site->start_page(CMS_PREFIX."config","Edition boite :".$page->titre);
    $frm = new form("editarticle","configurecms.php",true,"POST","Edition : ".$page->nom);
    $frm->add_hidden("action","save");
    $frm->add_hidden("box_name",$_REQUEST["box_name"]);
    $frm->add_text_field("title","Titre",$page->titre,true);
    $frm->add_rights_field($page,false,true,"pages");
    $frm->add_text_area("texte","Contenu",$page->texte,80,20,true);
    $frm->add_submit("save","Enregistrer");
    $site->add_contents($frm);
    $site->add_contents(new wikihelp());
    $site->end_page();
  }
}
elseif ( $_REQUEST["action"] == "save" )
{
  $page = new page ($site->db,$site->dbrw);
  $page->load_by_name(CMS_PREFIX."boxes:".$_REQUEST["box_name"]);
  
  if ($page->is_valid() )
  {
    $page->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $page->save( $_REQUEST['title'], $_REQUEST['texte'], CMS_PREFIX."accueil" );
  }
}

$req = new requete($site->db, "SELECT nom_page,titre_page FROM `pages` WHERE `nom_page` LIKE '" . mysql_real_escape_string(CMS_PREFIX) . "boxes:%'");	
$pages_boxes = array();
while ( $row = $req->get_row() )
  $pages_boxes[substr($row['nom_page'],strlen(CMS_PREFIX))] = $row['titre_page'];
  
$site->start_page ( CMS_PREFIX."config", "Configuration de AECMS" );

$cts = new contents("Configuration de AECMS");

$cts->add_title(2,"Onglets");

$dejafait = array();
$onglets_noms = array();

$liste_onglets = array();
foreach ( $site->tab_array as $row )
{
  if ( $row[0] != CMS_PREFIX."config" )
  {
    $dejafait[substr($row[0],strlen(CMS_PREFIX))] = true;
    
    if ( ereg("^index.php?name=(.*)$",$row[1],$regs) )
      $lien = "Page: ".$pages[$regs[1]];
    elseif ( $row[1] == "photos.php" )
      $lien = "Gallerie photos";
    elseif ( $row[1] == "d.php" )
      $lien = "Espace fichiers";
    elseif ( $row[1] == "membres.php" )
      $lien = "Membres";      
    elseif ( $row[1] == "contact.php" )
      $lien = "Contact";      
    elseif ( $row[1] == "index.php" )
      $lien = "Page: ".$pages["home"];
    else
      $lien = "Lien spécial (non supporté)";
      
    $liste_onglets[] = array("nom_onglet"=>$row[0],"titre_onglet"=>$row[2],"lien_onglet"=>$lien);
    $onglets_noms[$row[0]] = $row[2];
  }
}

$cts->add( new sqltable ( "onglets", "Onglets", $liste_onglets, 
"configurecms.php", "nom_onglet", array("titre_onglet"=>"Titre","lien_onglet"=>"Lien"), 
array("delete"=>"Supprimer","up"=>"Vers le haut","down"=>"Vers le bas"), array() ));

$cts->add_title(3,"Nouvel onglet");

$frm = new form("newonglet","configurecms.php",false,"POST","Nouvel onglet");


$frm->add_hidden("action","addonglet");
$frm->add_text_field("title","Titre","",true);

$sfrm = new form("typepage",null,null,null,"Page (article)");
$sfrm->add_select_field("nom_page","Page",$pages);
$frm->add($sfrm,false,true,true,"article",false,true);

if ( !isset($dejafait["fichiers"]) )
{
  $sfrm = new form("typepage",null,null,null,"Espace fichiers (aedrive)");
  $frm->add($sfrm,false,true,false,"aedrive",false,true);
}
if ( !isset($dejafait["sas"]) )
{
  $sfrm = new form("typepage",null,null,null,"Gallerie photos (sas2)");
  $frm->add($sfrm,false,true,false,"sas2",false,true);
}
if ( !isset($dejafait["contact"]) )
{
  $sfrm = new form("typepage",null,null,null,"Contact");
  $frm->add($sfrm,false,true,false,"contact",false,true);
}
if ( !isset($dejafait["membres"]) )
{
  $sfrm = new form("typepage",null,null,null,"Membres");
  $frm->add($sfrm,false,true,false,"membres",false,true);
}
$frm->add_submit("save","Ajouter");
$cts->add ( $frm );

$cts->add_title(2,"Boites");

// Boxes
if ( empty($site->config["boxes.names"]) )
  $boxes = array();
else
  $boxes = explode(",",$site->config["boxes.names"]);

$boxes_sections = explode(",",$site->config["boxes.sections"]);

$boxes_list = array();
foreach ( $boxes as $name )
{
  if ( $name == "calendrier" )
  {
    $title = "Calendrier";
    $type="Calendrier";
  }
  else
  {
    $title = $pages_boxes["boxes:".$name];
    $type="Personnalisée";
  }
  $boxes_list[] = array("box_name"=>$name,"box_title"=>$title,"box_type"=>$type);
}

$cts->add( new sqltable ( "boxes", "Boites", $boxes_list, 
"configurecms.php", "box_name", array("box_title"=>"Titre","box_type"=>"Type"), 
array("delete"=>"Supprimer","edit"=>"Editer","up"=>"Vers le haut","down"=>"Vers le bas"), array() ));

$cts->add_title(3,"Nouvelle boite");

$frm = new form("newbox","configurecms.php",false,"POST","Nouvelle boite");
$frm->add_hidden("action","addbox");

$sfrm = new form("typebox",null,null,null,"Personnalisée");
$sfrm->add_text_field("name","Code (nom)","",true);
$sfrm->add_text_field("title","Titre","",true);
$frm->add($sfrm,false,true,true,"custom",false,true);

if ( !in_array("calendrier",$boxes) )
{
  $sfrm = new form("typebox",null,null,null,"Calendrier");
  $frm->add($sfrm,false,true,false,"calendrier",false,true);
}
$frm->add_submit("save","Ajouter");
$cts->add ( $frm );

$cts->add_title(3,"Sections où les boites seront affichées");

$frm = new form("setboxsections","configurecms.php",false,"POST","Sections où les boites seront affichées");
$frm->add_hidden("action","setboxsections");

foreach ( $onglets_noms as $nom => $titre )
  $frm->add_checkbox("sections[$nom]","$titre",in_array($nom,$boxes_sections));

$frm->add_submit("save","Enregistrer");
$cts->add ( $frm );

$cts->add_title(2,"Général");

$cts->add_title(3,"Options");


$frm = new form("setconfig","configurecms.php",true,"POST","Options");
$frm->add_hidden("action","setconfig");

$frm->add_select_field("membres_upto","Membres, liste jusqu'au niveau",$GLOBALS['ROLEASSO'], $site->config["membres.upto"]);
$frm->add_checkbox("membres_allowjoinus","Membres, afficher le formulaire \"Rejoignez-nous\"",$site->config["membres.allowjoinus"]);

$frm->add_submit("save","Enregistrer");
$cts->add($frm);

$cts->add_title(3,"Outils");



$cts->add(new itemlist("Outils",false,array(
"<a href=\"index.php?page=new\">Creer une nouvelle page</a>",
"<a href=\"news.php\">Ajouter une nouvelle</a>"

)));




$site->add_contents($cts);

$site->end_page();

?>