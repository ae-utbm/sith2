<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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
require_once($topdir. "include/entities/wiki.inc.php");
$site = new site();
if ( $site->user->is_valid() && $_REQUEST["action"] == "create" )
{
  echo $_REQUEST;
}
$site->start_page ("wiki", "Page inexistante");
$parent = new wiki($site->db,$site->dbrw);
$parent->load_by_id(1);
$lastparent = clone $parent;
$frm = new form("newwiki","?",true,"POST");
$frm->add_hidden("action","create");
$frm->add_text_field("title","Titre","",true);
$frm->add_dokuwiki_toolbar("contents");
$frm->add_text_area("contents","Contenu","",80,20,true);
$frm->add_text_field("comment","Log","Créée");
$frm->add_rights_field($lastparent,true,true,"wiki");
$frm->add_submit("save","Ajouter");
$site->add_contents($frm);
$site->end_page ();
?>
