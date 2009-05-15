<?php
/**
 * @brief Admin de la boutique utbm
 *
 */

/* Copyright 2008
 *
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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

require_once("include/boutique.inc.php");

$site = new boutique();
if(!$site->user->is_in_group("root") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$site->start_page("services","Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Fermeture boutique");

if($_REQUEST['action'] && $_REQUEST['action']=='set')
{
  $site->set_boutique_param('close',$_REQUEST['debut']);
  $site->set_boutique_param('open',$_REQUEST['end']);
  $site->set_boutique_param('close_message',doku2xhtml($_REQUEST['texte']));
  $cts2 = new contents("Message enregistré",doku2xhtml($_REQUEST['texte']));
  $cts->add($cts2,true);
}

$frm = new form('close','close.php',false,'post','Paramétrage');
$frm->add_hidden('action','set');
$frm->add_dokuwiki_toolbar('texte',null,null,false,true);
$frm->add_text_area('texte','Message d\'information');
$frm->add_datetime_field('debut','Début de la période',time(),true);
$frm->add_datetime_field('end','Fin de la période',time()+(3600*24*7),true);
$frm->add_submit('valid','Enregistrer');
$cts->add($frm,true);
$site->add_contents($cts);
$site->end_page();
?>
