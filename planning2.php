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
require_once($topdir. "include/cts/sqltable.inc.php");


$site = new site ();
$site->add_css($topdir . "css/planning2.css");

if ( !$site->user->is_valid() )
  $site->error_forbidden("planning");

if(!isset($_REQUEST["id_planning"]))
{
	$grps = $site->user->get_groups_csv();
	$sql = new requete($site->db,
		"SELECT id_planning, name_planning, start, end FROM pl2_planning
		 WHERE end > NOW() 
		 AND
		 (
			is_public = 1
			OR
			id_group IN ($grps)
			OR
			id_admin_group IN ($grps)
		 )
		 ORDER BY name_planning ASC");
	$table = new sqltable("listeplannings", 
			"Plannings actuels", 
			$sql, 
			"planning2.php",
			"id_planning",
			array(
				"name_planning" => "Nom",
				"start"		=> "Date de debut",
				"end"		=> "Date de fin"
			),
			array("details" => "Details"),
			array(),
			array());
	$cts = new contents("Liste des plannings");
	$cts->add($table);
	$site->add_contents($cts);
	$site->end_page();
	exit();
}

$planning = new planning2($site->db, $site->dbrw);

if (isset($_REQUEST["id_planning"]))
  $planning->load_by_id($_REQUEST["id_planning"]);

$cts = new contents($planning->name);

if($_REQUEST["action"] === "add_to_gap" && isset($_REQUEST["gap_id"]))
{
	$gap_id = $_REQUEST["gap_id"];
	if( !$site->user->is_in_group_id($planning->admin_group) && !$site->user->is_in_group_id($planning->group) )
	{
		$cts->add_paragraph("Vous n'avez pas le droit de faire cela.");
		$site->add_contents($cts);
		$site->end_page();
		exit();
	}
	$gap = $planning->get_gap_info( $gap_id );
	if( list ( $id_gap, $name_gap, $start, $end ) = $gap->get_row())
	{
		$frm = new form("add_to_gap","./planning2.php?id_planning=".$planning->id,true,"POST","Permanence sur le creneau $name_gap de $planning->name");
		$frm->add_hidden("action","do_add_to_gap");
		$frm->add_hidden("gap_id",$gap_id);
		if($planning->weekly)
		{
			$week_start = $planning->get_week_start( time());
			$frm->add_info("Creneau du ".strftime("%A %H:%M",$week_start+strtotime($start))." au ".strftime("%A %H:%M",$week_start+strtotime($end))).
			$frm->add_date_field("start", "Date de debut ",$planning->start,true);
			$frm->add_date_field("end", "Date de fin ",$planning->end,true);
		}
		else
		{
			$frm->add_info("Creneau de $start a $end");
		}
		$frm->add_submit("do_add_to_gap","Valider");
		$cts->add($frm);
	}
}

if($_REQUEST["action"] === "remove_from_gap" && isset($_REQUEST["user_gap_id"]))
{
	$user_gap_id = $_REQUEST["user_gap_id"];
	$user_gap = $planning->get_user_gap_info($user_gap_id);
	if( list( $gap_id, $id_utl, $user_gap_start, $user_gap_end ) = $user_gap->get_row())
	{
		if( $id_utl != $site->user->id && !$site->user->is_in_group_id($planning->admin_group) && !$site->user->is_in_group("gestion_ae") )
		{
			$cts->add_paragraph("Vous n'avez pas le droit de faire cela.");
			$site->add_contents($cts);
			$site->end_page();
			exit();
		}
		$gap = $planning->get_gap_info( $gap_id );
		if( list ( $id_gap, $name_gap, $start, $end ) = $gap->get_row())
		{
			$frm = new form("remove_from_gap","./planning2.php?id_planning=".$planning->id,true,"POST","Permanence sur le creneau $name_gap de $planning->name");
			$frm->add_hidden("action","do_remove_from_gap");
			$frm->add_hidden("user_gap_id",$user_gap_id);
			if($id_utl != $site->user->id)
			{
				$user = new utilisateur($site->db);
				$user->load_by_id($id_utl);
				$frm->add_info("Desinscrir ".$user->get_surnom_or_alias()." du ".strftime("%A %H:%M",$week_start+strtotime($start)).
					" au ".strftime("%A %H:%M",$week_start+strtotime($end))."?");
			}
			else
			{
				$frm->add_info("Vous desinscrire du ".strftime("%A %H:%M",$week_start+strtotime($start)).
					" au ".strftime("%A %H:%M",$week_start+strtotime($end))."?");
			}
			$frm->add_submit("do_add_to_gap","Valider");
			$cts->add($frm);
		}
	}
}

if($_REQUEST["action"] === "do_remove_from_gap" && isset($_REQUEST["user_gap_id"]))
{
	$user_gap_id = $_REQUEST["user_gap_id"];
	$user_gap = $planning->get_user_gap_info($user_gap_id);
	if( list( $gap_id, $id_utl, $user_gap_start, $user_gap_end ) = $user_gap->get_row())
	{
		if( $id_utl != $site->user->id && !$site->user->is_in_group_id($planning->admin_group) && !$site->user->is_in_group("gestion_ae"))
		{
			$cts->add_paragraph("Vous n'avez pas le droit de faire cela.");
			$site->add_contents($cts);
			$site->end_page();
			exit();
		}
		$gap = $planning->get_gap_info( $gap_id );
		if( list ( $id_gap, $name_gap, $start, $end ) = $gap->get_row())
		{
			$planning->remove_user_from_gap( $user_gap_id );
			$cts->add_paragraph("Desinscription reussie!");
		}
	}
}

if($_REQUEST["action"] === "do_add_to_gap" && isset($_REQUEST["gap_id"]))
{
	if(!$planning->weekly || (isset($_REQUEST["start"]) && isset($_REQUEST["end"])))
	{
		$gap_id = $_REQUEST["gap_id"];
		$user_id = $site->user->id;
		$start = $_REQUEST["start"];
		$end = $_REQUEST["end"];
		if($planning->is_user_addable($gap_id, $user_id, $start, $end ))
		{
			$planning->add_user_to_gap($gap_id, $user_id, $start, $end );
			$cts->add_paragraph("Ajout effectue.");
		}
		else
		{
			$cts->add_paragraph("Impossible de vous ajouter.");
		}
	}
}

$planningv = new planningv("",$site->db,$planning->id, time(), time()+7*24*3600, $site);

$cts->add($planningv);

$site->add_contents($cts);
$site->end_page();
?>
