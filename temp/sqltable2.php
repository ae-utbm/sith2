<?php
$topdir="../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/cts/sqltable2.inc.php");

$site = new site();
$site->add_js("js/sqltable2.js");
$site->add_css("css/sqltable2.css");

$site->start_page("test", "Test de sqltable2" );
$cts = new contents("table");
$req = new requete($site->db, "SELECT " .
		"`cpt_debitfacture`.`id_facture`, " .
		"`cpt_debitfacture`.`date_facture`, " .
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		"`utilisateurs`.`id_utilisateur`, " .
		"`cpt_vendu`.`quantite`, " .
		"`cpt_vendu`.`prix_unit` AS `prix_unit`, " .
		"`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite` AS `total`," .
		"`cpt_comptoir`.`id_comptoir`, " .
		"`cpt_comptoir`.`nom_cpt`," .
		"`cpt_produits`.`id_produit`, " .
		"`cpt_produits`.`nom_prod` " .
		"FROM `cpt_vendu` " .
		"LEFT JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		"INNER JOIN `utilisateurs` ON `cpt_debitfacture`.`id_utilisateur` =`utilisateurs`.`id_utilisateur` " .
		"INNER JOIN `cpt_comptoir` ON `cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		"WHERE `id_utilisateur_client`='142' AND mode_paiement='AE' " .
		"AND EXTRACT(YEAR_MONTH FROM `date_facture`)='200710' " .
		"ORDER BY `cpt_debitfacture`.`date_facture` DESC");
		
$tbl = new sqltable2("compte","Test","sqltable2.php");
$tbl->add_column_number("id_facture","Facture");
$tbl->add_column("date_facture","Date");
$tbl->add_column("nom_prod","Produit");
$tbl->add_column("nom_cpt","Lieu");
$tbl->add_column("nom_utilisateur","Vendeur");
$tbl->add_column("nom_asso","Association");
$tbl->add_column_quantity("quantite","Quantité");
$tbl->add_column_price("prix_unit","Prix unitaire");
$tbl->add_column_price("total","Total");
$tbl->set_data("id_facture",$req);
$cts->add($tbl,true);
$site->add_contents($cts);
$site->end_page();

?>