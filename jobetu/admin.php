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

define("GRP_JOBETU_CLIENT", 35);
define("GRP_JOBETU_ETU", 36);

$site = new site();
$site->start_page("services", "AE Job Etu");

$cts = new contents("Administration AE Job Etu");

$jobetu = new jobetu($site->db, $site->dbrw);

$tabs = array(
		      array("", "jobetu/admin.php", "vue générale"),
		      array("annonces", "jobetu/admin.php?view=annonces", "annonces"),
		      array("clients", "jobetu/admin.php?view=clients", "clients"),
		      array("etudiants", "jobetu/admin.php?view=etudiants", "étudiants"),
		      array("categories", "jobetu/admin.php?view=categories", "catégories")
	      );
$cts->add(new tabshead($tabs, $_REQUEST['view']));


/***************************************************************
 * Onglet de gestion des catégories et sous catégories
 */
if(isset($_REQUEST['view']) && $_REQUEST['view'] == "categories")
{
	/*
	 * Traitement des réponses
	 */
		if(isset($_REQUEST['magicform']) && $_REQUEST['magicform']['name'] == "jobtypes")
		{
			if($_REQUEST['type_cat'] == "main")
				$jobetu->add_cat_type($_REQUEST['name_main']);
			else if($_REQUEST['type_cat'] == "sub")
				$jobetu->add_subtype($_REQUEST['name_sub'], $_REQUEST['mama_cat']);
		}
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == "delete")
		{
			$jobetu->del_subtype($_REQUEST['id_types']);
		}
	
	$cts->add_title(2, "Gestion de la liste des catégories");
	$jobetu->get_job_types();
//	$cts->add($jobetu->job_types);

	$sql = new requete($site->db, "SELECT id_type, nom, COUNT(id_type) AS nb_etu
																FROM `job_types_etu`
																NATURAL JOIN `job_types`
																GROUP BY id_type
																ORDER BY id_type ASC");
	$table = new sqltable("typetable", "Catégorie des jobs", $sql, null, "id_types", array("id_type" => "Num", "nom" => "Nom de la catégorie", "nb_etu" => "Nb d'étudiant"), array("delete" => "Supprimer"), array(), array());
	$cts->add($table);
	
	
	$frm = new form("jobtypes", "admin.php?view=categories", false, "post", "Ajouter une catégorie/sous-catégorie");
		$sfrm = new form("type_cat",null,null,null,"Catégorie principale");
		$sfrm->add_text_field("name_main", "Intitulé");
		$sfrm->add_submit("go", "Envoyer");
	$frm->add($sfrm,false,true,1,"main",false,true,true);
		
		$sfrm = new form("type_cat",null,null,null,"Sous catégorie");
		$sfrm->add_text_field("name_sub", "Intitulé");
		$sfrm->add_select_field("mama_cat","Catégorie mère",$jobetu->job_main_cat);
		$sfrm->add_submit("go", "Envoyer");
	$frm->add($sfrm,false,true,0,"sub",false,true);
	

	$cts->add($frm, true);	

}

/***************************************************************
 * Onglet de gestion des annonces 
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "annonces")
{
	$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur,
																	CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
																	FROM `utilisateurs`
																	NATURAL JOIN `utl_groupe`
																	WHERE id_groupe = ".GRP_JOBETU_ETU."
																	GROUP BY utilisateurs.id_utilisateur", false);
	
	$cts->add( new sqltable("list_clients", "Etudiants inscrits à AE JobEtu", $sql, "admin.php", "id_utilisateur", array("id_utilisateur" => "ID", "nom_utilisateur" => "Nom"), array(), array()), true );
}

/***************************************************************
 * Onglet des recruteurs
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "clients")
{
	$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur,
																	CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
																	FROM `utilisateurs`
																	NATURAL JOIN `utl_groupe`
																	WHERE id_groupe = ".GRP_JOBETU_CLIENT."
																	GROUP BY utilisateurs.id_utilisateur", false);
	
	$cts->add( new sqltable("list_clients", "Clients de AE JobEtu", $sql, "admin.php?view=clients", "id_utilisateur", array("id_utilisateur" => "ID", "nom_utilisateur" => "Nom"), array("mailto" => "Envoyer un mail", "del_account" => "Désactiver compte", "edit_prefs" => "Editer préférences"), array("mailto" => "Envoyer un mail", "del_account" => "Désactiver compte")), true );
}

/***************************************************************
 * Onglet de gestion des étudiants 
 */
else if(isset($_REQUEST['view']) && $_REQUEST['view'] == "etudiants")
{
	$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur,
																	CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
																	FROM `utilisateurs`
																	NATURAL JOIN `utl_groupe`
																	WHERE id_groupe = ".GRP_JOBETU_ETU."
																	GROUP BY utilisateurs.id_utilisateur", false);
	
	$cts->add( new sqltable("list_clients", "Etudiants inscrits à AE JobEtu", $sql, "admin.php?view=etudiants", "id_utilisateur", array("id_utilisateur" => "ID", "nom_utilisateur" => "Nom"), array("mailto" => "Envoyer un mail", "edit_profil" => "Editer profil", "edit_prefs" => "Editer préférences", "del_account" => "<img src=\"$topdir/images/action/delete.png\" title=\"Désactiver le compte\" />"), array("mailto" => "Envoyer un mail", "del_account" => "Désactiver compte")), true );
}

/***************************************************************
 * Onglet d'accueil
 */
else
{
	
}



$site->add_contents($cts);
$site->end_page();



?>
