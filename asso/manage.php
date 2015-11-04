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
require_once($topdir. "include/entities/utilisateur.inc.php");

function make_delete_link($mailing, $user=false, $mail=false) {
    $link = '<a href="manage.php?id_asso='.$mailing->id_asso_parent.'&id_mailing='.$mailing->id;
    if ($user != false)
        $link .= '&del_member='.$user->id;
    if ($mail != false)
        $link .= '&del_email='.$mail;
    return $link.'">Supprimer</a>';
}

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

$site->start_page("presentation", "Mailing: " . $asso->nom);

$cts = new contents($asso->nom);

$cts->add(new tabshead($asso->get_tabs($site->user),"mebs"));

$subtabs = array();
$subtabs[] = array("mailing","asso/mailing.php?id_asso=".$asso->id,"Mailing aux membres");
$subtabs[] = array("manage","asso/manage.php?id_asso=".$asso->id,"Gérer les mailings-lists");
$subtabs[] = array("trombino","asso/membres.php?view=trombino&id_asso=".$asso->id,"Trombino (membres actuels)");
$subtabs[] = array("vcards","asso/membres.php?action=getallvcards&id_asso=".$asso->id,"Télécharger les vCard (membres actuels)");
$subtabs[] = array("anciens","asso/membres.php?view=anciens&id_asso=".$asso->id,"Anciens membres");

$cts->add(new tabshead($subtabs,"mailing","","subtab"));


if ( $asso->is_mailing_allowed() )
{
    /* ACTIONS */
    if (isset($_REQUEST['add_mailing'])) {
        if (preg_match('/^[a-z-]{3,8}$/', strtolower($_REQUEST['add_mailing']), $name) === 1) {
            $mailing = new mailing($site->db, $site->dbrw);
            $modere = ($site->user->is_in_group('root')) ? 1 : 0;
            $mailing->create($name[0], $asso->id, $modere);
            $cts->add_paragraph("Mailing ".$mailing->get_address()." créée!");
        } else {
            $cts->add_paragraph("Mailing ".$_REQUEST['add_mailing']." non valide (entre 3 et 8 lettres, tiret autorisé)");
        }
    } 
    if (isset($_REQUEST['del_mailing'])) {
        $mailing = new mailing($site->db, $site->dbrw);
        $mailing->load_by_id($_REQUEST['del_mailing']);
        $name = $mailing->get_address();
        $mailing->remove();
        $cts->add_paragraph("Mailing ".$name." supprimée!");
    } 
    if (isset($_REQUEST['reset'])) {
        reset_default_mailing($site, $asso);
    }
    if (isset($_REQUEST['add_member'])) {
        $user = new utilisateur($site->db);
        $user->load_by_id($_REQUEST['add_member']);
        if ($user->id >= 1) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->load_by_id($_REQUEST['id_mailing']);
            $mailing->add_member($user->id);
            $cts->add_paragraph($user->get_html_link() . " ajouté à " . $mailing->get_address());
        }
    } 
    if (isset($_REQUEST['del_member'])) {
        $user = new utilisateur($site->db);
        $user->load_by_id($_REQUEST['del_member']);
        if ($user->id >= 1) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->load_by_id($_REQUEST['id_mailing']);
            $mailing->del_member($user->id);
            $cts->add_paragraph($user->get_html_link() . " supprimé de " . $mailing->get_address());
        }
    } 
    if (isset($_REQUEST['add_email']) && $_REQUEST['add_email'] != '') {
        if(filter_var($_REQUEST['add_email'], FILTER_VALIDATE_EMAIL)) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->load_by_id($_REQUEST['id_mailing']);
            $mailing->add_email($_REQUEST['add_email']);
            $cts->add_paragraph($_REQUEST['add_email'] . " ajouté à " . $mailing->get_address());
        } else {
            $cts->add_paragraph("e-mail non valide");
        }
    } 
    if (isset($_REQUEST['del_email']) && $_REQUEST['del_email'] != '') {
        if(filter_var($_REQUEST['del_email'], FILTER_VALIDATE_EMAIL)) {
            $mailing = new mailing($site->db, $site->dbrw);
            $mailing->load_by_id($_REQUEST['id_mailing']);
            $mailing->del_email($_REQUEST['del_email']);
            $cts->add_paragraph($_REQUEST['del_email'] . " supprimé de " . $mailing->get_address());
        } else {
            $cts->add_paragraph("e-mail non valide");
        }
    }


    /* PAGE */

    if ($asso->hidden == 0)
        $cts->add_paragraph('<a href="manage.php?id_asso='.$asso->id.'&reset">Réinitialiser la mailing du bureau</a>');

    $mllist = $asso->get_existing_ml();
    $mailing = new mailing($site->db);
    foreach ($mllist as $ml_id) {
        $mailing->load_by_id($ml_id);
        $cts->add_title(2, $mailing->get_address() . ($mailing->is_valid ? "" : " (Attention, mailing non modérée!)"));
        $table = new table(false, "sqltable");
        $table->set_head(array("Utilisateur", "email", ""));
        foreach($mailing->get_subscribed_user() as $user_id) {
            $user = new utilisateur($site->db);
            $user->load_by_id($user_id);
            $table->add_row(array($user->get_html_link(), $user->email, make_delete_link($mailing, $user)));
        }
        foreach($mailing->get_subscribed_email() as $mail)
            $table->add_row(array("Utilisateur non enregistré", $mail, make_delete_link($mailing, false, $mail)));
        $cts->add($table);
        $ml[$mailing->id] = $mailing->get_full_name();
    }
    $frm = new form("add_user","manage.php?id_asso=".$asso->id,false,"POST","Ajouter un membre");
    $frm->add_select_field("id_mailing", "Mailing", $ml);
    $frm->add_user_fieldv2("add_member","Membre");
    $frm->add_text_field("add_email","Email","");
    $frm->add_submit("valid","Ajouter");
    $cts->add($frm,true);
    $frm = new form("add_mailing","manage.php?id_asso=".$asso->id,false,"POST","Ajouter une mailing");
    $frm->add_text_field("add_mailing","Nom","");
    $frm->add_submit("valid","Ajouter");
    $cts->add($frm,true);
    $frm = new form("del_mailing","manage.php?id_asso=".$asso->id,false,"POST","Supprimer une mailing");
    $frm->add_select_field("del_mailing", "Mailing", $ml);
    $frm->add_submit("valid","Supprimer");
    $cts->add($frm,true);
} else {
    $cts->add_paragraph("Les mailings ne sont pas activées pour cette association.");
}

$site->add_contents($cts);
$site->end_page();


