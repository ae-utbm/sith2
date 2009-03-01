<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->start_page("services", "AE Pédagogie");

$usr = new pedag_user($site->db, $site->dbrw, $site->user->id);
print_r($usr);

$path = "<a href=\"".$topdir."jobetu/\" title=\"AE JobEtu\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" /> AE JobEtu</a>";
$path .= " / "."<a href=\"".$topdir."jobetu/board_etu.php\" title=\"Tableau de bord\"><img src=\"".$topdir."images/icons/16/board.png\" class=\"icon\" /> Tableau de bord candidat</a>";
$path .= " / "."<a href=\"".$topdir."user.php?id_utilisateur=$usr->id\" title=\"$usr->prenom $usr->nom\"><img src=\"".$topdir."images/icons/16/user.png\" class=\"icon\" /> $usr->prenom $usr->nom</a>";
$cts = new contents($path);

$tabs = array(
		      array("", "jobetu/board_etu.php", "mes annonces"),
		      array("candidatures", "jobetu/board_etu.php?view=candidatures", "mes candidatures"),
		      array("general", "jobetu/board_etu.php?view=general", "tout job-etu"),
		      array("profil", "jobetu/board_etu.php?view=profil", "profil"),
		      array("preferences", "jobetu/board_etu.php?view=preferences", "préférences")
	      );
$cts->add(new tabshead($tabs, $_REQUEST['view']));


?>
