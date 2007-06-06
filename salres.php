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
$topdir = "./";
 
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/sitebat.inc.php");
require_once($topdir. "include/entities/batiment.inc.php");
require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$site = new site ();
$user = new utilisateur($site->db);
$userop = new utilisateur($site->db);
$sitebat = new sitebat($site->db);
$bat = new batiment($site->db);
$salle = new salle($site->db);
$asso = new asso($site->db);
$resa = new reservation($site->db, $site->dbrw);

if ( isset($_REQUEST["id_salres"]) )
{
	$resa->load_by_id($_REQUEST["id_salres"]);
	if ( $resa->id < 1 )
	{
		header("Location: 404.php");
		exit();	
	}
	$asso->load_by_id($resa->id_asso);
	$can_edit = $site->user->is_in_group("gestion_ae") || ($resa->id_utilisateur == $site->user->id);

	if ( $asso->id > 0 )
		$can_edit = $can_edit || $asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);

}

if ( $_REQUEST["action"] == "delete" && $can_edit )
{
	$id_salle = $resa->id_salle;
	$resa->delete();
	header("Location: user/reservations.php");
	exit();
}

if ( $resa->id > 0 )
{
	$salle->load_by_id($resa->id_salle);
	$bat->load_by_id($salle->id_batiment);
	$sitebat->load_by_id($bat->id_site);
	$user->load_by_id($resa->id_utilisateur);
	$userop->load_by_id($resa->id_utilisateur_op);
	
	
	$site->start_page("services","Reservations de salle");
	
	$cts = new contents("Reservation n°".$resa->id);
	
	$tbl = new table("Informations");
	$tbl->add_row(array("Demande faite le ",date("d/m/Y H:i",$resa->date_demande)));
	$tbl->add_row(array("Période",date("d/m/Y H:i",$resa->date_debut)." au ".date("d/m/Y H:i",$resa->date_fin)));
	$tbl->add_row(array("Demandeur",classlink($user)));
	if ( $asso->id > 0 )
		$tbl->add_row(array("Association",classlink($asso)));
	$tbl->add_row(array("Convention de locaux requise",$salle->convention?"Oui":"Non"));
	$tbl->add_row(array("Convention de locaux faite",$resa->convention?"Oui":"Non"));
	if( $resa->date_accord )
		$tbl->add_row(array("Accord","le ".date("d/m/Y H:i",$resa->date_accord)." par ".classlink($userop)));
	$tbl->add_row(array("Salle",classlink($salle)));
	$tbl->add_row(array("Batiment",classlink($bat)));
	$tbl->add_row(array("Site",classlink($sitebat)));
	$tbl->add_row(array("Motif",htmlentities($resa->description,ENT_NOQUOTES,"UTF-8")));
	$tbl->add_row(array("Notes",htmlentities($resa->notes,ENT_NOQUOTES,"UTF-8")));
	$cts->add($tbl,true);
	
	
	if ( $can_edit )
		$cts->add_paragraph("<a href=\"reservation.php?id_salres=".$resa->id."&amp;action=delete\">Supprimer/Annuler</a>");
	
	$site->add_contents($cts);
	$site->end_page();
	exit();	

}

	header("Location: user/reservations.php");

?>