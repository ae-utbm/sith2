<?php

/* Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
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
require_once($topdir. "include/cts/user.inc.php");
$site = new site ();

	$site->start_page("none","Statistiques cotisants");

$cts = new contents("Statistiques cotisants");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0'");
list($total) = $req->get_row();

$req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW()");
list($cotisants) = $req->get_row();

if ( $site->user->is_in_group("gestion_ae") )
  $cts->add_paragraph("Cotisants : $cotisants, ".round($cotisants*100/$total,1)." % des inscrits hors anciens");

$req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$cotisants,1) AS `count`, `mode_paiement_cotis` FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW() GROUP BY `mode_paiement_cotis` ORDER BY `count` DESC");


$tbl = new sqltable(
  "paie", 
  "Mode de paiement des cotisations", $req, "", 
  "", 
  array("count"=>"%","mode_paiement_cotis"=>"Mode"), 
  array(), array(),
  array("mode_paiement_cotis"=> array(1=>"Espèces",2=>"Carte bleu",3=>"Chèque",4=>"Administration",5=>"E-Boutic"))
  );	
$cts->add($tbl,true);

$req = new requete($site->db,"SELECT SUM(a_pris_carte),SUM(a_pris_cadeau)  FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW()");

list($nbcarte,$nbcadeau) = $req->get_row();


$cts->add_title(2,"Retraits");
$cts->add_paragraph("".round($nbcarte*100/$cotisants,1)."% ont pris leur carte de membre");
$cts->add_paragraph("".round($nbcadeau*100/$cotisants,1)."% ont pris leur cadeau");


//$req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$cotisants,1) AS `count`, IF(`utl_etu_utbm`.`promo_utbm` IS NULL,0,`utl_etu_utbm`.`promo_utbm`)AS `promo` FROM `ae_cotisations` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() GROUP BY `promo` ORDER BY `count` DESC");
$req = new requete($site->db,"SELECT ROUND(SUM(`ae_utl`='1')*100/$cotisants,1) AS `count`, ROUND(SUM(`ae_utl`='1')*100/SUM(IF (ancien_etudiant_utl='0' OR ae_utl='1',1,0)),1) AS `taux`,IF(`utl_etu_utbm`.`promo_utbm` IS NULL,0,`utl_etu_utbm`.`promo_utbm`)AS `promo` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) GROUP BY `promo` ORDER BY `count` DESC");

$tbl = new sqltable(
  "paie", 
  "Distribution par promo", $req, "", 
  "", 
  array("count"=>"%","taux"=>"Taux de cotisants","promo"=>"Promo"), 
  array(), array(),
  array()
  );	
$cts->add($tbl,true);

//$req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$cotisants,1) AS `count`, IF(`utl_etu_utbm`.`branche_utbm` IS NULL,'Autre',`utl_etu_utbm`.`branche_utbm`) AS `branche` FROM `ae_cotisations` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() GROUP BY `branche` ORDER BY `count` DESC");
$req = new requete($site->db,"SELECT ROUND(SUM(`ae_utl`='1')*100/$cotisants,1) AS `count`, ROUND(SUM(`ae_utl`='1')*100/SUM(IF (ancien_etudiant_utl='0' OR ae_utl='1',1,0)),1) AS `taux`,IF(`utl_etu_utbm`.`branche_utbm` IS NULL,'Autre',`utl_etu_utbm`.`branche_utbm`) AS `branche` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) GROUP BY `branche` ORDER BY `count` DESC");

$tbl = new sqltable(
  "paie", 
  "Distribution par departement", $req, "", 
  "", 
  array("count"=>"%","taux"=>"Taux de cotisants","branche"=>"Branche"), 
  array(), array(),
  array("branche"=>$UserBranches)
  );	
$cts->add($tbl,true);

$month = date("m");

if ( $month >= 2 && $month < 9 )
	$debut_semestre = date("Y")."-02-01";
else if ( $month >= 9 )
	$debut_semestre = date("Y")."-09-01";
else
	$debut_semestre = (date("Y")-1)."-09-01";

$cts->add_title(2,"Carte AE");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur)  FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `mode_paiement`='AE' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");

list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont utilisé le paiement par carte AE ce semestre");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='3' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");

list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont utilisé l'e-boutic ce semestre");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='1' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé à la kfet ce semestre");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='7' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé à la MDE ce semestre");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='2' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé au foyer ce semestre");


$cts->add_title(2,"Matmatronch");

$req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_maj_utl`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont mis à jour leur fiche matmatronch ce semestre");

$req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `hash_utl`='valid'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont bien activé leur compte");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN asso_membre USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND ( `date_fin` IS NULL OR `date_fin`>'$debut_semestre' ) ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% sont inscrits à une activité (sur le site)");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` LEFT JOIN parrains AS p1 USING(`id_utilisateur`) LEFT JOIN parrains AS p2 ON(ae_cotisations.`id_utilisateur`=p2.`id_utilisateur_fillot`) WHERE `date_fin_cotis` > NOW() AND (p2.`id_utilisateur` IS NOT NULL OR p1.`id_utilisateur` IS NOT NULL )");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont un parrain ou un fillot renseigné sur le site");


$cts->add_title(2,"Site ae.utbm.fr");

$req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `droit_image_utl`='1'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont accordé leur droit à l'image de façon systèmatique");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN sdn_a_repondu USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_reponse`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont répondu à un sondage ce semestre sur le site");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `derniere_visite_utl`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site ce semestre");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND DATEDIFF(NOW(),`derniere_visite_utl`) < 30 ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site dans les 30 derniers jours");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND DATEDIFF(NOW(),`derniere_visite_utl`) < 7 ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site dans les 7 derniers jours");


$cts->add_title(2,"Vrac");

$req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN inv_emprunt USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_demande_emp`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont fait un emprunt de matériel ce semestre (livres, video projecteur, tables...)");

$site->add_contents($cts);




$cts = new contents("Statistiques générales hors anciens");



if ( $site->user->is_in_group("gestion_ae") )
  $cts->add_paragraph("Total : $total");

$req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$total,1) AS `count`, IF(`utl_etu_utbm`.`promo_utbm` IS NULL,0,`utl_etu_utbm`.`promo_utbm`)AS `promo` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0'  GROUP BY `promo` ORDER BY `count` DESC");

$tbl = new sqltable(
  "paie", 
  "Distribution par promo", $req, "", 
  "", 
  array("count"=>"%","promo"=>"Promo"), 
  array(), array(),
  array()
  );	
$cts->add($tbl,true);

$req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$total,1) AS `count`, IF(`utl_etu_utbm`.`branche_utbm` IS NULL,'Autre',`utl_etu_utbm`.`branche_utbm`) AS `branche` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0'  GROUP BY `branche` ORDER BY `count` DESC");

$tbl = new sqltable(
  "paie", 
  "Distribution par departement", $req, "", 
  "", 
  array("count"=>"%","branche"=>"Branche"), 
  array(), array(),
  array("branche"=>$UserBranches)
  );	
$cts->add($tbl,true);

$month = date("m");

if ( $month >= 2 && $month < 9 )
	$debut_semestre = date("Y")."-02-01";
else if ( $month >= 9 )
	$debut_semestre = date("Y")."-09-01";
else
	$debut_semestre = (date("Y")-1)."-09-01";


$cts->add_title(2,"Matmatronch");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `date_maj_utl`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont mis à jour leur fiche matmatronch ce semestre");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `hash_utl`='valid'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont bien activé leur compte");

$req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` INNER JOIN asso_membre USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0' AND ( `date_fin` IS NULL OR `date_fin`>'$debut_semestre' ) ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% sont inscrits à une activité (sur le site)");

$req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` LEFT JOIN parrains AS p1 USING(`id_utilisateur`) LEFT JOIN parrains AS p2 ON(utilisateurs.`id_utilisateur`=p2.`id_utilisateur_fillot`) WHERE `ancien_etudiant_utl`='0' AND (p2.`id_utilisateur` IS NOT NULL OR p1.`id_utilisateur` IS NOT NULL )");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont un parrain ou un fillot renseigné sur le site");


$cts->add_title(2,"Site ae.utbm.fr");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `droit_image_utl`='1'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont accordé leur droit à l'image de façon systèmatique");

$req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` INNER JOIN sdn_a_repondu USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0' AND `date_reponse`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont répondu à un sondage ce semestre sur le site");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `derniere_visite_utl`>'$debut_semestre'");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site ce semestre");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND DATEDIFF(NOW(),`derniere_visite_utl`) < 30 ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site dans les 30 derniers jours");

$req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND DATEDIFF(NOW(),`derniere_visite_utl`) < 7 ");
list($nbutil) = $req->get_row();
$cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site dans les 7 derniers jours");

$site->add_contents($cts);


$site->end_page();	
?>