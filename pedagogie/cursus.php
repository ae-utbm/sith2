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
require_once("include/cursus.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->allow_only_logged_users();
$site->add_js("pedagogie/pedagogie.js");
$site->add_css("css/pedagogie.css");
$site->start_page("services", "AE Pédagogie");

$path = "<a href=\"./\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";
$path .= " / "."Cursus";

$cts = new contents($path);

/***********************************************************************
 * Actions
 */

/* ajout/modification effectif des actions ajouts/editions */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
{
}

/* inscription d'un utilisateur a une seance (nom 'done' choisi pour l'icone uniquement */
if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'join' || $_REQUEST['action'] == 'done'))
{
}

/* inscription d'un utilisateur a une seance (nom 'done' choisi pour l'icone uniquement */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'leave')
{
}

/* ajout d'une nouvelle séance */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new')
{
}

/* modification d'une séance existante */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
{
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
{
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'view')
{
}

foreach($_DPT as $dept=>$desc){
  $tab = array();
  $cursuslist = cursus::get_list($site->db, $dept);
  if(empty($cursuslist))
    continue;

  $cts->add_title(2,"<a id=\"dept_".$dept."\" href=\"./uv.php?dept=$dept\">".$desc['long']."</a>");

  foreach($cursuslist as $c)
    $tab[] = array(
               'id_cursus'=>$c['id_cursus'],
               'type'=>$_CURSUS[ $c['type'] ]['long'],
               'intitule'=>$c['intitule'],
               'responsable'=>$c['responsable'],
               'closed'=>($c['closed'] == 1)?"Fermé":""
             );
  $cts->add(new sqltable($dept."_cursuslist", "", $tab, "cursus.php", 'id_cursus',
                         array("type"=>"Type",
                               "intitule"=>"Intitulé",
                               "responsable"=>"Responsable"),
                         array("view"=>"Voir détails"), array()));

}

$site->add_contents($cts);
$site->end_page();
?>
