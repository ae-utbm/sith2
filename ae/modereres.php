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
 
$topdir = "../";
require_once($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/sitebat.inc.php");
require_once($topdir. "include/entities/batiment.inc.php");
require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/planning.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("foyer_admin") )
	error_403();

$resa = new reservation($site->db, $site->dbrw);

if ( isset($_REQUEST['id_salres']))
	$resa->load_by_id($_REQUEST['id_salres']);

if ( $_REQUEST["action"] == "accord" && $resa->id > 0 )
	$resa->accord($site->user->id);
elseif ( $_REQUEST["action"] == "delete" && $resa->id > 0 )
	$resa->delete();
elseif ( $_REQUEST["action"] == "convention" && $resa->id > 0 )
	$resa->convention_done();
elseif ( $_REQUEST["action"] == "accords" )
{
	foreach ( $_REQUEST["id_salress"] as $id_salres )
	{
		$resa->load_by_id($id_salres);
		if( $resa->id > 0 )
			$resa->accord($site->user->id);
	}	
}
elseif ( $_REQUEST["action"] == "conventions" )
{
	foreach ( $_REQUEST["id_salress"] as $id_salres )
	{
		$resa->load_by_id($id_salres);
		if( $resa->id > 0 )
			$resa->convention_done();
	}	
}
elseif ( $_REQUEST["action"] == "deletes" )
{
	foreach ( $_REQUEST["id_salress"] as $id_salres )
	{
		$resa->load_by_id($id_salres);
		if( $resa->id > 0 )
			$resa->delete();
	}	
}
elseif ( $_REQUEST["action"] == "info")
{
	$user = new utilisateur($site->db);
	$userop = new utilisateur($site->db);
	$sitebat = new sitebat($site->db);
	$bat = new batiment($site->db);
	$salle = new salle($site->db);
	$asso = new asso($site->db);
	
	$salle->load_by_id($resa->id_salle);
	$bat->load_by_id($salle->id_batiment);
	$sitebat->load_by_id($bat->id_site);
	$user->load_by_id($resa->id_utilisateur);
	$userop->load_by_id($resa->id_utilisateur_op);
	$asso->load_by_id($resa->id_asso);
	
	if (isset($_REQUEST["notes"]))
	{
		$resa->set_notes($_REQUEST["notes"]);
	}
	
	$site->start_page("none","Moderation des reservations de salle");
	
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
	$cts->add($tbl,true);
	
	$frm = new form("notes","modereres.php?id_salres=".$resa->id."&action=info", false,"POST","Notes");
	$frm->add_text_area("notes","Notes",$resa->notes,40,4);
	$frm->add_submit("valid","Enregistrer");
	$cts->add($frm,true);
	
	
	$lst = new itemlist("Opérations");
	
	if( !$resa->date_accord )
		$lst->add("<a href=\"modereres.php?id_salres=".$resa->id."&action=accord\">Accord</a>");
	
	if ( !$resa->convention && $salle->convention )
		$lst->add("<a href=\"modereres.php?id_salres=".$resa->id."&action=convention\">Convention faite</a>");
	
	$lst->add("<a href=\"modereres.php?id_salres=".$resa->id."&action=delete\">Refuser/Supprimer</a>");
	$lst->add("<a href=\"modereres.php\">Retour modération</a>");
	
	$cts->add($lst,true);
	
	$site->add_contents($cts);
	$site->end_page();
	exit();	
}

$site->start_page("none","Moderation des reservations de salle");



$req = new requete($site->db,"SELECT `utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		"sl_salle.id_salle, sl_salle.nom_salle," .
		"asso.id_asso, asso.nom_asso," .
		"sl_reservation.id_salres,  sl_reservation.date_debut_salres," .
		"sl_reservation.date_fin_salres, sl_reservation.description_salres, " .
		"sl_reservation.date_accord_res," .
		"(sl_reservation.convention_salres*10+sl_salle.convention_salle) as `convention` " .
		"FROM sl_reservation " .
		"INNER JOIN utilisateurs ON `utilisateurs`.`id_utilisateur`=sl_reservation.id_utilisateur " .
		"INNER JOIN sl_salle ON sl_salle.id_salle=sl_reservation.id_salle " .
		"LEFT JOIN asso ON asso.id_asso=sl_reservation.id_asso " .
		"WHERE ((sl_reservation.date_accord_res IS NULL) OR " .
		"(sl_salle.convention_salle=1 AND sl_reservation.convention_salres=0)) " .
		"AND sl_reservation.date_debut_salres > NOW()");

if($site->user->is_in_group("foyer_admin"))
{
	$site->add_contents(new sqltable(
			"modereres", 
			"Demandes de reservation", $req, "modereres.php", 
			"id_salres", 
			array("nom_utilisateur"=>array("Demandeur","nom_utilisateur","nom_asso"),
				"nom_salle"=>"Salle",
				"date_debut_salres"=>"De",
				"date_fin_salres"=>"A",
				"description_salres" => "Motif",
				"convention"=>"Conv.",
				"date_accord_res"=>"Accord le"
				), 
			array("info"=>"Details") )
			));
}
else
{
	$site->add_contents(new sqltable(
			"modereres", 
			"Demandes de reservation", $req, "modereres.php", 
			"id_salres", 
			array("nom_utilisateur"=>array("Demandeur","nom_utilisateur","nom_asso"),
				"nom_salle"=>"Salle",
				"date_debut_salres"=>"De",
				"date_fin_salres"=>"A",
				"description_salres" => "Motif",
				"convention"=>"Conv.",
				"date_accord_res"=>"Accord le"
				), 
			array("accord"=>"Donner accord", "convention"=>"Convention faite", "delete"=>"Refuser","info"=>"Details"), 
			array("accords"=>"Donner accord", "conventions"=>"Convention faite", "deletes"=>"Refuser"),
			array("convention"=>array(0=>"Non requise",1=>"A faire",11=>"Faite") )
			));
}

$site->end_page();

?>
