<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet at oxynux dot org >
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
require_once($topdir. "include/graph.inc.php");
require_once("include/comptoirs.inc.php");

$site = new sitecomptoirs();

if ( !$site->user->is_valid() )
{
  header("Location: ../403.php?reason=session");
  exit();
}

$site->fetch_admin_comptoirs();
$comptoirs = array_merge(array(0=>"-"),$site->admin_comptoirs);

if ( !count($site->admin_comptoirs) && !$site->user->is_in_group("gestion_ae") )
  error_403();

$site->set_admin_mode();

$site->start_page("services","Statistiques de consommation");
$cts = new contents("Statistiques de consommation");

$cts->add_paragraph("<br />Hum... où en est le cours de la Vodka ce mois ci ?");

$frm = new form ("cptstats","stats.php",true,"POST","Critères de selection");
$frm->add_hidden("action","view");
$frm->add_datetime_field("debut","Date et heure de début");
$frm->add_datetime_field("fin","Date et heure de fin");
$frm->add_entity_select("id_assocpt", "Association", $site->db, "assocpt",$_REQUEST["id_assocpt"],true);
$frm->add_select_field("id_comptoir","Lieu", $comptoirs,$_REQUEST["id_comptoir"]);
$frm->add_submit("valid","Voir");
$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();  

?>
