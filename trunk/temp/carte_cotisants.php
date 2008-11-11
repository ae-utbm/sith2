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

define("RATIO", 1600);     // Le ratio pour la carte final (1/1600)
define("MODE", 1);         // 0 => points, 1 => nb/departement
define("WATERMARK", TRUE); // watermark TRUE ou FALSE

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
                                 FROM `utl_etu`
                                 LEFT JOIN `loc_ville` ON `loc_ville`.`id_ville` = `utl_etu`.`id_ville`
                                 GROUP BY `loc_ville`.`id_ville`");
}

elseif (MODE == 1)
{
  $req = new requete($site->db, "SELECT  `lat_ville`, `long_ville`, COUNT(*) AS `nb` FROM `loc_ville`
                                 LEFT JOIN `utl_etu` ON  `utl_etu`.`id_ville` = `loc_ville`.`id_ville`
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
  $img->addcolor('pblue_dark', 51, 102, 153);
  if(MODE==0)
  {
    $img->addcolor('pblue', 222, 235, 245);
  }
  elseif(MODE==1)
  {
    $img->addcolor('p0',   255, 242, 0); // 0   <  hts
    $img->addcolor('p1',   255, 231, 0); // 0   < hts  < 50
    $img->addcolor('p2',   255, 220, 0); // 50  <= hts < 100
    $img->addcolor('p3',   255, 198, 0); // 100 <= hts < 150
    $img->addcolor('p4',   255, 176, 0); // 150 <= hts < 200
    $img->addcolor('p5',   255, 165, 0); // 200 <= hts < 250
    $img->addcolor('p6',   255, 154, 0); // 250 <= hts < 300
    $img->addcolor('p7',   255, 143, 0); // 300 <= hts < 350
    $img->addcolor('p8',   255, 132, 0); // 350 <= hts < 400
    $img->addcolor('p9',   255, 121, 0); // 400 <= hts < 450
    $img->addcolor('p10',  255, 114, 0); // 450 <= hts < 500
    $img->addcolor('p11',  255, 101, 0); // 500 <= hts < 550
    $img->addcolor('p12',  255, 90, 0);  // 550 <= hts < 600
    $img->addcolor('p13',  255, 80, 0);  // 600 <= hts < 650
    $img->addcolor('p14',  255, 68, 0);  // 650 <= hts < 700
    $img->addcolor('p15',  255, 58, 0);  // 700 <= hts < 750
    $img->addcolor('p16',  255, 47, 0);  // 750 <= hts < 800
    $img->addcolor('p17',  255, 36, 0);  // 800 <= hts < 850
    $img->addcolor('p18',  255, 26, 0);  // 850 <= hts < 900
    $img->addcolor('p19',  255, 16, 0);  // 900 <= hts < 950
    $img->addcolor('p20',  255, 0, 0);  // 950 <= hts
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
      $dept[$numdept]['gid']=$result['gid'];
      $numdept++;
    }
  }

  $villes = array();
  if (MODE==0)
  {
    $i=0;
    /* on récupèré toutes les coordonnées pour les villes */
    foreach($loc AS $point)
    {
      $pgreq = new pgrequete($pgconn, "SELECT AsText(TRANSFORM(GeomFromText('POINT(".$point['long']." ".$point['lat'].")', 4030), 27582)) AS villecoords ".
                                      "FROM deptfr LIMIT 1");
      $rs = $pgreq->get_all_rows();
      if(isset($rs[0]))
      {
        $villes[$i] = $rs[0]['villecoords'];
        $i++;
      }
    }
  }
  elseif(MODE==1)
  {
    /* on récupèré toutes les coordonnées pour les villes */
    foreach($loc AS $point)
    {
      $pgreq = new pgrequete($pgconn, "SELECT gid ".
                                      "FROM deptfr ".
                                      "WHERE CONTAINS(the_geom, TRANSFORM(GeomFromText('POINT(".$point['long']." ".$point['lat'].")', 4030), 27582)) ".
                                      "LIMIT 1");
      $rs = $pgreq->get_all_rows();
      if(isset($rs[0]['gid']))
      {
        $d=$rs[0]['gid'];
        if(!isset($villes[$d]))
          $villes[$d] = $point['nb'];
        else
          $villes[$d] = $villes[$d]+$point['nb'];
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
        $color="white";
      elseif($villes[$d]>=950)
        $color="p20";
      elseif( 0< $villes[$d] && $villes[$d]<950)
      {
        $n=$villes[$d];
        $n=(int)($n/50);
        $color="p".$n;
      }
      else
        $color="white";

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

  if(WATERMARK)
  {
    $wm_img = new img_watermark (&$img->imgres);
    $wm_img->output();
  }
  else
  {
    $img->output();
  }

  exit();
}

echo "what the fuck ?!";
exit();
?>
