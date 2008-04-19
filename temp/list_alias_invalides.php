<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT id_utilisateur, CONCAT(prenom_utl,' ',nom_utl) AS nom_utilisateur, alias_utl FROM utilisateurs");

while( $row = $req->get_row() )
{
  if (preg_match("#^([a-z0-9][a-z0-9\.]+)$#i",strtolower($row["alias_utl"])) || empty($row["alias_utl"]) )
    echo $row["id_utilisateur"]." : ".$row["nom_utilisateur"]." (".$row["alias_utl"].")<br />";
}

?>
