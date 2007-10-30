<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet AT oxynux DOT org >
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
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

define("ID_ASSO_LAVERIE", 84);
define("GRP_BLACKLIST", 29);
define("CPT_MACHINES", 8);
define("JET_LAVAGE", 224);
define("JET_SECHAGE", 225);

$topdir = "../";
require_once($topdir. "laverie/include/laverie.inc.php");
require_once($topdir. "include/entities/jeton.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");

$site = new sitelaverie();

$site->allow_only_logged_users("services");

$site->start_page("none","Laverie");
$cts = new contents("Machines à laver de l'AE");

$site->get_rights();

$cts->add_title(2,"Nombre de jetons");

$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton`");
list($total) = $req->get_row();
$cts->add_paragraph("Total : $total");
$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton_utilisateur` WHERE `retour_jeton` IS NULL");
list($utilises) = $req->get_row();
$cts->add_paragraph("En circulation : $utilises");
$disponibles = $total - $utilises;
$cts->add_paragraph("En caisse : $disponibles");

/* Formulaire d'ajout de jetons */  
$lst = new itemlist("Résultats :");
$frm = new form("ajoutjeton", "inventaire.php", false, "POST", "Ajouter un jeton");

/* Test des valeurs de jetons envoyés et ajout dans la base (+ message) */
if (!empty($_REQUEST["numjetons"]) )
{
	$array_jetons = explode(" ", $_REQUEST["numjetons"]);
	foreach($array_jetons as $numjeton)
	{
	$jeton = new jeton($site->db, $site->dbrw);
	$jeton->add ( $_REQUEST["sallejeton"], $_REQUEST["typejeton"], $numjeton);
	if($jeton->id > -1)
		$lst->add("Le jeton $numjeton a bien été enregistré", "ok");
	}
}

if($_REQUEST['action'] == "supprimer")
{
	if(!empty($_REQUEST['id_jeton']))
		$id_jetons[] = $_REQUEST['id_jeton'];
	elseif($_REQUEST['id_jetons'])
	{
	foreach ($_REQUEST['id_jetons'] as $id_jeton)
		$id_jetons[] = $id_jeton;
	}

	foreach($id_jetons as $numjeton)
	{
		$jeton = new jeton($site->db, $site->dbrw);
		$jeton->load_by_id($numjeton);
		$retour = $jeton->delete();
		if($retour == 0)
			$lst->add("Le jeton $jeton->nom a bien été supprimé.", "ok");
		else
			$lst->add("Le jeton $jeton->nom est encore emprunté et ne peut donc être supprimé.", "ko");
	}	
}

$frm->add_info("Entrez les numéros des jetons séparés par des espaces");
$frm->add_text_area("numjetons", "Numéro ");
$frm->set_focus("numjetons");
$frm->add_select_field("typejeton", "Type du jeton :", $GLOBALS['types_jeton']);
$frm->add_select_field("sallejeton", "Salle concernée :", $GLOBALS['salles_jeton']);
$frm->add_submit("valid","Valider");
$frm->allow_only_one_usage();
$cts->add($lst);
$cts->add($frm,true);

/* Liste complète des jetons */
$sql = new requete($site->db, "SELECT * FROM mc_jeton
	INNER JOIN loc_lieu ON mc_jeton.id_salle = loc_lieu.id_lieu");

$table = new sqltable("listjeton",
	"Liste des jetons",
	$sql,
	"inventaire.php",
	"id_jeton",
	array(
		"id_jeton" => "ID du jeton",
		"nom_jeton" => "N° du jeton",
		"type_jeton" =>"Type du jeton",
		"nom_lieu" =>"Lieu"
	),
	array("supprimer" => "Supprimer"),
	array("supprimer" => "Supprimer"),
	array("type_jeton"=>$GLOBALS['types_jeton']) );

$cts->add($table, true);  

$site->add_content($cts);
$site->end_page();
?>
