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

define('TODO_TABLE', 'ae_info_todo');

$todo_status = array ('New', 'WontFix', 'Invalid', 'InProgress', 'Fixed');

class todoitem extends stdentity
{
    var $id_task;
    var $id_user_reporter;
    var $id_user_assignee;
    var $id_asso_concerned;
    var $date_deadline;
    var $date_submitted;
    var $priority;
    var $enh_or_bug;
    var $status;

    function load_by_id ($id)
    {
        $req = new requete($this->db, 'SELECT * FROM `'.TODO_TABLE.'` WHERE `id_task` = '.$id.' LIMIT 1');

        if ($req->lines == 1) {
            $this->_load ($req->get_row ());
            return true;
        }

        $this->id_task = -1;
        return false;
    }

    function _load ($row)
    {
        $this->id_task = $row['id_task'];
        $this->id_user_reporter = $row['id_user_reporter'];
        $this->id_user_assignee = $row['id_user_assignee'];
        $this->id_asso_concerned = $row['id_asso_concerned'];
        $this->date_submitted = $row['date_submitted'];
        $this->priority = $row['priority'];
        $this->enh_or_bug = $row['enh_or_bug'];
        $this->status = $row['status'];
    }
}
