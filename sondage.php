<?php

/* Copyright 2006
 * - Julien Etelian <julien CHEZ pmad POINT net>
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
require_once($topdir. "include/cts/newsflow.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
$site = new site ();

if ( $site->user->id < 1 )
	error_403();
	
$sdn = new sondage($site->db,$site->dbrw);

if (  isset($_REQUEST["answord"]) )
{

	$sdn->load_lastest();
	if ( $sdn->id > 0 && $_REQUEST["id_sondage"] == $sdn->id )
		$sdn->repondre($site->user->id,$_REQUEST["numrep"]);

	$res = new contents("Merci","Votre réponse a été enregistrée.");
}	
else if ( isset($_REQUEST["id_sondage"]) )
	$sdn->load_by_id($_REQUEST["id_sondage"]);

if ( $sdn->id > 0 )
{

	$site->start_page("accueil","Sondage");	
		
	$cts = new contents("Sondage");
		
	if ( $res )
		$cts->add($res);
	

	$cts->add_title(2,"Resultats");
	
	$cts->add_paragraph($sdn->question);
	
	$cts->puts("<p>");

	$res = $sdn->get_results();
	
	foreach ( $res as $re )
	{
		$cumul+=$re[1];
		$pc = $re[1]*100/$sdn->total;
		
		$cts->puts($re[0]."<br/>");
		
		$wpx = floor($pc);
		if ( $wpx != 0 )
			$cts->puts("<div class=\"activebar\" style=\"width: ".$wpx."px\"></div>");
		if ( $wpx != 100 )
			$cts->puts("<div class=\"inactivebar\" style=\"width: ".(100-$wpx)."px\"></div>");
		
		$cts->puts("<div class=\"percentbar\">".round($pc,1)."%</div>");
		$cts->puts("<div class=\"clearboth\"></div>\n");
		
	}
	
	if ( $cumul < $sdn->total )
	{
		$pc = ( $sdn->total-$cumul)*100/$sdn->total;
		$cts->puts("<br/>Blanc ou nul : ".round($pc,1)."%");
	}
	
	$cts->puts("</p>");

	$cts->add_title(2,"Voir aussi");
	$cts->add_paragraph("<a href=\"sondage.php\">Archives</a>");

	$site->add_contents($cts);
	
	$site->end_page();
	exit();
}

$site->start_page("accueil","Sondage");


$req = new requete($site->db, "SELECT * FROM `sdn_sondage` ORDER BY date_sondage");	

$site->add_contents(new sqltable(
	"listsondages", 
	"Archives", $req, "../sondage.php", 
	"id_sondage", 
	array(
		"question"=>"Question",
		"date_sondage"=>"Du",
		"date_fin"=>"Au"
		), 
	array(), 
	array(),
	array( )
	));

$site->end_page();

?>
