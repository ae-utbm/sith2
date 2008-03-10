<?php
/* Copyright 2006
 * - Laurent COLNAT < laurent DOT colnat AT utbm DOT FR >
 *
 * Ce fichier fait partie du site de l'Association des Ãtudiants de
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
$topdir = "./../";
include($topdir. "include/site.inc.php");

$site = new site ();
if ( !$site->user->is_valid() )
	error_403();

if (!$site->user->utbm && !$site->user->ae )
	error_403("reserved");

if ( !$site->user->is_in_group("matmatronch") )
	error_404();

$site->start_page("up_mmt","Envoi d'une photo mat'matronch");

$page = $_REQUEST['page'];

if ( $_REQUEST["action"] == "setphotosmmt")
{
	if ( is_uploaded_file($_FILES['mmtfile']['tmp_name'])  )
	{
		$src = $_FILES['mmtfile']['tmp_name'];
		$dest = "../var/img/matmatronch/".$_POST['id'].".jpg";
		exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 $dest");
		$success = 1;
		$page = "default";
	}
	/* Ici, si l'utilisateur n'a pas de photo d'identité à son actif, on ne lui en met pas une automatiquement 
	  * car on est pas sur de la qualite de la photo matmatronch */
}

if ( $_REQUEST["action"] == "setphotosident")
{
	if ( is_uploaded_file($_FILES['idtfile']['tmp_name'])  )
	{
		$src = $_FILES['idtfile']['tmp_name'];
		$dest = "../var/img/matmatronch/".$_POST['id'].".identity.jpg";
		exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 $dest");
		$success = 2;
		$page = "default";
	}
	/* Si l'utilisateur n'a pas de photo mat'matronch à son actif, on lui en met une */
	if ( !file_exists($topdir."var/img/matmatronch/".$user->id.".jpg") )
	{
		$src = $_FILES['idtfile']['tmp_name'];
		$dest = "../var/img/matmatronch/".$_POST['id'].".jpg";
		exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 $dest");
		$success = 4;
		$page = "default";
	}
}

if ($page == "upload_photos")
{
	if ($_REQUEST['open'])
		$open = $_REQUEST['open'];
	else
		$open = "idtbx";
	$user = new utilisateur($site->db,$site->dbrw);

	if ($_POST["id_utilisateur"])
		$user->load_by_id($_POST["id_utilisateur"]);
	else if ($_POST["id"])
		$user->load_by_id($_POST["id"]);
	else if ($_GET['id'])
		$user->load_by_id($_GET['id']);
		
	if (! $user->is_valid() )
	{
		header("Location: " . $topdir . "404.php");	
		exit();	
	}

	if ($user->id == $site->user->id)
		$can_edit = 0;
	else
		$can_edit = 1;
	
	if (!$user->utbm )
	{
		$warning_cts = new contents("ATTENTION");
		$warning_cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>"Retour")));
		$warning_cts->add_paragraph(utf8_encode("<font color=\"red\"><b>Il est impossible de modifier la photo d'un étudiant non UTBM !</b></font><br/><br/><font color=\"green\"> L'opération doit être effectuée à partir de la session de l'utilisateur concerné !</font>"));
		$site->add_contents($warning_cts);
	}
	else if (!$can_edit)
	{
		$warning_cts = new contents("ATTENTION");
		$warning_cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>"Retour")));
		$warning_cts->add_paragraph(utf8_encode("<font color=\"red\"><b>Il est impossible de modifier votre propre photo d'identité.</b></font><br/><br/><font color=\"green\"> L'opération doit être effectuée par l'intermédiaire d'une autre personne de l'équipe matmatronch !</font>"));
		$site->add_contents($warning_cts);
	}
	else
	{
	$cts = new contents("Upload de la photo de ".$user->nom." ".$user->prenom);

	$cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>"Retour")));

	$frm_identite = new form("idt_photo",null,null,null,"Changer sa photo d'identit&eacute;e (carte AE)");
	$frm_identite->add_hidden("action","setphotosident");
	$frm_identite->add_hidden("id",$user->id);
	$frm_identite->add_hidden("nom",$user->nom);
	$frm_identite->add_hidden("prenom",$user->prenom);

	if ( file_exists($topdir."var/img/matmatronch/".$user->id.".identity.jpg") )
	{
		$frm_identite->puts("Photo actuelle : <br/>");
		$frm_identite->add_info("<img src=\"".$topdir."var/img/matmatronch/".$user->id.".identity.jpg\" alt=\"\" /><br/><br/>");
	}
	else
		$frm_identite->add_info("Aucune photo d'identit&eacute; actuellement en ligne pour ".$user->nom. " " .$user->prenom);

	$frm_identite->add_file_field ( "idtfile", "Nouvelle photo :" );

	$frm_identite->add_submit("save","Envoyer");
	
	$cts->add($frm_identite,true, false, "ident_photo_bx", false, true, $open=="idtbx",false);

	$frm_mmt = new form("mmt_photo",$_SERVER['SCRIPT_NAME'],true,"POST","Changer sa photo mat'matronch");
	$frm_mmt->add_hidden("action","setphotosmmt");
	$frm_mmt->add_hidden("id",$user->id);
	$frm_mmt->add_hidden("nom",$user->nom);
	$frm_mmt->add_hidden("prenom",$user->prenom);
	
	if ( file_exists($topdir."var/img/matmatronch/".$user->id.".jpg") )
	{
		$frm_mmt->puts("Photo actuelle : <br/>");
		$frm_mmt->add_info("<img src=\"".$topdir."var/img/matmatronch/".$user->id.".jpg\" alt=\"\" /><br/><br/>");
	}
	else
		$frm_mmt->add_info("Aucune photo d'mat'matronch actuellement en ligne pour ".$user->nom. " " .$user->prenom);

	$frm_mmt->add_file_field ( "mmtfile", "Nouvelle photo : " );
	$frm_mmt->add_submit("save","Envoyer");
	
	$cts->add($frm_mmt,true, false, "mmt_photo_bx", false, true, $open=="mmtbx",false);
	
	$site->add_contents($cts);
	}
}
else
{

	if ($success == 1)
	{
		// Réussite de l'upload photo mat'matronch
			$congratulation = new contents("Upload Photo mat'matronch");
			$congratulation->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']."?page=upload_photos&open=idtbx&id=".$_REQUEST['id']=>utf8_encode("Changer sa photo d'identité"))));
			$congratulation->add_paragraph(utf8_encode("<p><img src=\"".$topdir."images/actions/done.png\">La photo mat'matronch de ".$_REQUEST['nom']. " " .$_REQUEST['prenom']." a été correctement mis à jour.</p>"));
			$site->add_contents($congratulation);
	}
	else if ($success > 1)
	{
		if ($success == 4)
		{
		// Réussite de l'upload photo identité et mat'matronch
			$congratulation = new contents(utf8_encode("Upload Photo identité & mat'matronch"));
			$congratulation->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']."?page=upload_photos&open=mmtbx&id=".$_REQUEST['id']=>utf8_encode("Changer sa photo d'mat'matronch"))));
			$congratulation->add_paragraph(utf8_encode("<p><img src=\"".$topdir."images/actions/done.png\">La photo d'identit&eacute; de ".$_REQUEST['nom']. " ".$_REQUEST['prenom']. " a été correctement mis &agrave; jour.</p>"));
			$congratulation->add_paragraph(utf8_encode("<br><p><img src=\"".$topdir."images/actions/info.png\">L'utilisateur ne possédnat pas de photo mat'matronch, la photo d'indentité sera utilisée comme photo mat'matronch</p>"));
			$site->add_contents($congratulation);
		}
		else
		{
		// Réussite de l'upload photo identité
			$congratulation = new contents(utf8_encode("Upload Photo identité"));
			$congratulation->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']."?page=upload_photos&open=mmtbx&id=".$_REQUEST['id']=>utf8_encode("Changer sa photo d'mat'matronch"))));
			$congratulation->add_paragraph(utf8_encode("<p><img src=\"".$topdir."images/actions/done.png\">La photo d'identit&eacute; de ".$_REQUEST['nom']. " ".$_REQUEST['prenom']. " a été correctement mis &agrave; jour.</p>"));
			$site->add_contents($congratulation);
		}
	}
	$cts = new contents(utf8_encode("Identification de l'étudiant :"));

  $frm_ident_email = new form("id_user_by_mail",$_SERVER["SCRIPT_NAME"],false,"POST","Par recherche :");
  $frm_ident_email->add_hidden("page","upload_photos");
  $frm_ident_email->add_entity_smartselect("id_utilisateur","Utilisateur", new utilisateur($site->db));
  $frm_ident_email->add_submit("envoi","Valider");
  $cts->add($frm_ident_email,true, false, "byemail-bx", true, true, true, false);
  
  $frm_ident_id = new form("id_user_by_id",$_SERVER["SCRIPT_NAME"],false,"POST","Par l'ID :");
  $frm_ident_id->add_hidden("page","upload_photos");
  $frm_ident_id->add_text_field("id","Entrez son id :","",true,"40");
  $frm_ident_id->add_submit("envoi","Valider");
  
  $cts->add($frm_ident_id,true, false, "byeid-bx", true, true, false, false);
  
  $site->add_contents($cts);

}
$site->end_page();

?>
