<?php
/**
 * @brief La page de generation des precedentes commandes en ligne
 *
 */

/* Copyright 2006
 * Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir="../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "comptoir/include/produit.inc.php");
require_once($topdir . "comptoir/include/venteproduit.inc.php");
require_once("include/e-boutic.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/gallery.inc.php");
require_once($topdir . "include/cts/vignette.inc.php");


$site = new eboutic ();

if ( $site->user->id < 0 )
	error_403();
	
$site->start_page ("E-boutic", "Commandes passees");

$accueil = new contents("E-boutic",
			"Sur cette page, vous allez pouvoir ".
			"generer vos factures correspondant a vos ".
			"achats en ligne.<br/>".
			"Apres choix de la commande, un fichier au ".
			"format PDF vous sera alors propose en ".
			"telechargement");

$site->add_contents ($accueil);
$req1 = new requete($site->db,
		    "SELECT " .
		    "`cpt_debitfacture`.`id_facture`, " .
		    "`cpt_debitfacture`.`date_facture`, " .
		    "`asso`.`id_asso`, " .
		    "`asso`.`nom_asso`, " .
		    "CONCAT(`utilisateurs`.`prenom_utl`,' ',".
		    "`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		    "`utilisateurs`.`id_utilisateur`, " .
		    "`cpt_vendu`.`quantite`, " .
		    "`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
		    "`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
		    "`cpt_comptoir`.`id_comptoir`, " .
		    "`cpt_comptoir`.`nom_cpt`," .
		    "`cpt_produits`.`nom_prod` " .
		    "FROM `cpt_vendu` " .
		    "INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		    "INNER JOIN `cpt_produits` ON ".
		    "`cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		    "INNER JOIN `cpt_debitfacture` ON ".
		    "`cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		    "INNER JOIN `utilisateurs` ON ".
		    "`cpt_debitfacture`.`id_utilisateur` =".
		    "`utilisateurs`.`id_utilisateur` " .
		    "INNER JOIN `cpt_comptoir` ON ".
		    "`cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		    "WHERE mode_paiement = 'SG' " .
		    "AND `cpt_comptoir`.`id_comptoir` = 3 ".
		    "AND `utilisateurs`.`id_utilisateur` = ".
		    $site->user->id ." ".
		    "GROUP BY `cpt_debitfacture`.`id_facture` ".
		    "ORDER BY `cpt_debitfacture`.`date_facture` DESC");

/* paye ta requete 2eme edition ... */
$req2 = new requete($site->db,
		    "SELECT " .
		    "`cpt_debitfacture`.`id_facture`, " .
		    "`cpt_debitfacture`.`date_facture`, " .
		    "`asso`.`id_asso`, " .
		    "`asso`.`nom_asso`, " .
		    "CONCAT(`utilisateurs`.`prenom_utl`,' ',".
		    "`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		    "`utilisateurs`.`id_utilisateur`, " .
		    "`cpt_vendu`.`quantite`, " .
		    "`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
		    "`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
		    "`cpt_comptoir`.`id_comptoir`, " .
		    "`cpt_comptoir`.`nom_cpt`," .
		    "`cpt_produits`.`nom_prod` " .
		    "FROM `cpt_vendu` " .
		    "INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		    "INNER JOIN `cpt_produits` ON ".
		    "`cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		    "INNER JOIN `cpt_debitfacture` ON ".
		    "`cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		    "INNER JOIN `utilisateurs` ON ".
		    "`cpt_debitfacture`.`id_utilisateur` =".
		    "`utilisateurs`.`id_utilisateur` " .
		    "INNER JOIN `cpt_comptoir` ON ".
		    "`cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		    "WHERE mode_paiement = 'AE' " .
		    "AND `cpt_comptoir`.`id_comptoir` = 3 ".
		    "AND `utilisateurs`.`id_utilisateur` = ".
		    $site->user->id ." ".
		    "GROUP BY `cpt_debitfacture`.`id_facture` ".
		    "ORDER BY `cpt_debitfacture`.`date_facture` DESC");

$site->add_contents(new sqltable("listresp",
				 "Ordre Societe Generale",
				 $req1,
				 "moncompte.php",
				 "id_facture",
				 array("id_facture"=>"Facture",
				       "date_facture"=>"Date",
				       "nom_cpt"=>"Lieu",
				       "nom_asso"=>"Association"),
				 array(),
				 array(),
				 array()));


$site->add_contents(new sqltable("listresp",
				 "Ordre Carte AE",
				 $req2,
				 "moncompte.php",
				 "id_facture",
				 array("id_facture"=>"Facture",
				       "date_facture"=>"Date",
				       "nom_cpt"=>"Lieu",
				       "nom_asso"=>"Association"),
				 array(),
				 array(),
				 array()));


$site->end_page ();
?>