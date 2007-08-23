<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

include($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/globals.inc.php");

$site = new site();

/* temporairement, si t'es pas logué tu lis pas */
if (!$site->user->id)
  error_403();

$site->start_page ("none", "Un wiki AE proof");

$tabs = array(array("","wiki2/index.php", "Accueil"),
              array("admin","wiki2/index.php?view=admin", "Administration")
             );
$cts = new contents("Un wiki AE proof");
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


$site->add_contents($cts);


$site->end_page ();

?>

