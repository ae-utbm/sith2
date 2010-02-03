<?php

if(!isset($argc))
  exit();

/*
 * Daily at 4am (04h)
 */
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

$topdir=$_SERVER['SCRIPT_FILENAME']."/../";
require_once($topdir. "include/site.inc.php");

$site = new site ();

echo "==== ".date("d/m/Y")." ====\n";

// Tâche 1 : Nettoyage des produits (et eventuels verrous liés), et des types

new requete($site->dbrw,"DELETE FROM `cpt_produits` WHERE prod_archive=1 AND NOT EXISTS(SELECT * FROM cpt_vendu WHERE cpt_vendu.id_produit=cpt_produits.id_produit)");

new requete($site->dbrw,"DELETE FROM `cpt_verrou` WHERE NOT EXISTS(SELECT * FROM cpt_produits WHERE cpt_verrou.id_produit=cpt_produits.id_produit)");

new requete($site->dbrw,"DELETE FROM `cpt_type_produit` WHERE NOT EXISTS ( SELECT * FROM cpt_produits WHERE cpt_produits.id_typeprod=cpt_type_produit.id_typeprod)");

// Tâche 2 : Nettoyage des créneaux "vides" expriés

/*new requete($site->dbrw,"DELETE FROM `pl_gap` WHERE NOT EXISTS ( SELECT * FROM pl_gap_user WHERE pl_gap_user.id_gap = pl_gap.id_gap AND pl_gap_user.id_planning = pl_gap.id_planning ) AND end_gap < NOW( )");*/

// Tâche 3 : Nettoyages des sessions expirés

new requete($site->dbrw, "DELETE FROM `site_sessions` WHERE expire_sess < NOW() AND expire_sess IS NOT NULL");

// Tâche 4 : Nettoyages des sessions trop vieilles

new requete($site->dbrw, "DELETE FROM `site_sessions` WHERE datediff(NOW(),derniere_visite) > 120");

// Tâche 5 : Optimisation de la table des créneaux machine

$req = new requete($site->db, "SHOW TABLES");
while(list($table)=$req->get_row())
  new requete($site->dbrw, "OPTIMIZE TABLE `".$table."`");

?>
