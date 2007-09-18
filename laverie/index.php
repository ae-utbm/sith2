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
require_once($topdir. "include/entities/planning.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "comptoir/include/comptoirs.inc.php");
require_once($topdir. "comptoir/include/venteproduit.inc.php");

$site = new sitelaverie ();

if ( !$site->user->is_valid() )
	error_403();

$site->start_page("none","Laverie");
$cts = new contents("Machines à laver de l'AE");

if ( !$site->user->is_in_group("blacklist_machines") )
{
	$site->user_is_admin();

	if ( $site->is_admin )
		$site->set_admin_mode();
	
	if ( $_REQUEST['view'] == "retour" )
	{
		if ( !$site->is_admin )
			error_403();

		$frm = new form("retourjetons","index.php?view=retour",false,"POST","Retour jetons");
		$lst = new itemlist("Résultats :");
		
		if($_REQUEST['action'] == "retourner")
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
				$jeton->given_back ();
				$lst->add("Le jeton $jeton->nom a bien été rendu.", "ok");
			}
		}
		
		/* Liste des jetons empruntés */
		$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton, 
			mc_jeton_utilisateur.id_utilisateur, 
			mc_jeton_utilisateur.prise_jeton,
			mc_jeton_utilisateur.penalite,
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
			"duree" => "Depuis (jours)",
			"penalite" => "Pénalité"
			), 
			array("retourner" => "Retourner"), array("retourner" => "Retourner"), 
			array("penalite" => array('0' => "Non", '1' => "Oui") )
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
				array("type_jeton"=>$GLOBALS['types_jeton']) );

		$cts->add($table, true);	

	}
	elseif($_REQUEST['view']=="mauvais")
	{
		if ( !$site->is_admin )
			error_403();

		$lst = new itemlist("Résultats :");

		if(!empty($_REQUEST['id_utilisateur']))
			$ids[] = $_REQUEST['id_utilisateur'];
		elseif($_REQUEST['id_utilisateurs'])
		{
			foreach ($_REQUEST['id_utilisateurs'] as $id_util)
			$ids[] = $id_util;
		}


		if($_REQUEST['action'] == "blacklist")
		{
			foreach ( $ids as $id )
			{
				$user = new utilisateur($site->db, $site->dbrw);
				$user->load_by_id($id);

				$user->add_to_group(GRP_BLACKLIST);
				$lst->add("L'utilisateur $user->prenom $user->nom a bien été banni de l'usage des machines", "ok");
			}
		}

		if($_REQUEST['action'] == "unblacklist")
		{
			foreach ( $ids as $id )
			{
				$user = new utilisateur($site->db, $site->dbrw);
				$user->load_by_id($id);

				$user->remove_from_group(GRP_BLACKLIST);
				$lst->add("L'utilisateur $user->prenom $user->nom a bien été débanni de l'usage des machines", "ok");
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
				WHERE `id_utilisateur` = $id AND mc_jeton_utilisateur.retour_jeton IS NULL");
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
						"From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae@utbm.fr");
				if ($mail)
		$lst->add("Mail de rappel &agrave; " .$user->prenom. " " .$user->nom. " : Envoy&eacute;","ok");	
				else
		$lst->add("Erreur lors de l'envoi du mail de rappel pour " . $user->prenom . " " . $user->nom ." !","ko");
				
			}
		}

		$cts->add($lst);

		/* Liste des mauvais clients */
		$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton,
			mc_jeton_utilisateur.id_utilisateur,
			mc_jeton_utilisateur.retour_jeton,
			COUNT(id_jeton) AS nombre,
			utilisateurs.nom_utl, 
			utilisateurs.prenom_utl, 
			utilisateurs.id_utilisateur,
			CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur,
			DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree
			FROM mc_jeton_utilisateur
			LEFT JOIN utilisateurs 
			ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
			WHERE mc_jeton_utilisateur.retour_jeton IS NULL
			AND `retour_jeton` IS NULL
			AND (DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) > 10)
			GROUP BY mc_jeton_utilisateur.id_utilisateur
			ORDER BY nombre DESC");


		$table = new sqltable("toploosers",
		"Top des mauvais clients (jetons non rendus depuis plus de 10 jours)",
		$sql,
		"index.php?view=mauvais",
		"id_utilisateur",
		array(
			"nom_utilisateur"=>"Utilisateur",
			"nombre" => "Nombre",
			"duree" => "Depuis (jours)"
		),
		array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
		array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
		array() );

		$cts->add($table, true);

		$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur, 
			CONCAT(utilisateurs.prenom_utl,' ', utilisateurs.nom_utl) AS nom_utilisateur
			FROM utl_groupe INNER JOIN utilisateurs ON utilisateurs.id_utilisateur = utl_groupe.id_utilisateur 
			WHERE utl_groupe.id_groupe = 29 
			ORDER BY utilisateurs.nom_utl, utilisateurs.prenom_utl");

		$table = new sqltable("blackmember", 
				"Liste des personnes bloquées",
				$sql,
				"index.php?view=mauvais",
				"id_utilisateur",
				array("nom_utilisateur" => "Utilisateur"),
				array("unblacklist" => "Débloquer"),
				array("unblacklist" => "Débloquer"),
				array()	);

		$cts->add($table, true);

	}
	elseif ( $_REQUEST['view'] == "machines" )
	{
		if ( !$site->is_admin )
			error_403();

		$lst = new itemlist("Resultats :");

		if(!empty($_REQUEST['id']))
			$ids[] = $_REQUEST['id'];
		elseif($_REQUEST['ids'])
		{
			foreach ($_REQUEST['ids'] as $id_machine)
			$ids[] = $id_machine;
		}

		if($_REQUEST['action'] == "hs")
		{
			foreach ( $ids as $id )
			{
				$sql = new requete($site->dbrw, "UPDATE mc_machines 
					SET mc_machines.hs = 1
					WHERE mc_machines.id = $id");

				$lst->add("La machine $id a bien été mise hors service","ok");
			}
			/* Cloturer le planning de la machine
			 * Champ 'name' du planning = 'id' de la machine (pas la lettre) */

		}
	
		if($_REQUEST['action'] == "es")
		{
			foreach ( $ids as $id )
			{
				$sql = new requete($site->dbrw, "UPDATE mc_machines 
					SET mc_machines.hs = 0
					WHERE mc_machines.id = $id");

				$lst->add("La machine $id a bien été mise en service","ok");
			}
			/* Créer un nouveau planning pour la machine 
			 * Champ 'name' du planning = 'id' de la machine (pas la lettre) */
		}

		if($_REQUEST['action'] == "supprimer")
		{
			foreach ( $ids as $id )
			{
				$sql = new requete($site->dbrw, "DELETE FROM mc_machines 
					WHERE mc_machines.id = $id");

				$lst->add("La machine $id  a bien été supprimée","ok");
			}
			/* Cloturer le planning de la machine
			 * Champ 'name' du planning = 'id' de la machine (pas la lettre) */

		}

		if (!empty($_REQUEST["lettre_machine"]) )
		{
			$sql = new insert ($site->dbrw,
				"mc_machines",
			   array(
				  "lettre" => $_REQUEST['lettre_machine'],
			    "type" => $_REQUEST['typemachine'],
			    "loc" => $_REQUEST['locmachine']) );
		}
		
		$frm = new form("ajoutmachine", "index.php?view=machines", false, "POST", "Ajouter une machine");

		$frm->add_text_field("lettre_machine", "Lettre de la machine :");
		$frm->add_select_field("typemachine", "Type de la machine :", $GLOBALS['types_jeton']);
		$frm->add_select_field("locmachine", "Salle concernée :", $GLOBALS['salles_jeton']);
		$frm->add_submit("valid","Valider");
		$frm->allow_only_one_usage();
		$cts->add($lst);
		$cts->add($frm,true);

		/* Liste des machines */
		$sql = new requete($site->db, "SELECT * FROM mc_machines
			INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
			WHERE mc_machines.hs = 0
			ORDER BY mc_machines.lettre,mc_machines.type");

		$table = new sqltable("listmachinesok",
			"Liste des machines en service",
			$sql,
			"index.php?view=machines",
			"id",
			array("lettre" => "Lettre",
				"type" => "Type de la machine",
				"nom_lieu" => "Lieu"),
			array("hs" => "Hors service",
				"supprimer" => "Supprimer"),
			array("hs" => "Hors service",
			  "supprimer" => "Supprimer"),
			array("type"=>$GLOBALS['types_jeton'] ) );

		$cts->add($table, true);

		$sql = new requete($site->db, "SELECT * FROM mc_machines
			INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
			WHERE mc_machines.hs = 1
			ORDER BY mc_machines.lettre,mc_machines.type");

		$table = new sqltable("listmachineshs",
			"Liste des machines hors service",
			$sql,
			"index.php?view=machines",
			"id",
			array("lettre" => "Lettre",
				"type" => "Type de la machine",
				"nom_lieu" => "Lieu"),
			array("es" => "En service",
				"supprimer" => "Supprimer"),
			array("es" => "En service",
			  "supprimer" => "Supprimer"),
			array("type"=>$GLOBALS['types_jeton'] ) );

		$cts->add($table, true);
	}
	elseif( $_REQUEST['view'] == "plannings" )
	{
		if ( !$site->is_admin )
			error_403();

		$now = date("Y-m-d H:i:s",time());

		$date = getDate();
            
		if($date['wday'] == 0)
		  $days_left = 0; 
		else
		  $days_left = 7 - $date['wday'];

		$current_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left, $date['year']);

		$next_week_start = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +1, $date['year']);

		$next_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +8, $date['year']);

		$lst = new itemlist("Resultats :");

		if($_REQUEST['action'] == "creneaux")
		{
			$sql = new requete($site->db, "SELECT *,
				CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur,
				pl_gap.id_gap AS id_gap
				FROM pl_gap
				LEFT JOIN pl_gap_user ON pl_gap_user.id_gap = pl_gap.id_gap
				LEFT JOIN utilisateurs ON pl_gap_user.id_utilisateur = utilisateurs.id_utilisateur
			 	WHERE pl_gap.id_planning = '".$_REQUEST['id_planning']."'
				ORDER BY pl_gap.start_gap");

			$table = new sqltable("listecreneaux",
				"Liste des créneaux",
				$sql,
				"index.php?view=plannings&id_planning=".$_REQUEST['id_planning'],
				"id_gap",
				array(
					"start_gap" => "Début",
					"end_gap" => "Fin",
					"nom_utilisateur" => "Réservé par"
				),
				array("modifier_reservation"=> "Modifier la réservation"),
				array() );

			$cts->add($table, true);
		}
		elseif($_REQUEST['action'] == "modifier_reservation")
		{
			$sql = new requete($site->db, "SELECT *,
				pl_gap_user.id_utilisateur AS id_util_old
				FROM pl_gap
				LEFT JOIN pl_gap_user ON pl_gap.id_gap = pl_gap_user.id_gap
				WHERE pl_gap.id_gap = ".$_REQUEST['id_gap']);
			$row = $sql->get_row();

			$cts->add_paragraph("<strong>Début du créneau :</strong> ".$row['start_gap']);
			$cts->add_paragraph("<strong>Fin du créneau :</strong> ".$row['end_gap']);
			/* Faire fonctionner l'autorefill (pas franchement urgent) */
			$frm = new form("modifierreservation","index.php?view=plannings&action=do_modifier_reservation",true,"POST","Modifier une réservation");
			$frm->add_user_fieldv2("id_util","Réservé par",$row['id_util_old'],true);
			$frm->add_hidden("id_old",$row['id_util_old']);
			$frm->add_hidden("id_gap",$_REQUEST['id_gap']);
			$frm->add_hidden("id_planning",$_REQUEST['id_planning']);
			$frm->add_submit("valid","Valider");
			$frm->allow_only_one_usage();
			$cts->add($frm,true);
		}
		elseif($_REQUEST['action'] == "do_modifier_reservation")
		{
			$planning = new planning($site->db,$site->dbrw);
			$planning->load_by_id($_REQUEST['id_planning']);
			if($_REQUEST['id_old'] != NULL)
				$planning->remove_user_from_gap($_REQUEST['id_gap'],$_REQUEST['id_old']);
			if($_REQUEST['id_util'] != 0)
				$planning->add_user_to_gap($_REQUEST['id_gap'],$_REQUEST['id_util']);
			header( 'Location: index.php?view=plannings&id_planning='.$_REQUEST['id_planning'].'&action=creneaux');
		}
		elseif($_REQUEST['action'] == "creer_planning")
		{
			if($_REQUEST['date_fin'] <= $next_week_end)
			{
				$planning = new planning($site->db,$site->dbrw);
				$planning->add(ID_ASSO_LAVERIE,$_REQUEST['id'],'1',$_REQUEST['date_debut'],$_REQUEST['date_fin'],'0');
			
				$date_temp_start = $planning->start_date;
				while ($date_temp_start <= $planning->end_date - 3600)
				{
					$date_temp_end = $date_temp_start + 3600;
					$planning->add_gap(date("Y-m-d H:i:s",$date_temp_start),date("Y-m-d H:i:s",$date_temp_end));
					$date_temp_start = $date_temp_end;
				}
				header( 'Location: index.php?view=plannings' );
			}
			else
			{
				header( 'Location: index.php?view=plannings&id='.$_REQUEST['id'].'&action=ajouter_planning' );
			}
		}
		elseif($_REQUEST['action'] == "ajouter_planning")
		{
			$sql = new requete($site->db, "SELECT * FROM mc_machines
				WHERE mc_machines.id = ".$_REQUEST['id']);
			$row = $sql->get_row();

			$lieu = new lieu($site->db);
			$lieu->load_by_id($row['loc']);
			
			$frm = new form("ajoutplanning", "index.php?view=plannings&action=creer_planning",false,"POST","Ajouter un planning");
			$cts->add_paragraph("<strong>Lieu :</strong> ".$lieu->get_html_link());
			$cts->add_paragraph("<strong>Type de machine :</strong> machine à ".$row['type']);
			$cts->add_paragraph("<strong>Identifiant :</strong> ".$row['lettre']);
			$cts->add_paragraph("Vous ne pouvez ajouter un planning que pour la semaine en cours et la semaine suivante.");
			$frm->add_datetime_field("date_debut","Date de début",$next_week_start);
			$frm->add_datetime_field("date_fin","Date de fin",$next_week_end);
			$frm->add_hidden("id",$_REQUEST['id']);
			$frm->add_submit("valid","Valider");
			$frm->allow_only_one_usage();
			$cts->add($frm,true);
		}
		else
		{
			if($_REQUEST['action'] == "supprimer")
			{
				$planning = new planning($site->db,$site->dbrw);
				$planning->load_by_id( $_REQUEST['id_planning'] );
				$planning->remove();
				$lst->add("Le planning a bien été supprimé");
			}
	
			$cts->add($lst);

			$sql = new requete($site->db, "SELECT * FROM pl_planning
				INNER JOIN mc_machines ON pl_planning.name_planning = mc_machines.id
				INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
				WHERE pl_planning.id_asso = '".ID_ASSO_LAVERIE."'
				AND pl_planning.end_date_planning > '".$now."'
				ORDER BY pl_planning.start_date_planning,mc_machines.lettre, mc_machines.type");

			$table = new sqltable("listeplannings",
				"Liste des plannings",
				$sql,
				"index.php?view=plannings",
				"id_planning",
				array(
					"lettre" => "Lettre",
					"type" => "Type",
					"nom_lieu" => "Lieu",
					"start_date_planning" => "Début",
					"end_date_planning" => "Fin"),
				array("creneaux" => "Voir les créneaux","supprimer" => "Supprimer"),
				array(),
				array("type" => $GLOBALS['types_jeton']) );

			$cts->add($table, true);
	
			$sql = new requete($site->db, "SELECT * FROM mc_machines
				INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
				WHERE mc_machines.hs = 0
				ORDER BY mc_machines.lettre,mc_machines.type");

			$table = new sqltable("listmachine",
				"Liste des machines en service",
				$sql,
				"index.php?view=plannings",
				"id",
				array("lettre" => "Lettre",
					"type" => "Type de la machine",
					"nom_lieu" => "Lieu"),
				array("ajouter_planning" => "Ajouter un planning"),
				array(),
				array("type"=>$GLOBALS['types_jeton'] ) );

			$cts->add($table, true);
		}
	}
	elseif( $_REQUEST['view'] == "reserver" )
	{
		if($_REQUEST['action'] == "choisir_machine")
		{
			$sql = new requete($site->db, "SELECT *,
				pl_gap.id_gap AS id
				FROM pl_gap
				INNER JOIN pl_planning ON pl_gap.id_planning = pl_planning.id_planning
				INNER JOIN mc_machines ON pl_planning.name_planning = mc_machines.id
				LEFT JOIN pl_gap_user ON pl_gap.id_gap = pl_gap_user.id_gap
				INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
				WHERE pl_planning.id_asso = '".ID_ASSO_LAVERIE."'
				AND pl_gap.start_gap > '".$now."'
				AND pl_gap_user.id_utilisateur IS NULL
				AND mc_machines.id = '".$_REQUEST['id']."'
				ORDER BY mc_machines.lettre,mc_machines.type,pl_gap.start_gap");

			$table = new sqltable("listecreneauxdispo",
				"Liste des créneaux disponibles",
				$sql,
				"index.php?view=reserver",
				"id",
				array("start_gap" => "Début du créneau",
					"end_gap" => "Fin du créneau"),
				array("choisir_creneau" => "Choisir ce créneau"),
				array(),
				array() );

			$cts->add($table, true);
		}	
		elseif($_REQUEST['action'] == "choisir_creneau")
		{
			$frm = new form("reservation","index.php?view=reserver&action=fin",false,"POST","Terminer la réservation");
			if($site->is_admin)
				$frm->add_user_fieldv2("id_util","Réservé pour");
			else
				$frm->add_hidden("id_util",$site->user->id);
			
			$frm->add_hidden("id_gap",$_REQUEST['id']);
			$frm->add_submit("valid","Valider");
			/* Ajouter un bouton d'annulation et un truc boulet proof en js pour forcer à remplir la case */
			$frm->allow_only_one_usage();
			$cts->add($frm,true);
		}
		elseif($_REQUEST['action'] == "fin")
		{
			$sql = new requete($site->db, "SELECT id_planning FROM pl_gap
				WHERE id_gap = '".$_REQUEST['id_gap']."'");
			$row = $sql->get_row();

			$planning = new planning($site->db,$site->dbrw);
			$planning->load_by_id($row['id_planning']);
			$planning->add_user_to_gap($_REQUEST['id_gap'],$_REQUEST['id_util']);
			header( 'Location: index.php' );
		}
		else
		{
			$sql = new requete($site->db, "SELECT * FROM mc_machines
				INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
				WHERE mc_machines.hs = 0
				ORDER BY mc_machines.lettre,mc_machines.type");

			$table = new sqltable("listmachine",
				"Liste des machines",
				$sql,
				"index.php?view=reserver",
				"id",
				array("lettre" => "Lettre",
					"type" => "Type de la machine",
					"nom_lieu" => "Lieu"),
				array("choisir_machine" => "Choisir cette machine"),
				array(),
				array("type"=>$GLOBALS['types_jeton'] ) );

			$cts->add($table, true);
		}
	}
	elseif( $_REQUEST['view'] == "vente" )
	{
		/* Interface administrateur de retrait d'un jeton et assignation du 
		 * retrait du jeton à un créneaux emploi du temps */

		if( $_REQUEST['action'] == "retirer_jeton" )
		{
		  $lst = new itemlist("Résultats :");

		  /* execution de la demande */
		  if (isset($_REQUEST["magicform"]) && $_REQUEST["magicform"]["name"] == "retirer_jeton")
		  {
		    $utl = new utilisateur($site->db, $site->dbrw);
		    $utl->load_by_id($_REQUEST['id_util']);
	    
		    if($utl->id == -1)
		      $error = "Utilisateur inconnu";
		
		    elseif($utl->is_in_group("cpt_bloque"))
		      $error = "Le compte de cet utilisateur est bloqué !";
	    
		    elseif($utl->is_in_group("blacklist_machines"))
		      $error = "Cet utilisateur n'est pas autorisé à emprunter de jeton !";
	
		    elseif( !$utl->ae )
		      $error = "Cotisation non renouvelée.";
		
		    if(!empty($error))
		      $lst->add($error, "ko");
		    else
		    {
		      if(!empty($_REQUEST['numjetlaver']))
					{
					  $jetlav = new jeton($site->db, $site->dbrw);
					  $jetlav->load_by_nom($_REQUEST['numjetlaver'], "laver");
	  
					  if($jetlav->id == -1)
					    $error = "Le jeton de machine à laver est invalide";
					  elseif($jetlav->is_borrowed())
					    $error = "Le jeton de machine à laver ($jetlav->nom) est censé etre emprunté, comment ce fait-ce ?";
					}			
		      if(!empty($_REQUEST['numjetsecher']))
					{
					  $jetsech = new jeton($site->db, $site->dbrw);
					  $jetsech->load_by_nom($_REQUEST['numjetsecher'], "secher");

				  if($jetsech->id == -1)
				    $error = "Le jeton de seche-linge est invalide";      
				  elseif($jetsech->is_borrowed())
				    $error = "Le jeton de seche-linge ($jetsech->nom) est censé etre emprunté, comment ce fait-ce ?";
					}		

		      if(!empty($error))
		        $cts->add_paragraph("Erreur : $error");
		      else
		      {
	          $cpt = new comptoir ($site->db, $site->dbrw);
	          $cpt->load_by_id (CPT_MACHINES);
					  $caddie = array();
	  
					  if($jetlav)
				    {
				      $vente_lav = new venteproduit($site->db, $site->dbrw);
				      $vente_lav->load_by_id(JET_LAVAGE, CPT_MACHINES);
				      $caddie[] = array(1, $vente_lav);
				    }
					  if($jetsech)
				    {
				      $vente_sech = new venteproduit($site->db, $site->dbrw);
				      $vente_sech->load_by_id(JET_SECHAGE, CPT_MACHINES);
				      $caddie[] = array(1, $vente_sech);
				    }
  
					  if($caddie)
				    {
				      $debit = new debitfacture($site->db, $site->dbrw);
				      $ok = $debit->debitAE($utl, $site->user, $cpt, $caddie, false);
				    }

          
		        if ( !$ok )
		          $lst->add("Solde insuffisant", "ko");
		        else
		        {
						  if($jetlav)
					    {
					      $jetlav->borrow_to_user($utl->id,$_REQUEST['id_gap']);
					      $lst->add("Le jeton n°$jetlav->nom (lavage) a bien ete prêté à $utl->prenom $utl->nom", "ok");
					    }
						  if($jetsech)
					    {
					      $jetsech->borrow_to_user($utl->id,$_REQUEST['id_gap']);
					      $lst->add("Le jeton n°$jetsech->nom (séchage) a bien ete prêté à $utl->prenom $utl->nom", "ok");
					    }
	       	 	}
     		 }	
    	}
  	}
			/* Donnée : gap_id
			   Récupérer l'id de l'utilisateur ayant fait la réservation
				 Affichage interface avec : nom du gugus, machine, lieu, horraires...
				 Entrer le numéro du jeton
				 Faire les vérifications
			*/
			$sql = new requete($site->db,"SELECT
				pl_gap_user.id_utilisateur AS id_util,
				CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur
				FROM pl_gap_user
				LEFT JOIN utilisateurs ON pl_gap_user.id_utilisateur = utilisateurs.id_utilisateur
				WHERE pl_gap_user.id_gap = '".$_REQUEST['id']."'");
			$row = $sql->get_row();

			$frm = new form("retirer_jeton","index.php?view=vente&action=valider",false,"POST","Retirer un jeton");
			$frm->add_hidden("id_gap",$_REQUEST['id']);
			$frm->add_hidden("id_util",$row['id_util']);
			$frm->add_text_field("numjetlaver","Numéro de jeton de lavage"));
			$frm->add_info("ou");
			$frm->add_text_field("numjetsecher","Numéro de jeton séchage");
			$frm->add_submit("valid","Valider");
			$frm->allow_only_one_usage();
			$cts->add($lst);
			$cts->add($frm,true);
		}
		else
		{
			$sql = new requete($site->db,"SELECT *,
				pl_gap.id_gap AS id,
				CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur
				FROM pl_gap
				INNER JOIN pl_planning ON pl_gap.id_planning = pl_planning.id_planning
				INNER JOIN mc_machines ON pl_planning.name_planning = mc_machines.id
				LEFT JOIN pl_gap_user ON pl_gap.id_gap = pl_gap_user.id_gap
				INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
				LEFT JOIN utilisateurs ON pl_gap_user.id_utilisateur = utilisateurs.id_utilisateur
				WHERE pl_planning.id_asso = '".ID_ASSO_LAVERIE."'
				AND pl_gap_user.id_utilisateur IS NOT NULL
				ORDER BY pl_gap.start_gap");

			$table = new sqltable("listecreneauxutil",
				"Liste des créneaux réservés",
				$sql,
				"index.php",
				"id",
				array("start_gap" => "Début du créneau",
				  "end_gap" => "Fin du créneau",
					"lettre" => "Lettre",
					"type" => "Type de la machine",
					"nom_lieu" => "Lieu",
					"nom_utilisateur" => "Réservé par"),
				array("retirer_jeton" => "Retirer le jeton"),
				array(),
				array("type"=>$GLOBALS['types_jeton']) );
		}

		$cts->add($table, true);

	}
	elseif( $_REQUEST['view'] == "recharger" || $_REQUEST['view'] == "cotiser" || $_REQUEST['view'] == "ajouter_util" )
	{
		$cts->add_paragraph("Cette fonctionnalité n'est pas encore en service.");
		$cts->add_paragraph("Veuillez nous excuser pour la gêne occasionnée.");
	}
	else
	{
		if($_REQUEST['action'] == "supprimer_reservation")
		{
			$sql = new requete($site->db, "SELECT id_planning FROM pl_gap
				WHERE id_gap = '".$_REQUEST['id']."'");
			$row = $sql->get_row();
			$planning = new planning($site->db,$site->dbrw);
			$planning->load_by_id($row['id_planning']);
			$planning->remove_user_from_gap($_REQUEST['id'],$site->user->id);
		}
		$list = new itemlist("Actions disponibles",false,array(
			"<a href=\"index.php?view=reserver\">Réserver un créneau</a>"
			));
		$cts->add($list,true);

		$sql = new requete($site->db,"SELECT *,
			pl_gap.id_gap AS id
			FROM pl_gap
			INNER JOIN pl_planning ON pl_gap.id_planning = pl_planning.id_planning
			INNER JOIN mc_machines ON pl_planning.name_planning = mc_machines.id
			LEFT JOIN pl_gap_user ON pl_gap.id_gap = pl_gap_user.id_gap
			INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
			WHERE pl_planning.id_asso = '".ID_ASSO_LAVERIE."'
			AND pl_gap_user.id_utilisateur = ".$site->user->id."
			ORDER BY pl_gap.start_gap");

		$table = new sqltable("listecreneauxutil",
			"Liste des créneaux réservés",
			$sql,
			"index.php",
			"id",
			array("start_gap" => "Début du créneau",
			  "end_gap" => "Fin du créneau",
				"lettre" => "Lettre",
				"type" => "Type de la machine",
				"nom_lieu" => "Lieu"),
			array("supprimer_reservation" => "Supprimer ma réservation"),
			array(),
			array("type"=>$GLOBALS['types_jeton']) );

		$cts->add($table, true);
	}
}
else
{
	$cts->add_paragraph("Vous n'avez pas le droit d'utiliser les machines de l'AE, car vous n'avez pas respecté les conditions d'utilisation.");

	$sql = new requete($site->db, "SELECT 
		`mc_jeton_utilisateur`.`id_jeton`
		, `mc_jeton`.`nom_jeton`
		, DATEDIFF(CURDATE(), `mc_jeton_utilisateur`.`prise_jeton`) AS `duree` 
		FROM `mc_jeton` 
		INNER JOIN `mc_jeton_utilisateur` ON `mc_jeton`.`id_jeton` = `mc_jeton_utilisateur`.`id_jeton` 
		WHERE `id_utilisateur` = ".$site->user->id." AND mc_jeton_utilisateur.retour_jeton IS NULL");

	if ($sql->lines >= 1)
	{
		$jetons = array();
	
		while ($row = $sql->get_row())
			array_push($jetons,"Jeton ".$row['nom_jeton'].", emprunté depuis ".$row['duree']." jours.");
	
		$list = new itemlist("Vous devez rendre les jetons suivants :",false,$jetons);

		$cts->add($list,true); 
	}
}

$site->add_contents($cts);
$site->end_page();  

?>
