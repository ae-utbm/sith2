<?php
/* Copyright 2005
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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

require_once("include/boutique.inc.php");
$site = new boutique ();
switch ($_REQUEST["domain"])
{
  case "utbm" :
    $site->user->load_by_email($_REQUEST["username"]."@utbm.fr");
  break;
  case "assidu" :
    $site->user->load_by_email($_REQUEST["username"]."@assidu-utbm.fr");
  break;
  case "id" :
    $site->user->load_by_id($_REQUEST["username"]);
  break;
  case "autre" :
    $site->user->load_by_email($_REQUEST["username"]);
  break;
  case "alias" :
    $site->user->load_by_alias($_REQUEST["username"]);
  break;
  default :
    $site->user->load_by_email($_REQUEST["username"]."@utbm.fr");
  break;
}

if ( !$site->user->is_valid() )
  $site->allow_only_logged_users();

if ( $site->user->hash != "valid" )
{
  header("Location: http://ae.utbm.fr/article.php?name=site:activate");
  exit();
}

if ( !$site->user->is_password($_POST["password"]) )
  $site->allow_only_logged_users();

$forever=false;

if ( isset($_REQUEST["personnal_computer"]) )
  $forever=true;

$site->connect_user($forever);

$page='index.php';
header("Location: $page");

?>
