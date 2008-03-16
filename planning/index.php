<?php

/* Copyright 2008
 * - Sarah Amsellem <sarah DOT amsellem AT gmail DOT com>
 * - Benjamin Collet <bcollet AT oxynux DOT org>
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

define("BUREAU_BELFORT", 6);
define("PERM_AE_BELFORT", 120);

$topdir = "../";
require_once($topdir. "include/site.inc.php");

require_once($topdir. "include/entities/planning.inc.php");
require_once($topdir. "include/cts/planning.inc.php");

/*require_once($topdir. "include/cts/sqltable.inc.php");

require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/entities/salle.inc.php");*/

$site = new site ();

$site->allow_only_logged_users("services");

$lieux = array(6=>"Bureau AE Belfort", 30=>"Bureau AE Sevenans", 5=>"Foyer", 28=>"MDE");

if ( $_REQUEST["action"] == "searchpl" )
{
  $site->add_css("css/weekplanning.css");
  
  $site->start_page("services","Planning");
  $cts = new contents("<a href=\"index.php\">Planning</a> / ".$lieux[$_REQUEST["id_salle"]]." / Affichage");
  
  // TEST
	/*$planning = new planning($site->db,$site->dbrw);
	$start_date = strtotime("2008-03-17");
	$end_date = strtotime("2008-07-01");
	
	$planning->add ( 
	  1,
	  "Planning d'essai", -1, $start_date, $end_date, true );
	
	$lundi = 0;
	$mardi = 3600*24;
	$jeudi = 3600*24*3;
	$h8 = 8*3600;
	$h12 = 12*3600;
	$h14 = 14*3600;
	$h18 = 18*3600;
	
	$id_creneau_3 = $planning->add_gap( $mardi+$h8, $mardi+$h12 );*/
 // FIN TEST 

  $sql = 
    "SELECT id_gap, start_gap, end_gap, pl_gap.id_planning
     IFNULL(utilisateurs.alias_utl,'(personne)') AS texte
     FROM pl_gap
     LEFT JOIN pl_gap_user USING(id_gap)
     LEFT JOIN utilisateurs USING(id_utilisateur)
     WHERE pl_gap.id_planning='".PERM_AE_BELFORT."'";
     
  $pl = new weekplanning ("Planning", $site->db, $sql, "id_gap", "start_gap", "end_gap", "texte", "index.php", "index.php?action=details", "",
     PL_LUNDI);

  $cts->add($pl,true); 
  
  if( $_REQUEST["id_salle"]==BUREAU_BELFORT)
  {
  	$is_admin = $site->user->is_in_group("gestion_ae");
  	if ( $is_admin )
  		$cts->add_paragraph("<a href=\"admin.php\">Administration</a>");
  }
  
  $frm = new form("searchpl","index.php",false,"POST","Nouvelle recherche");
  $frm->add_hidden("action","searchpl");
  if ( isset($_REQUEST["fallback"]) )
    $frm->add_hidden("fallback",$_REQUEST["fallback"]);
  $frm->add_select_field("id_salle","Lieu",$lieux, $_REQUEST["id_salle"]);
  $frm->add_submit("afficher","Afficher le planning");
  $cts->add($frm,true);
  
  $site->add_contents($cts);
  $site->end_page();
  
  exit();
}

$site->start_page("services","Planning");

$cts = new contents("<a href=\"index.php\">Planning</a>");

$frm = new form("searchpl","index.php",false,"POST","Consulter un planning");
$frm->add_hidden("action","searchpl");
$frm->add_select_field("id_salle","Lieu",$lieux, $_REQUEST["id_salle"]);
$frm->add_submit("afficher","Afficher le planning");
$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();  

?>