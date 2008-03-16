<?php

/* Copyright 2007
 * - Sarah Amsellem < sarah DOT amsellem AT gmail DOT com >
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
require_once($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/planning.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/planning.inc.php");

$site = new site();

$site->allow_only_logged_users();

$planning = new planning($site->db,$site->dbrw);
$start_date = strtotime("2008-04-01");
$end_date = strtotime("2008-08-01");

$planning->add ( 
  1, /* id_asso : ici 1 pour AE */
  "Planning d'essai", -1, $start_date, $end_date, true );

$lundi = 0;
$mardi = 3600*24;
$jeudi = 3600*24*3;
$h8 = 8*3600;
$h12 = 12*3600;
$h14 = 14*3600;
$h18 = 18*3600; 

$site->start_page("services","Planning");
$cts = new contents("<a href=\"index.php\">Planning</a> / <a href=\"admin.php\">Administration</a> / ".$salles[$id_salle]);

/*$frm = new form("searchpl","admin.php",false,"POST","Ajouter un creneau");
$frm->add_hidden("action","searchpl");
$frm->add_select_field("id_salle","Lieu",$lieux, $_REQUEST["id_salle"]);
$frm->add_submit("afficher","Afficher le planning");
$cts->add($frm,true);*/

$site->add_contents($cts);
$site->end_page(); 
    
?>