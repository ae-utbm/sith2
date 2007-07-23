<?php
/* Copyright 2007
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

define("RATIO", 1600);     // Le ratio pour la carte final (1/1600)
define("WATERMARK", TRUE); // watermark TRUE ou FALSE

$topdir = "../";

require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once ($topdir . "include/watermark.inc.php");

$img = new imgcarto();
$img->addcolor('pblue_dark', 51, 102, 153);
$img->addcolor('pblue', 222, 235, 245);

$pgconn = new pgsqlae();
$pgreq = new pgrequete($pgconn, "SELECT asText(the_geom) AS points".
                                    " FROM deptfr");
$rs = $pgreq->get_all_rows();
$numdept = 0;
foreach($rs as $result)
{
  $astext = $result['points'];
  $matched = array();
  preg_match_all("/\(([^)]*)\)/", $astext, $matched);
  $i = 0;
  foreach ($matched[1] as $polygon)
  {
    $polygon = str_replace("(", "", $polygon);
    $points = explode(",", $polygon);
    foreach ($points as $point)
    {
      $coord = explode(" ", $point);
      $dept[$numdept]['plgs'][$i][] = $coord[0];
      $dept[$numdept]['plgs'][$i][] = $coord[1];
    }
    $i++;
  }
  $numdept++;
}

foreach($dept as $departement)
{
  foreach($departement['plgs'] as $plg)
  {
    $img->addpolygon($plg, 'pblue_dark', false);
  }
}

$img->setfactor(RATIO);

print_r($img->map_area("carte_deèfrance"));

exit();
?>
