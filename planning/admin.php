<?php

/* Copyright 2007
 * - Sarah Amsellem < sarah DOT amsellem AT gmail DOT com >
 * - Benjamin Collet < bcollet AT oxynux DOT org >
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

require_once($topdir. "include/entities/planning.inc.php");
require_once($topdir. "include/cts/planning.inc.php");

$site = new site();

$site->allow_only_logged_users();

/*if ( !isset($_REQUEST["id_planning"]) )
{
  $site->start_page("services","Planning");
  $cts = new contents("<a href=\"index.php\">Planning</a> / <a href=\"admin.php\">Administration</a> / ".$plan[$_REQUEST["id_salle"]]);

  $lst = new itemlist("Veuillez choisir le planning � administrer");
  
  foreach ( $plan as $id => $nom )
	$lst->add("<a href=\"admin.php?id_planning=$id\">$nom</a>");

  $cts->add($lst,true);
  $site->add_contents($cts);
  $site->end_page(); 
  exit();
}*/

if($_REQUEST["action"] == "select")
{
	$site->start_page("services","Planning");
	$cts = new contents("<a href=\"index.php\">Planning</a> / <a href=\"admin.php\">Administration</a> / ".$plan[$id_salle]);
	
	$id_planning = intval($_REQUEST["id_planning"]);
	
	/* On recupere le planning */
	$sql = new requete($site->db, "SELECT start_date_planning, end_date_planning
     FROM pl_planning
     WHERE id_planning='".$id_planning."'");
     
	$row = $sql->get_row();
	
	/* On recupere le creneau choisi par la date de debut */
	$sql2 = new requete($site->db, "SELECT id_gap 
									FROM pl_gap 
									WHERE id_planning='".$id_planning."' 
									AND start_gap > '".strtotime($row['start_date_planning'])."' 
									AND start_gap= '".strtotime($_REQUEST['date_debut'])."'");
									
	$row2 = $sql2->get_row();
	$id_creneau = $row2['id_creneau'];
	
	/* On liste les personnes associees a ce creneau */
	
	
	$site->add_contents($cts);
	$site->end_page();
	
	exit();
}

$id_planning = intval($_REQUEST["id_planning"]);

$site->start_page("services","Planning");
$cts = new contents("<a href=\"index.php\">Planning</a> / <a href=\"admin.php\">Administration</a> / ".$plan[$id_salle]);

/*$sql = new requete($site->db, "SELECT start_date_planning, end_date_planning
     FROM pl_planning
     WHERE id_planning='".$id_planning."'");
     
$row = $sql->get_row();

$frm = new form("autoplanning", "admin.php?id_planning=$id_planning",false,"POST","Modifier les dates du planning");
$frm->add_hidden("action","autoplanning");
$frm->add_datetime_field("date_debut","Date de début",strtotime($row['start_date_planning']));
$frm->add_datetime_field("date_fin","Date de fin",strtotime($row['end_date_planning']));
$frm->add_submit("valid","Valider");
$frm->allow_only_one_usage();
$cts->add($frm,true);*/

$frm = new form("select", "admin.php?id_planning=$id_planning",false,"POST","Selectionner un creneau");
$frm->add_hidden("action","select");
$frm->add_datetime_field("date_debut","Date de début");
$frm->add_submit("valid","Valider");
$frm->allow_only_one_usage();
$cts->add($frm,true);


$site->add_contents($cts);
$site->end_page(); 
    
?>