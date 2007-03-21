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
require_once("include/compta.inc.php");
require_once($topdir . "include/assoclub.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
$site = new sitecompta();

if ( $site->user->id < 1 )
	error_403("session");

$budget = new budget($site->db,$site->dbrw);
$cla   = new classeur_compta($site->db);
$cptasso = new compte_asso($site->db);
$cpbc  = new compte_bancaire($site->db);
$asso  = new asso($site->db);

$budget->load_by_id($_REQUEST["id_budget"]);
if ( $budget->id < 1 )
{
	header("Location: ../404.php");
	exit();	
}
$cla->load_by_id($budget->id_classeur);
$cptasso->load_by_id($cla->id_cptasso);
$cpbc->load_by_id($cptasso->id_cptbc);
$asso->load_by_id($cptasso->id_asso);

if ( !$site->user->is_in_group("compta_admin") && !$asso->is_member_role($site->user->id,ROLEASSO_TRESORIER) )
	error_403();
	
$site->set_current($asso->id,$asso->nom,$cla->id,$cla->nom,$cpbc->nom);

if ( $_REQUEST["action"] == "newligne" )
{
	$opclb = new operation_club($site->db);
	$opclb->load_by_id($_REQUEST["id_opclb"]);

	if ( $opclb->id > 0 )
	{ 
		$budget->add_line($opclb->id,get_prix($_REQUEST["montant"]),$_REQUEST["description"]);
	}
}
else if ( $_REQUEST["action"] == "delete" )
{
	$budget->remove_line($_REQUEST["num_lignebudget"]);
	
}
else if ( $_REQUEST["action"] == "deletes" )
{
	foreach ( $_REQUEST["num_lignebudgets"] as $num )
		$budget->remove_line($num);
}
$site->start_page ("none", "Budget ".$budget->nom." dans classeur ".$cla->nom." ( ".$asso->nom ." - ". $cpbc->nom.")" );

$cts = new contents("Budget ".$budget->nom." dans classeur ".$cla->nom." ( ".$asso->nom ." - ". $cpbc->nom.")");
$cts->set_help_page("compta-budget");


$cts->add_paragraph("Compte: ".classlink($cptasso,$cpbc,$asso)." Classeur: ".classlink($cla));

$req = new requete ( $site->db, "SELECT cpta_ligne_budget.num_lignebudget, " .
		"cpta_ligne_budget.description_ligne," .
		"(cpta_ligne_budget.montant_ligne/100) AS montant_ligne, " .
		"cpta_op_clb.libelle_opclb " .
		"FROM cpta_ligne_budget " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_ligne_budget`.`id_opclb`=`cpta_op_clb`.`id_opclb` " .
		"WHERE cpta_ligne_budget.id_budget='".$budget->id."' AND `cpta_op_clb`.`type_mouvement`=1 " .
		"ORDER BY `cpta_op_clb`.`type_mouvement`,cpta_ligne_budget.description_ligne ");

$cts->add(new sqltable(
	"lstcredit", 
	"Recettes", $req, "budget.php?id_budget=".$budget->id, 
	"num_lignebudget", 
	array(
		"libelle_opclb"=>"Libéllé",
		"description_ligne"=>"Description",
		"montant_ligne"=>"Montant"
		), 
	array("delete"=>"Supprimer"), 
	array("deletes"=>"Supprimer"),
	array()
	),true);

$req = new requete ( $site->db, "SELECT cpta_ligne_budget.num_lignebudget, " .
		"cpta_ligne_budget.description_ligne," .
		"(cpta_ligne_budget.montant_ligne/100) AS montant_ligne, " .
		"cpta_op_clb.libelle_opclb " .
		"FROM cpta_ligne_budget " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_ligne_budget`.`id_opclb`=`cpta_op_clb`.`id_opclb` " .
		"WHERE cpta_ligne_budget.id_budget='".$budget->id."' AND `cpta_op_clb`.`type_mouvement`=-1 " .
		"ORDER BY `cpta_op_clb`.`type_mouvement`,cpta_ligne_budget.description_ligne ");

$cts->add(new sqltable(
	"lstdepenses", 
	"Depenses", $req, "budget.php?id_budget=".$budget->id, 
	"num_lignebudget", 
	array(
		"libelle_opclb"=>"Libéllé",
		"description_ligne"=>"Description",
		"montant_ligne"=>"Montant"
		), 
	array("delete"=>"Supprimer"), 
	array("deletes"=>"Supprimer"),
	array()
	),true);
	
$req = new requete ( $site->db, "SELECT  " .
		"SUM(`cpta_op_clb`.`type_mouvement`*cpta_ligne_budget.montant_ligne)/100 " .
		"FROM cpta_ligne_budget " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_ligne_budget`.`id_opclb`=`cpta_op_clb`.`id_opclb` " .
		"WHERE cpta_ligne_budget.id_budget='".$budget->id."'");	
	
list($sum) = $req->get_row();
	
	
$cts->add_paragraph("Somme: <b>$sum</b> Euros");	
	
$frm = new form("newligne","budget.php?id_budget=".$budget->id,true,"POST","Ajouter ligne budgetaire");
$frm->add_hidden("action","newligne");
$frm->add_select_field("id_opclb","Type",$site->get_typeop_clb($cptasso->id_asso,false));
$frm->add_text_field("description","Description","",false);
$frm->add_text_field("montant","Montant","0.00",false);
$frm->add_submit("newligne","Ajouter");
$cts->add($frm,true);

$frm = new form("newligne","budget.php?id_budget=".$budget->id,true,"POST","Ajouter la subvention AE");
$frm->add_hidden("action","newligne");
$frm->add_select_field("id_opclb","Type",$site->get_typeop_clb($cptasso->id_asso,false,1));
$frm->add_text_field("description","Description","Subvention AE",false);
$frm->add_text_field("montant","Montant","0.00",false);
$frm->add_submit("newligne","Ajouter");
$cts->add($frm,true);

$site->add_contents($cts);

$site->end_page ();

?>
