<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");

$site = new site (); 
$dbpg = new mysqlpg ();

new requete($site->dbrw,"TRUNCATE TABLE pg_category");

require_once($topdir."include/entities/pgfiche.inc.php");
require_once($topdir."include/entities/rue.inc.php");
require_once($topdir."include/entities/ville.inc.php");

$cat1_to_cat=array();
$cat2_to_cat=array();
$cat3_to_cat=array();

$rootcat = new pgcategory($site->db,$site->dbrw);
$rootcat->create ( null, "Le Guide", "", 1, null, null, null, null, null, null );

$req = new requete($dbpg,"SELECT * FROM pg_cat1");

while ( $row = $req->get_row() )
{
  $cat = new pgcategory($site->db,$site->dbrw);
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
  
  $cat = new pgcategory($site->db,$site->dbrw);
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
  
  $cat = new pgcategory($site->db,$site->dbrw);
  $cat->create ( $parent->id, utf8_encode($row['nom']), "", 1, 
    $parent->couleur_bordure_web,
    $parent->couleur_titre_web,
    $parent->couleur_contraste_web,
    $parent->couleur_bordure_print,
    $parent->couleur_titre_print,
    $parent->couleur_contraste_print );

  $cat3_to_cat[$row['id']] = $cat;
}


$ville = new ville($site->db);
$secteurs2=array();
$req = new requete($dbpg,"SELECT * FROM pg_secteur2");

$manual =  array (
"90800 Argièsans" => 34576,
"90800 Bavilliers" => 34580,"90140 Autrechêne" => 2283465, "90200 Auxelles Bas" => 34577,"90200 Auxelles Haut" => 34578,"90200 Ballon d'Alsace" => 34632,
"90200 Gromagny" => 34621,"90200 Lepuix Gy" => 34632, 
"90200 Riervescemont" => 2283466,"90110 Bourg sous Châtelet" => 34588,
"90110 Romagny sous Rougemont" => 34648, "90110 Rougemont le Château" => 34651,
"90110 Saint Germain le Châtelet" => 34653,"90700 Châtenois les Forges" => 34594,"90100 Chavannes les Grands" => 34597,
"90100 Courcelles" => 2283467,
"90100 Fêche l'Église" => 34612,
"90100 Lebetain" => 34630, "90100 Lepuix Neuf" => 34631,
"90100 Villars le Sec" => 34665,"90100 Saint Dizier l'Êveque" => 34652,"90340 Chevremont" => 34598,"90300 Eloie" => 34608,
"90300 Lachapelle sous Chaux" => 34624,"90170 Etueffont" => 2283468,
"90170 Petitmagny" => 2283469,
"90170 Lamadeleine Val des Anges" => 34628,"90350 Evette Salbert" => 2283470,"90400 Sévenans" => 90400,"90360 Lachapelle ss Rougemont" => 34625,
"90130 Montreux Château" => 34637,"90130 Petit Croix" => 34643,"Montchéroux" => 9142,"Le phoenix" => 25550,"Brussurel" => 27852,"Point de ronde" => 9206,"90400 Trévenans" => 2283471,
"90400 Meroux" => 2283472,
"90360 Petitefontaine" => 2283473,
"Echenans sur l'Etang" => 2283474
);


while ( $row = $req->get_row() )
{
  $id_ville=null;
  $complement="";
  
  $row['nom'] = utf8_encode($row['nom']);
  
  if ( preg_match('/^([^\(\)]*) \(([A-Z0-9]*)\)$/ui',$row['nom'], $match) )
  {
    $nom = $match[1];
    $complement = $match[2]; 
  }
  else
  {
    $nom = $row['nom'];
    $complement = "";
  }
  
  if ( !empty($row['code_postal']) )
    $nom = str_replace(" ","",$row['code_postal'])." ".$nom;
  
  if ( isset($manual[$nom]) )
  {
    $id_ville=$manual[$nom];
    
    $ville->load_by_id($id_ville);
  }
  else
  {
    $candidates = $ville->fsearch ( $nom, 2, array("id_pays"=>1) );
    
    if ( !is_null($candidates) && count($candidates) == 1 )
    {
      reset($candidates);
      $id_ville=key($candidates);
    }
    else
    {
      echo "$nom non trouvé !<br/>\n";
      print_r($candidates);
      print_r($row);
      exit();  
    }
  }
  $secteurs2[]=array("old"=>$nom,"id_ville"=>$id_ville,"complement"=>$complement);
}

new requete($site->dbrw,"TRUNCATE TABLE pg_rue");
new requete($site->dbrw,"TRUNCATE TABLE pg_typerue");

$typesderue=array();
$req = new requete($dbpg,"SELECT * FROM pg_voie_type");
while ( $row = $req->get_row() )
{
  $typerue = new typerue($site->db,$site->dbrw);
  $typerue->create(utf8_encode($row['nom']));
  $typesderue[$row['id']] = $typerue;
}

$rues = array();

$req = new requete($dbpg,"SELECT * FROM pg_voie");
while ( $row = $req->get_row() )
{
  $rue = new rue($site->db,$site->dbrw);
  
  $sec = array();
  
  for($i=1;$i<7;$i++)
    if ( $row["id_secteur$i"] != -1 )
      $sec[] = $secteurs2[$row["id_secteur$i"]];
  
  $id_ville=null;
  $complement="";
  
  foreach( $sec as $s )
  {
    if ( is_null($id_ville) )
      $id_ville = $s["id_ville"];
    elseif ( $id_ville != $s["id_ville"] )
    {
      echo "Pas cohérent !<br/>\n";
      print_r($row);
      print_r($sec);
      //exit();  
    }
    if ( !empty($s["complement"]) )
    {
      if ( empty($complement) )
        $complement = $s["complement"];
      else
        $complement .= ", ".$s["complement"];
    }
  }
  
  if ( is_null($id_ville) )
  {
    echo "Pas de ville !<br/>\n";
    print_r($row);
    print_r($sec);
    exit();  
  }
  
  $rue->create ( $row['nom'], $complement, $typesderue[$row['id_type']]->id, $id_ville);
  
  $rues[$row['id']] = $rue;
}


?>