<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Softwareus
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/selectbox.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->allow_only_logged_users();
$site->add_js("pedagogie/pedagogie.js");
$site->add_css("css/pedagogie.css");
$site->start_page("services", "AE Pédagogie");

$path = "<a href=\"./\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";

/* compatibilite sqltable bleh */
if(isset($_REQUEST['id_groupe']))
  $_REQUEST['id'] = $_REQUEST['id_groupe'];

/***********************************************************************
 * Actions
 */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
{
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new')
{
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
{
}

/*
if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'popup'
    && isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_seance')
{
}

if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'popup'
    && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_seance')
{
}
*/

/***********************************************************************
 * Affichage detail UV
 */
if($_REQUEST['id'])
{
}

/***********************************************************************
 * Affichage guide des UV
 */

$tabs = array(array("", "pedagogie/uv.php", "Guide des UV"));
foreach($_DPT as $dpt=>$desc)
  $tabs[] = array($dpt, "pedagogie/uv.php?dept=".$dpt, $desc['short']);

/* affichage par defaut de la page : guide des UV */
$path .= " / "."Guide des UV";
$cts = new contents($path);

$site->add_contents($cts);
$site->end_page();
?>
