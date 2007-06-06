<?php
/* Copyright 2006
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
$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/carteae.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae"))
  error_403();

if ( $_REQUEST["action"] == "pdf" )
{
	require_once($topdir . "include/pdf/carteae.inc.php");
	
	$pdf = new pdfcarteae();
	
	$ids = explode(",",$_REQUEST["ids"]);
	foreach ( $ids as $key => $val )
		$ids[$key] = intval($val);
	
	$req = new requete($site->db, 
			"SELECT " .
			"`utilisateurs`.`id_utilisateur`, " .
			"`utilisateurs`.`nom_utl`, " .
			"`utilisateurs`.`prenom_utl`, " .
			"`utl_etu_utbm`.`surnom_utbm`, " .
			"`ae_cotisations`.`date_fin_cotis`, " .
			"`ae_carte`.`id_carte_ae` " .
			"FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"INNER JOIN `utilisateurs` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
			"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
			"WHERE `utilisateurs`.`id_utilisateur` IN (".implode(",",$ids).") " .
			"ORDER BY `ae_carte`.`id_carte_ae`");	
	
	$pdf->render($req);
	$pdf->Output();
	exit();
}


$site->start_page ("none", "Cartes AE");

$cts = new contents("Cartes AE");

$tabs = array(
			array("","ae/cartesae.php", "Impression"),
			array("print","ae/cartesae.php?view=print", "Test apr&egrave;s impression"),
			array("bureau","ae/cartesae.php?view=bureau", "Reception au bureau"),
			array("retrait","ae/cartesae.php?view=retrait", "Retrait carte AE"),
			array("cadeau","ae/cartesae.php?view=cadeau", "Retrait cadeau"),
			);
//array("photos","ae/cartesae.php?view=photos", "Photo manquante")
			
$cts->add(new tabshead($tabs,$_REQUEST["view"]));

if ($_REQUEST["view"] == "cadeau")
{
	if ( isset($_REQUEST['code']))
	{
		$carte = new carteae($site->db);
		$cotiz = new cotisation($site->db, $site->dbrw);
		$user = new utilisateur($site->db);

		$lst = new itemlist("R&eacute;sultat");

		$carte->load_by_cbarre($_REQUEST['code']);
		if ( $carte->id > 1 )
		{
			$cotiz->load_by_id($carte->id_cotisation);
			$user->load_by_id($cotiz->id_utilisateur);
			if ( $cotiz->a_pris_cadeau )
				$lst->add("<b>ATTENTION : </b>Cadeau déjà pris pour " . $user->prenom. " ".$user->nom." !","ko");
			else
			{
				$cotiz->mark_cadeau();
				if ( !$cotiz->a_pris_carte )
				{
					$lst->add($user->prenom. " ".$user->nom." : OK","ok");
					$advert = 1;
				}
				else
					$lst->add($user->prenom. " ".$user->nom." : OK","ok");
			}
		}
		else
			$lst->add("Carte n�".$_REQUEST['code']." inconnue !","ko");
		$cts->add($lst,true);
		if ($advert)
		{
			$cts->puts("<br/><b style=\"red\">ATTENTION : </b>".$user->prenom." ".$user->nom.utf8_encode(" ne semble pas avoir d�j� pris sa carte AE !<br/><br/>"));
			$frm_retrait_cae = new form("gotoretraitcae","cartesae.php?view=retrait",false,"POST",null);
			$frm_retrait_cae->add_submit("go","Marquer sa carte AE comme retir&eacute;e");
			$frm_retrait_cae->add_hidden("code",$_REQUEST['code']);
			$cts->add($frm_retrait_cae);
		}
	}
	$frm = new form("retraitcadeau","cartesae.php?view=cadeau",false,"POST","Retrait cadeau");
	$frm->add_text_field("code","Carte AE : ");
	$frm->set_focus("code");
	$frm->add_submit("valid","Valider");
	$cts->add($frm,true);
}
elseif ( $_REQUEST["view"] == "retrait" )
{
	if ( isset($_REQUEST["code"]))
	{
		$carte = new carteae($site->db,$site->dbrw);
		$cotiz = new cotisation($site->db,$site->dbrw);
		$user = new utilisateur($site->db);
		
		$lst = new itemlist("R&eacute;sultats");
		
		$carte->load_by_cbarre($_REQUEST["code"]);
		if ( $carte->id > 1 )
		{
			$cotiz->load_by_id($carte->id_cotisation);
			$user->load_by_id($cotiz->id_utilisateur);
			
			if ( $_REQUEST["cadeau"] )	
			{
				$lst->puts("<h3>Cadeau :</h3>");			
				if ( $cotiz->a_pris_cadeau )
				{
					$lst->add("<b>ATTENTION : </b> ". $user->prenom . " " . $user->nom . " Cadeau déjà pris !","ko");
				}
				else
				{
					$cotiz->mark_cadeau();
					$lst->add($user->prenom." ".$user->nom." : OK","ok");
				}	
			}
			elseif ( !$cotiz->a_pris_cadeau )
			{
				$lst->puts("<h3>Cadeau :</h3>");			
				$distrib = new form("distrib","cartesae.php?view=cadeau",false,"POST","INFO : " .$user->prenom . " " . $user->nom . " n'a pas encore pris son cadeau !<br/><br/>");
				$distrib->add_hidden("code",$_REQUEST['code']);
				$distrib->add_submit("valid","Marquer son cadeau comme pris");
				$lst->add($distrib,"info");
			}
			$lst->puts("<h3>Carte AE :</h3>");
			if ( $cotiz->a_pris_carte )
			{
				$lst->add("<b>ATTENTION : </b> ". $user->prenom . " " . $user->nom . " Carte AE déjà prise !","ko");
			}
			else
			{
				$cotiz->mark_carte();
				$carte->set_state(CETAT_CIRCULATION);
				$lst->add($user->prenom." ".$user->nom." : OK","ok");
			}
		}
		else
			$lst->add("Carte n�".$_REQUEST['code']." inconnue !","ko");
		$cts->add($lst,true);
		if (!$cotiz->a_pris_cadeau)
		{
		$cts->puts("<br/>");
		$frm_suiv = new form("gotocadeau","cartesae.php?view=cadeau",false,"POST",null);
		$frm_suiv->add_submit("suiv","Etape Suivante");
		$cts->add($frm_suiv);
		}
	}
	
	$frm = new form("retraitcarte","cartesae.php?view=retrait",false,"POST","Retrait carte");
	$frm->add_checkbox("cadeau","Retrait du cadeau");
	$frm->add_text_field("code","Carte");
	$frm->set_focus("code");
	$frm->add_submit("valid","Valider");
	$cts->add($frm,true);
	
	$req = new requete($site->db, 
			"SELECT " .
			"`utilisateurs`.`id_utilisateur`, " .
			"CONCAT(`utilisateurs`.`nom_utl`,' ',`utilisateurs`.`prenom_utl`) as `nom_utilisateur`, " .
			"`ae_cotisations`.`date_fin_cotis`, " .
			"`ae_carte`.`id_carte_ae` " .
			"FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"INNER JOIN `utilisateurs` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
			"WHERE `ae_carte`.`etat_vie_carte_ae` = '" . CETAT_AU_BUREAU_AE . "'" .
			"ORDER BY `utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl`");	
			
	$cts->add(new sqltable(
		"listnonret", 
		"Liste des utilisateurs qui ne sont pas venus retirer leur carte ae", $req, "cartesae.php", 
		"id_utilisateur", 
		array(
			"id_carte_ae"=>"N° carte",
			"nom_utilisateur"=>utf8_encode("Nom & Pr�nom"),
			"date_fin_cotis"=>"Cotisation jusqu'au"), 
		array("email_retrait_carte"=>"Envoyer email de rappel"), 
		array("email_retrait_carte"=>"Envoyer email de rappel"),
		array( )
		),true);
}
elseif ( $_REQUEST["view"] == "bureau" )
{
	if ( isset($_REQUEST["codes"]))
	{
		$carte = new carteae($site->db,$site->dbrw);
		$user = new utilisateur($site->db);
		$codes = explode("\n",$_REQUEST["codes"]);
		$lst = new itemlist("R&eacute;sultats");
		foreach ( $codes as $num )
		{
			$carte->load_by_cbarre($num);
			if ( $carte->id > 0 )
			{
				$user->load_by_cotisation($carte->id_cotisation);
				if ($carte->set_state(CETAT_AU_BUREAU_AE))
				{
					$lst->add($user->prenom." ".$user->nom." : OK","ok");	
				}
				else
					$lst->add($user->prenom." ".$user->nom.utf8_encode(" : Carte d�j� r�ceptionn�e"),"ko");
			}
			else
				$lst->add("Carte n�".$num." inconnue !","ko");
		}
		$cts->add($lst,true);
		$cts->puts("<br/>");
		$frm_suiv = new form("gotoretrait","cartesae.php?view=retrait",false,"POST",null);
		$frm_suiv->add_submit("suiv","Etape Suivante");
		$cts->add($frm_suiv);
	}
		
	$frm = new form("aubureau","cartesae.php?view=bureau",false,"POST","Cartes");
	$frm->add_info("Scanner les cartes à marquer comme receptionnés au bureau de l'ae");
	$frm->add_text_area("codes","Cartes");
	$frm->set_focus("codes");
	$frm->add_submit("valid","Valider");
	$cts->add($frm,true);
}
elseif ( $_REQUEST["view"] == "print" )
{
	if ( isset($_REQUEST["codes"]))
	{
		$carte = new carteae($site->db,$site->dbrw);
		$user = new utilisateur($site->db);
		$codes = explode("\n",$_REQUEST["codes"]);
		$lst = new itemlist("R&eacute;sultats");
		foreach ( $codes as $num )
		{
			$carte->load_by_cbarre($num);
			if ( $carte->id > 0 )
			{
				$carte->set_state(CETAT_IMPRIMEE);
				$user->load_by_cotisation($carte->id_cotisation);
				$lst->add($user->prenom." ".$user->nom." : OK","ok");	
			}
			else
				$lst->add("Carte n�".$num." inconnue !","ko");
		}

		$cts->add($lst,true);
		$cts->puts("<br/>");
		$frm_suiv = new form("gotobureau","cartesae.php?view=bureau",false,"POST",null);
		$frm_suiv->add_submit("suiv","Etape Suivante");
		$cts->add($frm_suiv);
	}
	
	$frm = new form("printed","cartesae.php?view=print",false,"POST","Cartes");
	$frm->add_info("Scanner les cartes à marquer comme imprimées");
	$frm->add_text_area("codes","Cartes");
	$frm->set_focus("codes");
	$frm->add_submit("valid","Valider");
	$cts->add($frm,true);
}
else
{
	if ($_REQUEST['action']=="email_photo")
	{
		$lst = new itemlist("R&eacute;sultats :");
		if($_GET['id_utilisateur'])
			$ids[] = $_GET['id_utilisateur'];
		elseif($_POST['id_utilisateurs'])
		{

			foreach ($_POST['id_utilisateurs'] as $id_util)
				$ids[] = $id_util;
		}

		foreach ( $ids as $id )
		{
			$user = new utilisateur($site->db);
			$user->load_by_id($id);
			$user->load_all_extra();
			$body = "Bonjour,


Votre carte AE est pr�te a �tre imprim�e.

Cependant vous ne disposez pas de photo d'identit� sur votre profil pour figurer sur votre carte AE.
		  
Vous devez ajouter une photo d'identit� � votre profil en vous rendant � l'adresse :
	  http://ae.utbm.fr/user.php?id_utilisateur=".$user->id."&page=edit#setphotos
Votre carte AE sera d�sormais transmise pour impression.


Merci de votre compr�hension

L'�quipe info AE";

			$ret = mail($user->email_utbm, "[AE] Probl�me lors de l'impression de votre carte AE - Photo manquante", $body,
                            "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae.info@utbm.fr");
			if ($ret)
				$lst->add("Mail de rappel &agrave; " .$user->prenom. " " .$user->nom. " : Envoy&eacute;","ok");	
			else
				$lst->add("Erreur lors de l'envoi du mail de rappel pour " . $user->prenom . " " . $user->nom ." !","ko");
		}
		$cts->add($lst,true);
	}	
	$req = new requete($site->db, 
			"SELECT `ae_cotisations`.`id_utilisateur` FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"LEFT JOIN `utl_etu_utbm` ON `ae_cotisations`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur` ".
			"WHERE `ae_carte`.`etat_vie_carte_ae`=".CETAT_ATTENTE." " .
			"AND (branche_utbm='TC' OR branche_utbm='GMC') " .
			"ORDER BY `ae_carte`.`id_carte_ae`");	
	$printable['sev'] = array();
	while ( $row = $req->get_row() )
		if ( file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
			$printable['sev'][]=$row['id_utilisateur'];	
	
	$req = new requete($site->db, 
			"SELECT `ae_cotisations`.`id_utilisateur` FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"LEFT JOIN `utl_etu_utbm` ON `ae_cotisations`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur` ".
			"WHERE `ae_carte`.`etat_vie_carte_ae`=".CETAT_ATTENTE." " .
			"AND !(branche_utbm='TC' OR branche_utbm='GMC') " .
			"ORDER BY `ae_carte`.`id_carte_ae`");	
	$printable['bel'] = array();
	while ( $row = $req->get_row() )
		if ( file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
			$printable['bel'][]=$row['id_utilisateur'];	

	$req = new requete($site->db,"SELECT `ae_cotisations`.`id_utilisateur` FROM `ae_carte` " .
				"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
				"LEFT JOIN `utl_etu` ON `ae_cotisations`.`id_utilisateur`=`utl_etu`.`id_utilisateur` " .
				"INNER JOIN `utilisateurs` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"WHERE `ae_carte`.`etat_vie_carte_ae`=".CETAT_ATTENTE." AND `utilisateurs`.`utbm_utl` = '0' " .
				"ORDER BY `ae_carte`.`id_carte_ae`");
	$printable['oth'] = array();
	while ( $row = $req->get_row() )
		if ( file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
			$printable['oth'][]=$row['id_utilisateur'];	
	$print = new contents("PDFs &agrave; imprimer ");
	$print->add_paragraph(count($printable['sev'])." carte(s) imprimable(s) pour Sévenans : <a href=\"cartesae.php?action=pdf&ids=".implode(",",$printable['sev'])."\">Imprimer</a>");
	$print->add_paragraph(count($printable['bel'])." carte(s) imprimable(s) pour Belfort : <a href=\"cartesae.php?action=pdf&ids=".implode(",",$printable['bel'])."\">Imprimer</a>");
	$print->add_paragraph(count($printable['oth'])." carte(s) imprimable(s) categorie Autre (Prof,IUT ...) : <a href=\"cartesae.php?action=pdf&ids=".implode(",",$printable['oth'])."\">Imprimer</a>");	
	$cts->add($print,true);

	$req = new requete($site->db, 
			"SELECT `ae_cotisations`.`id_utilisateur` FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"LEFT JOIN `utl_etu_utbm` ON `ae_cotisations`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur` ".
			"WHERE `ae_carte`.`etat_vie_carte_ae`=".CETAT_ATTENTE." " .
			"ORDER BY `ae_carte`.`id_carte_ae`");	
	$nprintable = array();
	while ( $row = $req->get_row() )
		if ( !file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
			$nprintable[]=$row['id_utilisateur'];	
			
	$req = new requete($site->db, 
			"SELECT " .
			"`utilisateurs`.`id_utilisateur`, " .
			"CONCAT(`utilisateurs`.`nom_utl`,' ',`utilisateurs`.`prenom_utl`) as `nom_utilisateur`, " .
			"`ae_cotisations`.`date_fin_cotis`, " .
			"`ae_carte`.`id_carte_ae` " .
			"FROM `ae_carte` " .
			"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
			"INNER JOIN `utilisateurs` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
			"WHERE `utilisateurs`.`id_utilisateur` IN (".implode(",",$nprintable).") " .
			"ORDER BY `utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl`");	
			
	$cts->add(new sqltable(
		"listphmanq", 
		"Liste des utilisateurs dont les photos manquent sur les cartes AE", $req, "cartesae.php", 
		"id_utilisateur", 
		array(
			"id_carte_ae"=>"N° carte",
			"nom_utilisateur"=>utf8_encode("Nom & Pr�nom"),
			"date_fin_cotis"=>"Cotisation jusqu'au"), 
		array("email_photo"=>"Envoyer email de rappel"), 
		array("email_photo"=>"Envoyer email de rappel"),
		array( )
		),true);
			
}

$site->add_contents($cts);

$site->end_page ();

?>
