<?php

/* Copyright 2008
 * - Remy BURNEY < rburney <point> utbm <at> gmail <dot> com >
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


if ( !$site->user->is_in_group("moderateur_forum") )
  $site->error_forbidden("none","group",7);

	
$site->start_page("none","Administration du forum");

$cts = new contents("Administration");

$lst = new itemlist();
$lst->add("<a href=\"liste.php?action=new\">Ajouter un forum</a>");
$lst->add("<a href=\"liste_ban.php\">Afficher les utilisateurs bannis du forum</a>");
$lst->add("<a href=\"liste.php\">Afficher les forums</a>");

$cts->add($lst);


$site->add_contents($cts);

$site->end_page();

?>
