<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet at oxynux dot org >
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
 
require_once($topdir."include/site.inc.php");

class sitelaverie extends site
{
	var $is_admin;
	
	function sitelaverie()
	{
		global $topdir;
		
		$this->site();
		$this->set_side_boxes("left",array("laverie","connexion"));
	}

	function start_page ( $section, $title, $compact=false ) 
	{	
		global $topdir;

		
		parent::start_page("services",$title);
	}
	
	function is_user_admin()
	{
		$this->gestion_laverie = array();
		
		if ( $this->user->is_in_group("gestion_ae") OR $this->user->is_in_group("gestion_machines" ) )
		  $this->user_is_admin = true;
	}

	function get_rights($public=false)
	{
		$site->is_user_admin();

		if ( $site->user_is_admin )
		{
			$admcts = new contents("Laverie");
		
			$admcts->add_paragraph("<a href=\"index.php\">Laverie</a>");
		
			$lst_taches = new itemlist("Tâches usuelles","boxlist");
			$lst_taches->add("<a href=\"index.php?view=reserver\">Réserver un créneau</a>");
			$lst_taches->add("<a href=\"index.php?view=vente\">Retirer des jeton</a>");
			$lst_taches->add("<a href=\"index.php?view=retour\">Restituer des jetons</a>");
			$lst_admin = new itemlist("Administration","boxlist");
			$lst_admin->add("<a href=\"index.php?view=machines\">Gestion des machines</a>");
			$lst_admin->add("<a href=\"index.php?view=plannings\">Gestion des plannings</a>");
			$lst_admin->add("<a href=\"inventaire.php\">Inventaire des jetons</a>");
			$lst_util = new itemlist("Utilisateurs","boxlist");
			$lst_util->add("<a href=\"index.php?view=mauvais\">Mauvais clients</a>");
			$lst_util->add("<a href=\"index.php?view=recharger\">Rechargement</a>");
			$lst_util->add("<a href=\"index.php?view=cotiser\">Nouvelle cotisation</a>");
			$lst_util->add("<a href=\"index.php?view=ajouter_util\">Ajouter un utilisateur</a>");

			$admcts->add($lst_taches,true, true, "tachesbox", "boxlist", true, true);
			$admcts->add($lst_admin,true, true, "adminbox", "boxlist", true, true);
			$admcts->add($lst_util,true, true, "utilbox", "boxlist", true, true);
		
			$this->add_box("laverie",$admcts);
		}
		elseif ( $site->user->is_in_group("blacklist_machines") )
		{
		  $cts->add_paragraph("Vous n'avez pas le droit d'utiliser les machines de l'AE, car vous n'avez pas respecté les conditions d'utilisation.");
			/* Remplacer cette "chose" par une fonction dans la classe jeton */
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
			$site->page_end();
			exit();
		}
		elseif ( !$public )
		{
			$site->error_forbidden("none","group",30);
		}
	}
}

?>
