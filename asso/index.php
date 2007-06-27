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
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/page.inc.php");

$site = new site ();
$asso = new asso($site->db,$site->dbrw);

$site->allow_only_logged_users("presentation");

if ( isset($_REQUEST["id_asso"]) )
{
	$asso->load_by_id($_REQUEST["id_asso"]);
	if ( $asso->id < 1 )
	{
		header("Location: ../404.php");
		exit();
	}
	
	if ( !$site->user->is_in_group("gestion_ae") && !$asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU) )
		error_403();
	
	$site->start_page("presentation",$asso->nom);
		
	$cts = new contents($asso->get_html_path());
			
$cts->add(new tabshead($asso->get_tabs($site->user),"tools"));
	
	$sublist = new itemlist("A lire avant tout"); 
	$sublist->add(wikilink("presentation-siteae-respclub","Le site de l'AE pour les responsable de club"));
	$sublist->add(wikilink("asso-guides","Guides"));
	$cts->add($sublist,true);

	$sublist = new itemlist("Les outils et les guides"); 
	$sublist->add("<a href=\"../salle.php?page=reservation&amp;id_asso=".$asso->id."\">Reserver une salle</a>");
	$sublist->add("<a href=\"../emprunt.php?id_asso=".$asso->id."\">Reserver du matériel</a>");
	$sublist->add("<a href=\"../news.php?id_asso=".$asso->id."\">Ajouter une nouvelle</a>");
	$sublist->add("<a href=\"reservations.php?id_asso=".$asso->id."\">Suivre les reservations de salle et emprunts de matériel</a>");
	$sublist->add("<a href=\"sendfax.php?id_asso=".$asso->id."\">Envoyer un fax</a>");
	$cts->add($sublist,true);
	
	$req = new requete ($site->db,
			"SELECT DISTINCTROW cpta_cpbancaire.nom_cptbc, cpta_cpasso.id_cptasso " .
			"FROM `cpta_classeur` " .
			"INNER JOIN `cpta_cpasso` ON `cpta_cpasso`.`id_cptasso`=`cpta_classeur`.`id_cptasso` " .
			"INNER JOIN cpta_cpbancaire ON cpta_cpbancaire.id_cptbc=cpta_cpasso.id_cptbc " .
			"WHERE cpta_cpasso.id_asso='".$asso->id."' AND `cpta_classeur`.`ferme`='0'" .
			"ORDER BY `cpta_classeur`.`date_debut_classeur` DESC");	
			
	$sublist = new itemlist("La comptabilité"); 
	
	if ( $req->lines == 1 )
	{	
		$reqa = new requete ($site->db,
			"SELECT id_classeur,nom_classeur " .
			"FROM `cpta_classeur` " .
			"INNER JOIN `cpta_cpasso` ON `cpta_cpasso`.`id_cptasso`=`cpta_classeur`.`id_cptasso` " .
			"INNER JOIN cpta_cpbancaire ON cpta_cpbancaire.id_cptbc=cpta_cpasso.id_cptbc " .
			"WHERE cpta_cpasso.id_asso='".$asso->id."' AND `cpta_classeur`.`ferme`='0'" .
			"ORDER BY `cpta_classeur`.`date_debut_classeur` DESC");		
		list($nom_cpbc,$id_cptasso) = $req->get_row();
		if ( $reqa->lines == 1 )
		{
			list($id,$nom) = $reqa->get_row();
			$sublist->add("<a href=\"../compta/classeur.php?id_classeur=$id\">Consulter le classeur $nom</a>");
			$sublist->add("<a href=\"../compta/classeur.php?id_classeur=$id&amp;page=types\">Obtenir le bilan du classeur $nom</a>");
			$sublist->add("<a href=\"../compta/classeur.php?id_classeur=$id&amp;page=new\">Ajouter une opération dans le classeur $nom</a>");
			$sublist->add("<a href=\"../compta/classeur.php?id_classeur=$id&amp;view=budget\">Proposer un budget pour le classeur $nom</a>");
			
		}
		$sublist->add("<a href=\"../compta/cptasso.php?id_cptasso=$id_cptasso\">Gestion des classeurs $nom_cpbc</a>");
	}
	$sublist->add("<a href=\"../compta/\">Accès la comptabilité</a>");
	$sublist->add(wikilink("compta","Aide de la comptabilité"));
	$cts->add($sublist,true);
	
	$sublist = new itemlist("L'inventaire"); 
	$sublist->add("<a href=\"../objet.php?id_asso=".$asso->id."\">Ajouter un objet</a>");
	$sublist->add("<a href=\"inventaire.php?id_asso=".$asso->id."\">Consulter</a>");
	$sublist->add("Preter un objet");
	$cts->add($sublist,true);
	
	$sublist = new itemlist("Les membres"); 
	$sublist->add("<a href=\"membres.php?id_asso=".$asso->id."#add\">Ajouter un membre</a>");
	$sublist->add("<a href=\"membres.php?id_asso=".$asso->id."\">Consulter</a>");
	$sublist->add("<a href=\"mailing.php?id_asso=".$asso->id."\"><b>Envoyer un email à tous les membres</b></a>");
	$cts->add($sublist,true);
	
	
	require_once($topdir."sas2/include/cat.inc.php");
	$cat = new catphoto($site->db);
	$cat->load_by_asso_summary($asso->id);
	if ( $cat->id > 0 )	
	{
		$seealso[] = "<a href=\"".$topdir."sas2/?id_catph=".$cat->id."\">Photos</a>";
	}
	
	require_once($topdir."include/entities/folder.inc.php");
	$fl = new dfolder($site->db);
	$fl->load_root_by_asso($asso->id);
	if ( $fl->id > 0 )	
	{
		$seealso[] = "<a href=\"".$topdir."d.php?id_folder=".$fl->id."\">Fichiers</a>";
	}
	
	if ( count($seealso) > 0)
		$cts->add(new itemlist("A voir aussi",false,$seealso),true);
	
	$site->add_contents($cts);
	
	$cts = new contents("Informations administratives");
	
	$req = new requete ($site->db,
				"SELECT id_cptasso,CONCAT(asso.nom_asso,' sur ',cpta_cpbancaire.nom_cptbc) as nom_cptasso, " .
				"asso.id_asso, asso.nom_asso,  cpta_cpbancaire.nom_cptbc, cpta_cpbancaire.id_cptbc " .
				"FROM `cpta_cpasso` " .
				"INNER JOIN asso ON asso.id_asso=cpta_cpasso.id_asso " .
				"INNER JOIN cpta_cpbancaire ON cpta_cpbancaire.id_cptbc=cpta_cpasso.id_cptbc " .
				"WHERE `asso`.`id_asso`='".$asso->id."' " .
				"ORDER BY `cpta_cpbancaire`.`nom_cptbc`");
	if ( $req->lines > 0 )
	{			
		$tbl = new sqltable ("cpta_cptasso",
					       "Comptes",
					       $req,
					       "./index.php",
					       "id_cptasso",
					       array("nom_cptasso" => "Compte association",
						     "nom_cptbc" => "Compte bancaire"),
					       array(),
					       array(),
					       array());
		
		$cts->add($tbl,true);	
	}
	
	$site->add_contents($cts);
		
	$site->end_page();
	exit();
}

$site->start_page("presentation","Mes associations");

$req = new requete($site->db,
		"SELECT `asso`.`id_asso`, " .
		"`asso`.`nom_asso` " .
		"FROM `asso_membre` " .
		"INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
		"WHERE `asso_membre`.`role` > 1 AND `asso_membre`.`date_fin` IS NULL " .
		"AND `asso_membre`.`id_utilisateur`='".$site->user->id."' " .
		"ORDER BY asso.`nom_asso`");
		
$cts = new contents("Associations et clubs");		
$tbl = new sqltable ("user_assos",
			       "",
			       $req,
			       "index.php",
			       "id_asso",
			       array("nom_asso" => "Association"),
			       array("admin"=>"Administration"),
			       array(),
			       array());
			    
$cts->add($tbl);	

$cts->add_paragraph("<br/>Si le club dont vous êtes responsable n'est pas dans cette liste, merci de prendre contact avec l'AE pour mettre à jour la base de données.");

$site->add_contents($cts);       
$site->end_page();
?>
