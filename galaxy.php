<?php
/* Copyright 2007
 *
 * - Julien Etelain < julien at pmad dot net >
 *
 * "AE Recherche & Developpement" : Galaxy
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/galaxy.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new site ();
$galaxy = new galaxy($site->db,$site->dbrw);

// trichons un peu...

$GLOBALS["entitiescatalog"]["utilisateur"][3]="galaxy.php";

$ready = $galaxy->is_ready_public(); 

if ( !$ready )
{
  if ( $_REQUEST["action"] == "area_image" || $_REQUEST["action"] == "area_html"  )
    exit();  
  $site->fatal_partial();
  exit();  
}

define('AREA_WIDTH',500);
define('AREA_HEIGHT',500);

if ( $_REQUEST["action"] == "area_image" || $_REQUEST["action"] == "area_html"  )
{
  $lastModified = gmdate('D, d M Y H:i:s', filemtime("var/mini_galaxy.png") ) . ' GMT';    
  $etag=md5($_SERVER['SCRIPT_FILENAME']."?".$_SERVER['QUERY_STRING'].'#'.$lastModified);

  if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) )
  {
    $ifModifiedSince = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($lastModified == $ifModifiedSince)
    {
      header("HTTP/1.0 304 Not Modified");
      header('ETag: "'.$etag.'"');
      exit();
    }
  }
  
  if ( isset($_SERVER['HTTP_IF_NONE_MATCH']) )  {    if ( $etag == str_replace('"', '',stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) )
    {
      header("HTTP/1.0 304 Not Modified");
      header('ETag: "'.$etag.'"');
      exit();
    }
  }
  header("Cache-Control: must-revalidate");  header("Pragma: cache");
  header("Last-Modified: ".$lastModified);
  header("Cache-Control: public");
  header('ETag: "'.$etag.'"');
}

if ( $_REQUEST["action"] == "area_image" )
{
  $highlight = null;
  
  if ( isset($_REQUEST["highlight"]) )
    $highlight = explode(",",$_REQUEST["highlight"]);
  
  header("Content-type: image/png");
  $galaxy->render_area ( intval($_REQUEST['x']), intval($_REQUEST['y']), AREA_WIDTH, AREA_HEIGHT, null, $highlight );
  exit();
}

if ( $_REQUEST["action"] == "area_html" )
{
	header("Content-Type: text/html; charset=utf-8");
	$tx = intval($_REQUEST['x']);
	$ty = intval($_REQUEST['y']);
	
  if ( isset($_REQUEST["highlight"]) )	
  echo "<div style=\"position:relative;\"><img src=\"?action=area_image&amp;x=$tx&amp;y=$ty&amp;highlight=".$_REQUEST["highlight"]."\" width=\"500\" height=\"500\" />";
  else
  echo "<div style=\"position:relative;\"><img src=\"?action=area_image&amp;x=$tx&amp;y=$ty\" width=\"500\" height=\"500\" />";
  
  
  $x1 = $tx;
  $y1 = $ty;
  $x2 = $tx+(AREA_WIDTH);
  $y2 = $ty+(AREA_HEIGHT);
  $req = new requete($site->db, "SELECT ".
    "rx_star, ry_star, id_star ".
    "FROM  galaxy_star ".
    "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2" );  
  while($row = $req->get_row() )
  {
    $x = $row["rx_star"]-$tx-3;
    $y = $row["ry_star"]-$ty-3; 
    $id = $row["id_star"];
    echo "<a href=\"galaxy.php?id_utilisateur=$id\" id=\"g$id\" onmouseover=\"show_tooltip('g$id','./','utilisateur','$id');\" onmouseout=\"hide_tooltip('g$id');\" style=\"position:absolute;left:".$x."px;top:".$y."px;width:6px;height:6px;overflow:hidden;\" >&nbsp;</a>";
  }
  echo"</div>";
  exit();
}

if ( isset($_REQUEST["id_utilisateur"]) )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  
	if ( !$user->is_valid() )
		$site->error_not_found("rd");
		
  $site->start_page("rd","galaxy");
  $cts = new contents("Galaxy : ".$user->prenom . " " . $user->nom);
  
  $req = new requete($site->db,"SELECT rx_star,ry_star FROM galaxy_star WHERE id_star='".mysql_real_escape_string($user->id)."'");

  if ( $req->lines == 0 )
  {
    $cts->add_paragraph("Non présent dans galaxy");  
  }
  else
  {
    list($rx,$ry) = $req->get_row();
    
    $cts->add_title(2,"Localisation");
    
    $hl = $user->id;
    
    $req = new requete($site->db,
    "SELECT id_star_a
    FROM galaxy_link
    WHERE id_star_b='".mysql_real_escape_string($user->id)."'
    UNION
    SELECT id_star_b
    FROM galaxy_link
    WHERE id_star_a='".mysql_real_escape_string($user->id)."'");
    
    while (list($id) = $req->get_row() )
      $hl .= ",".$id;
    
    $tx = intval($rx-(AREA_WIDTH/2));
    $ty = intval($ry-(AREA_HEIGHT/2));    

$site->add_css("css/galaxy.css");
$site->add_js("js/galaxy.js");

$cts->puts("<div class=\"map\" id=\"map\"><img src=\"var/mini_galaxy.png\" />
<div class=\"position\" id=\"position\"></div></div>
<div class=\"viewer\" id=\"viewer\">
<div class=\"square\" id=\"square0\"></div>
<div class=\"square\" id=\"square1\"></div>
<div class=\"square\" id=\"square2\"></div>
<div class=\"square\" id=\"square3\"></div>
<div class=\"square\" id=\"square4\"></div>
<div class=\"square\" id=\"square5\"></div>
<div class=\"square\" id=\"square6\"></div>
<div class=\"square\" id=\"square7\"></div>
<div class=\"square\" id=\"square8\"></div>
<div class=\"square\" id=\"square9\"></div>
<div class=\"square\" id=\"square10\"></div>
<div class=\"square\" id=\"square11\"></div>
<div class=\"square\" id=\"square12\"></div>
<div class=\"square\" id=\"square13\"></div>
<div class=\"square\" id=\"square14\"></div>
<div class=\"square\" id=\"square15\"></div>
</div><script>init_galaxy($tx,$ty,\"&highlight=$hl\");</script>");  
    
   
    $req = new requete($site->db,
    "SELECT length_link, ideal_length_link, 
    tense_link, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur,
    id_utilisateur
    FROM galaxy_link
    INNER JOIN utilisateurs ON ( id_star_a=id_utilisateur)
    WHERE id_star_b='".mysql_real_escape_string($user->id)."'
    UNION
    SELECT length_link, ideal_length_link, 
    tense_link, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur,
    id_utilisateur
    FROM galaxy_link
    INNER JOIN utilisateurs ON ( id_star_b=id_utilisateur)
    WHERE id_star_a='".mysql_real_escape_string($user->id)."'
    ORDER BY 1");
   
    $tbl = new sqltable(
      "listvoisins",
      "Personnes liées", $req, "galaxy.php?id_utilisateur=".$user->id,
      "id_star",
      array("length_link"=>"Distance réelle","ideal_length_link"=>"Distance idéale","tense_link"=>"Score","nom_utilisateur"=>"Nom"),
      array(), array(), array( )
      );
    $cts->add($tbl,true);  
    
    $cts->add_paragraph("Le score par lien est calculé à partir du nombre de photos où vous êtes tous deux présents, les liens de parrainage, et le temps inscrits dans les mêmes clubs et associations. Ensuite le score permet de déterminer la longueur du lien en fonction du score maximal de tous les liens de chaque personne.");
    
    $req = new requete($site->db,
    "SELECT SQRT(POW(a.x_star-b.x_star,2)+POW(a.y_star-b.y_star,2)) AS dist, 
    COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur,
    id_utilisateur
    FROM galaxy_star AS a, galaxy_star AS b, utilisateurs
    WHERE a.id_star='".mysql_real_escape_string($user->id)."' 
    AND a.id_star!=b.id_star
    AND b.id_star=id_utilisateur 
    AND POW(a.x_star-b.x_star,2)+POW(a.y_star-b.y_star,2) < 4 
    ORDER BY 1");
    
    
    $tbl = new sqltable(
      "listvoisins",
      "Voisinnage", $req, "galaxy.php?id_utilisateur=".$user->id,
      "id_star",
      array("dist"=>"Distance","nom_utilisateur"=>"Nom"),
      array(), array(), array( )
      );
    $cts->add($tbl,true);    
    
    $cts->add_paragraph("Il est possible que de nombreuses personnes soient dans votre \"voisinnage\" par pur harsard. Cependant en général il s'agit soit de personnes liées soit de personnes avec un profil similaire.");
  } 
 
  $site->add_contents($cts);
  $site->end_page();
  exit();  
}


$site->start_page("rd","galaxy - ae r&d");
$cts = new contents("galaxy");

$site->add_css("css/galaxy.css");
$site->add_js("js/galaxy.js");

list($top_x,$top_y,$bottom_x,$bottom_y) = $galaxy->limits();

$top_x = floor($top_x);
$top_y = floor($top_y);
$bottom_x = ceil($bottom_x);
$bottom_y = ceil($bottom_y);
  
$goX = (($bottom_x-$top_x)*50)-250;
$goY = (($bottom_y-$top_y)*50)-250;
    
$cts->add_title(2,"Voici galaxy");
    
$cts->puts("<div class=\"map\" id=\"map\"><img src=\"var/mini_galaxy.png\" />
<div class=\"position\" id=\"position\"></div></div>
<div class=\"viewer\" id=\"viewer\">
<div class=\"square\" id=\"square0\"></div>
<div class=\"square\" id=\"square1\"></div>
<div class=\"square\" id=\"square2\"></div>
<div class=\"square\" id=\"square3\"></div>
<div class=\"square\" id=\"square4\"></div>
<div class=\"square\" id=\"square5\"></div>
<div class=\"square\" id=\"square6\"></div>
<div class=\"square\" id=\"square7\"></div>
<div class=\"square\" id=\"square8\"></div>
<div class=\"square\" id=\"square9\"></div>
<div class=\"square\" id=\"square10\"></div>
<div class=\"square\" id=\"square11\"></div>
<div class=\"square\" id=\"square12\"></div>
<div class=\"square\" id=\"square13\"></div>
<div class=\"square\" id=\"square14\"></div>
<div class=\"square\" id=\"square15\"></div>
</div><script>init_galaxy($goX,$goY,\"\");</script>");  

$cts->add_paragraph("<a href=\"var/galaxy.png\">Tout galaxy sur une seule image</a>");

$frm = new form("galaxygo",$topdir."galaxy.php",true,"GET","Aller vers une personne");
$frm->add_entity_smartselect("id_utilisateur","Nom/Surnom",new utilisateur($site->db));
$frm->add_submit("go","Y aller");

$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();

?>