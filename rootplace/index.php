<?php

/* Copyright 2007
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

$topdir="../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
	error_403();
	
$site->start_page("none","Administration");

$cts = new contents("Administration");

$cts->add_paragraph("Révision en production : ".get_rev());

$cts->add_title(2,"Administration");
$lst = new itemlist();
$lst->add("<a href=\"".$topdir."group.php\">Gestion des groupes</a>");
$cts->add($lst);

$cts->add_title(2,"AECMS");
$lst = new itemlist();
$lst->add("<a href=\"aecms.php\">Liste des AECMS</a>");
$lst->add("<a href=\"aecms.php?page=raz\">RAZ d'un AECMS</a> (remet les paramètres aux valeurs par défaut)");
$lst->add("<a href=\"aecms.php?page=install\">Installation d'un AECMS</a> (ou re-installation)");
$cts->add($lst);

$cts->add_title(2,"Maintenance");
$lst = new itemlist();
$lst->add("<a href=\"pollcoti.php\">Expiration des cotisations</a>");
$lst->add("<a href=\"repairdb.php\">Auto-Reparation de la base de données</a>");
$cts->add($lst);
$site->add_contents($cts);

$site->end_page();

?>
