<?php

function extract_tags_iso ( $textiso )
{
  global $nalnum;
  $motclefs=array();
  $nalnum = "\. _\n\r,;:'\!\?\(\)\-"; // '
  $text = strtolower($textiso);
  while ( eregi("(^|[$nalnum])([^$nalnum]{4,128})($|[$nalnum])(.*)",$text,$regs) )
  {
    $text = $regs[4];
    $motclef = utf8_encode($regs[2]);
    $motclefs[$motclef] = $motclef;
  }  
  return $motclefs;
}

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");

$site = new site (); 
$dbpg = new mysqlpg ();

new requete($site->dbrw,"TRUNCATE TABLE pg_category");
new requete($site->dbrw,"TRUNCATE TABLE pg_category_tags");

require_once($topdir."include/entities/pgfiche.inc.php");
require_once($topdir."include/entities/pgtype.inc.php");
require_once($topdir."include/entities/rue.inc.php");
require_once($topdir."include/entities/ville.inc.php");

$cat1_to_cat=array();
$cat2_to_cat=array();
$cat3_to_cat=array();

echo "<h1>Catégories</h1>\n";

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

  $cat->set_tags($cat->nom);

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

  $cat->set_tags(implode(",",$parent->_tags).",".$cat->nom);

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

  $cat->set_tags(implode(",",$parent->_tags).",".$cat->nom);

  $cat3_to_cat[$row['id']] = $cat;
}

echo "<h1>Traitement des secteurs</h1>\n";

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
  $secteurs2[$row['id']]=array("old"=>$nom,"id_ville"=>$id_ville,"complement"=>$complement);
}

new requete($site->dbrw,"TRUNCATE TABLE pg_rue");
new requete($site->dbrw,"TRUNCATE TABLE pg_typerue");

echo "<h1>Types de rues</h1>\n";

$typesderue=array();
$req = new requete($dbpg,"SELECT * FROM pg_voie_type");
while ( $row = $req->get_row() )
{
  $typerue = new typerue($site->db,$site->dbrw);
  $typerue->create(utf8_encode($row['nom']));
  $typesderue[$row['id']] = $typerue->id;
}

$rues = array();

echo "<h1>Rues</h1>\n";

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
  $err=false; 
      
  foreach( $sec as $s )
  {
    if ( is_null($id_ville) )
      $id_ville = $s["id_ville"];
    elseif ( $id_ville != $s["id_ville"] )
    {
      echo "Pas cohérent !<br/>\n";
      $err=true; 
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
    $err=true; 
  }
  
  if ( $err )
  {
    echo "<i>Tentative de resolution</i><br/>\n";
    $req1 = new requete($dbpg,"SELECT secteur FROM pg_liste WHERE pg_liste.voie='".$row['id']."' GROUP BY secteur");
    
    if ( $req1->lines > 0 )
    {
      $id_ville=null;
      $complement="";     
      $err=false;  
      
      echo "Secteurs d'origine : ";
      
      for($i=1;$i<7;$i++)
        if ( $row["id_secteur$i"] != -1 )
          echo $row["id_secteur$i"]."  ";
     
      echo "<br/>\nSecteurs retrouvés : ";
      
      $sec = array();
      
      while ( list($secteur) = $req1->get_row() )
      {
        $sec[] = $secteurs2[$secteur];
        echo $secteur."  ";
        if ( !isset($secteurs2[$secteur]) )
          echo "(non trouvé) ";
      }
      echo "<br/>\n";

      foreach( $sec as $s )
      {
        if ( is_null($id_ville) )
          $id_ville = $s["id_ville"];
        elseif ( $id_ville != $s["id_ville"] )
        {
          echo "Pas cohérent !<br/>\n";
          $err=true; 
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
        echo "<b>FATAL</b> Pas de ville !<br/>\n";
        $err=true; 
      }      
    }
    else
      echo "<b>FATAL</b> Jamais utilisé !<br/>\n";
  }
  
  
  if ( !isset($typesderue[$row['id_type']]) )
  {
    echo "<b>FATAL</b> Type de rue iconnu !<br/>\n";
    print_r($row);
    print_r($sec);
    $err = true;
  }
  
  if ( !$err )
  {
    $rue->create ( utf8_encode($row['nom']), utf8_encode($complement), $typesderue[$row['id_type']], $id_ville);
    $rues[$row['id']] = $rue->id;
  }
  else
  {
    echo "<b>Ignoré</b><br/>\n";
    print_r($row);
    print_r($sec);    
    echo "<br/>\n<br/>\n<br/>\n\n";
  }
}

echo "<h1>Creation des types (en dur)</h1>\n";

new requete($site->dbrw,"TRUNCATE TABLE pg_service");
new requete($site->dbrw,"TRUNCATE TABLE pg_typereduction");

$typesreduction=array();

$treduc = new typereduction($site->db,$site->dbrw);
$treduc->create ( "Etudiant", "", "" );
$typesreduction["etu"] = $treduc->id;
$treduc->create ( "BIJ", "", "" );
$typesreduction["bij"] = $treduc->id;
$treduc->create ( "CE PSA", "", "" );
$typesreduction["psa"] = $treduc->id;
$treduc->create ( "CE ALSTOM", "", "" );
$typesreduction["alsthom"] = $treduc->id;
$treduc->create ( "SMEREB", "", "" );
$typesreduction["smereb"] = $treduc->id;
$treduc->create ( "FRACAS", "", "" );
$typesreduction["fracas"] = $treduc->id;
$treduc->create ( "Petit Géni", "Sur presentation de l'edition papier du petit géni", "" );
$typesreduction["petitgeni"] = $treduc->id;
$treduc->create ( "Offre", "", "" );
$typesreduction["divers"] = $treduc->id;

$services =array();
$service = new service($site->db,$site->dbrw);
$service->create("Accès handicapé","","");
$services["handicape"] = $service->id;

new requete($site->dbrw,"DELETE FROM geopoint WHERE type_geopoint='pgfiche'");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche_reduction");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche_extra_pgcategory");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche_reduction");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche_service");
new requete($site->dbrw,"TRUNCATE TABLE pg_fiche_tags");

echo "<h1>Import des fiches</h1>\n";
$fiche = new pgfiche($site->db,$site->dbrw);

$fiches=array();
$req = new requete($dbpg,"SELECT * FROM pg_liste WHERE import_liste=1 AND id_liste_parent IS NULL");
while ( $row = $req->get_row() )
{
  if ( !isset($cat3_to_cat[$row['cat']]) )
  {
    echo "<p>Categorie inconnue : ".$row['cat']."</p>";
  }
  else
  {
  
    $fiche->id=null;
    
    $fiche->create ( $secteurs2[$row['secteur']]['id_ville'], utf8_encode($row['nom']), null, null, null, $cat3_to_cat[$row['cat']]->id, $rues[$row['voie']], null, utf8_encode($row['description']), utf8_encode($row['description']), utf8_encode($row['tel']), utf8_encode($row['fax']), utf8_encode($row['email']), utf8_encode($row['http']), utf8_encode($row['no']), utf8_encode($row['adresse'])."\n".$secteurs2[$row['secteur']]['old'], false, $row['mav'], !empty($row['coupdecoeur']), utf8_encode($row['coupdecoeur']), utf8_encode($row['remarques']), strtotime($row['date_maj']), null, null );
    
    $tags = /*array_merge(*/$cat3_to_cat[$row['cat']]->_tags/*,extract_tags_iso($row['description']));*/
    $fiche->set_tags(implode(",",$tags).",".$fiche->nom);
    
    if ( $row['handicape'] )
      $fiche->add_service ( $services["handicape"], "", strtotime($row['date_maj']) );
    
    if ( $row['reduc_bij'] )
      $fiche->add_reduction ( $typesreduction["bij"], "", "", "", strtotime($row['date_maj']) );
    
    if ( $row['reduc_psa'] )
      $fiche->add_reduction ( $typesreduction["psa"], utf8_encode($row['reduc_psa']), "", "", strtotime($row['date_maj']) );
    if ( $row['reduc_alsthom'] )
      $fiche->add_reduction ( $typesreduction["alsthom"], utf8_encode($row['reduc_alsthom']), "", "", strtotime($row['date_maj']) );
    if ( $row['reduc_smereb'] )
      $fiche->add_reduction ( $typesreduction["smereb"], utf8_encode($row['reduc_smereb']), "", "", strtotime($row['date_maj']) );
    if ( $row['reduc_fracas'] )
      $fiche->add_reduction ( $typesreduction["fracas"], utf8_encode($row['reduc_fracas']), "", "", strtotime($row['date_maj']) );
    if ( $row['reduc_divers'] )
      $fiche->add_reduction ( $typesreduction["divers"], utf8_encode($row['reduc_divers']), "", "", strtotime($row['date_maj']) );
    if ( $row['reduc_petitgeni'] )
      $fiche->add_reduction ( $typesreduction["petitgeni"], utf8_encode($row['reduc_petitgeni']), "", "", strtotime($row['date_maj']) );
  
    $fiches[$row['id']] = $fiche->id;  
  }
}

echo "<h1>Import des fiches doublés</h1>\n";

$req = new requete($dbpg,"SELECT * FROM pg_liste WHERE import_liste=1 AND id_liste_parent IS NOT NULL ORDER BY id");


while ( $row = $req->get_row() )
{
  if ( !isset($cat3_to_cat[$row['cat']]) )
  {
    echo "<p>Categorie inconnue : ".$row['cat']."</p>";
  }
  elseif ( isset($fiches[$row['id_liste_parent']]) )
  {
    $fiche->load_by_id($fiches[$row['id_liste_parent']]);
    
    $fiche->add_extra_pgcategory ( $cat3_to_cat[$row['cat']]->id, utf8_encode($row['nom']), utf8_encode($row['description']) );
    
    $fiches[$row['id']] = $fiche->id;  
    
  }
  else
  {
    echo "<p>[".$row['id']."] Oups : ".$row['id_liste_parent']."</p>";
  }
}




?>