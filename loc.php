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



if (isset($_REQUEST['genimg']) == 1)
{
  $lat  = rad2deg($_REQUEST['lat']);
  $long = rad2deg($_REQUEST['lng']);

  $lat = str_replace(",", ".", $lat);
  $long = str_replace(",", ".", $long);

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
                                           , asText(the_geom) AS points
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

  $img = new imgcarto();

  $img->addcolor('pred', 255, 192, 192);

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
	    $img->addpolygon($plg, 'black', false);
	}
    }

  $villecoords = str_replace("POINT(", "", $villecoords);
  $villecoords = str_replace(")", "", $villecoords);
  $villecoords = explode(" ", $villecoords);

  $img->addpoint($villecoords[0], $villecoords[1], 5, "red");

  $img->setfactor(1600);

  $img->draw();

  require_once ($topdir . "include/watermark.inc.php");  
  $wm_img = new img_watermark (&$this->img);

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
  $cts->add_paragraph("<center><img src=\"loc.php?genimg=1&lat=".$ville->lat."&lng=".$ville->long."\" alt=\"position ville\" /></center>\n");

  $req = new requete($site->db, "SELECT * FROM loc_lieu WHERE id_lieu_parent='".mysql_real_escape_string($lieu->id)."' ORDER BY nom_lieu");

  if ( $req->lines > 0 )
    $cts->add(new sqltable(
    	"listsublieux", "Sous-lieux", $req, "loc.php", 
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
  $cts->add_paragraph("<center><img src=\"loc.php?genimg=1&lat=".$ville->lat."&lng=".$ville->long."\" alt=\"position ville\" /></center>\n");

  $site->add_contents($cts);

  $site->end_page();
  exit();
}
elseif ( $pays->is_valid() )
{
  $site->start_page("none","Lieux");

  $cts = new contents($pays->nom);
  
  $site->add_contents($cts);

  $site->end_page();
  exit();
}


$site->start_page("none","Lieux");

$cts = new contents("Gestion des lieux");

$req = new requete($site->db, "SELECT * FROM loc_lieu LEFT JOIN loc_ville USING(id_ville) WHERE id_lieu_parent IS NULL ORDER BY nom_lieu");

$cts->add(new sqltable(
   "listsublieux", "Lieux racines", $req, "loc.php", 
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


?>