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
require_once($topdir."include/entities/utilisateur.inc.php");
require_once($topdir."include/entities/asso.inc.php");
require_once($topdir."include/cts/user.inc.php");
require_once($topdir."include/entities/todoitem.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","TODO list");

if (isset ($_REQUEST['action']) && $_REQUEST['action'] == 'nouveau') {

} else if (isset ($_REQUEST['id_task']) && isset ($_REQUEST['action']) && $_REQUEST['action'] == 'detail') {
    $idtask = $_REQUEST['id_task'];
    $todo = new todoitem ($site->db);
    $todo->load_by_id ($idtask);

    echo $todo->todo;
    echo htmlentities($todo->todo,ENT_NOQUOTES,"UTF-8");

    $util_reporter = new utilisateur ($site->db);
    $util_reporter->load_by_id ($todo->id_user_reporter);
    $util_assignee = new utilisateur ($site->db);
    $util_assignee->load_by_id ($todo->id_user_assignee);
    $asso_concerne = new asso ($site->db);
    $asso_concerne->load_by_id ($todo->id_asso_concerned);

    $frm = new form ('details', '?', false, 'POST', 'TODO');
    $frm->add_hidden ('id_task', $id_task);
    $frm->add_hidden ('action', 'modif');
    $frm->add_entity_smartselect ('utilisateur_reporter', 'Rapporteur', $util_reporter);
    $frm->add_entity_smartselect ('utilisateur_assignee', 'Assigné à', $util_reporter);
    $frm->add_entity_smartselect ('asso_concerned', 'Asso lié', $asso_concerne);
    $frm->add_date_field ('date_deadline', 'Deadline', strtotime ($todo->date_deadline));
    $frm->add_date_field ('date_submitted', 'Soumis le', strtotime ($todo->date_submitted), false, false);
    $frm->add_select_field ('priority', 'Priorité', $todo_priorities, $todo->priority);
    $frm->add_select_field ('status', 'Statut', $todo_status, $todo->status);
    $frm->add_text_field ('desc', 'Description', $todo->desc);
    $frm->add_text_area ('todo', 'Todo', $todo->todo);

    $cts = new contents ('Détail');
    $cts->add ($frm);
    $site->add_contents ($cts);
} else {
    $cts = new contents ('TODO');
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

    $tblcts = new contents('TODO list');
    $tbl = new sqltable ('infotodo', 'Liste des tâches', $req, 'infotodo.php', 'id_task',
                         array('nom_utilisateur_reporter' => 'Demandeur',
                               'nom_utilisateur_assignee' => 'Assigné à',
                               'nom_asso_concerned' => array('Club associé', 'nom_asso'),
                               'date_deadline' => 'Deadline',
                               'date_submitted' => 'Date soumission',
                               'priority' => 'Priorité',
                               'enh_or_bug' => 'Type',
                               'status' => 'Statut',
                               'description' => 'Description'),
                         array('detail' => 'Détails'),
                         array(),
                         array());
    $tblcts->add ($tbl);

    $site->add_contents ($cts);
    $site->add_contents ($tblcts);
}

$site->end_page();

?>