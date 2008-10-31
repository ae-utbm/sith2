<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
$topdir = "../";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");

require_once($topdir. "include/entities/pays.inc.php");
require_once($topdir. "include/entities/ville.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");

$site = new site ();

if(isset($_REQUEST["id"]))
  $req = new requete($site->db, "SELECT `id_pays`, `nomeng_pays` FROM `loc_pays` WHERE `id_pays`>".intval($_REQUEST["id"])." LIMIT 1");
else
  $req = new requete($site->db, "SELECT `id_pays`, `nomeng_pays` FROM `loc_pays` LIMIT 1");

echo "<pre>\n";
echo $req->lines."\n";
$ok=false;
$id=0;
while(list($idpays,$nom)=$req->get_row())
{
  $id=$idpays;
  $ok=true;
  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgcarto.inc.php");

  $idpays = intval($idpays);

  $imgfile = $topdir . "var/cache/loc/pays/".$idpays.".png";

  if (file_exists($imgfile))
  {
    echo $nom." (".$idpays.") : allready exists  \n";
    continue;
  }
  $i++;
  $nomengpays = $nom;

  $pgconn = new pgsqlae();
  $pgreq = new pgrequete($pgconn, "SELECT name , AsText(the_geom) AS points FROM worldadmwgs");
  $rs = $pgreq->get_all_rows();

  $numpays = 0;

  foreach($rs as $result)
  {

    $astext = $result['points'];
    $matched = array();
    preg_match_all("/\(([^)]*)\)/", $astext, $matched);

    /* récupère les différents polygones pour un pays donné */
    $i = 0;
    foreach ($matched[1] as $polygon)
    {
      $polygon = str_replace("(", "", $polygon);
      $points = explode(",", $polygon);

      foreach ($points as $point)
      {
        $coord = explode(" ", $point);
        /* 6400 Km = approximativement le rayon de la Terre */
        $country[$numpays]['plgs'][$i][] = deg2rad($coord[0]) * 6400000;
        $country[$numpays]['plgs'][$i][] = deg2rad($coord[1]) * 6400000;
      }
    $i++;
    }
    if ($result['name'] == $nomengpays)
      $country[$numpays]['isin'] = true;
    else
      $country[$numpays]['isin'] = false;
    $numpays++;
  }

  $img = new imgcarto();
  $img->addcolor('green', 150,255, 150);
  foreach($country as $c)
  {
    foreach($c['plgs'] as $plg)
    {
      if ($c['isin'] == true)
      {
        $img->addpolygon($plg, 'red', true);
        $img->addpolygon($plg, 'black', false);
      }
      else
      {
        $img->addpolygon($plg, 'green', true);
        $img->addpolygon($plg, 'black', false);
      }
    }
  }

  $img->setfactor(55000);
  $img->draw();

  /* hack : redimensionnement de l'image ... */
  $newimgres = imagecreatetruecolor($img->dimx, $img->dimy / 2);
  imagecopy($newimgres,        // image destination
            $img->imgres,      // image source
            0,0,               // coordonnées arrivée image cible
            0, $img->dimy / 2, // coordonnées de la région image copiée
            $img->dimx, $img->dimy / 2); // largeur / longueur de l'étendue

  require_once ($topdir . "include/watermark.inc.php");
  $wm_img = new img_watermark (&$newimgres);
  $wm_img->saveas($imgfile);
  //$wm_img->output();
  echo $nom." (".$idpays.") : OK \n";
  unset($img);
  unset($wm_img);
  unset($newimgres);
}

echo "this is the end\n";
echo "</pre>\n";
if($ok)
  echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL='loc.php?id=".$id."'\">";
exit();

?>
