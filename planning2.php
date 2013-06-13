<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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
setlocale(LC_ALL,"fr_FR.UTF8");

include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/planning2.inc.php");
require_once($topdir. "include/entities/planning2.inc.php");

$site = new site ();
$site->add_css($topdir . "css/planning2.css");

$planning = new planning2($site->db, $site->dbrw);

if (isset($_REQUEST["id_planning"]))
  $planning->load_by_id($_REQUEST["id_planning"]);

$cts = new contents($planning->name);

if($_REQUEST["action"] === "add_to_gap" && isset($_REQUEST["gap_id"]))
{
	$gap = $planning->get_gap_info( $gap_id );
	if( list ( $id_gap, $name_gap, $start, $end ) = $gap->get_row())
	{
		$cts->add_paragraph("Creneau");
		$frm = new form("add_to_gap","./planning2.php",true,"POST","Permanence sur le creneau $name_gap de $planning->name");
		$frm->add_hidden("action","do_add_to_gap");
		$frm->add_hidden("gap_id",$gap_id);
		if($planning->weekly)
		{
			$frm->add_date_field("start", "Date de debut ",strtotime($planning->start),true);
			$frm->add_date_field("start", "Date de fin ",strtotime($planning->end),true);
		}
		$frm->add_submit("do_add_to_gap","Valider");
		$cts->add($frm);
	}
}

$planningv = new planningv("",$site->db,$planning->id, time(), time()+7*24*3600);

$cts->add($planningv);

$site->add_contents($cts);
$site->end_page();
?>
