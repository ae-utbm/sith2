<?
/** @file
 *  Rendu d'un trajet.
 *
 */

/* Copyright 2006
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Ã‰tudiants de
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

session_start ();

$topdir = "../";

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");
require_once($topdir . "include/carto.inc.php");


$db = new mysqlae ();

$coords = array();


$villes[0] = $_SESSION['trajet']['start'];

if (is_array($_SESSION['trajet']['etapes']))
  foreach ($_SESSION['trajet']['etapes'] as $etape)
    $villes[] = $etape;

$villes[] = $_SESSION['trajet']['stop'];

foreach ($villes as $ville)
  $coords[] = get_coords_by_id($db, $ville);

$carte = new carto ($coords);
$carte->parse_links (true);


$carte->output ();
$carte->destroy ();


/*
 * Recherche de coordonnées dans une base
 *
 */
function get_coords_by_id ($db, $id)
{
  $id = intval ($id);
  $sql = new requete ($db,
		      "SELECT `lat_ville`, `long_ville`
                          FROM `villes`
                         WHERE `id_ville` = '" . $id . "'
                         LIMIT 1");
  $res = $sql->get_row();
  return array (rad2deg($res[0]), rad2deg($res[1]));
}