<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once("include/jobetu.inc.php");
require_once("include/annonce.inc.php");
require_once("include/cts/jobetu.inc.php");
require_once("include/jobuser_etu.inc.php");

$site = new site();
$site->allow_only_logged_users("services");
$site->add_css("jobetu/jobetu.css");
$site->start_page("services", "AE Job Etu");

$cts = new contents("Tableau de bord AE Job Etu");

$tabs = array(
		      array("", "jobetu/board_etu.php", "mes annonces"),
		      array("candidatures", "jobetu/board_etu.php?view=candidatures", "mes candidatures"),
		      array("general", "jobetu/board_etu.php?view=general", "tout job-etu"),
		      array("profil", "jobetu/board_etu.php?view=profil", "profil"),
		      array("preferences", "jobetu/board_etu.php?view=preferences", "préférences")
	      );
$cts->add(new tabshead($tabs, $_REQUEST['view']));


/*******************************************************************************
 * Onglet profil
 */
if(isset($_REQUEST['view']) && $_REQUEST['view'] == "profil")
{
		$jobetu = new jobetu($site->db, $site->dbrw);
		$usr = new jobuser_etu($site->db, $site->dbrw);
		$usr->load_by_id($site->user->id);
		$usr->load_competences();

		/**
		 * Gestion des données recues sur la mise à jour du profil
		 */
		if(isset($_REQUEST['magicform']) && $_REQUEST['magicform']['name'] == "jobcapa")
			$usr->update_competences($_REQUEST['id_jobs']);
		
		
		$cts->add_title(2, "Modifiez vos informations");
	  $cts->add_paragraph("Toutes vos informations personnelles, telles que votre adresse, téléphone, date de naissance... sont celles de votre fiche Matmatronch, pour les modifier, <a href=\"$topdir./user.php?id_utilisateur=$usr->id&page=edit\">cliquez ici</a>");
	
	$frm = new form("jobcapa", "board_etu.php?view=profil", true, "POST");
	$frm->puts("<h3>De quoi êtes vous capable ?</h3>");
	$jobetu->add_jobtypes_table($frm, "job_type", "Catégorie");
	
	$sql = new requete($site->db, "SELECT *  FROM job_types ORDER BY id_type ASC");
	$table = new sqltable("typetable", "Catégorie des jobs", $sql, null, "id_types", array("id_type" => "Num", "nom" => "Nom de la catégorie"), array("go"=>"Go"), array("go"=>"Go"), array());
	//$cts->add($table);
		
	$frm->puts("<h3>Vos CV \"traditionnels\"</h3>");
		$frm->add_file_field("cv_1", "Envoyez un CV");
		$frm->add_file_field("cv_2", "Un autre CV");
	$frm->add_submit("go", "Envoyer");
	$cts->add($frm);
}

/*******************************************************************************
 * Onglet candidatures
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "candidatures")
{
	$usr = new jobuser_etu($site->db, $site->dbrw);
	$usr->load_by_id($site->user->id);
		
	$sql = new requete($site->db, "SELECT * from `job_annonces_etu` WHERE `id_etu` = $usr->id AND `relation` = 'apply'");
	$cts->add(new sqltable("candidatures", "Candidatures", $sql, false, 'id_relation', array("id_relation"=>"Id", "id_etu"=>"Etu"), array(), array()), true);
}

/*******************************************************************************
 * Onglet tout jobetu
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "general")
{
	if(isset($_REQUEST['action']))
	{
		$ids = array();
		if(isset($_REQUEST['id_annonce']))
			$ids[] = $_REQUEST['id_annonce'];
		if(isset($_REQUEST['id_annonces']))
			foreach ($_REQUEST['id_annonces'] as $id)
				$ids[] = $id;
		
		if($_REQUEST['action'] == "detail")
		{				
			foreach ($ids as $id_annonce)
			{
				$annonce = new annonce($site->db);
				$annonce->load_by_id($id_annonce);
				$cts->add( new apply_annonce_box($annonce) );
			}
		}
		else if($_REQUEST['action'] == "reject")
		{
			$usr = new jobuser_etu($site->db);
			$usr->load_by_id($site->user->id);
			
			foreach ($ids as $id_annonce)
			{
				$annonce = new annonce($site->db);
				$annonce->load_by_id($id_annonce);
				$annonce->reject($usr);
			}
		}
		else if($_REQUEST['action'] == "apply")
		{
			$cts->add_paragraph("Namého ! tu te crois chez mémé ? ca se passe pas comme ça nondidiou !!");
		}
	}
	else
	{
		$sql = new requete($site->db, "SELECT `job_annonces`.*,
																		CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_utilisateur`,
																		`job_types`.`nom` AS `job_nom`
																		FROM `job_annonces`
																		LEFT JOIN `utilisateurs`
																		ON `job_annonces`.`id_client` = `utilisateurs`.`id_utilisateur`
																		LEFT JOIN `job_types`
																		ON `job_types`.`id_type` = `job_annonces`.`job_type`");
		
		$table = new sqltable("annlist", "Liste des annonces en cours", $sql, "board_etu.php?view=general", "id_annonce",
													array(
														"id_annonce" => "N°",
														"nom_utilisateur" => "Client",
														"job_nom" => "Catégorie",
														"titre" => "Titre"
													),
													array("detail" => "Détails", "reject" => "Ne plus me montrer"),
													array("detail" => "Détails", "reject" => "Ne plus me montrer"),
													array()											
												);
		
		$cts->add($table, true);
	}
}

/*******************************************************************************
 * Onglet préférénce
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "preferences")
{

}

/*******************************************************************************
 * Onglet d'accueil sinon
 */
else
{	
	if( isset($_REQUEST['action']) )
	{
		$usr = new jobuser_etu($site->db);
		$usr->load_by_id($site->user->id);

		$annonce = new annonce($site->db, $site->dbrw);
		$annonce->load_by_id($_REQUEST['id']);
		
		if($_REQUEST['action'] == "apply")
		{
			if( $annonce->apply_to($usr, $_REQUEST['comment']) )
			{
				$cts->add_paragraph("Votre candidature à bien été enregistrée pour l'annonce n°".$annonce->id." : <i>".$annonce->titre."</i>\n");
			}
		}
		else if($_REQUEST['action'] == "reject")
		{
			if( $annonce->reject($usr) )
			{
				$cts->add_paragraph("Votre souhait de ne plus voir l'annonce n°".$annonce->id." vous être proposée à bien été enregistré.\n");
			}
		}
		
	} //fin 'actions'

	
	$annonce = new annonce($site->db);
	$annonce->load_by_id(1);
	$cts->add( new apply_annonce_box($annonce) );
}


$site->add_contents($cts);

$site->end_page();

?>
