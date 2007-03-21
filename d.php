<?php

/* Copyright 2006
 *
 * - Maxime Petazzoni < sam at bulix dot org >
 * - Laurent Colnat < laurent dot colnat at utbm dot fr >
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

/**
 * @file Navigateur des dossiers virtuels
 * @see include/entities/files.inc.php
 * @see include/entities/folder.inc.php
 */

$topdir="./";
require_once($topdir."include/site.inc.php");
require_once($topdir."include/assoclub.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");

$site = new site();
$file = new dfile($site->db, $site->dbrw);
$folder = new dfolder($site->db, $site->dbrw);
$asso_folder = new asso($site->db);

//session_write_close(); // on n'a plus besoin de la session, liberons le semaphore...

if ( isset($_REQUEST["id_file"]))
{
  $file->load_by_id($_REQUEST["id_file"]);
  if ( $file->id > 0 )
  {
    if ( !$file->is_right($site->user,DROIT_LECTURE) )
      error_403();
    $folder->load_by_id($file->id_folder);
  }
  else
  {
    Header("Location: " . $topdir . "404.php");
    exit();
  }
}

// "Exception"
if ( $_REQUEST["action"] == "download" && $file->id > 0 )
{
  if ( $_REQUEST["download"] == "thumb" )
  {
    $filename = $file->get_thumb_filename();
    if ( ! file_exists($filename) )
    {
      header("Content-type: image/png");
      $filename = $topdir."images/icons128/".$file->get_icon_name();
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
      $filename = $topdir."images/icons128/".$file->get_icon_name();
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

if ( isset($_REQUEST["id_folder"]) && !( isset($_REQUEST["id_file"]) && $file->id > 0 ) )
  $folder->load_by_id($_REQUEST["id_folder"]);

if ( $folder->id < 1 )
{
  $file->id = -1;
  if ( isset($_REQUEST["id_asso"]) ) // On veut le dossier racine d'une association
  {
    $asso_folder->load_by_id($_REQUEST["id_asso"]);
    if ( $asso_folder->id > 0 ) // L'association existe, chouette
    {
      $folder->load_root_by_asso($asso_folder->id);
      if ( $folder->id < 1 ) // Le dossier racine n'existe pas... on va le creer :)
      {
        $folder->id_groupe_admin = $asso_folder->id + 20000; // asso-bureau
        $folder->id_groupe = $asso_folder->id + 30000; // asso-membres
        $folder->droits_acces = 0xDDD;
        $folder->id_utilisateur = null;
        $folder->add_folder ( "Fichiers", null, null, $asso_folder->id );
      }
    }
    else
      $folder->load_by_id(1);
  }
  else
    $folder->load_by_id(1);
}

if ( !$folder->is_right($site->user,DROIT_LECTURE) )
  error_403();


if ( $_REQUEST["action"] == "cut" )
{
	
	if ( $file->id > 0 && $file->is_right($site->user,DROIT_ECRITURE) )
	{
		$_SESSION["d_clipboard"]["I".$file->id] = $file->id;
    		$file->id=-1;
	}
	elseif ( $folder->id_folder_parent && $folder->is_right($site->user,DROIT_ECRITURE) ) // la racine ne peut pas être coupée
	{
		$_SESSION["d_clipboard"]["O".$folder->id] = $folder->id;
		$folder->load_by_id($folder->id_folder_parent);
	}
}
elseif ( $file->id > 0 && $_REQUEST["action"] == "delete" )
{
  if ( $file->is_right($site->user,DROIT_ECRITURE) )
  {
    $file->delete_file();
    $file->id=-1;
  }
}
elseif ( $folder->id > 0 && $_REQUEST["action"] == "delete" )
{
  if ( $folder->is_right($site->user,DROIT_ECRITURE) )
    if ( $site->is_sure ( "","Suppression du dossier ".$folder->nom,"folder".$folder->id, 1 ) )
    {
      $folder->delete_folder();
      $folder->load_by_id($folder->id_folder_parent);
      if ( $folder->id < 1 )
        $folder->load_by_id(1);
      if ( !$folder->is_right($site->user,DROIT_LECTURE) )
        error_403();
    }
}

if ( $file->id > 0 )
  $path = classlink($folder)." / ".classlink($file);
else
  $path = classlink($folder);

$id_asso = $folder->id_asso;

$pfolder = new dfolder($site->db);
$pfolder->load_by_id($folder->id_folder_parent);

while ( $pfolder->id > 0 )
{
  $id_asso = $pfolder->id_asso;
  $path = classlink($pfolder)." / $path";
  $pfolder->load_by_id($pfolder->id_folder_parent);
}

if ( $id_asso )
{
  $asso_folder->load_by_id($id_asso);
  if ( $asso_folder->id > 0 )
    $path = classlink($asso_folder)." / $path";
}

if ( $_REQUEST["action"] == "addfolder" && $folder->is_right($site->user,DROIT_AJOUTCAT) )
{
  $file->id=-1;
  if ( !$_REQUEST["nom"] )
  {
    $_REQUEST["page"] = "newfolder";
    $ErreurAjout="Veuillez préciser un nom pour le dossier";
  }
  else
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_REQUEST["id_asso"]);

    if ( $asso->id < 1 )
      $asso->id = null;

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

    if ( $asso->id < 1 )
      $asso->id = null;

    $file->herit($folder);
    $file->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $file->add_file ( $_FILES["file"], $_REQUEST["nom"], $folder->id, $_REQUEST["description"],$asso->id );
  }
}




if ( $file->id > 0 )
{
  if ( $_REQUEST["action"] == "save" && $file->is_right($site->user,DROIT_ECRITURE) )
  {
    if ( $_REQUEST["nom"] )
    {
      $asso = new asso($site->db);
      $asso->load_by_id($_REQUEST["id_asso"]);
      if ( $asso->id < 1 )
        $asso->id = null;
      $file->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
      $file->update_file( $_REQUEST["nom"], $_REQUEST["description"],$asso->id );
    }
  }
  elseif ( $_REQUEST["action"] == "edit" && $file->is_right($site->user,DROIT_ECRITURE) )
  {
    $site->start_page("fichiers","Fichiers");
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

  $site->start_page("fichiers","Fichiers");


  if ( $asso_folder->id > 0 )
  {
    $cts = new contents($asso_folder->nom);
    $cts->add(new tabshead($asso_folder->get_tabs($site->user),"files"));
    $cts->add_title(1,$path);
  }
  else
    $cts = new contents($path);

    $actions = array();

  $filename = $file->get_thumb_filename();
  if ( ! file_exists($filename) )
    $filename = $topdir."images/icons128/".$file->get_icon_name();
    else
        $actions[] = "<a href=\"d.php?id_file=".$file->id."&amp;action=download&amp;download=preview\">Voir</a>";

  $cts->add(new image("Miniature",$filename,"imgright"));
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
    if ( $asso->id < 1 )
      $asso->id = null;
    $folder->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $folder->update_folder ( $_REQUEST["nom"],$_REQUEST["description"], $asso->id );
  }

}
elseif ( $_REQUEST["action"] == "edit" && $folder->is_right($site->user,DROIT_ECRITURE) )
{
  $site->start_page("fichiers","Fichiers");
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
  $site->start_page("fichiers","Fichiers");
  $cts = new contents($path." / Ajouter un dossier");

  $frm = new form("addfolder","d.php?id_folder=".$folder->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfolder");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_area("description","Description","");
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",false,true);
  $frm->add_rights_field($folder,true,$folder->is_admin($site->user),"files");
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $_REQUEST["page"] == "newfile" && $folder->is_right($site->user,DROIT_AJOUTITEM) )
{
  $site->start_page("fichiers","Fichiers");
  $cts = new contents($path." / Ajouter un fichier");

  $frm = new form("addfile","d.php?id_folder=".$folder->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfile");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_file_field("file","Fichier",true);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_area("description","Description","");
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",false,true);
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


$site->start_page("fichiers","Fichiers");

if ( $site->user->is_in_group("gestion_ae") && isset($_SESSION["d_clipboard"]) )
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




if ( $asso_folder->id > 0 )
{
  $cts = new contents($asso_folder->nom);
  $cts->add(new tabshead($asso_folder->get_tabs($site->user),"files"));
  $cts->add_title(1,$path);
}
else
  $cts = new contents($path);

if ( $folder->is_right($site->user,DROIT_ECRITURE) )
  $cts->set_toolbox(new toolbox(array(
"d.php?id_folder=".$folder->id."&action=edit"=>"Editer",
"d.php?id_folder=".$folder->id."&action=delete"=>"Supprimer",
"d.php?id_folder=".$folder->id."&action=cut"=>"Couper",
)));

if ( $folder->id == 1 )
{
  $gal = new gallery("Derniers ajouts","aedrive",false,"d.php?id_folder_parent=".$folder->id,array("download"=>"Télécharger","info"=>"Details","edit"=>"Editer","delete"=>"Supprimer"));

  $sql = new requete($site->db,"SELECT * " .
        "FROM d_file " .
        "WHERE " .
        "((" .
          "(" .
            "((droits_acces_file & 0x1) OR " .
            "((droits_acces_file & 0x10) AND id_groupe IN (".$site->user->get_groups_csv().")))" .
          ") " .
          "AND modere_file='1'" .
        ") OR " .
        "(id_groupe_admin IN (".$site->user->get_groups_csv().")) OR " .
        "((droits_acces_file & 0x100) AND id_utilisateur='".$site->user->id."')) " .
        "ORDER BY `date_ajout_file` DESC LIMIT 4");

  $fd = new dfile(null);
  while ( $row = $sql->get_row() )
  {
    $acts = array("download","info");
    $fd->_load($row);
    if ( $fd->is_right($site->user,DROIT_ECRITURE) )
    {
      $acts[] ="edit";
      $acts[] ="delete";
      $acts[] ="cut"; 
    }

    if ( !file_exists($fd->get_thumb_filename()) )
      $img = $topdir."images/icons128/".$fd->get_icon_name();
    else
      $img = "d.php?id_file=".$fd->id."&amp;action=download&amp;download=thumb";

    $desc  =$fd->description;
    if ( strlen($desc) > 72 )
      $desc = substr($desc,0,72);

    $gal->add_item ( "<img src=\"$img\" alt=\"fichier\" />","<a href=\"d.php?id_file=".$fd->id."\" class=\"itmttl\">".$fd->titre."</a><br/><span class=\"itmdsc\">".$desc."</span>", "id_file=".$fd->id, $acts, "file" );

  }
  $cts->add($gal,true);
}



if ( $folder->description)
  $cts->add( new wikicontents ("Description",$folder->description),true );


$gal = new gallery("Fichiers et dossiers","aedrive",false,"d.php?id_folder_parent=".$folder->id,array("download"=>"Télécharger","info"=>"Details","edit"=>"Editer","delete"=>"Supprimer"));

$sub1 = $folder->get_folders ( $site->user);
$fd = new dfolder(null);
while ( $row = $sub1->get_row() )
{
  $acts = false;
  $fd->_load($row);
  if ( $fd->is_right($site->user,DROIT_ECRITURE) )
    $acts = array("edit","delete","cut");

  $desc  =$fd->description;
  if ( strlen($desc) > 72 )
    $desc = substr($desc,0,72)."...";

  $gal->add_item ( "<img src=\"images/icons128/folder.png\" alt=\"dossier\" />","<a href=\"d.php?id_folder=".$fd->id."\" class=\"itmttl\">".$fd->titre."</a><br/><span class=\"itmdsc\">".$desc."</span>", "id_folder=".$fd->id, $acts, "folder" );

}

$sub2 = $folder->get_files ( $site->user);
$fd = new dfile(null);
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

  if ( !file_exists($fd->get_thumb_filename()) )
    $img = $topdir."images/icons128/".$fd->get_icon_name();
  else
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
