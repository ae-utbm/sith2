<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT CONCAT(prenom_utl,' ',nom_utl) AS nom_utilisateur FROM utilisateurs WHERE ae_utl='1' ORDER by nom_utl");

while( $row = $req->get_row() )
  echo $row["nom_utilisateur"]."<br />";

?>
