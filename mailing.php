<?php

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


$topdir = "./";

require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");
require_once($topdir. "include/entities/mailing.inc.php");
require_once($topdir. "include/entities/utilisateur.inc.php");

if ( isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on" )
{
  echo "ERROR HTTPS REQUIRED";
  exit();
}

$db = new mysqlae ();

if ( !$db->dbh )
{
  echo "ERROR DB UNAVAILABLE";
  exit();
}

$valid = new requete($db,
  "SELECT `key` ".
  "FROM `sso_api_keys` ".
  "WHERE `key` = '".mysql_real_escape_string($_REQUEST["key"])."'");

if ( $valid->lines != 1 )
{
  echo "ERROR KEY NOT VALID\n";
  exit();
}

$req = new requete($db,"SELECT * FROM mailing WHERE is_valid = 1");
while ( $row = $req->get_row() )
{
    $mailing = new mailing($db);
    $mailing->load_by_id($row['id_mailing']);
    if (empty($mailing->get_subscribed_user()))
        continue;
    echo $mailing->get_full_name(),": ";
    foreach($mailing->get_subscribed_user() as $user_id) {
        $user = new utilisateur($db);
        $user->load_by_id($user_id);
        echo $user->email," ";
    }
    foreach($mailing->get_subscribed_email() as $mail)
        echo $mail,"  ";
    echo "\n";
}
