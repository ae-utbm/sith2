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
 
$topdir = "../";
require_once($topdir. "laverie/include/laverie.inc.php");
require_once($topdir. "include/entities/jeton.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");

$site = new sitelaverie ();

if ( !$site->user->is_valid() )
	error_403();

$site->user_is_admin();

if ( $site->is_admin )
  $site->set_admin_mode();

$site->start_page("none","Machines");
$cts = new contents("Machines à laver de l'AE");

if ( $_REQUEST['view'] == "inventaire" )
{
	if ( !$site->is_admin )
		error_403();

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
	$frm = new form("ajoutjeton", "index.php?view=inventaire", false, "POST", "Ajouter un jeton");

/* Test des valeurs de jetons envoyés et ajout dans la base (+ message) */
	if (isset($_REQUEST["numjetons"]))
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

	if($_REQUEST['action'] == "retourner")
	{
				
		if(isset($_REQUEST['id_jeton']))
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
			$jeton->given_back ();
			$lst->add("Le jeton $numjeton a bien été rendu.", "ok");
		}
	}

	if($_REQUEST['action'] == "supprimer")
	{
		if(isset($_REQUEST['id_jeton']))
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
				$lst->add("Le jeton $numjeton a bien été supprimé.", "ok");
			else
				$lst->add("Le jeton $numjeton est encore emprunté et ne peut donc être supprimé.", "ko");
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

	/* Liste des jetons empruntés */
	$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton, 
					mc_jeton_utilisateur.id_utilisateur, 
					mc_jeton_utilisateur.prise_jeton,
					mc_jeton.id_jeton,
					mc_jeton.nom_jeton,
					DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree,
					utilisateurs.id_utilisateur,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
					FROM mc_jeton_utilisateur
					INNER JOIN utilisateurs
					ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
					LEFT JOIN mc_jeton
					ON mc_jeton_utilisateur.id_jeton = mc_jeton.id_jeton
					WHERE mc_jeton_utilisateur.retour_jeton IS NULL
					ORDER BY duree DESC
					"); 
	
	$table = new sqltable("listeemprunts",
				"Liste des jetons empruntés",
				$sql, 
				"index.php?view=inventaire", 
				"id_jeton", 
				array(
					"nom_jeton" => "Jeton",
					"nom_utilisateur"=>"Utilisateur",
					"prise_jeton" => "Date d'emprunt",
					"duree" => "Depuis (jours)"
					), 
					array("retourner" => "Retourner"), array("retourner" => "Retourner"), array()
				);
	$cts->add($table,true);

		/* Liste complète des jetons */
	$sql = new requete($site->db, "SELECT * FROM mc_jeton
						INNER JOIN loc_lieu ON mc_jeton.id_salle = loc_lieu.id_lieu");
	
	$table = new sqltable("listjeton",
			      "Liste des jetons",
			      $sql,
			      "index.php?view=inventaire",
			      "id_jeton",
			      array(
				    "id_jeton" => "ID du jeton",
				    "nom_jeton" => "N° du jeton",
				    "type_jeton" =>"Type du jeton",
						"nom_lieu" =>"Lieu"
				    ),
			      array("supprimer" => "Supprimer"),
			      array("supprimer" => "Supprimer"),
			      array()
			      );

	$cts->add($table, true);				

}
else
{
  $cts->add_paragraph("<br />Ici trônera joyeusement l'interface cotisant-machine à laver.");
  $cts->add_paragraph("Le tout via un système révolutionnaire de responsables machines nourris au Bob AE et à la cancoillote afin de vous assurer une productivité parfaite dans le nettoyage de vos chaussettes sales et de vos caleçons dégueus (non rien pour les filles, elle sont juste un mythe à l'UTBM).");
}

$site->add_contents($cts);
$site->end_page();  

?>
