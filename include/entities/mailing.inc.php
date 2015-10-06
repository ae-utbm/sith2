<?php

/** @file Gestion des mailing list
 *
 */

/* Copyright 2015
 * - Skia <lordbanana25 AT mailoo DOT org>
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
 */

require_once("std.inc.php");


class mailing extends stdentity 
{
    var $nom;
    var $id_asso_parent;

    function create($nom, $id_asso_parent) {
        if ( is_null($this->dbrw) ) return; // "Read Only" mode

        $this->nom = $nom;
        $this->id_asso_parent = $id_asso_parent;

        $sql = new insert ($this->dbrw,
            "mailing",
            array(
                "id_asso_parent" => $this->id_asso_parent,
                "nom" => $this->nom
            )
        );

        if ( $sql )
            $this->id = $sql->get_id();
        else
            $this->id = null;
    }

    function update($nom, $id_asso_parent) {
        if ( is_null($this->dbrw) ) return; // "Read Only" mode

        $this->nom = $nom;
        $this->id_asso_parent = $id_asso_parent;

        $sql = new update ($this->dbrw,
            "mailing",
            array(
                "id_asso_parent" => $this->id_asso_parent,
                "nom_asso" => $this->nom
            ),
            array ( "id_mailing" => $this->id )

        );
    }

    function remove() {
        new delete($site->dbrw,
            'mailing_member',
            array(
                'id_mailing'=>$this->id
            )
        );
        new delete($site->dbrw,
            'mailing',
            array(
                'id_mailing'=>$this->id
            )
        );
    }

    function _load($row) {
        $this->nom = $row['nom'];
        $this->id_asso_parent = $row['id_asso_parent'];
    }

    /** Charge une mailing par son ID
     * @param $id ID de la mailing
     */
    function load_by_id ( $id )
    {
        $req = new requete($this->db, "SELECT * FROM `mailing`
            WHERE `id_mailing` = '" . mysql_real_escape_string($id) . "'
            LIMIT 1");
        if ( $req->lines == 1 )
        {
            $this->_load($req->get_row());
            return true;
        }
        $this->id = null;
        return false;
    }

    function add_member($id_user) {
        $sql = new insert ($this->dbrw,
            "mailing_member",
            array(
                "id_mailing" => $this->id,
                "id_user" => $id_user
            )
        );
        if ($sql)
            return 0;
        else
            return 1;
    }

    function add_email($email) {
        $sql = new insert ($this->dbrw,
            "mailing_member",
            array(
                "id_mailing" => $this->id,
                "email" => $email
            )
        );
        if ($sql)
            return 0;
        else
            return 1;
    }

    function del_user($id_user) {
        $sql = new delete($site->dbrw,
            'mailing_member',
            array(
                'id_mailing'=>$this->id,
                'id_user'=>$id_user
            )
        );
        if ($sql)
            return 0;
        else
            return 1;
    }

    function del_email($email) {
        $sql = new delete ($this->dbrw,
            "mailing_member",
            array(
                "id_mailing" => $this->id,
                "email" => $email
            )
        );
        if ($sql)
            return 0;
        else
            return 1;
    }

    function get_subscribed_user() {
        $req = new requete($this->db, "SELECT * FROM `mailing_member`
            WHERE `id_mailing` = '" . mysql_real_escape_string($id) . "'
            AND id_user IS NOT NULL");
        $list = array();
        foreach($req->get_row() as $row) {
            $list[] = $row['id_user'];
        }
        return $list;
    }

    function get_subscribed_email() {
        $req = new requete($this->db, "SELECT * FROM `mailing_member`
            WHERE `id_mailing` = '" . mysql_real_escape_string($id) . "'
            AND email IS NOT NULL");
        $list = array();
        foreach($req->get_row() as $row) {
            $list[] = $row['email'];
        }
        return $list;
    }
}

