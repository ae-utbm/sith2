<?php
/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
require_once($topdir . "include/cts/sqltable.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae"))
  error_403();


$site->start_page ("none", "statistiques du site");

$cts = new contents("Classement");

if ( $_REQUEST["action"] == "reset" )
{
	$req = new requete($site->dbrw, "DELETE FROM `stats_page` WHERE `page`!=''");
	$req = new requete($site->dbrw, "DELETE FROM `stats_os` WHERE `os`!=''");
	$req = new requete($site->dbrw, "DELETE FROM `stats_browser` WHERE `browser`!=''");
	$cts->add_title(2, "Reset");
	$cts->add_paragraph("Le reset des stats a &eacute;t&eacute; effectu&eacute; avec succ&egrave;s");
}

$cts->add_title(2, "Administration");
$cts->add_paragraph("Remettre &agrave; z&eacute;ro les stats du site ae.".
                    "<br /><img src=\"".$topdir."images/actions/delete.png\"><b>ATTENTION CECI EST IRREVERSIBLE</b> : <a href=\"stats_site.php?action=reset\">Reset !</a>");

$req = new requete($site->db,"SELECT * FROM `stats_page`  ORDER BY `visites` DESC");


$cts->add(new sqltable("top_full",
                       "Pages visit&eacute;es visit&eacute;s", $req, "stats.php",
                       "page",
                       array("=num" => "N°",
                             "page"=>"page",
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
         ),true);

$req = new requete($site->db,"SELECT * FROM `stats_browser`  ORDER BY `visites` DESC");
$cts->add(new sqltable("top_full",
                       "Navigateurs utilis&eacute;s", $req, "stats.php",
                       "browser",
											 array("=num" => "N°",
                             "browser"=>"Navigateur",
                             "visites"=>"Total"),
                       array(),
                       array(),
                       array()
         ),true);

$req = new requete($site->db,"SELECT * FROM `stats_os`  ORDER BY `visites` DESC");
$cts->add(new sqltable("top_full",
                       "Syst&egrave;mes d'exploitation utilis&eacute;s", $req, "stats.php",
                       "id_utilisateur",
                       array("=num" => "N°",
                             "os"=>"Syst&egrave;me d'exploitation",
                             "visites"=>"Total"),
                       array(),
                       array(),
                       array()
         ),true);


$site->add_contents($cts);

$site->end_page ();

?>
