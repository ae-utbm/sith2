<?php

/* Copyright 2008
 * - Benjamin Collet < bcollet AT oxynux DOT org >
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

$topdir="../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");


$site = new site;
$site->allow_only_logged_users("sas");
$site->start_page ("SAS", "Stock à Souvenirs");

$cts = new contents("Problème technique");

$text = "Le disque dur du serveur de l'AE contenant les photos du SAS est tombé en panne cette nuit.<br />
  Nous faisons notre possible pour récupérer les données, mais l'état du disque nous incite à envisager le pire.<br />
  Veuillez nous excuser pour la gêne occasionnée.<br />
  <br />
  <em>L'équipe informatique.</em>";
	
$cts->add_paragraph($text);

$site->add_contents($cts);

$site->end_page ();

?>
