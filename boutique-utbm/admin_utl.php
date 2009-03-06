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
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();


$user = new utilisateur($site->db,$site->dbrw);
if ( isset($_REQUEST["id_utilisateur"]) )
  $user->load_by_id($_REQUEST["id_utilisateur"]);
$site->start_page("services","Administration");
if( $user->is_valid() && $user->type=='srv')
{
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin_utl.php\">Services</a> / Service");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
$cts = new contents("<a href=\"admin.php\">Administration</a> / Services");

$req=new requete($site->db,'SELECT id_utilisateur, CONCAT(`prenom_utl`,\' \',`nom_utl`) AS srv FROM utilisateurs WHERE type_utl=\'srv\'');
$cts->add(new sqltable("utls",
          null,
          $req,
          "admin_utl.php",
          "id_utilisateur",
          array("srv"=>"Service"),
          array(),
          array(),
          array(),
          true,
          false));

$cts->add($lst,true);
$site->add_contents($cts);
$site->end_page();

?>
