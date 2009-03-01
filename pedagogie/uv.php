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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->start_page("services", "AE Pédagogie");

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";

/**
 * Affichage detail UV
 */
if($_REQUEST['id'])
{
  $uv = new uv($site->db, $site->dbrw, $_REQUEST['id']);
  if(!$uv->is_valid())
    $site->redirect('/pedagogie/');

  print_r($_DPT);
  print_r($uv->get_dept_list());

  $cts = new contents($path);

  $tabs = array(
            array("", "pedagogie/uv.php?id=".$uv->id, "Informations générales"),
            array("candidatures", "pedagogie/uv.php?id=".$uv->id."&view=commentaires", "Commentaires"),
            array("general", "pedagogie/uv.php?id=".$uv->id."&view=suivi", "Séances & Élèves"),
            array("profil", "pedagogie/uv.php?id=".$uv->id."&view=ressources", "Ressources")
          );
  $cts->add(new tabshead($tabs, $_REQUEST['view']));
  $site->add_contents($cts);
  
  $site->end_page();
}

/**
 * Affichage 'sommaire' par departement
 */
if($_REQUEST['dept'])
{
}

?>
