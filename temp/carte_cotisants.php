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
exit();

define("RATIO", 1600);
define("MODE", 0); // 0 => points, 1 => nb/departement
$topdir = "../";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once ($topdir . "include/watermark.inc.php");
require_once($topdir. "include/entities/ville.inc.php");

$site = new site ();

if (MODE == 0)
{
  $req = new requete($site->db, "SELECT `loc_ville`.`lat_ville`, `loc_ville`.`long_ville`
                                 FROM `loc_ville`
                                 LEFT JOIN `utl_etu` AS E1 ON (`loc_ville`.`id_ville` = `E1`.`id_ville`
                                 OR CAST( `E2`.`cpostal_parents` AS UNSIGNED )
                                 = CAST( `loc_ville`.`cpostal_ville` AS UNSIGNED ))
                                 GROUP BY `loc_ville`.`id_ville`");
}

elseif (MODE == 1)
{
  // on ne veux que les codes postaux et on va essayer un truc : faire en 2 requetes
  // la première : jointure sur les id_ville
  $req = new requete($site->db, "SELECT  `lat_ville`, `long_ville`, COUNT(*) AS `nb` FROM `loc_ville`
                                 LEFT JOIN `utl_etu` ON  (`utl_etu`.`id_ville` = `loc_ville`.`id_ville`
                                 OR CAST( `utl_etu`.`cpostal_parents` AS UNSIGNED )
                                 = CAST( `loc_ville`.`cpostal_ville` AS UNSIGNED ))
                                 GROUP BY `loc_ville`.`id_ville`");
}
else
{
  echo "what the fuck ?!";
  exit();
}

if($req->lines!=0)
{
  $img = new imgcarto();
  $img->addcolor('pblue', 222, 235, 245);
  $img->addcolor('pblue_dark', 51, 102, 153);
  if(MODE==1)
  {
    $img->addcolor('p10', 172, 213, 245); // 0 < hts < 10
    $img->addcolor('p20', 172, 194, 245); // 10 <= hts < 20
    $img->addcolor('p30', 74, 170, 245); // 20 <= hts < 30
    $img->addcolor('p40', 39, 123, 227); // 30 <= hts < 40
    $img->addcolor('p50', 39, 52, 227); // 40 <= hts < 50
    $img->addcolor('p60', 99, 0, 227); // 50 <= hts < 60
    $img->addcolor('p70', 168, 0, 185); // 60 <= hts < 70
    $img->addcolor('p80', 167, 0, 71); // 70 <= hts < 80
    $img->addcolor('p90', 190, 59, 68); // 80 <= hts < 90
    $img->addcolor('p100', 255, 120, 0); // 90 <= hts < 100
    $img->addcolor('pmax', 255, 0, 0); // 100 <= hts
  }

  $i=0;
  $loc = array();
  if(MODE == 0)
  {
    while(list($_lat, $_long) = $req->get_row())
    {
      $lat  = rad2deg($_lat);
      $long = rad2deg($_long);
      $lat = str_replace(",", ".", $lat);
      $long = str_replace(",", ".", $long);
      $loc[$i]['lat']=$lat;
      $loc[$i]['long']=$long;
      $i++;
    }
  }
  elseif(MODE==1)
  {
    while(list($_lat, $_long, $nb) = $req->get_row())
    {
      $lat  = rad2deg($_lat);
      $long = rad2deg($_long);
      $lat = str_replace(",", ".", $lat);
      $long = str_replace(",", ".", $long);
      $loc[$i]['lat']=$lat;
      $loc[$i]['long']=$long;
      $loc[$i]['nb']=$nb;
      $i++;
    }
  }

  $pgconn = new pgsqlae();
  if(MODE==0)
  {
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
  }
  elseif(MODE==1)
  {
    $pgreq = new pgrequete($pgconn, "SELECT gid, asText(the_geom) AS points".
                                    " FROM deptfr");
    $rs = $pgreq->get_all_rows();
    $numdept=0;
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
      $dept[$numdept]['id']=$result['gid'];
      $numdept++;
    }
  }

  if (MODE==0)
  {
    $villes = array();
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
  }
  if(MODE==1)
  {
    $villes = array();
    /* on récupèré toutes les coordonnées pour les villes */
    foreach($loc AS $point)
    {
      $pgreq = new pgrequete($pgconn, "SELECT gid".
                                      "FROM deptfr ".
                                      "WHERE CONTAINS(the_geom, TRANSFORM(GeomFromText('POINT(".$point['lat']." ".$point['long'].")', 4030), 27582)) ".
                                      "LIMIT 1");
      $rs = $pgreq->get_all_rows();
      foreach($rs as $result)
      {
        $d=$result['gid'];
        if(!isset($villes[$d]))
          $villes[$d] = $point['nb'];
        else
          $villes[$d] = $villes[$d]+$point['nb'];
        break;
      }
    }
  }

  if(MODE==0)
  {
    foreach($dept as $departement)
    {
      foreach($departement['plgs'] as $plg)
      {
        $img->addpolygon($plg, 'pblue', true);
        $img->addpolygon($plg, 'pblue_dark', false);
      }
    }
  }
  elseif(MODE==1)
  {
    foreach($dept as $departement)
    {
      $d=$departement['gid'];
      if(!isset($villes[$d]))
        $color="pblue";
      elseif($villes[$d]>=100)
        $color="pmax";
      elseif(10 <= $villes[$d] && $villes[$d]<100)
      {
        $n=int($villes[$d]/10);
        $color="p".$n;
      }
      else
        $color="pblue";

      foreach($departement['plgs'] as $plg)
      {
        $img->addpolygon($plg, $color, true);
        $img->addpolygon($plg, 'pblue_dark', false);
      }
    }
  }

  if(MODE==0)
  {
    foreach($villes as $ville)
    {
      $villecoords = str_replace("POINT(", "", $ville);
      $villecoords = str_replace(")", "", $villecoords);
      $villecoords = explode(" ", $villecoords);
      $img->addpoint($villecoords[0], $villecoords[1], 5, "black");
    }
  }

  $img->setfactor(RATIO);
  $img->draw();
  $wm_img = new img_watermark (&$img->imgres);
  $wm_img->output();
  exit();
}

echo "what the fuck ?!";
exit();
?>
