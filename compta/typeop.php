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

$asso  = new asso($site->db);

$opstd = new operation_comptable($site->db);
$opclb = new operation_club($site->db,$site->dbrw);

if ( $site->user->id < 1 )
	error_403();

if ( isset($_REQUEST["id_asso"]))
{
	$asso->load_by_id($_REQUEST["id_asso"]);
	if( $asso->id < 1 )
	{
		header("Location: ../404.php");
		exit();	
	}
}


if ( $asso->id > 0 )
{	
	if ( !$site->user->is_in_group("compta_admin") && !$asso->is_member_role($site->user->id,ROLEASSO_TRESORIER) )
		error_403();
	
	$site->set_current($asso->id,$asso->nom,null,null,null);
	
	if ( $_REQUEST["action"] == "newclubop" )
	{
		if ( $_REQUEST["libelle"] && isset($types_mouvements_reel[$_REQUEST["type_mouvement"]]) )
		{
			if ( $site->user->is_in_group("compta_admin") ) $opstd->load_by_id($_REQUEST["id_opstd"]);
			if ( $opstd->id < 1 ) $opstd->id = null;
			
			$opclb->new_op_pstd ( $asso->id, $opstd->id, $_REQUEST["libelle"], $_REQUEST["type_mouvement"] );
		}
	}
	elseif ( $_REQUEST["action"] == "save" )
	{
		$opclb->load_by_id( $_REQUEST["id_opclb"]);
		
		if ( $_REQUEST["libelle"] && $opclb->id > 0 )
		{
			if ( $site->user->is_in_group("compta_admin") ) $opstd->load_by_id($_REQUEST["id_opstd"]);
			if ( $opstd->id < 1 ) $opstd->id = null;
			
			$opclb->save ( $asso->id, $opstd->id, $_REQUEST["libelle"], $opclb->type_mouvement );
		}
	}
	elseif ( $_REQUEST["action"] == "edit" )
	{
		$opclb->load_by_id($_REQUEST["id_opclb"]);
		if( $opclb->id < 1 )
		{
			header("Location: ../404.php");
			exit();	
		}	
		
		$site->start_page ("none", "Operations ".$asso->nom );

		$frm = new form ("newclubop","typeop.php?id_asso=".$asso->id,true,"POST","Edition");
		$frm->add_hidden("action","save");
		$frm->add_hidden("id_opclb",$opclb->id);
		$frm->add_info($types_mouvements_reel[$opclb->type_mouvement]);
		$frm->add_text_field("libelle","Libellé",$opclb->libelle,true);
		if ( $site->user->is_in_group("compta_admin") )
			$frm->add_select_field("id_opstd","Type comptable",$site->get_typeop_std(false,$opclb->type_mouvement),$opclb->id_opstd);
		$frm->add_submit("valid","Enregistrer");

		$site->add_contents($frm);
		
		$site->add_contents(new contents(false,"<a href=\"typeop.php?id_asso=".$asso->id."\">Annuler</a>"));
		
		
		$site->end_page ();
		
		exit();
	}
	elseif ( $_REQUEST["action"] == "fusion" )
	{
		$opclb2 = new operation_club($site->db,$site->dbrw);
		
		$opclb->load_by_id($_REQUEST["id_opclbs"][0]);
		
		unset($_REQUEST["id_opclbs"][0]);
		
		foreach ( $_REQUEST["id_opclbs"] as $id)
		{
			$opclb2->load_by_id($id);
			
			if ( $opclb2->id != $opclb->id && $opclb2->type_mouvement == $opclb->type_mouvement )
				$opclb2->replace_and_remove($opclb);
		}
		
		
	}
	
	$site->start_page ("none", "Operations ".$asso->nom );

	$cts = new contents("Opérations ".$asso->nom );

	$req = new requete ($site->db, "SELECT cpta_op_clb.*," .
			"`cpta_op_plcptl`.`code_plan` " .
			"FROM cpta_op_clb " .
			"LEFT JOIN cpta_op_plcptl ON cpta_op_plcptl.id_opstd = cpta_op_clb.id_opstd " .
			"WHERE cpta_op_clb.id_asso='".$asso->id."' " .
			"ORDER BY type_mouvement,libelle_opclb");

	$cts->add(new sqltable(
		"listtops", 
		"Natures (types) d'opération", $req, "typeop.php?id_asso=".$asso->id, 
		"id_opclb", 
		array(
			"libelle_opclb"=>"Libelle",
			"type_mouvement"=>"Type de mouvement",
			"code_plan"=>"Code plan."
			), 
		array("edit"=>"Editer"), 
		array("fusion"=>"Fusionner natures (types) d'opérations"),
		array("type_mouvement"=>$types_mouvements_reel)
		),true);


	$frm = new form ("newclubop","typeop.php?id_asso=".$asso->id,true,"POST","Ajouter une nature d'opération");
	$frm->add_hidden("action","newclubop");
	
	$frm->add_text_field("libelle","Libellé","",true);
	$frm->add_select_field("type_mouvement","Type de mouvement",$types_mouvements_reel);
	if ( $site->user->is_in_group("compta_admin") )
		$frm->add_select_field("id_opstd","Type comptable",$site->get_typeop_std(true));
	$frm->add_submit("valid","Ajouter");
	$cts->add($frm,true);


	$cts->set_help_page("compta-types");
	$site->add_contents($cts);
	$site->end_page ();
	exit();
}

if ( !$site->user->is_in_group("compta_admin") )
	error_403();

if ( $_REQUEST["action"] == "setsdt" )
{
	
	foreach( $_REQUEST["id_opstd"] as $id_opclb => $id_opstd )
	{
		$opclb->load_by_id($id_opclb);
		$opstd->load_by_id($id_opstd);
		if ( $opclb->id > 0 && $opstd->id > 0 )
			$opclb->attach($opstd->id);
	}
}



$site->start_page ("none", "Administration : operations" );

$types[-1] = $site->get_typeop_std(true,-1);
$types[1] = $site->get_typeop_std(true,1);

$frm= new form ("setsdt","typeop.php",true,"POST","Definition comptable des natures d'opérations (30 derniers seulement)");
$frm->add_hidden("action","setsdt");

$req = new requete ($site->db, "SELECT cpta_op_clb.*, asso.nom_asso, asso.id_asso " .
			"FROM cpta_op_clb "  .
			"INNER JOIN asso ON asso.id_asso = cpta_op_clb.id_asso " .
			"WHERE cpta_op_clb.id_opstd IS NULL " .
			"LIMIT 30");

while ( $row = $req->get_row())
{
	$mvt = "Debit";
	if ( $row["type_mouvement"] > 0 )
		$mvt = "Credit";
	
	$frm->add_info("<a href=\"typeop.php?id_asso=".$row["id_asso"]."\">".$row["nom_asso"]." ".$mvt."</a>: " .
			"<a href=\"typeop.php?id_asso=".$row["id_asso"]."&amp;id_opclb=".$row['id_opclb']."\">".$row['libelle_opclb']."</a>");
	$frm->add_select_field("id_opstd[".$row['id_opclb']."]", "Opération comptable",$types[$row["type_mouvement"]]);
	
}

$frm->add_submit("valid","Valider");

$site->add_contents($frm);

$site->end_page ();


?>
