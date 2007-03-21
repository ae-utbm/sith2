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
require_once("include/comptoirs.inc.php");

$site = new sitecomptoirs();
$site->add_css("css/comptoirs.css");

$site->start_page("services","TOP 10");

$cts = new contents("Top 10 (semestre)");

$month = date("m");

if ( $month >= 2 && $month < 9 )
	$debut_semestre = date("Y")."-02-01";
else if ( $month >= 9 )
	$debut_semestre = date("Y")."-09-01";
else
	$debut_semestre = (date("Y")-1)."-09-01";

$req = new requete ($site->db, "SELECT `utilisateurs`.`id_utilisateur`, " .
		"IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur`, " .
		"sum(`cpt_vendu`.`quantite`*`cpt_vendu`.prix_unit) as total " .
		"FROM cpt_vendu " .
		"INNER JOIN cpt_debitfacture ON cpt_debitfacture.id_facture=cpt_vendu.id_facture " .
		"INNER JOIN utilisateurs ON cpt_debitfacture.id_utilisateur_client=utilisateurs.id_utilisateur " .
		"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
		"WHERE cpt_debitfacture.mode_paiement='AE' AND date_facture > '$debut_semestre' " .
		"GROUP BY utilisateurs.id_utilisateur " .
		"ORDER BY total DESC");

$lst = new itemlist(false,"top10");

$n=1;

while ( $row = $req->get_row() )
{
	$class = $n<=10?"top":false;
	
	if ( $row["id_utilisateur"] == $site->user->id )
		$class = $class?"$class me":"me";
        if ( !$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("foyer_admin") && !$site->user->is_in_group("kfet_admin"))
	  $lst->add("N°$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"]),$class);
        else
	  $lst->add("N°$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] ).(isset($_REQUEST["fcsoldes"])?" ".($row["total"]/100):""),$class);
	$n++;
}

$cts->add($lst);

$site->add_contents($cts);
$site->end_page();


?>
