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
 
/** @file
 * @defgroup comptoirs Comptoirs/E-boutic
 * @{
 */ 
 
 
require_once($topdir."include/site.inc.php");

require_once("comptoir.inc.php");
require_once("cptasso.inc.php");
require_once("defines.inc.php");
require_once("facture.inc.php");
require_once("produit.inc.php");
require_once("typeproduit.inc.php");
require_once("venteproduit.inc.php");
require_once($topdir."include/entities/books.inc.php");

class sitecomptoirs extends site
{
	var $id_asso;
	var $nom_asso;
	var $id_classeur;
	var $nom_classeur;
	var $nom_cpbc;
	
	var $comptoir;
	
	
	var $admin_comptoirs;
	
	function sitecomptoirs ($modevente=false)
	{
		global $topdir;
		
		$this->site();
		$this->set_side_boxes("left",array("comptoir","connexion"));
		
		if ( $modevente )
		{
			$this->comptoir = new comptoir($this->db,$this->dbrw);
		}
	}

	function start_page ( $section, $title, $compact=false ) 
	{	
		global $topdir;

		
		parent::start_page("services",$title);
	}
	
	function fetch_admin_comptoirs()
	{
		$this->admin_comptoirs = array();
		
		if ( $this->user->is_in_group("gestion_ae") )
		  $req = new requete($this->db,"SELECT `id_comptoir`,`nom_cpt` FROM `cpt_comptoir`");
		else
		  $req = new requete($this->db,"SELECT `id_comptoir`,`nom_cpt`
				   FROM `cpt_comptoir` 
				   WHERE (`id_groupe` IN (".$this->user->get_groups_csv().") OR `id_assocpt` IN (".$this->user->get_assos_csv(4).") ) AND nom_cpt != 'test' ");
		
		while ( list($id,$nom) = ($row = $req->get_row()) )
			$this->admin_comptoirs[$id] = $nom;
		
	}

	function set_admin_mode()
	{
		if ( !isset($this->admin_comptoirs))
			$this->fetch_admin_comptoirs();
		
		$admcts = new contents("Comptoirs");
		
		$admcts->add_paragraph("<a href=\"index.php\">Comptoirs</a>");
		
		if ( $this->user->is_in_group("gestion_ae") )
		{
			$lst = new itemlist("Administration","boxlist");
			$lst->add("<a href=\"admin.php?page=addcomptoir\">Ajouter un comptoir</a>");
			$lst->add("<a href=\"admin.php?page=addasso\">Ajouter une association</a>");
			$lst->add("<a href=\"facture.php\">Génération des factures</a>");
			$admcts->add($lst,true, true, "gestbox", "boxlist", true, true);
		}
		
		$lst = new itemlist("Gestion des produits","boxlist");
		$lst->add("<a href=\"admin.php?page=addproduit\">Ajouter un produit</a>");
		$lst->add("<a href=\"admin.php?page=addtype\">Ajouter un type de produit</a>");
		$lst->add("<a href=\"admin.php?page=produits\">Liste des produits et des types de produits</a>");
		$admcts->add($lst,true, true, "prodbox", "boxlist", true, true);
		
		$lst = new itemlist("Gestion des comptoirs","boxlist");
		foreach( $this->admin_comptoirs as $id => $nom )
			$lst->add("<a href=\"admin.php?id_comptoir=$id\">".$nom."</a>");
		$admcts->add($lst,true, true, "cptbox", "boxlist", true, true);
		
		$lst = new itemlist("Comptabilité","boxlist");
		$lst->add("<a href=\"comptarech.php\">Rechargements</a>");
		
		foreach( $this->admin_comptoirs as $id => $nom )
			$lst->add("<a href=\"compta.php?id_comptoir=$id\">".$nom."</a>");
		$admcts->add($lst,true, true, "cptabox", "boxlist", true, true);
		
		$this->add_box("comptoir",$admcts);	

	}
	
	
	
}



/**@}*/

?>
