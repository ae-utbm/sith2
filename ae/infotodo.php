<?php
/* Copyright 2011
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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

require_once($topdir."include/site.inc.php");
require_once($topdir."include/cts/user.inc.php");
require_once($topdir."include/entities/todoitem.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") )
  $site->error_forbidden("none","group",7);

//$site->add_js("js/sqltable2.js");
$site->start_page("none","TODO list");

$cts = new contents ('Foo');
$frmfilter = new form('filter', '?', false, 'GET', 'Filter');
$frmfilter->add_select_field('etat', 'Etat', array('' => 'Tout', 'new' => 'Nouveau', 'resolu' => 'Résolu', 'encours' => 'En cours'), '');
$frmfilter->add_checkbox ('onlyme', 'Uniquement ceux assigné à moi');
$frmfilter->add_submit ('submit', 'Filtrer');
$cts->add ($frmfilter, false);

$where = array();
if (isset ($_REQUEST['onlyme']) && $_REQUEST['onlyme'])
    $where[] = '`id_user_assignee` = '.$site->user;
if (isset ($_REQUEST['etat'])) {
    $etats = array('new' => 0, 'resolu' => 4, 'encours' => 3);
    if (array_key_exists ($_REQUEST['etat'], $etats))
        $where[] = $etats[$_REQUEST['etat']];
}

$sql = 'SELECT * FROM ae_info_todo ORDER BY priority, date_deadline, date_submitted';
$req = new requete($site->db, $sql);
if (!empty ($where))
    $sql .= ' WHERE '.implode(' AND ', $where);

/*$tbl = new sqltable2 ('todos', 'Liste TODO', 'infotodo.php');
$tbl->add_column_entity ('id_user_reporter', 'Reporter', array('nom_utilisateur_reporter'));
$tbl->add_column_entity ('id_user_assignee', 'Assigné à', array('nom_utilisateur_assignee'));
$tbl->set_sql ($site->db, 'id_task', $sql);*/

$tbl = new sqltable ('infotodo', 'Liste des tâches', $req, 'infotodo.php', 'id_task',
                     array('nom_utilisateur_reporter' => 'Demandeur',
                           'nom_utilisateur_assignee' => 'Assigné à',
                           'nom_asso_concerned' => 'Club associé',
                           'date_deadline' => 'Deadline',
                           'date_submitted' => 'Date soumission',
                           'priority' => 'Priorité',
                           'enh_or_bug' => 'Type',
                           'status' => 'Statut',
                           'description' => 'Description'),
                     array('detail', 'Détails'),
                     array(),
                     array());
$cts->add ($tbl);

$site->add_contents ($cts);
$site->end_page();

?>