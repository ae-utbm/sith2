<?php
/* 
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *        
 * Copyright 2006,2007
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
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");

$file = new dfile($site->db, $site->dbrw);
$folder = new dfolder($site->db, $site->dbrw);

if ( isset($_REQUEST["id_file"]))
{
  $file->load_by_id($_REQUEST["id_file"]);
  if ( $file->is_valid() )
  {
    if ( !$file->is_right($site->user,DROIT_LECTURE) )
      $site->error_forbidden(CMS_PREFIX."fichiers","group",$file->id_groupe);
      
    $folder->load_by_id($file->id_folder);
  }
  else
  {
    Header("Location: index.php?name=404");
    exit();
  }
}

if ( $_REQUEST["action"] == "download" && $file->is_valid() )
{
  if ( $_REQUEST["download"] == "thumb" )
  {
    $filename = $file->get_thumb_filename();
    if ( ! file_exists($filename) )
    {
      header("Content-type: image/png");
      $filename = $topdir."/images/icons/128/".$file->get_icon_name();
    }
    else
      header("Content-type: image/jpeg");
    readfile($filename);
    exit();
  }
  elseif ( $_REQUEST["download"] == "preview" )
  {
    $filename = $file->get_screensize_filename();
    if ( ! file_exists($filename) )
    {
      header("Content-type: image/png");
      $filename = $topdir."/images/icons/128/".$file->get_icon_name();
    }
    else
      header("Content-type: image/jpeg");
    readfile($filename);
    exit();
  }
  $file->increment_download();
  $filename = $file->get_real_filename();
  header("Content-type: ".$file->mime_type);
  header("Content-Disposition: filename=".$file->nom_fichier);
  if ( file_exists($filename) )
  {
      header("Content-Length: ".filesize($filename));
      readfile($filename);
  }
  exit();
}

if ( isset($_REQUEST["id_folder"]) && !( isset($_REQUEST["id_file"]) && $file->is_valid() ) )
  $folder->load_by_id($_REQUEST["id_folder"]);
  
if ( !$folder->is_valid() )
{
  $file->id = null;
  $folder->load_root_by_asso($site->asso->id);
  if ( !$folder->is_valid() ) // Le dossier racine n'existe pas... on va le creer :)
  {
    $folder->id_groupe_admin = $site->asso->id + 20000; // asso-bureau
    $folder->id_groupe = $site->asso->id + 30000; // asso-membres
    $folder->droits_acces = 0xDDD;
    $folder->id_utilisateur = null;
    $folder->add_folder ( "Fichiers", null, null, $site->asso->id );
  }
}

if ( !$folder->is_right($site->user,DROIT_LECTURE) )
  $site->error_forbidden(CMS_PREFIX."fichiers","group",$folder->id_groupe);
  
if ( $_REQUEST["action"] == "cut" )
{
	
	if ( $file->is_valid() && $file->is_right($site->user,DROIT_ECRITURE) )
	{
		$_SESSION["d_clipboard"]["I".$file->id] = $file->id;
    		$file->id=null;
	}
	elseif ( $folder->id_folder_parent && $folder->is_right($site->user,DROIT_ECRITURE) ) // la racine ne peut pas être coupée
	{
		$_SESSION["d_clipboard"]["O".$folder->id] = $folder->id;
		$folder->load_by_id($folder->id_folder_parent);
	}
}
elseif ( $file->is_valid() && $_REQUEST["action"] == "delete" )
{
  if ( $file->is_right($site->user,DROIT_ECRITURE) )
  {
    $file->delete_file();
    $file->id=null;
  }
}
elseif ( $folder->is_valid() && $_REQUEST["action"] == "delete" )
{
  if ( $folder->is_right($site->user,DROIT_ECRITURE) )
    if ( $site->is_sure ( "","Suppression du dossier ".$folder->nom,"folder".$folder->id, 1 ) )
    {
      $folder->delete_folder();
      $folder->load_by_id($folder->id_folder_parent);
      if ( !$folder->is_valid() )
        $folder->load_by_id(1);
      if ( !$folder->is_right($site->user,DROIT_LECTURE) )
        $site->error_forbidden(CMS_PREFIX."fichiers","group",$folder->id_groupe);
    }
}

if ( $file->is_valid() )
  $path = classlink($folder)." / ".classlink($file);
else
  $path = classlink($folder);

$pfolder = new dfolder($site->db);
$pfolder->load_by_id($folder->id_folder_parent);

while ( $pfolder->is_valid() )
{
  $id_asso = $pfolder->id_asso;
  $path = classlink($pfolder)." / $path";
  $pfolder->load_by_id($pfolder->id_folder_parent);
}

/** @toto vérifier à partir de cette ligne */

if ( $_REQUEST["action"] == "addfolder" && $folder->is_right($site->user,DROIT_AJOUTCAT) )
{
  $file->id=null;
  if ( !$_REQUEST["nom"] )
  {
    $_REQUEST["page"] = "newfolder";
    $ErreurAjout="Veuillez préciser un nom pour le dossier";
  }
  else
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_REQUEST["id_asso"]);

    $nfolder = new dfolder($site->db,$site->dbrw);
    $nfolder->herit($folder);
    $nfolder->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);

    $nfolder->add_folder ( $_REQUEST["nom"], $folder->id, $_REQUEST["description"], $asso->id );

    $folder = $nfolder;
    $path .= " / ".classlink($folder);
  }
}
elseif ( $_REQUEST["action"] == "addfile" && $folder->is_right($site->user,DROIT_AJOUTITEM) )
{
  if ( !$_REQUEST["nom"] )
  {
    $_REQUEST["page"] = "newfolder";
    $ErreurAjout="Veuillez préciser un nom pour le fichier.";
  }
  elseif( !is_uploaded_file($_FILES['file']['tmp_name']) || ($_FILES['file']['error'] != UPLOAD_ERR_OK ) )
  {
    $_REQUEST["page"] = "newfolder";
    $ErreurAjout="Erreur lors du transfert.";
  }
  else
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_REQUEST["id_asso"]);
    $file->herit($folder);
    $file->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $file->add_file ( $_FILES["file"], $_REQUEST["nom"], $folder->id, $_REQUEST["description"],$asso->id );
  }
}




if ( $file->is_valid() )
{
  if ( $_REQUEST["action"] == "save" && $file->is_right($site->user,DROIT_ECRITURE) )
  {
    if ( $_REQUEST["nom"] )
    {
      $asso = new asso($site->db);
      $asso->load_by_id($_REQUEST["id_asso"]);
      $file->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
      $file->update_file( $_REQUEST["nom"], $_REQUEST["description"],$asso->id );
    }
  }
  elseif ( $_REQUEST["action"] == "edit" && $file->is_right($site->user,DROIT_ECRITURE) )
  {
    $site->start_page(CMS_PREFIX."fichiers","Fichiers");
    $cts = new contents($path." / Editer");

    $frm = new form("savefile","d.php?id_file=".$file->id);
    $frm->add_hidden("action","save");
    $frm->add_text_field("nom","Nom",$file->titre,true);
    $frm->add_text_area("description","Description",$file->description);
    $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$file->id_asso,true);
    $frm->add_rights_field($file,false,$file->is_admin($site->user),"files");
    $frm->add_submit("valid","Enregistrer");

    $cts->add($frm);


    $site->add_contents($cts);
    $site->end_page();
    exit();
  }

  $user = new utilisateur($site->db);
  $user->load_by_id($file->id_utilisateur);

  $site->start_page(CMS_PREFIX."fichiers","Fichiers");

  $cts = new contents($path);

  $actions = array();

  if ( ! file_exists($file->get_screensize_filename()) )
    $actions[] = "<a href=\"d.php?id_file=".$file->id."&amp;action=download&amp;download=preview\">Voir</a>";
  
  $cts->add(new image("Miniature","d.php?id_file=".$file->id."&amp;action=download&amp;download=thumb","imgright"));
  $cts->add( new wikicontents ("Description",$file->description),true );

  $actions[] = "<a href=\"d.php?id_file=".$file->id."&amp;action=download\">T&eacute;l&eacute;charger</a>";

  if ( $file->is_right($site->user,DROIT_ECRITURE) )
  {
    $actions[] = "<a href=\"d.php?id_file=".$file->id."&amp;action=edit\">Editer</a>";
    $actions[] = "<a href=\"d.php?id_file=".$file->id."&amp;action=delete\">Supprimer</a>";
  }

  $cts->add(new itemlist(false,false,$actions));

  $cts->add(new itemlist("Informations",false,
      array(
        "Taille: ".$file->taille." Octets",
        "Type: ".$file->mime_type,
        "Date d'ajout: ".date("d/m/Y",$file->date_ajout),
        "Nom r&eacute;el: ".$file->nom_fichier,
        "Nombre de t&eacute;l&eacute;chargements: ".$file->nb_telechargement,
        "Propos&eacute; par : ". classlink($user)
      )),true);



  $site->add_contents($cts);
  $site->end_page();
  exit();
}
if ( $_REQUEST["action"] == "save" && $folder->is_right($site->user,DROIT_ECRITURE) )
{
  if ( $_REQUEST["nom"] )
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_REQUEST["id_asso"]);
    $folder->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $folder->update_folder ( $_REQUEST["nom"],$_REQUEST["description"], $asso->id );
  }

}
elseif ( $_REQUEST["action"] == "edit" && $folder->is_right($site->user,DROIT_ECRITURE) )
{
  $site->start_page(CMS_PREFIX."fichiers","Fichiers");
  $cts = new contents($path." / Editer");
  $frm = new form("savefolder","d.php?id_folder=".$folder->id);
  $frm->add_hidden("action","save");
  $frm->add_text_field("nom","Nom",$folder->titre,true);
  $frm->add_text_area("description","Description",$folder->description);
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$folder->id_asso,true);
  $frm->add_rights_field($folder,true,$folder->is_admin($site->user),"files");
  $frm->add_submit("valid","Enregistrer");
  $cts->add($frm);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $_REQUEST["page"] == "newfolder" && $folder->is_right($site->user,DROIT_AJOUTCAT) )
{
  $site->start_page(CMS_PREFIX."fichiers","Fichiers");
  $cts = new contents($path." / Ajouter un dossier");

  $frm = new form("addfolder","d.php?id_folder=".$folder->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfolder");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_area("description","Description","");
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$site->asso->id,true);
  $frm->add_rights_field($folder,true,$folder->is_admin($site->user),"files");
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $_REQUEST["page"] == "newfile" && $folder->is_right($site->user,DROIT_AJOUTITEM) )
{
  $site->start_page(CMS_PREFIX."fichiers","Fichiers");
  $cts = new contents($path." / Ajouter un fichier");

  $frm = new form("addfile","d.php?id_folder=".$folder->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfile");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_file_field("file","Fichier",true);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_area("description","Description","");
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$site->asso->id,true);
  $frm->add_rights_field($folder,false,$folder->is_admin($site->user),"files");
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $folder->is_right($site->user,DROIT_ECRITURE) && $_REQUEST["action"] == "paste" )
{
	$inffile = new dfile($site->db,$site->dbrw);
	$inffolder = new dfolder($site->db,$site->dbrw);
	
	foreach( $_SESSION["d_clipboard"] as $aid => $id )
	{
		if ( $aid{0} == 'I' )
		{
			$inffile->load_by_id($id);
			$inffile->move_to($folder->id);
		}
		else
		{
			$inffolder->load_by_id($id);
			$inffolder->move_to($folder->id);			
		}
	}
	
	unset($_SESSION["d_clipboard"]);
}

require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/cts/gallery.inc.php");

$site->add_css("css/d.css");


$site->start_page(CMS_PREFIX."fichiers","Fichiers");

if ( isset($_SESSION["d_clipboard"]) )
{
	$inffile = new dfile($site->db);
	$inffolder = new dfolder($site->db);
	
	$cts = new contents("Presse papier");
	
	if ( $folder->is_right($site->user,DROIT_ECRITURE) )
	$cts->add_paragraph("<a href=\"d.php?id_folder=".$folder->id."&amp;action=paste\">Deplacer ici</a>");
	
	$lst = new itemlist("Contenu");

	foreach( $_SESSION["d_clipboard"] as $aid => $id )
	{
		if ( $aid{0} == 'I' )
		{
			$inffile->load_by_id($id);
			$lst->add(classlink($inffile));
		}
		else
		{
			$inffolder->load_by_id($id);
			$lst->add(classlink($inffolder));
		}
	}
	
	$cts->add($lst,true);
	
	$site->add_contents($cts);
}


$cts = new contents($path);

if ( $folder->is_right($site->user,DROIT_ECRITURE) )
  $cts->set_toolbox(new toolbox(array(
"d.php?id_folder=".$folder->id."&action=edit"=>"Editer",
"d.php?id_folder=".$folder->id."&action=delete"=>"Supprimer",
"d.php?id_folder=".$folder->id."&action=cut"=>"Couper",
)));


if ( $folder->description)
  $cts->add( new wikicontents ("Description",$folder->description),true );

$gal = new gallery("Fichiers et dossiers","aedrive",false,"d.php?id_folder_parent=".$folder->id,array("download"=>"Télécharger","info"=>"Details","edit"=>"Editer","delete"=>"Supprimer"));

$sub1 = $folder->get_folders ( $site->user);
$fd = new dfolder($site->db);
while ( $row = $sub1->get_row() )
{
  $acts = false;
  $fd->_load($row);
  if ( $fd->is_right($site->user,DROIT_ECRITURE) )
    $acts = array("edit","delete","cut");

  $desc  =$fd->description;
  if ( strlen($desc) > 72 )
    $desc = substr($desc,0,72)."...";

  $gal->add_item ( "<img src=\"images/icons/128/folder.png\" alt=\"dossier\" />","<a href=\"d.php?id_folder=".$fd->id."\" class=\"itmttl\">".$fd->titre."</a><br/><span class=\"itmdsc\">".$desc."</span>", "id_folder=".$fd->id, $acts, "folder" );

}

$sub2 = $folder->get_files ( $site->user);
$fd = new dfile($site->db);
while ( $row = $sub2->get_row() )
{
  $acts = array("download","info");
  $fd->_load($row);
  if ( $fd->is_right($site->user,DROIT_ECRITURE) )
  {
    $acts[] ="edit";
    $acts[] ="delete";
    $acts[] ="cut";   
  }

  $img = "d.php?id_file=".$fd->id."&amp;action=download&amp;download=thumb";

  $desc  =$fd->description;
  if ( strlen($desc) > 72 )
    $desc = substr($desc,0,72)."...";

  $gal->add_item ( "<img src=\"$img\" alt=\"fichier\" />","<a href=\"d.php?id_file=".$fd->id."\" class=\"itmttl\">".$fd->titre."</a><br/><span class=\"itmdsc\">".$desc."</span>", "id_file=".$fd->id, $acts, "file" );

}
$cts->add($gal,true);

if ( $folder->is_right($site->user,DROIT_AJOUTCAT) )
  $cts->add_paragraph("<a href=\"d.php?id_folder=".$folder->id."&amp;page=newfolder\">Ajouter un dossier</a>");

if ( $folder->is_right($site->user,DROIT_AJOUTITEM) )
  $cts->add_paragraph("<a href=\"d.php?id_folder=".$folder->id."&amp;page=newfile\">Ajouter un fichier</a>");



$site->add_contents($cts);
$site->end_page();

  
?>
