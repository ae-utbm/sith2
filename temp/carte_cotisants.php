<?php
/* Copyright 2007
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once ($topdir . "include/watermark.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT `loc_ville`.`lat_ville`, `loc_ville`.`long_ville` from `utilisateurs`
                               INNER JOIN `loc_ville` ON `utilisateurs`.`id_ville` = `loc_ville`.`id_ville`
                               WHERE `utilisateurs`.`id_ville` IS NOT NULL
                               GROUP BY `utilisateurs`.`id_ville`");

if($req->lines!=0)
{
  $img = new imgcarto();
  $img->addcolor('pblue', 222, 235, 245);
  $img->addcolor('pblue_dark', 51, 102, 153);

  $i=0;
  $loc = array();
  while(list($_lat, $_long) = $req->get_row())
  {
    $lat  = rad2deg($_lat);
    $long = rad2deg($_long);
    $lat = str_replace(",", ".", $lat);
    $long = str_replace(",", ".", $long);
    $loc[$i]['lat']=$_lat;
    $loc[$i]['long']=$_long;
    $i++;
  }

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

  $viles = array();
  /* on récupèré toutes les coordonnées pour les villes */
  foreach($loc AS $point)
  {
    $pgreq = new pgrequete($pgconn, "SELECT AsText(TRANSFORM(GeomFromText('POINT(".$point['long']." ".$point['lat'].")', 4030), 27582)) AS villecoords ".
                                    "FROM deptfr LIMIT 1");
    $rs = $pgreq->get_all_rows();
    foreach($rs as $result)
    {
      $villes[] = $result['villecoords'];
      break;
    }
  }
  foreach($dept as $departement)
  {
    foreach($departement['plgs'] as $plg)
    {
      $img->addpolygon($plg, 'pblue', true);
      $img->addpolygon($plg, 'pblue_dark', false);
    }
  }

  foreach($villes as $ville)
	{
		print_r($ville);
    $villecoords = str_replace("POINT(", "", $ville);
    $villecoords = str_replace(")", "", $villecoords);
		$villecoords = explode(" ", $villecoords);
		$lat = explode(".", $villecoords[0]);
		$long = explode(".", $villecoords[1]);
    $img->addpoint((int)$lat[0], (int)$long[0], 5, "black");
	}

  $img->setfactor(1600);
  $img->draw();
  $wm_img = new img_watermark (&$img->imgres);
  $wm_img->output();
  exit();

}

exit();
?>
