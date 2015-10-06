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
    if (isset($_REQUEST['add_mailing'])) {
        if (preg_match('/^[a-z-]{3,8}$/', strtolower($_REQUEST['add_mailing'], $name))) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->create($name[0], $asso->id);
            $cts->add_paragraph("Mailing ".$name[0]." créée!");
        }
    } elseif (isset($_REQUEST['del_mailing'])) {
        $mailing = new mailing($site->db, $site->dbrw);
        $mailing->load_by_id($_REQUEST['del_mailing']);
        $name = $mailing->nom;
        $mailing->remove();
        $cts->add_paragraph("Mailing ".$name." supprimée!");
    } elseif (isset($_REQUEST['add_member'])) {
        $user = new user($site->db);
        $user->load_by_id($_REQUEST['add_member']);
        if ($user->id >= 1) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->add_member($user->id);
            $cts->add_paragraph($user->get_display_name() . " ajouté à " . $mailing->nom);
        }
    } elseif (isset($_REQUEST['del_member'])) {
        $user = new user($site->db);
        $user->load_by_id($_REQUEST['del_member']);
        if ($user->id >= 1) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->del_member($user->id);
            $cts->add_paragraph($user->get_display_name() . " supprimé de " . $mailing->nom);
        }
    } elseif (isset($_REQUEST['add_email'])) {
        if(filter_var($_REQUEST['add_email'], FILTER_VALIDATE_EMAIL)) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->add_email($_REQUEST['add_email']);
            $cts->add_paragraph($_REQUEST['add_email'] . " ajouté à " . $mailing->nom);
        }
    } elseif (isset($_REQUEST['del_email'])) {
        if(filter_var($_REQUEST['del_email'], FILTER_VALIDATE_EMAIL)) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->del_email($_REQUEST['del_email']);
            $cts->add_paragraph($_REQUEST['del_email'] . " supprimé de " . $mailing->nom);
        }
    }

    $mllist = $asso->get_existing_ml();
    $mailing = new mailing($site->db);
    foreach ($mllist as $ml_id) {
        $mailing->load_by_id($ml_id);
        $table = new table($mailing->nom);
        foreach($mailing->get_subscribed_user() as $user_id) {
            $user = new user($site->db);
            $user->load_by_id($user_id);
            $table->add_row($user->get_display_name());
        }
        foreach($mailing->get_subscribed_email() as $mail)
            $table->add_row($mail);
        $cts->add($table);
    }
}

$site->add_contents($cts);
$site->end_page();


