<?php

/* Copyright 2015
 *
 * - Skia < lordbanana25 AT mailoo DOT org >
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
require_once($topdir. "include/entities/mailing.inc.php");
require_once($topdir. "include/entities/asso.inc.php");


$site = new site ();
$asso = new asso($site->db,$site->dbrw);
$asso->load_by_id($_REQUEST["id_asso"]);

if ( $asso->id < 1 )
{
  $site->error_not_found("services");
  exit();
}

if ( !$site->user->is_in_group("gestion_ae")&&!$asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU))
  $site->error_forbidden("presentation","role","bureau");

$sucess=null;


$site->start_page("presentation", "Mailing: " . $asso->nom);

$cts = new contents($asso->nom);

$cts->add(new tabshead($asso->get_tabs($site->user),"mebs"));

$subtabs = array();
$subtabs[] = array("mailing","asso/mailing.php?id_asso=".$asso->id,"Mailing aux membres");
$subtabs[] = array("mldiff","asso/mldiff.php?id_asso=".$asso->id,"Gérer les mailings-lists");
$subtabs[] = array("trombino","asso/membres.php?view=trombino&id_asso=".$asso->id,"Trombino (membres actuels)");
$subtabs[] = array("vcards","asso/membres.php?action=getallvcards&id_asso=".$asso->id,"Télécharger les vCard (membres actuels)");
$subtabs[] = array("anciens","asso/membres.php?view=anciens&id_asso=".$asso->id,"Anciens membres");

$cts->add(new tabshead($subtabs,"mailing","","subtab"));


if ( $asso->is_mailing_allowed() )
{
    if($_REQUEST['view'] === 'list') {
        $mllist = $asso->get_existing_ml();
        $mailing = new mailing($site->db);
        foreach ($mllist as $ml_id) {
            $mailing->load_by_id($ml_id);
            $cts.add_paragraph($mailing);
        }
    }
}


$site->add_contents($cts);
$site->end_page();


