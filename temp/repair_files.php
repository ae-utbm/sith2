<?php

$topdir="../";
require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");

$dbrw = new mysqlae ("rw");

echo "<h2>Vérification des fichiers</h2>";

echo "<ul>\n";
$req = new requete($dbrw,"SELECT * FROM d_file");

while ( $row = $req->get_row() )
{
  $file = $topdir."var/files/".$row["id_file"];
  
  if ( !file_exists($file) )
    echo "<li><b>problème non solvable</b> : Fichier ".$row["id_file"]." absent.</li>\n";

  else
  {
    if ( $row["taille_file"] != file_size($file) )
    echo "<li><b>problème</b> : Fichier ".$row["id_file"]." taille invalide.</li>\n";
  }
}
echo "</ul>\n";

?>