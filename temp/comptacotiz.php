<?php

/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
$site = new site ();

$site->start_page("none","Statistiques");
$cts = new contents("Statistiques");

if (!$site->user->is_in_group ("gestion_ae"))
{
  $site->error_forbidden("none","group",9);
}
$_cts=new contents("Modes paiement cotisation");

$_cts->add_paragraph(
'<ol>'.
'<il>cheque</il>'.
'<il>carte bleue</il>'.
'<il>liquide</il>'.
'<il>administration</il>'.
'<il>eboutic</il>'.
'</ol>');
$cts->add($_cts,true);

$req = new requete($site->db,
   'SELECT `mode_paiement_cotis` as pouet '.
   ', CONCAT(CAST(`prix_paye_cotis`/100 as UNSIGNED),\' €\') as prix '.
   ', count(*) as nb '.
   ', CONCAT(CAST(count(*)*`prix_paye_cotis`/100 as UNSIGNED), \' €\') as total '.
   'FROM `ae_cotisations` '.
   'WHERE `date_cotis` >= \'2008-08-15 00:00:00\' '.
//   'AND `mode_paiement_cotis` NOT LIKE 3 '.
   'GROUP BY `mode_paiement_cotis` , `prix_paye_cotis`');
$tbl = new sqltable(
    "cotisations",
    "Cotisations",
    $req,
    "",
    "",
    array("pouet"=>"Type","nb"=>"Nombre","prix"=>"P.U.", "total"=>"Total"),
    array(), array(),
    array()
  );
$cts->add($tbl,true);
$site->add_contents($cts);
$site->end_page();  

?>
