<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
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
$topdir = "./";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");

require_once($topdir. "include/entities/pays.inc.php");
require_once($topdir. "include/entities/ville.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");

$site = new site ();

if ( $_REQUEST["action"] == "kml" )
{
  header("Content-type: application/vnd.google-earth.kml+xml");
  header("Content-Disposition: filename=ae_utbm.kml");

  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  echo "<kml xmlns=\"http://earth.google.com/kml/2.1\">";
  
  echo "<Document id=\"ae_utbm_fr_lieux\">";
  echo "<name>ae utbm</name>";
  $req = new requete($site->db, "SELECT * FROM loc_lieu");
  while ( $row = $req->get_row() )
  {   
    echo "<Placemark id=\"ae_utbm_fr_lieu_".$row['id_lieu']."\">";
    echo "<name>".htmlspecialchars($row['nom_lieu'])."</name>";
    echo "<description></description>";
    echo "<Point>";
    echo "<coordinates>".sprintf("%.12F",$row['long_lieu']*360/2/M_PI).",".
      sprintf("%.12F",$row['lat_lieu']*360/2/M_PI)."</coordinates>";
    echo "</Point>";
    echo "</Placemark>";
  } 
  echo "</Document>";
  echo "</kml>";
    
  exit();
  
}


if ($_REQUEST['action'] == 'genimgpays')
{
  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgcarto.inc.php");

  $idpays = intval($_REQUEST['idpays']);

  $imgfile = $topdir . "var/cache/loc/pays/".$idpays.".png";

  if (file_exists($imgfile))
  {
    header("Content-Type: image/png");
    readfile($imgfile);
    exit();
  }


  $req = new requete($site->db,
                     "SELECT nomeng_pays FROM loc_pays WHERE id_pays = " . $idpays);

  $nomengpays = $req->get_row();
  $nomengpays = $nomengpays['nomeng_pays'];


  /*
  if ($nomengpays == '')
    exit();
  */
  $pgconn = new pgsqlae();

  /* 3395 est le SRID de la projection "globale" cylindrique (mercator) */
  $pgreq = new pgrequete($pgconn, "SELECT 
                                           name
                                           , AsText(Transform(Simplify(the_geom, 0.2), 3395)) AS points
                                   FROM 
                                           worldadmwgs
                                   WHERE
                                           region != 'Antarctica'");

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
	$step = count($country[$numpays]['plgs'][$i]); 

	/* premier point */
	if ($step == 0)
	  {
	    $country[$numpays]['plgs'][$i][] = $coord[0];
	    $country[$numpays]['plgs'][$i][] = $coord[1];
	  }
	/* points suivants : détection de la connerie */
	else if (checkcoords($country[$numpays]['plgs'][$i][$step - 2],
			     $country[$numpays]['plgs'][$i][$step - 1],
			     $coord[0],
			     $coord[1],
			     10000000)) // tolérance
	  {
	    $country[$numpays]['plgs'][$i][] = $coord[0];
	    $country[$numpays]['plgs'][$i][] = $coord[1];
	  }
      }
      $i++;
    }
    if ($result['name'] == $nomengpays)
      $country[$numpays]['isin'] = true;
    else
      $country[$numpays]['isin'] = false;
    $numpays++;
  }

  $img = new imgcarto(800, 10);
  $img->addcolor('green', 150,255, 150);
  foreach($country as $c)
  {
    if (count($c['plgs']) > 0)
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
  }

  $img->draw();

  require_once ($topdir . "include/watermark.inc.php");
  $wm_img = new img_watermark ($img->imgres);

  $wm_img->saveas($imgfile);
  $wm_img->output();

  exit();
}

if ($_REQUEST['action'] == 'genimgville')
{


  /* on utilise les lat/long pour la localisation */
  if (isset($_REQUEST['lat']))
  {
    $lat  = rad2deg($_REQUEST['lat']);
    $long = rad2deg($_REQUEST['lng']);

    $lat = str_replace(",", ".", $lat);
    $long = str_replace(",", ".", $long);
  }
  /* code postal */
  else if (isset($_REQUEST['cpostal']))
  {
    $cpostal = intval($_REQUEST['cpostal']);
    $req = new requete($site->db, "SELECT 
                                   lat_ville
                                   , long_ville
                                   FROM
                                   loc_ville
                                   WHERE
                                   cpostal_ville = $cpostal");

    if ($req->lines > 0)
    {
      while ($rs = $req->get_row())
      {
        $lat[] = $rs['lat_ville'];
        $long[] = $rs['long_ville'];
      }

      $lat  = array_sum($lat)  / count($lat);
      $long = array_sum($long) / count($long); 

      $lat = rad2deg($lat);
      $long = rad2deg($long);

      $lat = str_replace(",", ".", $lat);
      $long = str_replace(",", ".", $long);
    }
  }

  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgcarto.inc.php");

  /* les coordonnées sont considérées en srid 4030, et on les
   * transforme à la volée en Lambert II étendu, pour coller avec
   * les données de l'IGN
   */

  $pgconn = new pgsqlae();
  $pgreq = new pgrequete($pgconn, "SELECT 
                                   nom_dept
                                   , nom_region
                                   , AsText(TRANSFORM(GeomFromText('POINT(".$long.
                                                                          " ".$lat.")', 4030), 27582)) AS villecoords
                                   , asText(Simplify(the_geom, 400)) AS points
                                   , CONTAINS(the_geom, TRANSFORM(GeomFromText('POINT(".$long.
                                   " ".$lat.")', 4030), 27582)) AS indept
                                   FROM 
                                   deptfr");
  $rs = $pgreq->get_all_rows();
  
  $numdept = 0;

  foreach($rs as $result)
  {
    $astext = $result['points'];
    $matched = array();
    preg_match_all("/\(([^)]*)\)/", $astext, $matched);
    /* récupère les différents polygones pour un département */
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
    $dept[$numdept]['isin'] = $result['indept'];
    $numdept++;
  }
  $villecoords = $result['villecoords'];

  $img = new imgcarto(800, 10);

  $img->addcolor('pred', 255, 192, 192);
  $img->addcolor('pgreen', 192,255, 192);

  $i = 0;
  foreach($dept as $departement)
  {
    foreach($departement['plgs'] as $plg)
    {
      if ($departement['isin'] == 't')
      {
        $img->addpolygon($plg, 'pred', true);
        $img->addpolygon($plg, 'black', false);
      }
      else
      {
        $img->addpolygon($plg, 'pgreen', true);
        $img->addpolygon($plg, 'black', false);
      }
    }
  }

  $villecoords = str_replace("POINT(", "", $villecoords);
  $villecoords = str_replace(")", "", $villecoords);
  $villecoords = explode(" ", $villecoords);

  $img->addpoint($villecoords[0], $villecoords[1], 5, "black");

  $img->draw();

  require_once ($topdir . "include/watermark.inc.php");  
  $wm_img = new img_watermark (&$img->imgres);
  $wm_img->output();

  exit();
}

/* Spécifique Belfort / montbé */
if ($_REQUEST['action'] == 'genimgbfmontbe')
{
  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgcarto.inc.php");
  
  $pgconn = new pgsqlae();
  /* on dessine les contours de la Franche Comté */
  $pgreq = new pgrequete($pgconn, "SELECT 
                                           nom_dept
                                           , code_dept
                                           , asText(the_geom) AS points
                                           , AsText(centroid(the_geom)) AS center
                                   FROM 
                                           deptfr
                                   WHERE
                                           code_dept = '90'");
  $rs = $pgreq->get_all_rows();
  
  $numdept = 0;

  foreach($rs as $result)
  {
    $astext = $result['points'];
    $matched = array();
    preg_match_all("/\(([^)]*)\)/", $astext, $matched);
    /* récupère les différents polygones pour un département */
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
    /* centre */
    $center = $result['center'];
    $center = str_replace('POINT(', '', $center);
    $center = str_replace(')', '', $center);
    $dept[$numdept]['center'] = explode( ' ', $center);
    /* nom */
    $dept[$numdept]['name'] = $result['nom_dept'] . " (". $result['code_dept'] . ")";

    $numdept++;
  }

  $villecoords = $result['villecoords'];

  $img = new imgcarto(500, 10);

  $img->addcolor('pred', 255, 192, 192);
  $img->addcolor('pgreen', 192,255, 192);
  $img->addcolor('grey', 120, 120, 120);

  $i = 0;
  foreach($dept as $departement)
  {
    foreach($departement['plgs'] as $plg)
    {
      $img->addpolygon($plg, 'pgreen', true);
      $img->addpolygon($plg, 'black', false);
    }
    $img->addtext(16, 0, 
		  $departement['center'][0] + 200, 
		  $departement['center'][1] + 2500, 
		  'pred', 
		  ucfirst(strtolower($departement['name'])));
  }

  /* on plotte quelques villes bien connues ;-) */
  $psql = new pgrequete($pgconn, 
			    "SELECT DISTINCT
                                           name_loc, 
                                           AsText(TRANSFORM(the_geom, 27582)) AS points 
                             FROM 
                                           worldloc 
                             WHERE 
                                           name_loc IN ('Belfort', 'Montbeliard', 'Sevenans') 
                             AND 
                                           countryc_loc = 'FR'
                             GROUP BY 
                                           name_loc, the_geom");

  
  $rq = $psql->get_all_rows();
  foreach($rq as $result)
    {
      $point = $result['points'];
      $point = str_replace("POINT(", '', $point);
      $point = str_replace(")", '', $point);
      $point = explode(' ', $point);

      $img->addpointwithlegend($point[0], $point[1], 10, 'black', 12, 0, $result['name_loc'], 'black');
      //      $img->addpoint($point[0], $point[1], 4, 'black');
      //      $img->addtext(12, 0, $point[0] + 7500, $point[1] - 400, 'black', $result['name_loc']); 
    }

  $img->draw();
  

  require_once ($topdir . "include/watermark.inc.php");  
  $wm_img = new img_watermark ($img->imgres);
  $wm_img->output();
  
  exit();
}


/* Echelle d'un département */
if ($_REQUEST['action'] == 'genimgdept')
{
  $iddept = intval($_REQUEST['iddept']);

  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgcarto.inc.php");
  
  $pgconn = new pgsqlae();
  /* on dessine les contours du département */
  $pgreq = new pgrequete($pgconn, "SELECT 
                                           nom_dept
                                           , code_dept
                                           , asText(the_geom) AS points
                                           , AsText(centroid(the_geom)) AS center
                                   FROM 
                                           deptfr
                                   WHERE
                                           code_dept = $iddept");
  $rs = $pgreq->get_all_rows();
  
  $numdept = 0;

  foreach($rs as $result)
  {
    $astext = $result['points'];
    $matched = array();
    preg_match_all("/\(([^)]*)\)/", $astext, $matched);
    /* récupère les différents polygones pour un département */
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
    /* centre */
    $center = $result['center'];
    $center = str_replace('POINT(', '', $center);
    $center = str_replace(')', '', $center);
    $dept[$numdept]['center'] = explode( ' ', $center);
    /* nom */
    $dept[$numdept]['name'] = $result['nom_dept'] . " (". $result['code_dept'] . ")";

    $numdept++;
  }

  $villecoords = $result['villecoords'];

  $img = new imgcarto(500, 10);

  $img->addcolor('pred', 255, 192, 192);
  $img->addcolor('pgreen', 192,255, 192);
  $img->addcolor('grey', 120, 120, 120);

  $i = 0;
  foreach($dept as $departement)
  {
    foreach($departement['plgs'] as $plg)
    {
      $img->addpolygon($plg, 'black', false);
    }
    $img->addtext(16, 0, 
		  $departement['center'][0], 
		  $departement['center'][1], 
		  'pred', 
		  ucfirst(strtolower($departement['name'])));
  }

  /* on plotte quelques villes */
  $psql = new pgrequete($pgconn, 
			"SELECT
                                           name_loc, 
                                           AsText(TRANSFORM(the_geom, 27582)) AS points 
                             FROM 
                                           worldloc 
                             WHERE 
                                           CONTAINS((SELECT TRANSFORM(the_geom, 4030) FROM deptfr WHERE code_dept = '".$iddept."'), the_geom) 
                             AND 
                                           countryc_loc = 'FR'
                             GROUP BY 
                                           name_loc, the_geom
                             ORDER BY
                                           RANDOM()
                             LIMIT 5");

  
  $rq = $psql->get_all_rows();

  foreach($rq as $result)
    {
      $point = $result['points'];
      $point = str_replace("POINT(", '', $point);
      $point = str_replace(")", '', $point);
      $point = explode(' ', $point);
      //$img->addpointwithlegend($point[0], $point[1], 10, 'black', 12, 0, $result['name_loc'], 'black');

      $img->addpoint($point[0], $point[1], 4, 'black');
      $img->addtext(12, 0, $point[0], $point[1], 'black', $result['name_loc']); 
    }

  $img->draw();
  

  require_once ($topdir . "include/watermark.inc.php");  
  $wm_img = new img_watermark ($img->imgres);
  $wm_img->output();
  
  exit();
}



$pays = new pays($site->db,$site->dbrw);
$ville = new ville($site->db,$site->dbrw);
$lieu = new lieu($site->db,$site->dbrw);

if ( isset($_REQUEST["id_lieu"]) )
  $lieu->load_by_id($_REQUEST["id_lieu"]);

elseif ( isset($_REQUEST["id_ville"]) )
  $ville->load_by_id($_REQUEST["id_ville"]);
  
elseif ( isset($_REQUEST["id_pays"]) )
  $pays->load_by_id($_REQUEST["id_pays"]);

if ( $lieu->is_valid() && !is_null($lieu->id_ville) )
  $ville->load_by_id($lieu->id_ville);    

if ( $ville->is_valid() )
  $pays->load_by_id($ville->id_pays);

if ( $_REQUEST["action"] == "addlieu" && $site->user->is_in_group("gestion_ae") )
{
  $lieu_parent = new lieu($site->db);
  $lieu_parent->load_by_id($_REQUEST["id_lieu_parent"]);

  $lieu->create ( $ville->id, $lieu_parent->id, $_REQUEST["nom"], $_REQUEST["lat"], $_REQUEST["long"], $_REQUEST["eloi"] );
}
elseif ( $_REQUEST["action"] == "editlieu" && $site->user->is_in_group("gestion_ae") )
{
  $lieu_parent = new lieu($site->db);
  $lieu_parent->load_by_id($_REQUEST["id_lieu_parent"]);

  $lieu->update ( $ville->id, $lieu_parent->id, $_REQUEST["nom"], $_REQUEST["lat"], $_REQUEST["long"], $_REQUEST["eloi"] );
}


if ( $lieu->is_valid() )
{
  $lieu_parent = new lieu($site->db);
  $lieu_parent->load_by_id($lieu->id_lieu_parent);
  $path = $lieu->get_html_link();
  while ( $lieu_parent->is_valid() )
  {
    $path = $lieu_parent->get_html_link(). " / ". $path;
    $lieu_parent->load_by_id($lieu_parent->id_lieu_parent);
  }
  
  
  $site->start_page("none",$lieu->nom);

  $cts = new contents("<a href=\"loc.php\">Lieux</a> / ".$path);
  $cts->add_paragraph("Ville: ".$ville->get_html_link());
  $cts->add_paragraph("Position: ".geo_radians_to_degrees($lieu->lat)."N , ".geo_radians_to_degrees($lieu->long)."E");

  $req = new requete($site->db, "SELECT * FROM loc_lieu WHERE id_lieu_parent='".mysql_real_escape_string($lieu->id)."' ORDER BY nom_lieu");

  if ( $req->lines > 0 )
    $cts->add(new sqltable("listsublieux", "Sous-lieux", $req, "loc.php", 
                           "id_lieu", 
                           array("nom_lieu"=>"Nom"), 
                           array(), array(),array()),true); 

  if ( $site->user->is_in_group("gestion_ae") )
  {
    $frm = new form("editlieu","loc.php?id_lieu=".$lieu->id,true,"POST","Editer");
    $frm->add_hidden("action","editlieu");
    $frm->add_text_field("nom","Nom",$lieu->nom,true);
    $frm->add_entity_smartselect ("id_ville", "Ville", $ville );
    $frm->add_entity_select("id_lieu_parent", "Lieu parent", $site->db, "lieu",$lieu->id_lieu_parent,true);
    $frm->add_geo_field("lat","Latitude","lat",$lieu->lat);
    $frm->add_geo_field("long","Longitude","long",$lieu->long);
    $frm->add_text_field("eloi","Eloignement",$lieu->eloi);
    $frm->add_submit("valid","Enregistrer");
    $cts->add($frm,true);
  }

  $site->add_contents($cts);

  $site->end_page();
  exit();
}
elseif ( $ville->is_valid() )
{
  $site->start_page("none","Lieux");

  $cts = new contents($ville->nom);
  $cts->add_paragraph("Pays: ".$pays->get_html_link());
  $cts->add_paragraph("Position: ".geo_radians_to_degrees($ville->lat)."N , ".geo_radians_to_degrees($ville->long)."E");
  $cts->add_paragraph("<center><img src=\"loc.php?action=genimgville&lat=".$ville->lat."&lng=".$ville->long."\" alt=\"position ville\" /></center>\n");

  $site->add_contents($cts);

  $site->end_page();
  exit();
}
elseif ( $pays->is_valid() )
{
  $site->start_page("none","Lieux");

  $cts = new contents($pays->nom);
  $cts->add_paragraph("<center><img src=\"loc.php?action=genimgpays&idpays=".$pays->id."\" alt=\"position pays\" /></center>\n");
  
  $site->add_contents($cts);

  $site->end_page();
  exit();
}


$site->start_page("none","Lieux");

$cts = new contents("Gestion des lieux");

$req = new requete($site->db, "SELECT * FROM loc_lieu LEFT JOIN loc_ville USING(id_ville) WHERE id_lieu_parent IS NULL ORDER BY nom_lieu");

$cts->add(new sqltable("listsublieux", "Lieux racines", $req, "loc.php", 
                       "id_lieu", 
                       array("nom_lieu"=>"Nom","nom_ville"=>"Ville"), 
                       array(), array(),array()),true); 

if ( $site->user->is_in_group("gestion_ae") )
{
  $frm = new form("addlieu","loc.php",true,"POST","Nouveau lieu");
  $frm->add_hidden("action","addlieu");
  $frm->add_text_field("nom","Nom","",true);
  
  //$frm->add_entity_select("id_ville", "Ville", $site->db, "ville",false,true);
  $frm->add_entity_smartselect ("id_ville", "Ville", $ville );
  $frm->add_entity_select("id_lieu_parent", "Lieu parent", $site->db, "lieu",false,true);

  $frm->add_geo_field("lat","Latitude","lat");
  $frm->add_geo_field("long","Longitude","long");
  $frm->add_text_field("eloi","Eloignement");
  
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);
}


$site->add_contents($cts);

$site->end_page();



function checkcoords($lx, $ly, $x, $y, $tolerance)
{
  if (sqrt(pow($x - $lx, 2) + pow($y - $ly, 2)) > $tolerance)
    return false;
  return true;
}

?>
