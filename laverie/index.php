<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet AT oxynux DOT org >
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
require_once($topdir. "laverie/include/laverie.inc.php");

$site = new sitelaverie ();

if ( !$site->user->is_valid() )
	error_403();

$site->user_is_admin();

if ( $site->is_admin )
  $site->set_admin_mode();

$site->start_page("none","Machines");
$cts = new contents("Machines à laver de l'AE");

if ( $_REQUEST['action'] == "inventaire" )
{
	$cts->add_title(2,"Nombre de jetons");

	$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton`");
	list($total) = $req->get_row();
	$cts->add_paragraph("Total : $total");
	$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton_utilisateur` WHERE `retour_jeton` IS NULL");
	list($utilises) = $req->get_row();
	$cts->add_paragraph("En circulation : $utilises");
	$disponibles = $total - $utilises;
	$cts->add_paragraph("En caisse : $disponibles");


}
else
{
  $cts->add_paragraph("<br />Ici trônera joyeusement l'interface cotisant-machine à laver.");
  $cts->add_paragraph("Le tout via un système révolutionnaire de responsables machines nourris au Bob AE et à la cancoillote afin de vous assurer une productivité parfaite dans le nettoyage de vos chaussettes sales et de vos caleçons dégueus (non rien pour les filles, elle sont juste un mythe à l'UTBM).");
}

$site->add_contents($cts);
$site->end_page();  

?>
