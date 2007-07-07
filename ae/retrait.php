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
require_once($topdir. "comptoir/include/facture.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") )
	$site->error_forbidden("none","group",1);
	
if ( $_REQUEST["action"] == "retires")
{
	$fact = new debitfacture($site->db,$site->dbrw);
	foreach( $_REQUEST["id_factprods"] as $id_factprod )
	{
		list($id_facture,$id_produit) = explode(",",$id_factprod);	
		$fact->load_by_id($id_facture);
		if ( $fact->is_valid() )
			$fact->set_retire($id_produit);
	}
}	

$site->start_page("none","Boutic: Produit en attente de retrait");

$cts = new contents("Produit en attente de retrait");

$req = new requete($site->db, "SELECT " .
  "CONCAT(`cpt_debitfacture`.`id_facture`,',',`cpt_produits`.`id_produit`) AS `id_factprod`, " .
  "`cpt_debitfacture`.`id_facture`, " .
  "`cpt_debitfacture`.`date_facture`, " .
  "`asso`.`id_asso`, " .
  "`asso`.`nom_asso`, " .
  "`cpt_vendu`.`a_retirer_vente`, " .
  "`cpt_vendu`.`a_expedier_vente`, " .
  "`cpt_vendu`.`quantite`, " .
  "`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
  "`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
  "`cpt_produits`.`nom_prod`, " .
  "`cpt_produits`.`id_produit`, " .
  "`utilisateurs`.`id_utilisateur` AS `id_utilisateur`, " .
  "CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_utilisateur`, " .
  "IF(`cpt_vendu`.`a_retirer_vente`='1','a retirer','a expedier') AS `info` " .
  "FROM `cpt_vendu` " .
  "INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
  "INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
  "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
  "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`cpt_debitfacture`.`id_utilisateur_client` " .
  "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
  "WHERE `cpt_vendu`.`a_retirer_vente`='1' OR `cpt_vendu`.`a_expedier_vente`='1' " .
  "ORDER BY `cpt_debitfacture`.`date_facture` DESC");


$cts->add(new sqltable(
  "listresp",
  "Produits à retirer/à expédier", $req,
  "retrait.php",
  "id_factprod",
  array(
  "nom_utilisateur"=>"Client",
  "id_facture"=>"Facture",
  "nom_prod"=>"Produit",
  "quantite"=>"Quantité",
  "date_facture"=>"Depuis le",
  "info"=>""
  ),
  array(),
  array("retires"=>"Marquer comme retiré"),
  array()), true);

$site->add_contents($cts);
		
$site->end_page();	

?>