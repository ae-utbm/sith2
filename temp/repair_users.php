<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
$site = new site();

header("Content-Type: text/html; charset=utf-8");

/**
 * Script de vérification des catégories des doublons utilisateurs
 */

echo "<h1>AE2/USER: Auto repair</h1>\n";

$sql = new requete($site->db,"SELECT * FROM `utilisateurs`");

echo "<h2>Vérification des potentiels doublons</h2>\n";

echo "<ul>\n";

while ( $row = $sql->get_row() )
{
  if(!isset($names[$row['nom_utl']]))
  {
    $names[$row['nom_utl']]=array();
    $names[$row['nom_utl']][$row['prenom_utl']]=1;
  }
  else
  {
    if(!isset($names[$row['nom_utl']][$row['prenom_utl']]))
      $names[$row['nom_utl']][$row['prenom_utl']]=1;
    else
      $names[$row['nom_utl']][$row['prenom_utl']]++;
  }
}

foreach($names as $name => $firstnames)
{
  foreach($firstnames as $firstname => $num)
    if($num>1)
      echo "<li>".$name." ".$firstname." : ".$num."</li>\n";
}

echo "</ul>\n";


?>
