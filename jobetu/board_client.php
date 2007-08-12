<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once("include/jobetu.inc.php");
require_once("include/annonce.inc.php");
require_once("include/cts/jobetu.inc.php");
require_once("include/jobuser_client.inc.php");

$site = new site();
$site->allow_only_logged_users("services");
if(!$site->user->is_in_group("jobetu_client")) header("Location: index.php");

$site->add_css("jobetu/jobetu.css");
$site->add_css("css/mmt.css");
$site->start_page("services", "AE Job Etu");

$cts = new contents("Tableau de bord AE Job Etu");

$tabs = array(
		      array("", "jobetu/board_client.php", "annonces"),
		      array("preferences", "jobetu/board_client.php?view=preferences", "préférences"),
		      array("annonce", "jobetu/depot.php?action=annonce", "nouvelle annonce")
	      	);
$cts->add(new tabshead($tabs, $_REQUEST['view']));


/*******************************************************************************
 * Onglet préférences
 */
if(isset($_REQUEST['view']) && $_REQUEST['view'] == "preferences")
{
	
}

/*******************************************************************************
 * Onglet accueil: annonces
 */
else
{
	$user = new jobuser_client($site->db);
	$user->load_by_id($site->user->id);
	$user->load_annonces();
	
	$cts->add_paragraph("Vous avez `".count($user->annonces)."` nouveau(x) message(s)");
	
	foreach($user->annonces as $ann)
	{
		$annonce = new annonce($site->db);
		$annonce->load_by_id($ann['id_annonce']);
		$annonce->load_applicants();
		$annonce->load_applicants_fullobj();
		$box = new annonce_box($annonce);
		$cts->add($box);
	}
	
}

$site->add_contents($cts);
$site->end_page();

?>
