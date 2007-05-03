<?php

/** @file
 *
 * @brief Accès au site e l'integ pour la box ATTENTION
 *
 */
/* Copyright 2006
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
 *
 * Ce fichier fait partie du site de l'Association des 0tudiants de
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

require_once($topdir . "include/mysql.inc.php");


$UserBranches = array("TC"             => "TC",
                      "GI"             => "GI",
                      "GSP"            => "IMAP",
                      "GSC"            => "GESC",
                      "GMC"            => "GMC",
                      "Enseignant"     => "Enseignant",
                      "Administration" => "Administration",
                      "Autre"          => "Autre");

class integ
{
  var $db;

  function integ()
  {
    $this->db = new mysql("integ","lecombev","localhost","integ");
  }

  function check_if_registered($email)
  {
    $req = new requete($this->db,"SELECT `email` FROM `2007_preparainage` WHERE `email` = '".mysql_real_escape_string(utf8_decode($email))."'");
    return ($req && ($req->lines == 1));
  }
}

?>
