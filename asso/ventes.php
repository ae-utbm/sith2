<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
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
$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/assoclub.inc.php");
require_once($topdir. "include/page.inc.php");

$site = new site ();
$asso = new asso($site->db,$site->dbrw);

if ( $site->user->id < 1 )
	error_403("session");


$asso->load_by_id($_REQUEST["id_asso"]);
if ( $asso->id < 1 )
{
	header("Location: ../404.php");
	exit();
}

if ( !$site->user->is_in_group("gestion_ae") && !$asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU) )
	error_403();

$site->start_page("services",$asso->nom);

$cts = new contents($asso->nom);
		
$cts->add(new tabshead($asso->get_tabs($site->user),"slds"));

$cts->add_title(1,"Ventes cartes AE + e-boutic");

$frm = new form ("cptacpt","ventes.php",true,"POST","Critères de selection");
$frm->add_hidden("action","view");
$frm->add_hidden("id_asso",$asso->id);
$frm->add_datetime_field("debut","Date et heure de début");
$frm->add_datetime_field("fin","Date et heure de fin");
$frm->add_entity_select("id_typeprod", "Type", $site->db, "typeproduit",$_REQUEST["id_typeprod"],true);
$frm->add_entity_select("id_comptoir","Lieu", $site->db, "comptoir",$_REQUEST["id_comptoir"],true);
$frm->add_entity_select("id_produit", "Produit", $site->db, "produit",$_REQUEST["id_produit"],true);
$frm->add_submit("valid","Voir");
$cts->add($frm,true);


$conds = array("cpt_vendu.id_assocpt='".$asso->id."'");
$comptoir = false;

if ( $_REQUEST["debut"] )
	$conds[] = "cpt_debitfacture.date_facture >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";

if ( $_REQUEST["fin"] )
	$conds[] = "cpt_debitfacture.date_facture <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";

if ( $_REQUEST["id_comptoir"] )
{
	$conds[] = "cpt_debitfacture.id_comptoir='".intval($_REQUEST["id_comptoir"])."'";
	$comptoir=true;
}
		
if ( $_REQUEST["id_typeprod"] )
	$conds[] = "cpt_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";
		
if ( $_REQUEST["id_produit"] )
	$conds[] = "cpt_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";

if ( count($conds) )
{
	
$req = new requete($site->db, "SELECT " .
		"COUNT(`cpt_vendu`.`id_produit`), " .		
		"SUM(`cpt_vendu`.`quantite`), " .
		"SUM(`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`) AS `total`," .
		"SUM(`cpt_produits`.`prix_achat_prod`*`cpt_vendu`.`quantite`) AS `total_coutant`" .
		"FROM `cpt_vendu` " .
		"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		"INNER JOIN `cpt_type_produit` ON `cpt_produits`.`id_typeprod` =`cpt_type_produit`.`id_typeprod` " .
		"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		"INNER JOIN `utilisateurs` AS `vendeur` ON `cpt_debitfacture`.`id_utilisateur` =`vendeur`.`id_utilisateur` " .	
		"INNER JOIN `utilisateurs` AS `client` ON `cpt_debitfacture`.`id_utilisateur_client` =`client`.`id_utilisateur` " .
		"INNER JOIN `cpt_comptoir` ON `cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		"WHERE " .implode(" AND ",$conds).
		"ORDER BY `cpt_debitfacture`.`date_facture` DESC");		
	
list($ln,$qte,$sum,$sumcoutant) = $req->get_row();
	
	
$cts->add_title(2,"Sommes");
$cts->add_paragraph("Quantitée : $qte unités<br/>" .
		"Chiffre d'affaire: ".($sum/100)." Euros<br/>" .
		"Prix countant total estimé* : ".($sumcoutant/100)." Euros");
	
if ( $ln < 1000 )
{
	
$req = new requete($site->db, "SELECT " .
		"`cpt_debitfacture`.`id_facture`, " .
		"`cpt_debitfacture`.`date_facture`, " .
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		"CONCAT(`client`.`prenom_utl`,' ',`client`.`nom_utl`) as `nom_utilisateur_client`, " .
		"`client`.`id_utilisateur` AS `id_utilisateur_client`, " .
		"CONCAT(`vendeur`.`prenom_utl`,' ',`vendeur`.`nom_utl`) as `nom_utilisateur_vendeur`, " .
		"`vendeur`.`id_utilisateur` AS `id_utilisateur_vendeur`, " .			
		"`cpt_vendu`.`quantite`, " .
		"`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
		"`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
		"`cpt_produits`.`prix_achat_prod`*`cpt_vendu`.`quantite`/100 AS `total_coutant`," .
		"`cpt_comptoir`.`id_comptoir`, " .
		"`cpt_comptoir`.`nom_cpt`," .
		"`cpt_produits`.`nom_prod`, " .
		"`cpt_produits`.`id_produit`, " .
		"`cpt_type_produit`.`id_typeprod`, " .
		"`cpt_type_produit`.`nom_typeprod`" .			
		"FROM `cpt_vendu` " .
		"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		"INNER JOIN `cpt_type_produit` ON `cpt_produits`.`id_typeprod` =`cpt_type_produit`.`id_typeprod` " .
		"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		"INNER JOIN `utilisateurs` AS `vendeur` ON `cpt_debitfacture`.`id_utilisateur` =`vendeur`.`id_utilisateur` " .	
		"INNER JOIN `utilisateurs` AS `client` ON `cpt_debitfacture`.`id_utilisateur_client` =`client`.`id_utilisateur` " .
		"INNER JOIN `cpt_comptoir` ON `cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		"WHERE " .implode(" AND ",$conds).
		"ORDER BY `cpt_debitfacture`.`date_facture` DESC");
	
	
$cts->add(new sqltable(
	"listresp", 
	"Listing", $req, "ventes.php", 
	"id_facture", 
	array(
		"id_facture"=>"Facture",
		"date_facture"=>"Date",
		"nom_typeprod"=>"Type",
		"nom_prod"=>"Produit",
		"nom_cpt"=>"Lieu",
		"nom_utilisateur_vendeur"=>"Vendeur",
		"nom_utilisateur_client"=>"Client",
		"nom_asso"=>"Asso.",
		"quantite"=>"Qte",
		"total"=>"Som.",
		"total_coutant"=>"Coutant*"), 
	array(), 
	array(),
	array( )
	),true);
}
	$cts->add_paragraph("* ATTENTION: Prix coutant basé sur le prix actuel.");
	
}




$site->add_contents($cts);
		
$site->end_page();
?>
