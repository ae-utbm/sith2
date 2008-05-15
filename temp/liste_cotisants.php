<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT CONCAT(prenom_utl,' ',nom_utl) AS nom_utilisateur FROM utilisateurs WHERE ae_utl='1' ORDER by nom_utl");

$i = 0;
echo "<table>";
while( $row = $req->get_row() )
{
  if($i == 0){echo "<tr>";}
  echo "<td>".$row["nom_utilisateur"]."</td>";
  if($i == 3){echo "</tr>"; $i = 0;}else{$i++;}
}
echo "</table>";
?>
