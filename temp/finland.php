<?php
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */
$topdir = "../";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");

require_once($topdir. "include/entities/pays.inc.php");
require_once($topdir. "include/entities/ville.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgloc.inc.php");

$site = new site ();
$pgconn = new pgsqlae();

$lvl = IMGLOC_COUNTRY;


$loc = new imgloc(800, $lvl, $site->db, $pgconn);


$sql = new requete($site->db, "SELECT `id_ville` FROM `loc_ville` WHERE `id_pays` = 69");

while ($rs = $sql->get_row())
{
  $idvilles[] = $rs['id_ville'];
}   

foreach ($idvilles as $idville)
{
     $loc->add_location_by_idville($idville);
}

$loc->add_context();


$img = $loc->generate_img();
$img->output();

exit();

?>