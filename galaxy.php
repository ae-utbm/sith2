<?php

$topdir = "./";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/galaxy.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new site ();
$galaxy = new galaxy($site->db,$site->dbrw);

// trichons un peu...

$GLOBALS["entitiescatalog"]["utilisateur"][3]="galaxy.php";


define('AREA_WIDTH',500);
define('AREA_HEIGHT',500);

if ( $_REQUEST["action"] == "area_image" )
{
  $highlight = null;
  
  if ( isset($_REQUEST["highlight"]) )
    $highlight = explode(",",$_REQUEST["highlight"]);
  
  header("Content-type: image/png");
  $galaxy->render_area ( intval($_REQUEST['x']), intval($_REQUEST['y']), AREA_WIDTH, AREA_HEIGHT, null, $highlight );
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
    
    $buffer= "<div style=\"position:relative;\"><img src=\"galaxy.php?action=area_image&amp;x=$tx&amp;y=$ty&amp;highlight=$hl\" />";
    
    $x1 = $tx-3;
    $y1 = $ty-3;
    $x2 = $tx+(AREA_WIDTH+3);
    $y2 = $ty+(AREA_HEIGHT+3);
    
    $req = new requete($site->db, "SELECT ".
      "rx_star, ry_star, id_star ".
      "FROM  galaxy_star ".
      "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2" );  
    
    while($row = $req->get_row() )
    {
      $x = $row["rx_star"]-$tx-3;
      $y = $row["ry_star"]-$ty-3; 
      $buffer .= "<a href=\"galaxy.php?id_utilisateur=".$row["id_star"]."\" style=\"position:absolute;left:".$x."px;top:".$y."px;width:6px;height:6px;overflow:hidden;\">&nbsp;</a>";
    }
    $buffer.="</div>";
    
    $cts->puts($buffer);  
   
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


$site->start_page("rd","galaxy");
$cts = new contents("Galaxy");

$buffer= "<div style=\"position:relative;\"><img src=\"var/galaxy.png\" />";

$req = new requete($site->db, "SELECT ".
  "rx_star, ry_star, id_star ".
  "FROM  galaxy_star");  

while($row = $req->get_row() )
{
  $x = $row["rx_star"]-3;
  $y = $row["ry_star"]-3; 
  $buffer .= "<a href=\"galaxy.php?id_utilisateur=".$row["id_star"]."\" style=\"position:absolute;left:".$x."px;top:".$y."px;width:6px;height:6px;overflow:hidden;\">&nbsp;</a>";
}
$buffer.="</div>";

$cts->puts($buffer);  


$site->add_contents($cts);
$site->end_page();

?>