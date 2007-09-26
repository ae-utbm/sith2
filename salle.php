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
 $topdir = "./";
setlocale(LC_ALL,"fr_FR.UTF8"); 
 
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/sitebat.inc.php");
require_once($topdir. "include/entities/batiment.inc.php");
require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/planning.inc.php");

$site = new site ();
$sitebat = new sitebat($site->db,$site->dbrw);
$bat = new batiment($site->db,$site->dbrw);
$salle = new salle($site->db,$site->dbrw);
$asso = new asso($site->db);
$resa = new reservation($site->db, $site->dbrw);

if (isset($_REQUEST["id_salle"]))
	$salle->load_by_id($_REQUEST["id_salle"]);

if (isset($_REQUEST["id_salres"]))
{
	$resa->load_by_id($_REQUEST["id_salres"]);
	
	if ( $resa->is_valid() )
	{
  	$salle->load_by_id($resa->id_salle);
  	$asso->load_by_id($resa->id_asso);
  	$can_edit = $site->user->is_in_group("gestion_ae") || ($resa->id_utilisateur == $site->user->id);
  
  	if ( $asso->is_valid() )
  		$can_edit = $can_edit || $asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU);
  		
    if ( $_REQUEST["action"] == "delete" && $can_edit )
    {
    	$resa->delete();
    	$resa->id = null;
    }  	
	}
}

if ( $salle->is_valid() )
{	
	$bat->load_by_id($salle->id_batiment);
	$sitebat->load_by_id($bat->id_site);
	
	$tabs = array(array("","salle.php?id_salle=".$salle->id, "Informations"));
	
	if ( $salle->reservable )
	{
		$tabs[] = array("pln","salle.php?id_salle=".$salle->id."&view=pln","Planning");
		$tabs[] = array("res","salle.php?action=reservation&id_salle=".$salle->id,"Reserver");
	}
	
	if ( $site->user->is_in_group("gestion_ae") )
	{
		$sql = new requete ( $site->db, "SELECT COUNT(*) FROM `inv_objet` WHERE `id_salle`='".$salle->id."'" );
		list($count) = $sql->get_row();
		$tabs[] = array("inv","salle.php?id_salle=".$salle->id."&view=inv", "Inventaire ($count)");
	}
	
	if ( $_REQUEST["action"] == "addasso" )
	{
		$asso = new asso($site->db);
		$asso->load_by_id($_REQUEST["id_asso"]);
		if ( $asso->id > 0 )
			$salle->add_asso($asso->id);
	}
	
  if ( $_REQUEST["action"] == "weekplanning" )
  {
    $today = mktime ( 0, 0, 0, date("m"),  date("d"), date("Y") );
  
    require_once($topdir. "include/pdf/planning.inc.php");
  
    $pdf = new pdfplanning("Salle ".$salle->nom,"Seules les reservations sur le site de l'AE seront prises en compte : http://ae.utbm.fr/",7,$today);
    
    $end = $today + (8*SECONDSADAY) - 1;
    
		$req = new requete($site->db, 
		"SELECT * FROM sl_reservation ".
		"LEFT JOIN `asso` USING(`id_asso`) ".
		"WHERE id_salle='".$salle->id."' ".
		"AND date_debut_salres >= '".date("Y-m-d H:i:s",$today)."' ".
		"AND date_fin_salres <= '".date("Y-m-d H:i:s",$end)."' ".
		" ORDER BY date_debut_salres");
		
		while ( $row = $req->get_row() )
		{
      $desc = $row['description_salres'];
      
      if(  !is_null($row["nom_asso"] ) )
        $desc.= " (".$row["nom_asso"].")";

		  $pdf->add_element (
		  strtotime($row['date_debut_salres']), 
		  strtotime($row['date_fin_salres']), 
		  $desc );
		}

	 $pdf->Output();
    exit();
  }
	
	if ( $_REQUEST["action"] == "reserver" && $salle->reservable )
	{
		
		$resa = new reservation($site->db, $site->dbrw);
		$asso = new asso($site->db, $site->dbrw);
		
		if  ($_REQUEST["id_asso"] ) $asso->load_by_id($_REQUEST["id_asso"]);
		if ( $asso->id > 0 ) $id_asso = $asso->id;
		else $id_asso = NULL;
		
		
		if ( isset($_REQUEST["seq"]) && $_REQUEST['description'])
		{
			$result = new itemlist("Dates réservés :");
			
			foreach($_REQUEST["seq"] as $seq => $reserved )
			{
				list($debut,$fin) = explode(":",$seq);
				
				if ( $resa->add ( $salle->id, $site->user->id, $id_asso, $debut, $fin, $_REQUEST['description'] ) )
				{

					$result->add("Le ".textual_plage_horraire($debut,$fin));
				}
			}
		}
		else if ( !$_REQUEST['debut'] || !$_REQUEST['fin']||!$_REQUEST['description'] )
		{
			$_REQUEST["action"] = "reservation";
			$ErreurResa = "Incomplet";
		}	
		else if ( $_REQUEST['debut'] > $_REQUEST['fin'] )
		{
			$_REQUEST["action"] = "reservation";
			$ErreurResa = "BOULET! La date de fin doit être après la date de début.";
		}		
		else if ( $_REQUEST["allweeks"] )
		{
			$site->start_page("none","Reservation ".$salle->nom);
			$cts = new contents(classlink($sitebat)." / ".classlink($bat)." / ".classlink($salle));
			$cts->add(new tabshead($tabs,"res"));	
			$cts->add_paragraph("Selectionnez les dates à réserver.");
			$frm = new form("selectdateresa","salle.php?id_salle=".$salle->id,false);
			$frm->add_hidden("action","reserver");
			$frm->add_hidden("description",$_REQUEST['description']);
			$frm->add_hidden("id_asso",$id_asso);
			$h = intval(date("H",$_REQUEST["debut"]));
			
			for($debut=$_REQUEST["debut"];$debut<$_REQUEST["until"];$debut+=60*60*24*7)
			{
				$debut += ($h-intval(date("H",$debut)))*(60*60);
				
				$fin = $debut+($_REQUEST["fin"]-$_REQUEST["debut"]);
				$nom = "Le ".textual_plage_horraire($debut,$fin);
				
				$dispo = $resa->est_disponible($salle->id,$debut,$fin);
				if ( ! $dispo )
				{
					$nom .= " : Non disponible";
				}
				$frm->add_checkbox("seq|$debut:$fin",$nom,$dispo,!$dispo);
			}
			$frm->add_submit("valide","Demander");
			$cts->add($frm);
			
			$site->add_contents($cts);
			$site->end_page();
			exit();			
			
		}
		else if ( !$resa->est_disponible($salle->id,$_REQUEST['debut'],$_REQUEST['fin']) )
		{
		  if ( $resa->est_disponible_hors_non_accord($salle->id,$_REQUEST['debut'],$_REQUEST['fin']) )
        $ErreurResa = "L'horraire a déjà été demandé, mais la reservation n'a pas été validée. Il n'est pas possible d'ajouter votre demande pour l'instant. Veuillez contacter le VPI.";
		  else
        $ErreurResa = "Horraire non disponible";
			$_REQUEST["action"] = "reservation";
		}
		else
			$resa->add ( $salle->id, $site->user->id, $id_asso, $_REQUEST['debut'], $_REQUEST['fin'], $_REQUEST['description'] );
		
		
		
	}
	
	if ( $_REQUEST["action"] == "reservation" && $salle->reservable )
	{
		$site->start_page("none","Reservation ".$salle->nom);
		$cts = new contents(classlink($sitebat)." / ".classlink($bat)." / ".classlink($salle));

		$cts->add(new tabshead($tabs,"res"));	
		
		$cts->add_paragraph("Votre réservation est immédiate, mais elle est soumise à modération, en cas de refus elle sera supprimée.");

		if ( $salle->convention )
		{
			$cts->add_paragraph("<b>ATTENTION : Une convention de locaux sera nécessaire.</b><br/>" .
					"Veuillez lire l'article suivant : ".wikilink("asso-convention","Article sur les conventions de locaux."));

		}			
	
		$frm = new form("newresasalle","salle.php?id_salle=".$salle->id,true,"POST","Formulaire de réservation");
		$frm->add_hidden("action","reserver");
		if( $ErreurResa ) $frm->error($ErreurResa);
		$frm->add_datetime_field("debut","Date et heure de début",-1,true);
		$frm->add_datetime_field("fin","Date et heure de fin",-1,true);
		$frm->add_text_field("description","Motif","",true);
		$frm->add_entity_select("id_asso", "Association", $site->db, "asso",$_REQUEST["id_asso"],true);
		$frm->add_checkbox("allweeks","Toutes les semaines ...");
		$frm->add_datetime_field("until","... jusqu'au");
		$frm->add_submit("valide","Demander");
		$cts->add($frm,true);
		
		$site->add_contents($cts);
		$site->end_page();
		exit();
	}
	
	$site->start_page("none","Salle ".$salle->nom);
	
	$cts = new contents(classlink($sitebat)." / ".classlink($bat)." / ".classlink($salle));

	$cts->add(new tabshead($tabs,$resa->is_valid()?"pln":$_REQUEST["view"]));	
	
	if ( ($_REQUEST["view"] == "pln" && $salle->reservable) || $resa->is_valid() )
	{
  $cts->add_paragraph("<a href=\"?view=pln&amp;id_salle=".$salle->id."\">Retour au planning</a>");	
	  if ( $resa->is_valid() )
	  {
      $user = new utilisateur($site->db);
      $userop = new utilisateur($site->db);
    	$user->load_by_id($resa->id_utilisateur);
    	$userop->load_by_id($resa->id_utilisateur_op);
    	$tbl = new table("Reservation n°".$resa->id);
    	$tbl->add_row(array("Demande faite le ",date("d/m/Y H:i",$resa->date_demande)));
    	$tbl->add_row(array("Période",date("d/m/Y H:i",$resa->date_debut)." au ".date("d/m/Y H:i",$resa->date_fin)));
    	$tbl->add_row(array("Demandeur",classlink($user)));
    	if ( $asso->id > 0 )
    		$tbl->add_row(array("Association",classlink($asso)));
    	$tbl->add_row(array("Convention de locaux requise",$salle->convention?"Oui":"Non"));
    	$tbl->add_row(array("Convention de locaux faite",$resa->convention?"Oui":"Non"));
    	if( $resa->date_accord )
    		$tbl->add_row(array("Accord","le ".date("d/m/Y H:i",$resa->date_accord)." par ".classlink($userop)));
    	$tbl->add_row(array("Motif",htmlentities($resa->description,ENT_NOQUOTES,"UTF-8")));
    	$tbl->add_row(array("Notes",htmlentities($resa->notes,ENT_NOQUOTES,"UTF-8")));
    	$cts->add($tbl,true);
	  }
	  else
	  {
  		$cts->add_paragraph("<a href=\"?action=weekplanning&amp;id_salle=".$salle->id."\">Version PDF</a>");	
  		$planning = new weekplanning ( "Planning de reservation",$site->db, "SELECT * FROM sl_reservation WHERE id_salle='".$salle->id."'", "id_salres", "date_debut_salres", "date_fin_salres", "description_salres", "salle.php?view=pln&id_salle=".$salle->id, "salle.php" );
  		$cts->add($planning);	
	  }
	}
	elseif ( $_REQUEST["view"] == "inv" && $site->user->is_in_group("gestion_ae") )
	{
		$req = new requete ( $site->db, "SELECT `inv_objet`.`id_objet`," .
				"CONCAT(`inv_objet`.`nom_objet`,' ',`inv_objet`.`cbar_objet`) AS `nom_objet`, " .
				"`asso_gest`.`id_asso` AS `id_asso_gest`, " .
				"`asso_gest`.`nom_asso` AS `nom_asso_gest`, " .				
				"`asso_prop`.`id_asso` AS `id_asso_prop`, " .
				"`asso_prop`.`nom_asso` AS `nom_asso_prop`, " .
				"`inv_type_objets`.`id_objtype`,`inv_type_objets`.`nom_objtype`  " .
				"FROM `inv_objet` " .
				"INNER JOIN `asso` AS `asso_gest` ON `inv_objet`.`id_asso`=`asso_gest`.`id_asso` " .
				"INNER JOIN `asso` AS `asso_prop` ON `inv_objet`.`id_asso_prop`=`asso_prop`.`id_asso` " .
				"INNER JOIN `inv_type_objets` ON `inv_objet`.`id_objtype`=`inv_type_objets`.`id_objtype` " .
				"WHERE `id_salle`='".$salle->id."'" );
		
		$tbl = new sqltable(
			"listobjets", 
			"Inventaire", $req, "objtype.php", 
			"id_objet", 
			array("nom_objet"=>"Objet","nom_objtype"=>"Type","nom_asso_gest"=>"Gestionnaire","nom_asso_prop"=>"Propriétaire"), 
			array(), array(), array()
			);
		
		$cts->add($tbl);
		
	}
	else
	{
	
	
	if ( $result ) $cts->add($result,true);
	
	$tbl = new table("Informations");
	$tbl->add_row(array("Etage:",$salle->etage));
	$tbl->add_row(array("Fumeur",$salle->fumeur?"Oui":"Non"));
	$tbl->add_row(array("Convention de locaux requise",$salle->convention?"Oui":"Non"));
	$tbl->add_row(array("Reservable",$salle->reservable?"Oui":"Non"));
	$tbl->add_row(array("Telephone:",$salle->tel));
	$tbl->add_row(array("Batiment",classlink($bat)));
	$tbl->add_row(array("Site",classlink($sitebat)));
	
	$cts->add($tbl,true);
	
	$cts->add_paragraph("Voir aussi : <a href=\"sitebat.php\">Autre sites</a>");
	
	if ( $salle->reservable )
		$cts->add_paragraph("<a href=\"salle.php?action=reservation&amp;id_salle=".$salle->id."\"><b>Reserver la salle</b></a>");
	
	$req = new requete($site->db,"SELECT `asso`.`id_asso` , `asso`.`nom_asso` FROM `sl_association` " .
			"INNER JOIN `asso` ON `sl_association`.`id_asso`=`asso`.`id_asso`" .
			"WHERE `sl_association`.`id_salle`='".$salle->id."'");
	
	if ( $req->lines > 0 )
	{
		$tbl = new sqltable(
			"listassosalle", 
			"Associations", $req, "salle.php?id_salle=".$salle->id, 
			"id_asso", 
			array("nom_asso"=>"Association"), 
			array(), array(),array()
			);	
		$cts->add($tbl,true);
	}
	
	
	
	if ( $site->user->is_in_group("gestion_ae") )
	{
		$req = new requete($site->db,"SELECT `asso`.`id_asso`,`asso`.`nom_asso` FROM `asso` " .
				"LEFT JOIN `sl_association` ON (`sl_association`.`id_asso`=`asso`.`id_asso` AND `sl_association`.`id_salle`='".$salle->id."')" .
				"WHERE `sl_association`.`id_asso` IS NULL " .
				"ORDER BY `nom_asso`");
		
		if ( $req->lines > 0 )
		{
			while ( $row = $req->get_row() )
				$assos[$row['id_asso']] = $row['nom_asso'];		
			
	
			$frm = new form("addassotosalle","salle.php?id_salle=".$salle->id,false,"POST","Ajouter association");
			$frm->add_hidden("action","addasso");
			$frm->add_select_field("id_asso","Association",$assos);
			$frm->add_submit("valid","Ajouter");
			$cts->add($frm,true);
		}
	}
	}
	$site->add_contents($cts);
		
	$site->end_page();
	exit();
}

$site->start_page("none","Salles");

$cts = new contents("Salles");

if ( $_REQUEST["page"] = "reservation" )
{
	$cond = "WHERE `sl_salle`.`reservable`='1'";
	$cts->add_paragraph("Cliquer sur l'iconne de la salle que vous souhaitez reserver.");
}
$req = new requete($site->db,"SELECT `id_salle`,`nom_salle`,`etage`,`sl_batiment`.`id_batiment`,`nom_bat`,`sl_site`.`id_site`,`nom_site` FROM `sl_salle` " .
							"INNER JOIN `sl_batiment` ON `sl_batiment`.`id_batiment`=`sl_salle`.`id_batiment` " .
							"INNER JOIN `sl_site` ON `sl_site`.`id_site`=`sl_batiment`.`id_site` $cond");
$tbl = new sqltable(
	"listsalles", 
	"Salles", $req, $_REQUEST["id_asso"]?"salle.php?id_asso=".$_REQUEST["id_asso"]:"salle.php", 
	"id_salle", 
	array("nom_salle"=>"Salle","etage"=>"Etage","nom_bat"=>"Batiment","nom_site"=>"Site"), 
	( $_REQUEST["page"] = "reservation" ) ? array("reservation"=>"Reserver") : array(), array(),array()
	);	
$cts->add($tbl);
$site->add_contents($cts);
$site->end_page();
?>
