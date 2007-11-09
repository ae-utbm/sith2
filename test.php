<?php

/** @file
 *
 * @brief quelques tests
 *
 */

/* Copyright2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */
 
$topdir = "./";
require_once($topdir. "include/site.inc.php");
$site = new site ();

if ( !preg_match('/^\/var\/www\/ae\/www\/(taiste|taiste21)\//', $_SERVER['SCRIPT_FILENAME']) )
  $site->fatal("taiste only");


if ( $_REQUEST["action"] == "fatal" )
  $site->fatal("test");
elseif ( $_REQUEST["action"] == "partial" )
  $site->fatal_partial("services");


$site->start_page("test","Test / Debug");

$cts = new contents("TEST / DEBUG");

$cts->add_title(2,"Utilisateur connecté");

$site->user->load_groups();

$cts->add_paragraph("id: ".$site->user->id);
$cts->add_paragraph("groupes: ".implode(", ",$site->user->groupes));
$cts->add_paragraph("id groupes: ".$site->user->get_groups_csv());

$frm = new form("test","test.php");
$frm->add_attached_files_field("files","Fichiers",$_REQUEST["files"],1,"Comptabilité/2007/Factures");
$frm->add_submit("bleh","Valider");
$cts->add($frm);

$site->add_contents($cts);

$site->end_page();

?>