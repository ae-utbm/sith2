<?php
/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
$topdir="../";
require_once("include/sas.inc.php");
require_once($topdir. "include/entities/page.inc.php");
$site = new sas();
$site->add_css("css/sas.css");

$site->allow_only_logged_users("sas");

$photo = new photo($site->db,$site->dbrw);

$site->start_page("sas","Droit à l'image");
$cts = new contents("Droit à l'image");

$frm = new form("auto","meslicences.php",false,"POST","Licence par défaut pour mes photos");
$frm->add_hidden("action","defaultlicence");
$frm->add_entity_select('id_licence',
                        'Choix de la licence',
                        $site->db,
                        'licence',
                        $site->user->id_licence_default_sas,
                        false,
                        array(),
                        '\'id_licence\' ASC');
$frm->add_checkbox('applyall','Appliquer à toutes mes photos sans licences');
$frm->add_submit("setdroit","Enregistrer");
$cts->add($frm,true);


$cts->add_title(2,"Mes photos sans licences");
$sql = new requete($site->db,
  "SELECT COUNT(*) " .
  "FROM sas_photos " .
  "WHERE id_utilisateur_photographe=".$site->user->id." " .
  "AND id_licence IS NULL ");
list($count) = $sql->get_row();
$cts->add_paragraph("<a href=\"meslicences.php?page=process\">$count photo(s) en sans licence définie</a>");

$site->add_contents($cts);
$site->end_page ();

?>
