<?php

$topdir = "./";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/galaxy.inc.php");

$site = new site ();
$galaxy = new galaxy($site->db,$site->dbrw);

define('AREA_WIDTH',500);
define('AREA_HEIGHT',500);

if ( $_REQUEST["action"] == "area_image" )
{
  header("Content-type: image/png");
  $galaxy->render_area ( intval($_REQUEST['x']), intval($_REQUEST['y']), AREA_WIDTH, AREA_HEIGHT );
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
  
  $req = new requete($site->db,"SELECT rx_star,ry_star WHERE galaxy_star WHERE id_star='".mysql_real_escape_string($user->id)."'");

  if ( $req->lines == 0 )
  {
    $cts->add_paragraph("Non présent dans galaxy");  
  }
  else
  {
    list($rx,$ry) = $req->get_row();
    
    $cts->add_title(2,"Localisation");
    $cts->add_paragraph("<img src=\"galaxy.php?action=area_image&amp;x=".($rx-250)."&amp;y=".($ry-250)."\" />");  
   
    $req = new requete($site->db,
    "SELECT length_link, ideal_length_link, 
    tense_link, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur
    FROM galaxy_link
    INNER JOIN utilisateurs ON ( id_star_a=id_utilisateur)
    WHERE id_star_b='".mysql_real_escape_string($user->id)."'
    UNION
    SELECT length_link, ideal_length_link, 
    tense_link, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur
    FROM galaxy_link
    INNER JOIN utilisateurs ON ( id_star_b=id_utilisateur)
    WHERE id_star_a='".mysql_real_escape_string($user->id)."'
    ORDER BY 1 DESC");
   
    $tbl = new sqltable(
      "listvoisins",
      "Personnes liées", $req, "galaxy.php?id_utilisateur=".$user->id,
      "id_star",
      array("length_link"=>"Distance réelle","ideal_length_link"=>"Distance idéale","tense_link"=>"Score","nom_utilisateur"=>"Nom"),
      array(), array(), array( )
      );
    $cts->add($tbl,true);  
    
    $req = new requete($site->db,
    "SELECT SQRT(POW(a.x_star-b.x_star,2)+POW(a.y_star-b.y_star,2)) AS dist, 
    COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom_utilisateur
    FROM galaxy_star AS a, galaxy_star AS b, utilisateurs
    WHERE a.id_star='".mysql_real_escape_string($user->id)."' 
    AND b.id_star=id_utilisateur 
    AND POW(a.x_star-b.x_star,2)+POW(a.y_star-b.y_star,2) < 1");
    
    
    $tbl = new sqltable(
      "listvoisins",
      "Voisinnage", $req, "galaxy.php?id_utilisateur=".$user->id,
      "id_star",
      array("dist"=>"Distance","nom_utilisateur"=>"Nom"),
      array(), array(), array( )
      );
    $cts->add($tbl,true);    
    
    
  }
 
  $site->add_contents($cts);
  $site->end_page();
  exit();  
}


$site->start_page("rd","galaxy");
$cts = new contents("Galaxy");

$site->add_contents($cts);
$site->end_page();

?>