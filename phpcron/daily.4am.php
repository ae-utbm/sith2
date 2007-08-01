<?php

/*
 * Daily at 4am (04h)
 */
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

$topdir="../";
require_once($topdir. "include/site.inc.php");

$site = new site ();

echo "==== ".date("d/m/Y")." ====\n";

// Tâche 1 : Enelver les verrous expirés

require_once($topdir . "comptoir/include/venteproduit.inc.php");

$req = new requete($site->db,"SELECT * FROM `cpt_verrou` WHERE DATEDIFF(NOW(),date_res) >= 1");

$vp = new venteproduit($site->db,$site->dbrw);
$client = new utilisateur($site->db);

while ( $row = $req->get_row() )
{
  echo "debloquer('".$row['id_utilisateur']."','".$row['id_produit']."','".$row['id_comptoir']."','".$row['quantite']."');\n";
  
  $client->id = $row['id_utilisateur'];
  $vp->load_by_id ( $row['id_produit'], $row['id_comptoir'], true );
  $vp->debloquer ( $client, $row['quantite'] );
}

// Tâche 2 : Nettotage des produits (et eventuels verrous liés), et des types

$req = new requete($site->dbrw,"DELETE FROM `cpt_produits` WHERE prod_archive=1 AND NOT EXISTS(SELECT * FROM cpt_vendu WHERE cpt_vendu.id_produit=cpt_produits.id_produit)");

$req = new requete($site->dbrw,"DELETE FROM `cpt_verrou` WHERE NOT EXISTS(SELECT * FROM cpt_produits WHERE cpt_verrou.id_produit=cpt_produits.id_produit)");

$req = new requete($site->dbrw,"DELETE FROM `cpt_type_produit` WHERE NOT EXISTS ( SELECT * FROM cpt_produits WHERE cpt_produits.id_typeprod=cpt_type_produit.id_typeprod)");


?>
