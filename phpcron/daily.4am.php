<?php

/*
 * Daily at 4am (04h)
 */
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

$topdir=$_SERVER['SCRIPT_FILENAME']."/../";
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

new requete($site->dbrw,"DELETE FROM `cpt_produits` WHERE prod_archive=1 AND NOT EXISTS(SELECT * FROM cpt_vendu WHERE cpt_vendu.id_produit=cpt_produits.id_produit)");

new requete($site->dbrw,"DELETE FROM `cpt_verrou` WHERE NOT EXISTS(SELECT * FROM cpt_produits WHERE cpt_verrou.id_produit=cpt_produits.id_produit)");

new requete($site->dbrw,"DELETE FROM `cpt_type_produit` WHERE NOT EXISTS ( SELECT * FROM cpt_produits WHERE cpt_produits.id_typeprod=cpt_type_produit.id_typeprod)");

// Tâche 3 : Nettoyage des créneaux "vides" expriés

new requete($site->dbrw,"DELETE FROM `pl_gap` WHERE NOT EXISTS ( SELECT * FROM pl_gap_user WHERE pl_gap_user.id_gap = pl_gap.id_gap AND pl_gap_user.id_planning = pl_gap.id_planning ) AND end_gap < NOW( )");

// Tâche 4 : Nettoyages des sessions expirés

new requete($site->dbrw, "DELETE FROM `site_sessions` WHERE expire_sess < NOW() AND expire_sess IS NOT NULL");

// Tâche 5 : Nettoyages des sessions trop vieilles

new requete($site->dbrw, "DELETE FROM `site_sessions` WHERE datediff(NOW(),derniere_visite) > 120");

// Tâche 6 : Optimisation de la table des créneaux machine

new requete($site->dbrw, "OPTIMIZE TABLE `mc_creneaux`");

?>
