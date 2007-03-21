<?php
/* Copyright 2006
 * - Julien Etelain <julien CHEZ pmad POINT net>
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

$topdir="../";
require_once("include/comptoirs.inc.php");
require_once ($topdir. "include/assoclub.inc.php");
require_once ($topdir . "include/pdf/facture_pdf.inc.php");

$site = new sitecomptoirs();

if ( !$site->user->is_in_group("gestion_ae") )
	error_403();	

$site->set_admin_mode();

if ( isset($_REQUEST["generate"]) )
{
	$asso = new asso($site->db);
	$asso->load_by_id($_REQUEST["id_asso"]);
	
	$month = $_REQUEST["month"];
	
	$factured_infos = array ('name' => "AE - UTBM",
			 'addr' => array("6 Boulevard Anatole France",
					 "90000 BELFORT"),
			 'logo' => "http://ae.utbm.fr/images/Ae-blanc.jpg");

	$facturing_infos = array ('name' => $asso->nom,
			 'addr' => explode("\n",utf8_decode($asso->adresse_postale)),
			 'logo' => "/var/www/ae/www/ae2/images/logos/".$asso->nom_unix.".jpg");

	$date_facturation = date("d/m/Y", mktime ( 0, 0, 0, substr($month,4)+1, 1, substr($month,0,4)));
	
	$titre = utf8_decode("Facture système carte AE");
	
	$ref = $month;

	$query = new requete ($site->db, "SELECT " .
			"CONCAT(`cpt_produits`.`id_typeprod`,'-',`cpt_vendu`.`id_produit`,'-',`cpt_vendu`.`prix_unit`) AS `groupby`, " .
			"SUM(`cpt_vendu`.`quantite`) AS `quantite`, " .
			"`cpt_vendu`.`prix_unit` AS `prix_unit`, " .
			"SUM(`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`) AS `total`," .
			"`cpt_produits`.`nom_prod`," .
			"`cpt_type_produit`.`nom_typeprod`"  .
			"FROM `cpt_vendu` " .
			"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
			"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
			"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
			"INNER JOIN `utilisateurs` ON `cpt_debitfacture`.`id_utilisateur` =`utilisateurs`.`id_utilisateur` " .
			"INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod` " .
			"WHERE `cpt_vendu`.`id_assocpt`='".mysql_real_escape_string($asso->id)."' AND `cpt_produits`.`id_typeprod`!='11' " .
			"AND EXTRACT(YEAR_MONTH FROM `date_facture`)='".mysql_real_escape_string($month)."' " .
			"GROUP BY `groupby` " .
			"ORDER BY `cpt_type_produit`.`nom_typeprod`, `cpt_produits`.`nom_prod`, `cpt_vendu`.`prix_unit`");

	
	while ($line = $query->get_row ())
	{
	  $lines[] = array('nom' => utf8_decode($line['nom_prod']),
			   'quantite' => intval($line['quantite']),
			   'prix' => $line['prix_unit'],
			   'sous_total' => intval($line['quantite']) * $line['prix_unit']);
	}
	
	
	$fact_pdf = new facture_pdf ($facturing_infos,
				     $factured_infos,
				     $date_facturation,
				     $titre,
				     $ref,
				     $lines);
	
	$fact_pdf->renderize ();
	
	
	exit();	
}



$site->start_page("services","Factures système carte AE");


$req = new requete($site->db, "SELECT " .
		"EXTRACT(YEAR_MONTH FROM `date_facture`) as `month` " .
		"FROM `cpt_debitfacture` " .
		"GROUP BY `month` " .
		"ORDER BY `month` DESC");

while ( list($month) = $req->get_row() )
	$months[$month] = substr($month,4)."/".substr($month,0,4);


$frm = new form ("genfact","facture.php",false,"POST","Génération d'une facture");
$frm->add_select_field("month","Mois",$months);
$frm->add_entity_select("id_asso", "Association émétrice", $site->db, "assocpt");
$frm->add_submit("generate","Générer");
$site->add_contents($frm);
$site->end_page();

?>
