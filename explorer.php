<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
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
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir."include/cts/gallery.inc.php");
$site = new site();
$site->add_css("css/explorer.css");
$site->add_js("js/explorer.js");


$file = new dfile($site->db, $site->dbrw);
$folder = new dfolder($site->db, $site->dbrw);

if ( isset($_REQUEST["id_folder"]) && !( isset($_REQUEST["id_file"]) && $file->is_valid() ) )
  $folder->load_by_id($_REQUEST["id_folder"]);

if ( !$folder->is_valid() )
{
  $file->id = null;
  if ( isset($_REQUEST["id_asso"]) ) // On veut le dossier racine d'une association
  {
    $asso_folder->load_by_id($_REQUEST["id_asso"]);
    if ( $asso_folder->is_valid() ) // L'association existe, chouette
    {
      $folder->load_root_by_asso($asso_folder->id);
      if ( !$folder->is_valid() ) // Le dossier racine n'existe pas... on va le creer :)
      {
        $folder->id_groupe_admin = $asso_folder->get_bureau_group_id(); // asso-bureau
        $folder->id_groupe = $asso_folder->get_membres_group_id(); // asso-membres
        $folder->droits_acces = 0xDDD;
        $folder->id_utilisateur = null;
        $folder->add_folder ( $section, null, null, $asso_folder->id );
      }
    }
    else
      $folder->load_by_id(1);
  }
  else
    $folder->load_by_id(1);
}

function explore_folders ( &$user, $path )
{
  $folder = array_shift($path);
  
  $req = $folder->get_folders($user,"id_folder, nom_fichier_folder, titre_folder");
  
  $buffer="";
  
  while ( $row = $req->get_row() )
  {
    $buffer .= "<li>"; 
    $buffer .= "<a href=\"#\" onclick=\"explore('".$row["id_folder"]."'); return false;\">";
    $buffer .= "<img src=\"".$wwwtopdir."images/icons/16/".
         $GLOBALS["entitiescatalog"]["folder"][2]."\" alt=\"\" />";
    $buffer .= htmlspecialchars($row["titre_folder"]);
    $buffer .= "</a>";
    $buffer .= "</li>"; 
    
    $buffer .= "<ul id=\"folder_".$row["id_folder"]."\">";
    if ( !empty($path) && $path[0]->id == $row["id_folder"] )
      $buffer .= explore_folders($user,$path);
    $buffer .= "</ul>";
  }
  return $buffer;
}


if ( $_REQUEST["get"] == "folderchilds" )
{
  header("Content-Type: text/html; charset=utf-8");
  
  echo explore_folders($site->user,array($folder));
  
  exit();  
}

$fcts = new contents($folder->nom);

$fcts->add_title(2,"Fichiers");


$gal = new gallery(false,"explorerfiles");

$req = $folder->get_files ( $site->user);
$fd = new dfile($site->db);
while ( $row = $req->get_row() )
{
  $fd->_load($row);
  if ( !file_exists($fd->get_thumb_filename()) )
    $img = $topdir."images/icons/128/".$fd->get_icon_name();
  else
    $img = "d.php?id_file=".$fd->id."&amp;action=download&amp;download=thumb";

  $desc  =$fd->description;
  if ( strlen($desc) > 72 )
    $desc = substr($desc,0,72)."...";

  $gal->add_item ( "<img src=\"$img\" alt=\"fichier\" />","<a href=\"#\" class=\"itmttl\">".$fd->titre."</a><br/><span class=\"itmdsc\">".$desc."</span>" );
}
$fcts->add($gal);

if ( $folder->is_right($site->user,DROIT_AJOUTITEM) )
{
  $fcts->add_title(2,"Nouveau fichier");
  
  $frm = new form("addfile","explorer.php");
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfile");
  $frm->add_hidden("id_folder",$folder->id);
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_file_field("file","Fichier",true);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_field("tags","Tags (séparateur: virgule)","");
  $frm->add_text_area("description","Description","");
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",false,true);
  $frm->add_rights_field($folder,false,$folder->is_admin($site->user),"files");
  $frm->add_submit("valid","Ajouter");
  $fcts->add($frm);
}

if ( $_REQUEST["get"] == "foldercontents" )
{
  header("Content-Type: text/html; charset=utf-8");
  echo $fcts->html_render();
  exit();
}

$path = array(&$folder);

$pfolder = new dfolder($site->db);
$pfolder->load_by_id($folder->id_folder_parent);

while ( $pfolder->is_valid() )
{
  array_unshift ($path,$pfolder);
  
  $pfolder = new dfolder($site->db);
  $pfolder->load_by_id($pfolder->id_folder_parent);
}

$root = new dfolder($site->db);
$root_folders = array();
$req = $root->get_folders($site->user,"id_folder, nom_fichier_folder");
while ( list($id,$name) = $req->get_row() )
  $root_folders[$id]=$name;

$site->start_page("na","Explorer");
$cts = new contents();

$sub = new contents();
$frm = new form("chspace","explorer.php");
$frm->add_select_field("id_folder","Espace",$root_folders,$path[0]->id);
$frm->add_submit("ok","OK");
$cts->add( $frm, false, true, "spaces" );

$sub = new contents();
$sub->buffer = explore_folders($site->user,$path);
$cts->add( $sub, false, true, "folders" );

$cts->add( $fcts, false, true, "foldercontents" );

$site->add_contents($cts);
$site->popup_end_page();

?>