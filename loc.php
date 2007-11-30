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
require_once($topdir. "include/cts/gmap.inc.php");

$site = new site ();

if ( $_REQUEST["action"] == "allkml" )
{
  header("Content-type: application/vnd.google-earth.kml+xml");
  header("Content-Disposition: filename=ae_utbm.kml");

  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  echo "<kml xmlns=\"http://earth.google.com/kml/2.1\">";
  
  echo "<Document id=\"ae_utbm_fr_geopoints\">";
  echo "<name>ae utbm</name>";
  $req = new requete($site->db, "SELECT * FROM geopoint");
  while ( $row = $req->get_row() )
  {   
    echo "<Placemark id=\"ae_utbm_fr_geopoint_".$row['id_geopoint']."\">";
    echo "<name>".htmlspecialchars($row['nom_geopoint'])."</name>";
    echo "<description></description>";
    echo "<Point>";
    echo "<coordinates>".sprintf("%.12F",$row['long_geopoint']*360/2/M_PI).",".
      sprintf("%.12F",$row['lat_geopoint']*360/2/M_PI)."</coordinates>";
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
  require_once($topdir. "include/cts/imgloc.inc.php");

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

  $img = new imgloc(800, IMGLOC_WORLD, $site->db, new pgsqlae());
  $img->add_hilighted_context($nomengpays);
  $img->add_context();

  $img = $img->generate_img();

  require_once ($topdir . "include/watermark.inc.php");
  $wm_img = new img_watermark ($img->imgres);

  //  $wm_img->saveas($imgfile);
  $wm_img->output();

  exit();
}

if ($_REQUEST['action'] == 'genimgville')
{

  /* on utilise les lat/long pour la localisation */
  if (isset($_REQUEST['idville']))
  {
    $idvilles[] = intval($_REQUEST['idville']);

  }
  /* code postal */
  else if (isset($_REQUEST['cpostal']))
  {
    $cpostal = intval($_REQUEST['cpostal']);
    $req = new requete($site->db, "SELECT 
                                      id_ville
                                   FROM
                                      loc_ville
                                   WHERE
                                      cpostal_ville = $cpostal");

    if ($req->lines > 0)
      {
	while ($rs = $req->get_row())
	  {
	    $idvilles[] = $rs['id_ville'];
	  }
      }
  }

  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgloc.inc.php");
  
  $pgconn = new pgsqlae();

  if (isset($_REQUEST['level']))
    $lvl = intval($_REQUEST['level']);
  else
    $lvl = IMGLOC_COUNTRY;

  $loc = new imgloc(800, $lvl, $site->db, $pgconn);
   
  foreach ($idvilles as $idville)
    $loc->add_location_by_idville($idville);
  $loc->add_context();

  require_once ($topdir . "include/watermark.inc.php");  
  $img = $loc->generate_img();
  $wm_img = new img_watermark ($img->imgres);
  $wm_img->output();
  exit();
}

if ($_REQUEST['action'] == 'genimglieu')
{
  $idlieu = intval($_REQUEST['id_lieu']);
  $level  = intval($_REQUEST['level']);
  
  $lieu = new lieu($site->db);
  $lieu->load_by_id($idlieu);

  require_once($topdir. "include/pgsqlae.inc.php");
  require_once($topdir. "include/cts/imgloc.inc.php");
  
  $pgconn = new pgsqlae();

  $loc = new imgloc(800, $level, $site->db, $pgconn);
  $loc->add_location_by_object($lieu);
  $loc->add_context();

  require_once ($topdir . "include/watermark.inc.php");  
  $img = $loc->generate_img();
  $wm_img = new img_watermark ($img->imgres);
  $wm_img->output();
  
  exit();
}

$pays = new pays($site->db,$site->dbrw);
$ville = new ville($site->db,$site->dbrw);
$lieu = new lieu($site->db,$site->dbrw);

if ( isset($_REQUEST["id_lieu"]) )
  $lieu->load_by_id($_REQUEST["id_lieu"]);
  
elseif ( isset($_REQUEST["id_geopoint"]) )
  $lieu->load_by_id($_REQUEST["id_geopoint"]);
  
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
  if ( $_REQUEST["action"] == "kml" )
  {
    header("Content-type: application/vnd.google-earth.kml+xml");
    header("Content-Disposition: filename=ae_utbm_".$lieu->id.".kml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
    echo "<kml xmlns=\"http://earth.google.com/kml/2.1\">";
    echo "<Placemark id=\"ae_utbm_fr_geopoint_".$lieu->id_geopoint."\">";
    echo "<name>".htmlspecialchars($lieu->nom)."</name>";
    echo "<description>http://ae.utbm.fr/lieu.php?id_lieu=".$lieu->id."</description>";
    echo "<Point>";
    echo "<coordinates>".sprintf("%.12F",$lieu->long*360/2/M_PI).",".
      sprintf("%.12F",$lieu->lat*360/2/M_PI)."</coordinates>";
    echo "</Point>";
    echo "</Placemark>";
    echo "</kml>";
    exit();
  }
  
  
  
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
  
  $map = new gmap("map");
  $map->add_marker("lieu",$lieu->lat,$lieu->long);
  $cts->add($map);
  
  $req = new requete($site->db, "SELECT id_lieu, nom_geopoint FROM loc_lieu 
  INNER JOIN geopoint ON (geopoint.id_geopoint=loc_lieu.id_lieu)
  WHERE id_lieu_parent='".mysql_real_escape_string($lieu->id)."' ORDER BY nom_geopoint");
		      
  if ( $req->lines > 0 )
    $cts->add(new sqltable("listsublieux", "Sous-lieux", $req, "loc.php", 
                           "id_lieu", 
                           array("nom_geopoint"=>"Nom"), 
                           array(), array(),array()),true); 

  //TODO: Lister les informations liées à ce lieu
  // - Catégories du SAS
  
  $sql = new requete($site->db,"SELECT MIN(nvl_dates.date_debut_eve) AS date, nvl_nouvelles.* FROM nvl_nouvelles " .
  			"LEFT JOIN nvl_dates ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
  			"WHERE id_lieu='".$lieu->id."' AND modere_nvl='1' " .
  			"GROUP BY nvl_dates.id_nouvelle ".
  			"ORDER BY 1 DESC");
  
  if ( $sql->lines > 0 )
  {
    $lst = new itemlist("Nouvelles liées à ce lieu");
  	while ( $row = $sql->get_row() )
  	  $lst->add("<a href=\"news.php?id_nouvelle=".$row['id_nouvelle']."\">".$row['titre_nvl']."</a> <span class=\"hour\">le ".strftime("%A %d %B %G à %H:%M",strtotime($row['date_debut_eve']))."</span>");	
  	$cts->add($lst,true);
  }			
  			
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
  if (isset($_REQUEST['level']))
    {
      if ($_REQUEST['level'] == 1)
	$cts->add_paragraph("Echelle: Mondiale");
      if ($_REQUEST['level'] == 2)
	$cts->add_paragraph("Echelle: Continentale");
      if ($_REQUEST['level'] == 3)
	$cts->add_paragraph("Echelle: Nationale");
      if ($_REQUEST['level'] == 4)
	$cts->add_paragraph("Echelle: France / Régionale");
      if ($_REQUEST['level'] == 5)
	$cts->add_paragraph("Echelle: France / Départementale");
     
    }

  $cts->add_paragraph("Pays: ".$pays->get_html_link());
  $cts->add_paragraph("Position: ".geo_radians_to_degrees($ville->lat)."N , ".geo_radians_to_degrees($ville->long)."E");
  if (isset($_REQUEST['level']))
    $cts->add_paragraph("<center><img src=\"loc.php?action=genimgville&idville=".$ville->id."&level=".intval($_REQUEST['level'])."\" alt=\"position ville\" /></center>\n");
  else
    $cts->add_paragraph("<center><img src=\"loc.php?action=genimgville&idville=".$ville->id."\" alt=\"position ville\" /></center>\n");
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

$req = new requete($site->db, "SELECT * 
FROM loc_lieu 
INNER JOIN geopoint ON (loc_lieu.id_lieu=geopoint.id_geopoint)
LEFT JOIN loc_ville ON (geopoint.id_ville=loc_ville.id_ville) 
WHERE id_lieu_parent IS NULL ORDER BY nom_geopoint");

$cts->add(new sqltable("listsublieux", "Lieux racines", $req, "loc.php", 
                       "id_lieu", 
                       array("nom_geopoint"=>"Nom","nom_ville"=>"Ville"), 
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