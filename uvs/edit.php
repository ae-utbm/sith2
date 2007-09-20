<?php
/** @file
 *
 * @brief Page d'édition des emplois du temps.
 *
 */

/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$site = new site();

$site->start_page("services", "Emploi du temps");

/* protection d'usage */
if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}


$edt = new edt($site->db, $site->dbrw);

$semestre = mysql_real_escape_string($_REQUEST['semestre']);

$edt->load($site->user->id, $semestre);

$cts = new contents("Emploi du temps ...");

$cts->add_paragraph("<pre>" . print_r($edt, true) . "</pre>");

$site->add_contents($cts);

$site->end_page();

?>

