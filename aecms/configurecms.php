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

$req = new requete($site->db, "SELECT nom_page,titre_page FROM `pages` WHERE `nom_page` LIKE '" . mysql_real_escape_string(CMS_PREFIX) . "%'");	
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
elseif ( $_REQUEST["action"] == "setconfig" )
{
  print_r($_REQUEST);
  $site->config["membres.upto"] = intval($_REQUEST["membres.upto"]);
  $site->config["membres.allowjoinus"] = isset($_REQUEST["membres.allowjoinus"])?1:0;
  $site->save_conf();
}
elseif ( $_REQUEST["action"] == "delete" )
{
  foreach ( $site->tab_array as $key => $row )
  {
    if ( $_REQUEST["nom_onglet"] == $row[0] )
      unset($site->tab_array[$key]);
  }
  $site->save_conf();
}
  
  
$site->start_page ( CMS_PREFIX."config", "Configuration de AECMS" );

$cts = new contents("Configuration de AECMS");

$cts->add_title(2,"Onglets");


$liste_onglets = array();
foreach ( $site->tab_array as $row )
{
  if ( $row[0] != CMS_PREFIX."config" )
  {
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
  }
}

$cts->add( new sqltable ( "onglets", "Onglets", $liste_onglets, 
"configurecms.php", "nom_onglet", array("titre_onglet"=>"Titre","lien_onglet"=>"Lien"), 
array("delete"=>"Supprimer","edit"=>"Editer"), array() ));

$frm = new form("newonglet","configurecms.php",true,"POST","Nouvel onglet");


$frm->add_hidden("action","addonglet");
$frm->add_text_field("title","Titre","",true);

$sfrm = new form("typepage",null,null,null,"Page (article)");
$sfrm->add_select_field("nom_page","Page",$pages);
$frm->add($sfrm,false,true,true,"article",false,true);

$sfrm = new form("typepage",null,null,null,"Espace fichiers (aedrive)");
$frm->add($sfrm,false,true,false,"aedrive",false,true);

$sfrm = new form("typepage",null,null,null,"Gallerie photos (sas2)");
$frm->add($sfrm,false,true,false,"sas2",false,true);

$sfrm = new form("typepage",null,null,null,"Contact");
$frm->add($sfrm,false,true,false,"contact",false,true);

$sfrm = new form("typepage",null,null,null,"Membres");
$frm->add($sfrm,false,true,false,"membres",false,true);

$frm->add_submit("save","Ajouter");
$cts->add($frm,true);

$frm = new form("setconfig","configurecms.php",true,"POST","Configuration");
$frm->add_hidden("action","setconfig");

$frm->add_select_field("membres.upto","Membres, liste jusqu'au niveau",$GLOBALS['ROLEASSO'], $site->config["membres.upto"]);
$frm->add_checkbox("membres.allowjoinus","Membres, afficher le formulaire \"Rejoignez-nous\"",$site->config["membres.allowjoinus"]);

$frm->add_submit("save","Enregistrer");
$cts->add($frm,true);




$cts->add(new itemlist("Outils",false,array(
"<a href=\"index.php?page=new\">Creer une nouvelle page</a>",
"<a href=\"news.php\">Ajouter une nouvelle</a>"

)),true);




$site->add_contents($cts);

$site->end_page();

?>