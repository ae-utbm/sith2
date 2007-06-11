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
$topdir="../";
require_once($topdir."include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."comptoir/include/defines.inc.php");
$site = new site();

$site->allow_only_logged_users("matmatronch");

	
if ( isset($_REQUEST['id_utilisateur']) )
{
	$user = new utilisateur($site->db,$site->dbrw);
	$user->load_by_id($_REQUEST["id_utilisateur"]);	
	
	if ( !$user->is_valid() )
		$site->error_not_found("matmatronch");
		
	if ( !($user->id==$site->user->id || $site->user->is_in_group("gestion_ae")) )
		$site->error_forbidden("matmatronch","private");
}
else
	$user = &$site->user;

if ( ($_REQUEST["action"] == "delete") && $site->user->is_in_group("gestion_ae") && ( $site->user->is_in_group("root") || $site->user->id != $user->id ) )
{
	if ( isset($_REQUEST["id_facture"]))
	{
		require_once("../comptoir/include/facture.inc.php");
		$fact = new debitfacture ($site->db,$site->dbrw);
		$fact->load_by_id($_REQUEST["id_facture"]);
		if ( $fact->id > 0 )
			$fact->annule_facture();
	}
	else	if ( isset($_REQUEST["id_rechargement"]))
	{
		$user->annuler_credit($_REQUEST["id_rechargement"]);
	}
}

if ( $_REQUEST["page"] == "AE" || $_REQUEST["page"] == "SG" )
{
	$mode = $_REQUEST["page"];
	
	$req = new requete($site->db, "SELECT " .
			"`cpt_debitfacture`.`id_facture`, " .
			"`cpt_debitfacture`.`date_facture`, " .
			"`asso`.`id_asso`, " .
			"`asso`.`nom_asso`, " .
			"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
			"`utilisateurs`.`id_utilisateur`, " .
			"`cpt_vendu`.`quantite`, " .
			"`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
			"`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
			"`cpt_comptoir`.`id_comptoir`, " .
			"`cpt_comptoir`.`nom_cpt`," .
			"`cpt_produits`.`nom_prod` " .
			"FROM `cpt_vendu` " .
			"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
			"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
			"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
			"INNER JOIN `utilisateurs` ON `cpt_debitfacture`.`id_utilisateur` =`utilisateurs`.`id_utilisateur` " .
			"INNER JOIN `cpt_comptoir` ON `cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
			"WHERE `id_utilisateur_client`='".$user->id."' AND mode_paiement='$mode' " .
			"AND EXTRACT(YEAR_MONTH FROM `date_facture`)='".mysql_real_escape_string($_REQUEST["month"])."' " .
			"ORDER BY `cpt_debitfacture`.`date_facture` DESC");

	$mois = substr($_REQUEST["month"],4);
	$annee = substr($_REQUEST["month"],0,4);
		
	$site->start_page("matmatronch", $user->prenom . " " . $user->nom );
  $cts = new contents( $user->prenom . " " . $user->nom );
  $cts->add(new tabshead($user->get_tabs($site->user),"compte"));
	
	$cts->add(new sqltable(
		"listresp", 
		"Depenses", $req, "compteae.php?id_utilisateur=".$user->id, 
		"id_facture", 
		array(
			"id_facture"=>"Facture",
			"date_facture"=>"Date",
			"nom_prod"=>"Produit",
			"nom_cpt"=>"Lieu",
			"nom_utilisateur"=>"Vendeur",
			"nom_asso"=>"Association",
			"quantite"=>"Quantité",
			"prix_unit"=>"Prix unitaire",
			"total"=>"Total"), 
		($mode == "AE") && ($site->user->is_in_group("gestion_ae") &&
                                    ($site->user->is_in_group("root") || $site->user->id != $user->id)) ? array("delete"=>"Annuler la facture"):array(),
		array(),
		array( )
		));

	$site->add_contents($cts);
	$site->end_page();
	exit();
}
elseif ( $_REQUEST["page"] == "rech" )
{
	$mois = substr($_REQUEST["month"],4);
	$annee = substr($_REQUEST["month"],0,4);	
	
	$req = new requete($site->db, "SELECT " .
			"`cpt_rechargements`.`id_rechargement`, " .
			"`cpt_rechargements`.`date_rech`, " .
			"`cpt_rechargements`.`type_paiement_rech`, " .
			"`cpt_rechargements`.`montant_rech`/100 AS `montant_rech`, " .
			"`asso`.`id_asso`, " .
			"`asso`.`nom_asso`, " .
			"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
			"`utilisateurs`.`id_utilisateur`, " .
			"`cpt_comptoir`.`id_comptoir`, " .
			"`cpt_comptoir`.`nom_cpt` " .
			"FROM `cpt_rechargements` " .
			"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_rechargements`.`id_assocpt` " .
			"INNER JOIN `utilisateurs` ON `cpt_rechargements`.`id_utilisateur_operateur` =`utilisateurs`.`id_utilisateur` " .
			"INNER JOIN `cpt_comptoir` ON `cpt_rechargements`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
			"WHERE `cpt_rechargements`.`id_utilisateur`='".$user->id."' " .
			"AND EXTRACT(YEAR_MONTH FROM `date_rech`)='".mysql_real_escape_string($_REQUEST["month"])."' " .
			"ORDER BY `cpt_rechargements`.`date_rech` DESC");
				
	$site->start_page("matmatronch", $user->prenom . " " . $user->nom );
  $cts = new contents( $user->prenom . " " . $user->nom );
  $cts->add(new tabshead($user->get_tabs($site->user),"compte"));
	
	$cts->add(new sqltable(
		"listresp", 
		"Depenses", $req, "compteae.php?id_utilisateur=".$user->id,
		"id_rechargement", 
		array(
			"id_rechargement"=>"Rechargement",
			"date_rech"=>"Date",
			"montant_rech"=>"Montant",
			"type_paiement_rech"=>"Type",
			"nom_cpt"=>"Lieu",
			"nom_utilisateur"=>"Opérateur",
			"nom_asso"=>"Association"), 
		($site->user->is_in_group("gestion_ae") && ( $site->user->is_in_group("root") || $site->user->id != $user->id ))?array("delete"=>"Annuler la facture"):array(), 
		array(),
		array("type_paiement_rech"=> $TypesPaiementsFull)
		));
	
	
	$site->add_contents($cts);		
	$site->end_page();
	exit();
	
}


$site->start_page("matmatronch", $user->prenom . " " . $user->nom );
$cts = new contents( $user->prenom . " " . $user->nom );
$cts->add(new tabshead($user->get_tabs($site->user),"compte"));

$cts->puts("<a href=\"".$topdir."e-boutic/?cat=11\"><img src=\"".$topdir."images/comptoir/eboutic/pub-eb-rech.png\" border=\"0\" alt=\"Recharger par carte bleue\" class=\"imgright\" /></a>");

$cts->add_title(2,"Carte AE");

if ( !$user->ae )
{
	$cts->add_paragraph("Remarque: Cotisation AE non renouvelée, ce compte n'est plus utilisable.");
	
	if ( $user->montant_compte >= 500 )
		$cts->add_paragraph("Vous pouvez demander le remboursement des ".($user->montant_compte/100)." Euros restant sur le compte.");
	elseif ( $user->montant_compte > 0 )
		$cts->add_paragraph("Le solde restant est insufisent pour pouvoir obtenir un remboursement. Conformèment au réglement intérieur de l'AE.");
	
}

$cts->add_paragraph("Solde : ".($user->montant_compte/100)." Euros");


$req = new requete($site->db, "SELECT SUM(`montant_facture`), " .
		"EXTRACT(YEAR_MONTH FROM `date_facture`) as `month` " .
		"FROM `cpt_debitfacture` " .
		"WHERE `id_utilisateur_client`='".$user->id."' AND mode_paiement='AE' " .
		"GROUP BY `month` " .
		"ORDER BY `month` DESC");
		
while ( list($sum,$month) = $req->get_row() ) 
	$report[$month]["depense"] = $sum;


$req = new requete($site->db, "SELECT SUM(`montant_rech`), " .
		"EXTRACT(YEAR_MONTH FROM `date_rech`) as `month` " .
		"FROM `cpt_rechargements` " .
		"WHERE `id_utilisateur`='".$user->id."' " .
		"GROUP BY `month` " .
		"ORDER BY `month` DESC");
		
while ( list($sum,$month) = $req->get_row() ) 
	$report[$month]["recharge"] = $sum;

if(!empty($report))
{
  $tbl = new table(false,"sqltable");
  $tbl->add_row(array("Mois","Depenses","Rechargements"),"head");
  $t=0;
  foreach( $report as $month => $data )
  {
	  $t = $t^1;
  	$mois = substr($month,4);
	  $annee = substr($month,0,4);	
  	$tbl->add_row(array("$mois / $annee",
		  "<a href=\"compteae.php?page=AE&amp;month=$month&amp;id_utilisateur=".$user->id."\">".($data["depense"]/100)."</a>",
	  	"<a href=\"compteae.php?page=rech&amp;month=$month&amp;id_utilisateur=".$user->id."\">".($data["recharge"]/100)."</a>"),"ln$t");	
  }
  
  $cts->add($tbl);  
}
/*
$cts->add_title(2,"Carte bleue (sogenactif)");
unset($report);
$req = new requete($site->db, "SELECT SUM(`montant_facture`), " .
		"EXTRACT(YEAR_MONTH FROM `date_facture`) as `month` " .
		"FROM `cpt_debitfacture` " .
		"WHERE `id_utilisateur_client`='".$user->id."' AND mode_paiement='SG' " .
		"GROUP BY `month` " .
		"ORDER BY `month` DESC");
		
while ( list($sum,$month) = $req->get_row() ) 
	$report[$month] = $sum;

if(!empty($report))
{
  $tbl = new table(false,"sqltable");
  $tbl->add_row(array("Mois","Depenses"),"head");
  $t=0;
  foreach( $report as $month => $data )
  {
	  $t = $t^1;
  	$mois = substr($month,4);
	  $annee = substr($month,0,4);	
  	$tbl->add_row(array("$mois / $annee",
	  	"<a href=\"compteae.php?page=SG&amp;month=$month&amp;id_utilisateur=".$user->id."\">".($data / 100)."</a>"),"ln$t");	
  }
  
  $cts->add($tbl);  
}*/


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
        $user->id ." ".
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
        $user->id ." ".
        "GROUP BY `cpt_debitfacture`.`id_facture` ".
        "ORDER BY `cpt_debitfacture`.`date_facture` DESC");

$cts->add(new sqltable("eblstcb",
         "E-boutic, Commandes payés par carte bleu",
         $req1,
         "moncompte.php",
         "id_facture",
         array("id_facture"=>"Facture",
               "date_facture"=>"Date",
               "nom_cpt"=>"Lieu",
               "nom_asso"=>"Association"),
         array(),
         array(),
         array()),true);


$cts->add(new sqltable("eblstae",
         "E-boutic, Commandes payés par carte AE",
         $req2,
         "moncompte.php",
         "id_facture",
         array("id_facture"=>"Facture",
               "date_facture"=>"Date",
               "nom_cpt"=>"Lieu",
               "nom_asso"=>"Association"),
         array(),
         array(),
         array()),true);


$site->add_contents($cts);		

$site->end_page();
?>
