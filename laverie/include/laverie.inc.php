<?php

/* Copyright 2006
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
	
	function user_is_admin()
	{
		$this->gestion_laverie = array();
		
		if ( $this->user->is_in_group("gestion_ae") OR $this->user->is_in_group("gestion_machines" ) )
		  $this->is_admin = true;
	}

	function set_admin_mode()
	{
		if ( !isset($this->is_admin) )
			$this->user_is_admin();
		
		$admcts = new contents("Laverie");
		
		$admcts->add_paragraph("<a href=\"index.php\">Laverie</a>");
		
		if ( $this->is_admin )
		{
			$lst_taches = new itemlist("Tâches usuelles","boxlist");
			$lst_taches->add("<a href=\"index.php?action=reserver\">Réserver un créneau</a>");
			$lst_taches->add("<a href=\"index.php?action=vente\">Retirer un jeton</a>");
			$lst_admin = new itemlist("Administration","boxlist");
			$lst_admin->add("<a href=\"index.php?action=machines\">Gestion des machines</a>");
			$lst_admin->add("<a href=\"index.php?action=crenaux\">Gestion des créneaux</a>");
			$lst_admin->add("<a href=\"index.php?action=inventaire\">Inventaire des jetons</a>");
			$lst_util = new itemlist("Utilisateurs","boxlist");
			$lst_util->add("<a href=\"index.php?action=recharger\">Rechargement</a>");
			$lst_util->add("<a href=\"index.php?action=cotiser\">Nouvelle cotisation</a>");
			$lst_util->add("<a href=\"index.php?action=ajouter_util\">Ajouter un utilisateur</a>");

			$admcts->add($lst_taches,true, true, "tachesbox", "boxlist", true, true);
			$admcts->add($lst_admin,true, true, "adminbox", "boxlist", true, true);
			$admcts->add($lst_util,true, true, "utilbox", "boxlist", true, true);
		}
		
		$this->add_box("laverie",$admcts);	
	}
}

?>
