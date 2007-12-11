<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");

$site = new site (); 
$dbpg = new mysqlpg ();

new requete($site->dbrw,"TRUNCATE TABLE pg_category");

require_once($topdir."include/entities/pgfiche.inc.php");

$cat1_to_cat=array();
$cat2_to_cat=array();
$cat3_to_cat=array();

$rootcat = pgcategory($site->db,$site->dbrw);
$rootcat->create ( null, "Le Guide", "", 1, null, null, null, null, null, null );

$req = new requete($dbpg,"SELECT * FROM pg_cat1");

while ( $row = $req->get_row() )
{
  $cat = pgcategory($site->db,$site->dbrw);
  $cat->create ( $rootcat->id, utf8_encode($row['nom']), "", $row['ordre'], 
    sprintf("%02x%02x%02x",$row['r1'],$row['v1'],$row['b1']), 
    sprintf("%02x%02x%02x",$row['r2'],$row['v2'],$row['b2']), 
    sprintf("%02x%02x%02x",$row['r3'],$row['v3'],$row['b3']), 
    sprintf("%02x%02x%02x%02x",$row['c1'],$row['m1'],$row['j1'],$row['n1']), 
    sprintf("%02x%02x%02x%02x",$row['c2'],$row['m2'],$row['j2'],$row['n2']), 
    sprintf("%02x%02x%02x%02x",$row['c3'],$row['m3'],$row['j3'],$row['n3']) );

  $cat1_to_cat[$row['id']] = $cat;
}


$req = new requete($dbpg,"SELECT * FROM pg_cat2");
while ( $row = $req->get_row() )
{
  $parent = $cat1_to_cat[$row['id_cat1']];
  
  $cat = pgcategory($site->db,$site->dbrw);
  $cat->create ( $parent->id, utf8_encode($row['nom']), "", 1, 
    $parent->couleur_bordure_web,
    $parent->couleur_titre_web,
    $parent->couleur_contraste_web,
    $parent->couleur_bordure_print,
    $parent->couleur_titre_print,
    $parent->couleur_contraste_print );

  $cat2_to_cat[$row['id']] = $cat;
}

$req = new requete($dbpg,"SELECT * FROM pg_cat3");
while ( $row = $req->get_row() )
{
  $parent = $cat2_to_cat[$row['id_cat2']];
  
  $cat = pgcategory($site->db,$site->dbrw);
  $cat->create ( $parent->id, utf8_encode($row['nom']), "", 1, 
    $parent->couleur_bordure_web,
    $parent->couleur_titre_web,
    $parent->couleur_contraste_web,
    $parent->couleur_bordure_print,
    $parent->couleur_titre_print,
    $parent->couleur_contraste_print );

  $cat3_to_cat[$row['id']] = $cat;
}




?>