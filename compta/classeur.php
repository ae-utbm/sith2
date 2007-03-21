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
require_once($topdir . "include/entreprise.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new sitecompta();

if ( $site->user->id < 1 )
	error_403("session");

$op    = new operation($site->db,$site->dbrw);
$cla   = new classeur_compta($site->db,$site->dbrw);
$cptasso = new compte_asso($site->db);
$cpbc  = new compte_bancaire($site->db);
$asso  = new asso($site->db);

if( isset($_REQUEST['id_op']))
{
	$op->load_by_id($_REQUEST['id_op']);
	if( $op->id > 0 )
		$cla->load_by_id($op->id_classeur);
}

if( !($cla->id > 0) && isset($_REQUEST['id_classeur']))
{
	$cla->load_by_id($_REQUEST['id_classeur']);
	if( $cla->id < 1 )
	{
		header("Location: ../404.php");
		exit();	
	}
}
$cptasso->load_by_id($cla->id_cptasso);
$cpbc->load_by_id($cptasso->id_cptbc);
$asso->load_by_id($cptasso->id_asso);

if ( !$site->user->is_in_group("compta_admin") && !$asso->is_member_role($site->user->id,ROLEASSO_TRESORIER) )
	error_403();

$site->set_current($asso->id,$asso->nom,$cla->id,$cla->nom,$cpbc->nom);

if ( $_REQUEST["action"] == "newop" && $GLOBALS["svalid_call"] )
{
	$opclb = new operation_club($site->db);
	$opstd = new operation_comptable($site->db);
	$ent = new entreprise($site->db);
	$assotier = new asso($site->db);
	$utl = new utilisateur($site->db);
	$cptasso2 = new compte_asso($site->db);
	
	
	if ( $site->user->is_in_group("compta_admin") )
	{ 
		if ( $_REQUEST["kindtp"] == "cpt" )
			$opstd->load_by_id($_REQUEST["id_opstd"]);
		else
			$opclb->load_by_id($_REQUEST["id_opclb"]);
	}
	else
		$opclb->load_by_id($_REQUEST["id_opclb"]);
		
	if ( $_REQUEST["kindtg"] == "ent" )	
	{
		$ent->load_by_id($_REQUEST["id_ent"]);
		if( $_REQUEST["email_utl_ent"] )
			$utl->load_by_email($_REQUEST["email_utl_ent"]);
	}
	elseif ( $_REQUEST["kindtg"] == "asso" )	
	{	
		$assotier->load_by_id($_REQUEST["id_asso"]);
	}
	elseif ( $_REQUEST["kindtg"] == "cptasso" )	
	{
		$cptasso2->load_by_id($_REQUEST["id_cptasso"]);
	}
	elseif ( $_REQUEST["kindtg"] == "etu" )	
	{
		$utl->load_by_email($_REQUEST["email_utl"]);
	}
	elseif ( $_REQUEST["kindtg"] == "etustier" )	
	{
		$ent->load_by_id(3);
	}
	
	if ( $opclb->id < 1 ) $opclb->id = NULL;
	if ( $opstd->id < 1 ) $opstd->id = NULL;
	if ( $ent->id < 1 ) $ent->id = NULL;
	if ( $assotier->id < 1 ) $assotier->id = NULL;
	if ( $cptasso2->id < 1 ) $cptasso2->id = NULL;
	if ( $utl->id < 1 ) $utl->id = NULL;
	
	if ( $opclb->id )
		$opstd->id = $opclb->id_opstd;
	
	if ( !$_REQUEST["date"] )
		$_REQUEST["page"] = "new";
	else if ( is_null($opclb->id) && is_null($opstd->id)  )
		$_REQUEST["page"] = "new";
	else if ( is_null($ent->id) && is_null($assotier->id) && is_null($cptasso->id) && is_null($utl->id ) )
		$_REQUEST["page"] = "new";
	else
	{
		$op->add_op ( $cla->id,
					$opclb->id, $opstd->id,
					$utl->id,
					$assotier->id, $ent->id, $cptasso2->id,
					$_REQUEST["montant"], $_REQUEST["date"], $_REQUEST["commentaire"], $_REQUEST["effectue"]!=NULL,
					$_REQUEST["mode"], $_REQUEST["num_cheque"],
					$_REQUEST["id_libelle"]?$_REQUEST["id_libelle"]:NULL
					);
		$succes = true;
		$_REQUEST["page"] = "new";
		
		if ( $cptasso2->id )
		{	
			if ( $opclb->id > 0 )
				$type_mouvement = $opclb->type_mouvement;
			else 
				$type_mouvement = $opstd->type_mouvement;		
			
			/* CODE DUPLIQUE PLUS LOIN */
			$site->start_page ("none", "Classeur ".$cla->nom." ( ".$asso->nom ." - ". $cpbc->nom.")" );
		
			$frm = new form("newopjum","classeur.php?id_classeur=".$cla->id,true,"POST","Opération jumelée");
			$frm->add_hidden("action","newoplinked");
			$frm->add_hidden("id_op",$op->id);
			$frm->allow_only_one_usage ();
			$frm->add_text_field("commentaire","Commentaire",$_REQUEST["commentaire"],false);
			$frm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso2->id_asso,true,$type_mouvement*-1));
			if ( $site->user->is_in_group("compta_admin") )
				$frm->add_select_field("id_opstd","...ou type comptable",$site->get_typeop_std(true,$type_mouvement*-1));
			$frm->add_checkbox("effectue","Opération éffectuée",$_REQUEST["effectue"]);
			$frm->add_submit("newopjum","Ajouter");
			$site->add_contents($frm);
			$site->end_page ();
		
		
			exit();
		
		}
		
	}
		
}
elseif ( $_REQUEST["action"] == "save" )
{
	$opclb = new operation_club($site->db);
	$opstd = new operation_comptable($site->db);
	$ent = new entreprise($site->db);
	$assotier = new asso($site->db);
	$utl = new utilisateur($site->db);
	$cptasso2 = new compte_asso($site->db);
	
	if ( $site->user->is_in_group("compta_admin") )
	{ 
		if ( $_REQUEST["kindtp"] == "cpt" )
			$opstd->load_by_id($_REQUEST["id_opstd"]);
		else
			$opclb->load_by_id($_REQUEST["id_opclb"]);
	}
	else
		$opclb->load_by_id($_REQUEST["id_opclb"]);
		
	if ( $_REQUEST["kindtg"] == "ent" )	
	{
		$ent->load_by_id($_REQUEST["id_ent"]);
		if( $_REQUEST["email_utl_ent"] )
			$utl->load_by_email($_REQUEST["email_utl_ent"]);
	}
	elseif ( $_REQUEST["kindtg"] == "asso" )	
	{	
		$assotier->load_by_id($_REQUEST["id_asso"]);
	}
	elseif ( $_REQUEST["kindtg"] == "cptasso" )	
	{
		$cptasso2->load_by_id($_REQUEST["id_cptasso"]);
	}
	elseif ( $_REQUEST["kindtg"] == "etu" )	
	{
		$utl->load_by_email($_REQUEST["email_utl"]);
	}
	elseif ( $_REQUEST["kindtg"] == "etustier" )	
	{
		$ent->load_by_id(3);
	}
	
	if ( $opclb->id < 1 ) $opclb->id = NULL;
	if ( $opstd->id < 1 ) $opstd->id = NULL;
	if ( $ent->id < 1 ) $ent->id = NULL;
	if ( $assotier->id < 1 ) $assotier->id = NULL;
	if ( $cptasso2->id < 1 ) $cptasso2->id = NULL;
	if ( $utl->id < 1 ) $utl->id = NULL;
	
	if ( $opclb->id )
		$opstd->id = $opclb->id_opstd;
	
	if ( !$_REQUEST["date"] )
		$_REQUEST["action"] = "edit";
	else if ( is_null($opclb->id) && is_null($opstd->id)  )
		$_REQUEST["action"] = "edit";
	else if ( is_null($ent->id) && is_null($assotier->id) && is_null($cptasso->id) && is_null($utl->id ) )
		$_REQUEST["action"] = "edit";
	else
	{
		$op->save ( 
					$opclb->id, $opstd->id,
					$utl->id,
					$assotier->id, $ent->id, $cptasso2->id,
					$_REQUEST["montant"], $_REQUEST["date"], $_REQUEST["commentaire"], $_REQUEST["effectue"]!=NULL,
					$_REQUEST["mode"], $_REQUEST["num_cheque"],
					$_REQUEST["id_libelle"]?$_REQUEST["id_libelle"]:NULL
					);	
	}
		
}
else if ( $_REQUEST["action"] == "newoplinked" && ($op->id > 0) && $GLOBALS["svalid_call"])
{
	$opclb = new operation_club($site->db);
	$opstd = new operation_comptable($site->db);
	$cla2   = new classeur_compta($site->db);
	
	$opclb->load_by_id($_REQUEST["id_opclb"]);
	if ( $site->user->is_in_group("compta_admin") ) $opstd->load_by_id($_REQUEST["id_opstd"]);
	$cla2->load_opened($op->id_cptasso, $cla->id);
	
	if ( $opclb->id < 1 ) $opclb->id = NULL;
	if ( $opstd->id < 1 ) $opstd->id = NULL;
	
	if ( $opclb->id )
		$opstd->id = $opclb->id_opstd;	
	
	if ( !($opstd->id||$opclb->id) )
	{		
		$opclb->load_by_id($op->id_opclb);
		$opstd->load_by_id($op->id_opstd);
		if ( $opclb->id > 0 )
			$type_mouvement = $opclb->type_mouvement;
		else 
			$type_mouvement = $opstd->type_mouvement;				
		
		/* CODE DUPLIQUE PLUS HAUT */
		$site->start_page ("none", "Classeur ".$cla->nom." ( ".$asso->nom ." - ". $cpbc->nom.")" );
		$frm = new form("newopjum","classeur.php?id_classeur=".$cla->id,true,"POST","Opération jumelée");
		$frm->add_hidden("action","newoplinked");
		$frm->add_hidden("id_op",$op->id);
		$frm->allow_only_one_usage ();
		$frm->add_text_field("commentaire","Commentaire",$_REQUEST["commentaire"],false);
		$frm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso2->id_asso,true,$type_mouvement*-1));
		if ( $site->user->is_in_group("compta_admin") )
			$frm->add_select_field("id_opstd","...ou type comptable",$site->get_typeop_std(true,$type_mouvement*-1));
		$frm->add_checkbox("effectue","Opération éffectuée",$_REQUEST["effectue"]);
		$frm->add_submit("newopjum","Ajouter");
		$site->add_contents($frm);
		$site->end_page ();
		exit();
	}
	elseif ( $cla2->id > 0 )
	{

		$op2 = new operation($site->db,$site->dbrw);
		$op2->add_op ( $cla2->id,
					$opclb->id, $opstd->id,
					$op->id_utilisateur,
					NULL, NULL, $cptasso->id,
					$op->montant, $op->date, $_REQUEST["commentaire"], $_REQUEST["effectue"]!=NULL,
					$op->mode, $op->num_cheque
					);
		$op->link_op($op2);
		
		$succes = true;
		$_REQUEST["page"] = "new";
	}

}
elseif ( $_REQUEST["action"] == "delete" && ($op->id > 0))
{
	if ( $op->id_op_liee && !$site->user->is_in_group("compta_admin") )
		$Erreur = "Vous ne pouvez pas supprimer cette opération car elle est liée à une autre opération. Seuls les administrateurs sont habilités à faire une telle opération.";
	else
		$op->delete();
}
elseif ( $_REQUEST["action"] == "done" && ($op->id > 0))
{
	$op->mark_done();	
}
elseif($_REQUEST["action"] == "deletes")
{
	foreach($_REQUEST["id_ops"] as $id)
	{
		$op->load_by_id($id);
		
		if ( $op->id > 0 )
		{
			if ( $op->id_op_liee && !$site->user->is_in_group("compta_admin") )
				$Erreur = "Une (ou plusieurs) opérations n'a pas été supprimée car elle est liée à une autre opération. Seuls les administrateurs sont habilités à faire une telle opération.";
			else	
				$op->delete();
		}
	}	
}
elseif ( ereg("^ssetlbl=([0-9]*)$",$_REQUEST["action"],$regs) )
{
  $libelle = new compta_libelle($site->db);
  $libelle->load_by_id( $regs[1]);

  if ( $libelle->id_asso == $cptasso->id_asso )
  {
    foreach($_REQUEST["id_ops"] as $id)
    {
      $op->load_by_id($id);
      if ( $op->id > 0 )
        $op->set_libelle($libelle->id);
    }	
  }
}
elseif($_REQUEST["action"] == "dones")
{
	foreach($_REQUEST["id_ops"] as $id)
	{
		$op->load_by_id($id);
		if ( $op->id > 0 )
			$op->mark_done();
	}	
}
elseif( $_REQUEST["action"] == "print" || $_REQUEST["action"] == "prints" )
{
	if ( isset($_REQUEST["id_op"])) $_REQUEST["id_ops"] = array($_REQUEST["id_op"]);

    define('FPDF_FONTPATH', $topdir . 'font/');
	require_once("include/bonpdf.inc.php");

    $bons = new compta_bonpdf();
    $bons->AliasNbPages();
    
	$opclb = new operation_club($site->db);
	$opstd = new operation_comptable($site->db);
	$ent = new entreprise($site->db);
	$assotier = new asso($site->db);
	$utl = new utilisateur($site->db);
	$cptasso2 = new compte_asso($site->db);
	
	foreach($_REQUEST["id_ops"] as $id)
	{
		$op->load_by_id($id);
		$opclb->load_by_id($op->id_opclb);
		$opstd->load_by_id($op->id_opstd);
		$utl->load_by_id($op->id_utilisateur);
		$assotier->load_by_id($op->id_asso);
		$ent->load_by_id($op->id_ent);
		$cptasso2->load_by_id($op->id_cptasso);

		$bons->add_op($op,$site->user,$cla,$asso,$cpbc,$opclb,$opstd,$utl,$assotier,$ent,$cptasso2);
	}
	
	$bons->Output();	
	exit();	
}
elseif ( $_REQUEST["action"] == "newbudget" )
{
	$budget = new budget($site->db,$site->dbrw);
		
	if ( $_REQUEST["nom"])
		$budget->new_budget($cla->id,$_REQUEST["nom"]);
	
	$_REQUEST["view"] = "budget";
}



$site->start_page ("none", "Classeur ".$cla->nom." ( ".$asso->nom ." - ". $cpbc->nom.")" );

$req = new requete ( $site->db, "SELECT " .
		"`cpta_operation`.`op_effctue`, " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"GROUP BY `cpta_operation`.`op_effctue`" );
while($row=$req->get_row())
	$globalsum[$row[0]] = $row[1];	
	
	
$cts = new contents("<a href=\"./\">Compta</a> / ".classlink($cpbc)." / ".classlink($cptasso)." / ".classlink($cla));


/*
$lst = new itemlist("Outils");
$lst->add("<a href=\"classeur.php?id_classeur=".$cla->id."&amp;page=new\">Ajouter une opération</a>");
$lst->add("<a href=\"typeop.php?id_asso=".$asso->id."\">Types d'opérations</a>");
$lst->add("<a href=\"../entreprise.php\">Entreprises</a> (commun à tous)");
$cts->add($lst,true);	*/


$cts->add(new tabshead(array(
  array("typ","compta/typeop.php?id_asso=".$asso->id,"Natures(types) d'opérations"),
  array("lbl","compta/libelle.php?id_asso=".$asso->id,"Etiquettes"),
  array("ent","entreprise.php","Entreprises (commun à tous)")),
  "","","subtab"));

if ( $Erreur )
	$cts->add_paragraph("<b>$Erreur</b>");

$tabsentries = array ( 
		array( !$_REQUEST["view"] && $_REQUEST["page"] != "new", "compta/classeur.php?id_classeur=".$cla->id, "Opérations" ),
		
		array( $_REQUEST["page"] == "new", "compta/classeur.php?id_classeur=".$cla->id."&page=new", "Ajouter" ),
		array( $_REQUEST["view"] == "pending", "compta/classeur.php?id_classeur=".$cla->id."&view=pending", "Non effectué" ),
		array( $_REQUEST["view"] == "budget", "compta/classeur.php?id_classeur=".$cla->id."&view=budget", "Budget" ),
		array( $_REQUEST["view"] == "types", "compta/classeur.php?id_classeur=".$cla->id."&view=types", "Bilan/nature" ),
		array( $_REQUEST["view"] == "actors", "compta/classeur.php?id_classeur=".$cla->id."&view=actors", "Bilan/personne" ),
		array( $_REQUEST["view"] == "blcpt", "compta/classeur.php?id_classeur=".$cla->id."&view=blcpt", "Bilan comptable" )
		);

$cts->add(new tabshead($tabsentries,true));

if( $_REQUEST["action"] == "edit" && ($op->id > 0))
{
	$utl = new utilisateur($site->db);
	$utl->load_by_id($op->id_utilisateur);

	$frm = new form("editop","classeur.php?id_classeur=".$cla->id,true,"POST","Opération n°".$op->num);
	
  $frm->set_toolbox(new toolbox(array(
  "classeur.php?action=print&id_classeur=".$cla->id."&id_op=".$op->id=>"Imprimer"
  )));
	
	$frm->add_hidden("action","save");
	$frm->add_hidden("id_op",$op->id);
	$frm->add_price_field("montant","Montant",$op->montant,true);
	$frm->add_date_field("date","Date",$op->date,true);
	$frm->add_select_field("mode","Mode",$modes_operation,$op->mode);
	$frm->add_text_field("num_cheque","Numéro de chèque",$op->num_cheque);	
	$frm->add_text_field("commentaire","Commentaire",$op->commentaire,false);
	$frm->add_select_field("id_libelle","Etiquette",$site->get_libelles($cptasso->id_asso),$op->id_libelle);

	$frm->add_checkbox("effectue","Opération éffectuée",$op->effectue);
	
	$cfrm = new form(null,null,null,null,"Type");
	
	if ( $site->user->is_in_group("compta_admin") )
	{
		$sfrm = new form("kindtp",null,null,null,"Type simplifié");
		$sfrm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso->id_asso,true),$op->id_opclb);
		$cfrm->add($sfrm,false,true,($op->id_opclb > 0),"clb",false,true,$s);
		
		$sfrm = new form("kindtp",null,null,null,"Type comptable");
		$sfrm->add_select_field("id_opstd","Nature (type)",$site->get_typeop_std(true),$op->id_opstd);
		$cfrm->add($sfrm,false,true,!($op->id_opclb),"cpt",false,true,!$s);
	}	
	else
		$cfrm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso->id_asso,true),$op->id_opclb);
		
	$frm->add($cfrm);
	
	$cfrm = new form(null,null,null,null,"Crediteur/Debiteur");
	
	$sfrm = new form("kindtg",null,null,null,"Entreprise");
	$sfrm->add_entity_select ("id_ent","Nom", $site->db, "entreprise",$op->id_ent);
	$sfrm->add_user_email_field("email_utl_ent","Etudiant intermédiaire (email)",$utl->email);
	$cfrm->add($sfrm,false,true,(($op->id_ent > 0) && ($op->id_ent!=3)),"ent",false,true);

	$sfrm = new form("kindtg",null,null,null,"Association tier");
	$sfrm->add_entity_select ("id_asso","Nom", $site->db, "asso",$op->id_asso);
	$cfrm->add($sfrm,false,true,($op->id_asso >0),"asso",false,true);
	
	$sfrm = new form("kindtg",null,null,null,"Compte asso");
	$sfrm->add_select_field("id_cptasso","Nom",$site->get_lst_cptasso(),$op->id_cptasso);
	$cfrm->add($sfrm,false,true,($op->id_cptasso >0),"cptasso",false,true);
	
	$sfrm = new form("kindtg",null,null,null,"Etudiant");
	$sfrm->add_user_email_field("email_utl","Addresse email",$utl->email);
	$cfrm->add($sfrm,false,true,(!$op->id_ent && $utl->id >0),"etu",false,true);
	
	$sfrm = new form("kindtg",null,null,null,"Etudiants / Tiers");
	$cfrm->add($sfrm,false,true,($op->id_ent ==3),"etustier",false,true);
	
	$frm->add($cfrm);
		
	$frm->add_submit("saveop","Enregistrer");

	$cts->add($frm,true);
	
	if ( $op->id_op_liee )
	{
    $lien_op    = new operation($site->db,$site->dbrw);
    $lien_cla   = new classeur_compta($site->db,$site->dbrw);
    $lien_cptasso = new compte_asso($site->db);
    $lien_cpbc  = new compte_bancaire($site->db);
    $lien_asso  = new asso($site->db);

    $lien_op->load_by_id($op->id_op_liee);
    $lien_cla->load_by_id($lien_op->id_classeur);
    $lien_cptasso->load_by_id($lien_cla->id_cptasso);
    $lien_cpbc->load_by_id($lien_cptasso->id_cptbc);
    $lien_asso->load_by_id($lien_cptasso->id_asso);

    $cts->add_title(2,"Opération liée");

    $cts->add_paragraph(classlink($lien_cpbc)." / ".classlink($lien_cptasso)." / ".classlink($lien_cla)." / ".classlink($lien_op));
	}
	

}
else if ( $_REQUEST["view"] == "budget" ) /* **** Budgets **** */
{
	$req = new requete ( $site->db, "SELECT id_budget,nom_budget,date_budget,total_budget/100 AS total_budget,valide_budget FROM cpta_budget WHERE id_classeur='".$cla->id."'");
	
	$cts->add(new sqltable(
		"listbudget", 
		"Budgets", $req, "classeur.php?id_classeur=".$cla->id, 
		"id_budget", 
		array(
			"nom_budget"=>"Nom du budget",
			"date_budget"=>"Proposé le",
			"total_budget"=>"Somme",
			"valide_budget"=>"Validé"
			), 
		array(), 
		array(),
		array("valide_budget"=>array(0=>"Non",1=>"Oui"))
		),true);
	
	$frm = new form("newbudget","classeur.php?id_classeur=".$cla->id,true,"POST","Nouveau budget");
	$frm->set_help_page("compta-budget");
	$frm->add_hidden("action","newbudget");
	$frm->add_text_field("nom","Nom","",true);
	$frm->add_submit("newbudget","Ajouter");
	$cts->add($frm,true);
	
}
elseif ( $_REQUEST["view"] == "blcpt" ) /* **** Bilan selon le plan comptable **** */
{

	$req = new requete ( $site->db, "SELECT " .
		"`cpta_op_plcptl`.`code_plan`, " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) AS `sum` " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"GROUP BY `cpta_op_plcptl`.`code_plan`" );

	

	while( list($code,$sum) = $req->get_row())
	{
		if ( !$code )
		{
			if ( $sum < 0 )
				$pl["debit"] += abs($sum);
			else
				$pl["credit"] += abs($sum);
			
		} 
		else
		{
			for($i=1;$i<=strlen($code);$i++)
			{
				$pl[substr($code,0,$i)] += abs($sum);	
			}
		}
	}

	$sum = $globalsum[0]+$globalsum[1];

	if ( $sum > 0 )
	{
		$pl["681"] += abs($sum);
		$pl["68"] += abs($sum);
		$pl["6"] += abs($sum);
	}
	else if ( $sum < 0 )
	{
		$pl["781"] += abs($sum);
		$pl["78"] += abs($sum);
		$pl["7"] += abs($sum);
	}
	
	$req = new requete ( $site->db, "SELECT " .
		"`code_plan`, `libelle_plan` " .
		"FROM  `cpta_op_plcptl`  ".
		"WHERE `type_mouvement`!=0 " .
		"ORDER BY `code_plan`");

	$tbl = new table("Bilan comptable","plct");

	while( list($code,$libelle) = $req->get_row())
	{
		if ( $pl[$code]  )
			$tbl->add_row(array($code,$libelle,$pl[$code]/100),"plct".strlen($code));
	}
	if ( $pl["debit"] )
	$tbl->add_row(array("","Debit non codé",$pl["debit"]/100));
	if ( $pl["credit"] )
	$tbl->add_row(array("","Credit non codé",$pl["credit"]/100));
	$cts->add($tbl);
	
}
elseif ( $_REQUEST["view"] == "types" ) /* **** Bilan par types **** */
{
	
	
	$cts->add_title(1,"Bilan général");
	
	$req = new requete ( $site->db, "SELECT " .
		"IF (`cpta_op_clb`.`libelle_opclb` IS NULL, CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`),`cpta_op_clb`.`libelle_opclb`) AS `type`, " .
		"SUM(`montant_op`)/100 AS `sum` " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' AND " .
		"(`cpta_op_clb`.`type_mouvement`=1 OR`cpta_op_plcptl`.`type_mouvement`=1)" .
		"GROUP BY `type`" );
	
	$cts->add(new sqltable(
		"listops", 
		"Credit", $req, "classeur.php?id_classeur=".$cla->id, 
		"type", 
		array(
			"type"=>"Nature(type) d'opération",
			"sum"=>"Somme"
			
			), 
		array(), 
		array(),
		array()
		),true);
		
	$req->go_first();
	$sum=0;
	while ( $row = $req->get_row() ) $sum += $row['sum'];		
	$cts->add_paragraph("Total : $sum");		
		
		
	$req = new requete ( $site->db, "SELECT " .
		"IF (`cpta_op_clb`.`libelle_opclb` IS NULL, CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`),`cpta_op_clb`.`libelle_opclb`) AS `type`, " .
		"SUM(`montant_op`)/-100 AS `sum` " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' AND " .
		"(`cpta_op_clb`.`type_mouvement`=-1 OR`cpta_op_plcptl`.`type_mouvement`=-1)" .
		"GROUP BY `type`" );
	
	$cts->add(new sqltable(
		"listops", 
		"Debit", $req, "classeur.php?id_classeur=".$cla->id, 
		"type", 
		array(
			"type"=>"Nature(type) d'opération",
			"sum"=>"Somme"
			
			), 
		array(), 
		array(),
		array()
		),true);
		
	$req->go_first();
	$sum=0;
	while ( $row = $req->get_row() ) $sum += $row['sum'];		
	$cts->add_paragraph("Total : $sum");
	
	$reqlbl = new requete ( $site->db, "SELECT " .
		"`cpta_libelle`.`id_libelle`, `cpta_libelle`.`nom_libelle` " .
		"FROM `cpta_operation` " .
    "LEFT JOIN `cpta_libelle` ON `cpta_operation`.`id_libelle`=`cpta_libelle`.`id_libelle` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"GROUP BY `cpta_operation`.`id_libelle`" );
	
  while ( list($id,$nom) = $reqlbl->get_row() )
  {
    $sum=0;

    if ( is_null($id) )
      $cond = "`cpta_operation`.`id_libelle` IS NULL";
    else
      $cond = "`cpta_operation`.`id_libelle` = '$id'";
    
    $req = new requete ( $site->db, "SELECT " .
      "IF (`cpta_op_clb`.`libelle_opclb` IS NULL, CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`),`cpta_op_clb`.`libelle_opclb`) AS `type`, " .
      "SUM(`montant_op`)/100 AS `sum` " .
      "FROM `cpta_operation` " .
      "LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
      "LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
      "WHERE `cpta_operation`.id_classeur='".$cla->id."' AND " .
      "(`cpta_op_clb`.`type_mouvement`=1 OR`cpta_op_plcptl`.`type_mouvement`=1) AND $cond " .
      "GROUP BY `type`" );
    
    $tbl1= new sqltable(
      "listops", 
      "Credit", $req, "classeur.php?id_classeur=".$cla->id, 
      "type", 
      array(
        "type"=>"Type d'opération",
        "sum"=>"Somme"
        ), 
      array(), 
      array(),
      array()
      );
      
    $req->go_first();
    $sum1=0;
    while ( $row = $req->get_row() ) $sum1 += $row['sum']*100;
      
    $req = new requete ( $site->db, "SELECT " .
      "IF (`cpta_op_clb`.`libelle_opclb` IS NULL, CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`),`cpta_op_clb`.`libelle_opclb`) AS `type`, " .
      "SUM(`montant_op`)/-100 AS `sum` " .
      "FROM `cpta_operation` " .
      "LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
      "LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
      "WHERE `cpta_operation`.id_classeur='".$cla->id."' AND " .
      "(`cpta_op_clb`.`type_mouvement`=-1 OR`cpta_op_plcptl`.`type_mouvement`=-1) AND $cond " .
      "GROUP BY `type`" );
    
    $tbl2= new sqltable(
      "listops", 
      "Debit", $req, "classeur.php?id_classeur=".$cla->id, 
      "type", 
      array(
        "type"=>"Type d'opération",
        "sum"=>"Somme"
        ), 
      array(), 
      array(),
      array()
      );
      
    $req->go_first();
    $sum2=0;
    while ( $row = $req->get_row() ) $sum2 += $row['sum']*100;		
    
    if ( is_null($id) )
      $cts->add_title(1,"Bilan des opérations sans étiquette : ".(($sum1+$sum2)/100));
    else
      $cts->add_title(1,"Bilan des opérations avec étiquette $nom : ".(($sum1+$sum2)/100));
    
    $cts->add($tbl1,true);
    $cts->add_paragraph("Total : ".($sum1/100));
    
    $cts->add($tbl2,true);
    $cts->add_paragraph("Total : ".($sum2/100));		

  }


	
}
elseif ( $_REQUEST["view"] == "actors" ) /* **** Bilan par acteur **** */
{	
	
	//"nom_entreprise","nom_asso","nom_cptasso", "nom_utilisateur"
	$req = new requete ( $site->db, "SELECT " .

		"IF(`entreprise`.`id_ent` IS NOT NULL, CONCAT('ident-',`entreprise`.`id_ent`)," .
		"IF(`asso`.`id_asso` IS NOT NULL,  CONCAT('idasso-',`asso`.`id_asso`), " .
		"IF( `cpta_cpasso`.`id_cptasso` IS NOT NULL, CONCAT('idcptasso-',`cpta_cpasso`.`id_cptasso`)," .
		"CONCAT('idutl-',`utilisateurs`.`id_utilisateur`)))) AS id_actor," .
		"SUM(`montant_op`)/100 AS `sum`, ".
		"`entreprise`.`nom_entreprise`, " .
		"`entreprise`.`id_ent`, " .
		
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		
		"`cpta_cpasso`.`id_cptasso`, " .
		"CONCAT(`asso2`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc` ) AS `nom_cptasso`, " .
		
		"`utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur` " .
		
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".		
		"LEFT JOIN `cpta_cpasso` ON `cpta_operation`.`id_cptasso`=`cpta_cpasso`.`id_cptasso` ".
		"LEFT JOIN `asso` ON `cpta_operation`.`id_asso`=`asso`.`id_asso` ".
		"LEFT JOIN `entreprise` ON `cpta_operation`.`id_ent`=`entreprise`.`id_ent` ".
		"LEFT JOIN `utilisateurs` ON `cpta_operation`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
		"LEFT JOIN `asso` AS `asso2` ON `cpta_cpasso`.`id_asso`=`asso2`.`id_asso` ".
		"LEFT JOIN `cpta_cpbancaire` ON `cpta_cpasso`.`id_cptbc`=`cpta_cpbancaire`.`id_cptbc` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"AND (`cpta_op_clb`.`type_mouvement`=1 OR`cpta_op_plcptl`.`type_mouvement`=1) " .
		"GROUP BY id_actor" );
	
	$cts->add(new sqltable(
		"listops", 
		"Credit", $req, "classeur.php?id_classeur=".$cla->id, 
		"type", 
		array(
			"nom_entreprise"=>array("Entreprise/asso/compte","nom_utilisateur","nom_entreprise","nom_asso","nom_cptasso"),
			"sum"=>"Somme"
			), 
		array(), 
		array(),
		array()
		),true);
		
	$req->go_first();
	$sum=0;
	while ( $row = $req->get_row() ) $sum += $row['sum'];		
	$cts->add_paragraph("Total : $sum");
	
	$req = new requete ( $site->db, "SELECT " .

		"IF(`entreprise`.`id_ent` IS NOT NULL, CONCAT('ident-',`entreprise`.`id_ent`)," .
		"IF(`asso`.`id_asso` IS NOT NULL,  CONCAT('idasso-',`asso`.`id_asso`), " .
		"IF( `cpta_cpasso`.`id_cptasso` IS NOT NULL, CONCAT('idcptasso-',`cpta_cpasso`.`id_cptasso`)," .
		"CONCAT('idutl-',`utilisateurs`.`id_utilisateur`)))) AS id_actor," .
		"SUM(`montant_op`)/-100 AS `sum`, ".
		"`entreprise`.`nom_entreprise`, " .
		"`entreprise`.`id_ent`, " .
		
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		
		"`cpta_cpasso`.`id_cptasso`, " .
		"CONCAT(`asso2`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc` ) AS `nom_cptasso`, " .
		
		"`utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur` " .
		
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".		
		"LEFT JOIN `cpta_cpasso` ON `cpta_operation`.`id_cptasso`=`cpta_cpasso`.`id_cptasso` ".
		"LEFT JOIN `asso` ON `cpta_operation`.`id_asso`=`asso`.`id_asso` ".
		"LEFT JOIN `entreprise` ON `cpta_operation`.`id_ent`=`entreprise`.`id_ent` ".
		"LEFT JOIN `utilisateurs` ON `cpta_operation`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
		"LEFT JOIN `asso` AS `asso2` ON `cpta_cpasso`.`id_asso`=`asso2`.`id_asso` ".
		"LEFT JOIN `cpta_cpbancaire` ON `cpta_cpasso`.`id_cptbc`=`cpta_cpbancaire`.`id_cptbc` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"AND (`cpta_op_clb`.`type_mouvement`=-1 OR`cpta_op_plcptl`.`type_mouvement`=-1) " .
		"GROUP BY id_actor" );
	
	$cts->add(new sqltable(
		"listops", 
		"Debit", $req, "classeur.php?id_classeur=".$cla->id, 
		"type", 
		array(
			"nom_entreprise"=>array("Entreprise/asso/compte","nom_utilisateur","nom_entreprise","nom_asso","nom_cptasso"),
			"sum"=>"Somme"
			), 
		array(), 
		array(),
		array()
		),true);
		
	$req->go_first();
	$sum=0;
	while ( $row = $req->get_row() ) $sum += $row['sum'];		
	$cts->add_paragraph("Total : $sum");
	
}
elseif ( $_REQUEST["page"] == "new" )
{
	if ( $succes )
	{
		$lst = new itemlist("Opération ajoutée");
		$lst->add("<a href=\"classeur.php?id_op=".$op->id."&amp;action=print\">Imprimer</a>");
		$cts->add($lst,true);
	}

	$frm = new form("newop","classeur.php?id_classeur=".$cla->id,!$succes,"POST","Ajouter une opération");
	$frm->add_hidden("action","newop");
	$frm->set_help_page("compta-op");
	$frm->allow_only_one_usage();
	$frm->add_price_field("montant","Montant",0,true);
	$frm->add_date_field("date","Date",time(),true);
	$frm->add_select_field("mode","Mode",$modes_operation);
	$frm->add_text_field("num_cheque","Numéro de chèque");	
	$frm->add_text_field("commentaire","Commentaire","",false);	
	$frm->add_select_field("id_libelle","Etiquette",$site->get_libelles($cptasso->id_asso),null);
	$frm->add_checkbox("effectue","Opération éffectuée");
	
	$cfrm = new form(null,null,null,null,"Type");
	
	if ( $site->user->is_in_group("compta_admin") )
	{
		$sfrm = new form("kindtp",null,null,null,"Type simplifié");
		$sfrm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso->id_asso,true));
		$cfrm->add($sfrm,false,true,true,"clb",false,true,true);
		
		$sfrm = new form("kindtp",null,null,null,"Type comptable");
		$sfrm->add_select_field("id_opstd","Nature (type)",$site->get_typeop_std(true));
		$cfrm->add($sfrm,false,true,false,"cpt",false,true,false);
	}	
	else
		$cfrm->add_select_field("id_opclb","Nature (type)",$site->get_typeop_clb($cptasso->id_asso,true));
		
	$frm->add($cfrm);
	
	$cfrm = new form(null,null,null,null,"Crediteur/Debiteur");
	
	$sfrm = new form("kindtg",null,null,null,"Entreprise");
	$sfrm->add_entity_select ("id_ent","Nom", $site->db, "entreprise");
	$sfrm->add_user_email_field("email_utl_ent","Etudiant intermédiaire (email)");
	$cfrm->add($sfrm,false,true,true,"ent",false,true,true);

	$sfrm = new form("kindtg",null,null,null,"Association tier");
	$sfrm->add_entity_select ("id_asso","Nom", $site->db, "asso");
	$cfrm->add($sfrm,false,true,false,"asso",false,true,false);
	
	$sfrm = new form("kindtg",null,null,null,"Compte asso");
	$sfrm->add_select_field("id_cptasso","Nom",$site->get_lst_cptasso());
	$cfrm->add($sfrm,false,true,false,"cptasso",false,true,false);
	
	$sfrm = new form("kindtg",null,null,null,"Etudiant");
	$sfrm->add_user_email_field("email_utl","Addresse email");
	$cfrm->add($sfrm,false,true,false,"etu",false,true,false);
	
	$sfrm = new form("kindtg",null,null,null,"Etudiants / Tiers");
	$cfrm->add($sfrm,false,true,false,"etustier",false,true,false);
	
	$frm->add($cfrm);
	

	$frm->add_submit("newop","Ajouter");
	$cts->add($frm,true);
}
elseif ( $_REQUEST["view"] ==  "pending" )
{	
	
$req = new requete ( $site->db, "SELECT " .
		"`cpta_operation`.`id_op`, " .
		"`cpta_operation`.`num_op`, " .
		"`cpta_operation`.`date_op`, " .
		"`cpta_operation`.`op_effctue`, " .
		"`cpta_operation`.`commentaire_op`, " .
		
		"IF(`cpta_operation`.`mode_op`='1',CONCAT('Chèque ',`cpta_operation`.`num_cheque_op`),NULL) AS `cheque`, " .
		"`cpta_operation`.`mode_op`, " .		
		
		"(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`/100) as `montant`, " .
		
		"`cpta_op_clb`.`libelle_opclb`, " .
		//"CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`) AS `libelle_plcptl`, " .
		"`cpta_op_plcptl`.`code_plan`, " .
		
		"`entreprise`.`nom_entreprise`, " .
		"`entreprise`.`id_ent`, " .
		
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		
		"`cpta_cpasso`.`id_cptasso`, " .
		"CONCAT(`asso2`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc` ) AS `nom_cptasso`, " .
		
		"`utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		
    "`cpta_libelle`.`nom_libelle` ".
		
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"LEFT JOIN `cpta_cpasso` ON `cpta_operation`.`id_cptasso`=`cpta_cpasso`.`id_cptasso` ".
		"LEFT JOIN `asso` ON `cpta_operation`.`id_asso`=`asso`.`id_asso` ".
		"LEFT JOIN `entreprise` ON `cpta_operation`.`id_ent`=`entreprise`.`id_ent` ".
		"LEFT JOIN `utilisateurs` ON `cpta_operation`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
		"LEFT JOIN `asso` AS `asso2` ON `cpta_cpasso`.`id_asso`=`asso2`.`id_asso` ".
		"LEFT JOIN `cpta_cpbancaire` ON `cpta_cpasso`.`id_cptbc`=`cpta_cpbancaire`.`id_cptbc` ".
    "LEFT JOIN `cpta_libelle` ON `cpta_operation`.`id_libelle`=`cpta_libelle`.`id_libelle` ".
    
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' AND `cpta_operation`.`op_effctue`='0' " .
		"ORDER BY `cpta_operation`.`num_op` DESC" );

$cts->add_paragraph("Effectue : ".($globalsum[1]/100)." Eur, Total : ".(($globalsum[0]+$globalsum[1])/100)." Eur");

$cts->add(new sqltable(
	"listops", 
	"Opérations", $req, "classeur.php?id_classeur=".$cla->id."&view=pending", 
	"id_op", 
	array(
		"num_op"=>"N°",
		"date_op"=>"Date",
		"nom_libelle" => "Etiquette",
		"montant"=>"Montant",
		"mode_op"=>array("Paiement","mode_op","cheque"),
		"nom_entreprise"=>array("Débiteur/Crediteur","nom_utilisateur","nom_entreprise","nom_asso","nom_cptasso"),
		"code_plan"=>"Code",
		"libelle_opclb" => "Nature(type)",
		"op_effctue"=>"Eff.",
		"commentaire_op"=>"Commentaire"
		), 
	array("edit"=>"Editer","delete"=>"Supprimer","done"=>"Effectué","print"=>"Imprimer"), 
	array("dones"=>"Marquer comme effectué","deletes"=>"Supprimer","prints"=>"Imprimer"),
	array("op_effctue"=>array(0=>"Non",1=>"Oui"),"mode_op"=>$modes_operation)
	));	
	
	
}
else /* **** Relevé des opérations **** */
{
	
$req = new requete ( $site->db, "SELECT " .
		"`cpta_operation`.`id_op`, " .
		"`cpta_operation`.`num_op`, " .
		"`cpta_operation`.`date_op`, " .
		"`cpta_operation`.`op_effctue`, " .
		"`cpta_operation`.`commentaire_op`, " .
		
		"IF(`cpta_operation`.`mode_op`='1',CONCAT('Chèque ',`cpta_operation`.`num_cheque_op`),NULL) AS `cheque`, " .
		"`cpta_operation`.`mode_op`, " .		

		"(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`/100) as `montant`, " .
		
		"`cpta_op_clb`.`libelle_opclb`, " .
		//"CONCAT(`cpta_op_plcptl`.`code_plan`,' ',`cpta_op_plcptl`.`libelle_plan`) AS `libelle_plcptl`, " .
		"`cpta_op_plcptl`.`code_plan`, " .
		
		"`entreprise`.`nom_entreprise`, " .
		"`entreprise`.`id_ent`, " .
		
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .

		"`cpta_cpasso`.`id_cptasso`, " .
		"CONCAT(`asso2`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc` ) AS `nom_cptasso`, " .
		
		"`utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		
		"`cpta_libelle`.`nom_libelle` ".
		
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"LEFT JOIN `cpta_cpasso` ON `cpta_operation`.`id_cptasso`=`cpta_cpasso`.`id_cptasso` ".
		"LEFT JOIN `asso` ON `cpta_operation`.`id_asso`=`asso`.`id_asso` ".
		"LEFT JOIN `entreprise` ON `cpta_operation`.`id_ent`=`entreprise`.`id_ent` ".
		"LEFT JOIN `utilisateurs` ON `cpta_operation`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
    "LEFT JOIN `cpta_libelle` ON `cpta_operation`.`id_libelle`=`cpta_libelle`.`id_libelle` ".

		"LEFT JOIN `asso` AS `asso2` ON `cpta_cpasso`.`id_asso`=`asso2`.`id_asso` ".
		"LEFT JOIN `cpta_cpbancaire` ON `cpta_cpasso`.`id_cptbc`=`cpta_cpbancaire`.`id_cptbc` ".
		"WHERE `cpta_operation`.id_classeur='".$cla->id."' " .
		"ORDER BY `cpta_operation`.`num_op` DESC" );

$cts->add_paragraph("Effectue : ".($globalsum[1]/100)." Eur, Total : ".(($globalsum[0]+$globalsum[1])/100)." Eur");

$batch = array("dones"=>"Marquer comme effectué","deletes"=>"Supprimer","prints"=>"Imprimer");

$reqlbl = new requete ($site->db, "SELECT * " .
    "FROM cpta_libelle " .
    "WHERE id_asso='".$asso->id."' " .
    "ORDER BY nom_libelle");

while ( $row = $reqlbl->get_row() )
  $batch["ssetlbl=".$row['id_libelle']] = "Rattacher à l'étiquette ".$row['nom_libelle'];

$cts->add(new sqltable(
	"listops", 
	"Opérations", $req, "classeur.php?id_classeur=".$cla->id, 
	"id_op", 
	array(
		"num_op"=>"N°",
		"date_op"=>"Date",
		"nom_libelle" => "Etiquette",
		"montant"=>"Montant",
		"mode_op"=>array("Paiement","mode_op","cheque"),
		"nom_entreprise"=>array("Débiteur/Crediteur","nom_utilisateur","nom_entreprise","nom_asso","nom_cptasso"),
		"code_plan"=>"Code",
		"libelle_opclb" => "Nature(type)",
		"op_effctue"=>"Eff.",
		"commentaire_op"=>"Commentaire"
		), 
	array("edit"=>"Editer","delete"=>"Supprimer","done"=>"Effectué","print"=>"Imprimer"), 
	$batch,
	array("op_effctue"=>array(0=>"Non",1=>"Oui"),"mode_op"=>$modes_operation)
	));

}
$cts->add_title(2,"");
$cts->add_paragraph("Effectue : ".($globalsum[1]/100)." Eur, Total : ".(($globalsum[0]+$globalsum[1])/100)." Eur");

$site->add_contents($cts);

$site->end_page ();

?>
