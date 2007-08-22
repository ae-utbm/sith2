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

if ( $_REQUEST['view'] == "retour" )
{
	if ( !$site->is_admin )
		error_403();

	$frm = new form("retourjetons","index.php?view=retour",false,"POST","Retour jetons");
	$lst = new itemlist("Résultats :");
	
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
			$lst->add("Le jeton $jeton->nom a bien été rendu.", "ok");
		}
	}
	
	/* Test des valeurs de jetons envoyés et modif dans la base (+ message)*/ 
	if (isset($_REQUEST["numjetons"]) && isset($_REQUEST["typejeton"]))
	{
		$array_jetons = explode(" ", $_REQUEST["numjetons"]);
		foreach($array_jetons as $numjeton)
		{
			$jeton = new jeton($site->db, $site->dbrw);
			$jeton->load_by_nom($numjeton, $_REQUEST["typejeton"]);
				
			if($jeton->id < 0)
			  $lst->add("Erreur pour le jeton $jeton->nom (mauvais type ?).", "ko");
			elseif(!$jeton->is_borrowed())
			  $lst->add("Le jeton $jeton->nom n'est pas emprunté.", "ko");
			else
			{	
				$jeton->given_back ( $_REQUEST["sallejeton"], $_REQUEST["typejeton"], $numjeton);
				if($jeton->id > -1)
				  $lst->add("Le jeton $jeton->nom a bien été marqué comme restitué.", "ok");
			}
		}
	}

	$frm->add_info("Entrez les numéros des jetons séparés par des espaces :");
	$frm->add_text_area("numjetons","Numéros :");
	$frm->set_focus("numjetons");
	$frm->add_select_field("typejeton", "Type du jeton :", $GLOBALS['types_jeton']);
	$frm->add_submit("valid","Valider");
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
				"index.php?view=retour", 
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

}
elseif ( $_REQUEST['view'] == "inventaire" )
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
elseif($_REQUEST['view']=="mauvais")
{

	$lst = new itemlist("Résultats :");

	if($_REQUEST['action'] == "blacklist")
	{
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db, $site->dbrw);
		  $user->load_by_id($id);

		  $user->add_to_group(GRP_BLACKLIST);

		}
	}

	if($_REQUEST['action'] == "unblacklist")
	{
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db, $site->dbrw);
		  $user->load_by_id($id);

		  $user->remove_from_group(GRP_BLACKLIST);
		}
	}
	      	
	if($_REQUEST['action'] == "mail_rappel")
	{
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db);

		  $id = intval($id);

		  $user->load_by_id($id);
		  $sql = new requete($site->db, "SELECT 
						`mc_jeton_utilisateur`.`id_jeton`
            , `mc_jeton`.`nom_jeton`
            , DATEDIFF(CURDATE(), `mc_jeton_utilisateur`.`prise_jeton`) AS `duree` 
						FROM `mc_jeton` 
						INNER JOIN `mc_jeton_utilisateur` ON `mc_jeton`.`id_jeton` = `mc_jeton_utilisateur`.`id_jeton` 
						WHERE `id_utilisateur` = $id AND `retour_jeton` IS NULL");
		  /* et si y'a pas de lignes ? */
		  if ($sql->lines <= 0)
		    continue;

		  $body = "Bonjour, 

Vous utilisez le service de machines à laver proposé par l'AE et nous vous en remercions, nous attirons votre attention sur le fait que les jetons vous sont prêtés pour une utilisation des machines dans la journée suivante, ceci afin de permettre une bonne circulation des jetons, garantissant ainsi à tous la possiblité de bénéficier de ce service.

Or vous avez encore en votre possession le(s) jeton(s) suivant(s) : \n";
		  
		  while ($row = $sql->get_row())
		    $body .= "- Jeton n°".$row['nom_jeton'].", emprunté depuis ".$row['duree']." jours \n";
		  

		  $body .= "\n Afin que tout le monde puisse profiter des machines mises à disposition par l'AE nous vous remercions de bien vouloir utiliser ou rapporter ces jetons dans les plus brefs délais, à défaut de quoi, vous pourriez vous voir bloquer l'accès à ce service.

Merci d'avance

Les responsables machines à laver";
			  
		  $mail = mail($user->email, utf8_decode("[AE] Jetons de machines à laver"), utf8_decode($body),
                            "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: marie-anne.mittet@utbm.fr,sebastien.dete@utbm.fr");
			if ($mail)
				$lst->add("Mail de rappel &agrave; " .$user->prenom. " " .$user->nom. " : Envoy&eacute;","ok");	
			else
				$lst->add("Erreur lors de l'envoi du mail de rappel pour " . $user->prenom . " " . $user->nom ." !","ko");
		  
		}
	}

	/* Liste des mauvais clients */
	$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton,
					mc_jeton_utilisateur.id_utilisateur,
					mc_jeton_utilisateur.retour_jeton,
					COUNT(id_jeton) AS nombre,
					utilisateurs.nom_utl, 
					utilisateurs.prenom_utl, 
					utilisateurs.id_utilisateur,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur
					FROM mc_jeton_utilisateur
					LEFT JOIN utilisateurs 
					ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
					WHERE mc_jeton_utilisateur.retour_jeton IS NULL
					GROUP BY mc_jeton_utilisateur.id_utilisateur
					ORDER BY nombre DESC");


	$table = new sqltable("toploosers",
				"Top des mauvais clients",
				$sql,
				"jetons.php?view=listing",
				"id_utilisateur",
				array(
					"nom_utilisateur"=>"Utilisateur",
					"nombre" => "Nombre"
					),
			      array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
			      array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
			      array()
				);

	$cts->add($table, true);

	$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur, 
					CONCAT(utilisateurs.prenom_utl,' ', utilisateurs.nom_utl) AS nom_utilisateur 
					FROM utl_groupe INNER JOIN utilisateurs ON utilisateurs.id_utilisateur = utl_groupe.id_utilisateur 
					WHERE utl_groupe.id_groupe = 29 
					ORDER BY utilisateurs.nom_utl, utilisateurs.prenom_utl");

	$table = new sqltable("blackmember", 
			"Liste des personnes bloquées",
			$sql,
			"jetons.php?view=listing",
			"id_utilisateur",
			array("nom_utilisateur" => "Utilisateur"),
			      array("unblacklist" => "Débloquer"),
			      array("unblacklist" => "Débloquer"),
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
