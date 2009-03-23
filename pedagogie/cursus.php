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
  $cursus = new cursus($site->db, $site->dbrw);

  if($_REQUEST['magicform']['name']=='newcursus'){
    $cursus->add($_REQUEST['intitule'],
                 $_REQUEST['type'],
                 $_REQUEST['description'],
                 $_REQUEST['responsable'],
                 $_REQUEST['nb_some_of'],
                 $_REQUEST['nb_all_of'],
                 $_REQUEST['departement']);

    $site->redirect("cursus.php?id=".$cursus->id."&action=edit#edituvcursus");
  }
  if($_REQUEST['magicform']['name']=='editcursus'){
    $cursus->load_by_id(intval($_REQUEST['id']));
    if(!$cursus->is_valid())
      $site->redirect("cursus.php");

    $cursus->update($_REQUEST['intitule'], $_REQUEST['type'], $_REQUEST['departement'], $_REQUEST['description'], $_REQUEST['responsable']);

    $site->redirect("cursus.php?id=".$cursus->id);
  }
  if($_REQUEST['magicform']['name']=='edituvcursus'){
    $cursus->load_by_id(intval($_REQUEST['id']));
    if(!$cursus->is_valid())
      $site->redirect("cursus.php");

    $cursus->update(null, null, null, null, null, $_REQUEST['nb_some_of'], $_REQUEST['nb_all_of']);

    /** maj liste des UV all_of */
    $del = array_diff($cursus->uv_all_of, $_REQUEST['all_of_to']);
    $add = array_diff($_REQUEST['all_of_to'], $cursus->uv_all_of);
    foreach($del as $d)
      $cursus->remove_uv($d);
    foreach($add as $a)
      $cursus->add_uv($a, 'ALL_OF');

    /** maj liste des UV some_of */
    $del = array_diff($cursus->uv_some_of, $_REQUEST['some_of_to']);
    $add = array_diff($_REQUEST['some_of_to'], $cursus->uv_some_of);
    foreach($del as $d)
      $cursus->remove_uv($d);
    foreach($add as $a)
      $cursus->add_uv($a, 'SOME_OF');

    $site->redirect("cursus.php?id=".$cursus->id);
  }

  $site->redirect("cursus.php");
}

/* ajout d'une nouvelle séance */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new')
{
  $frm = new form("newcursus", "cursus.php?action=save", true);
  $frm->add_text_field("intitule", "Intitulé", "", true, 36);
  $frm->add_text_field("responsable", "Responsable", "", true, 36);
  $avail_type=array();
  foreach($_CURSUS as $type=>$desc)
    $avail_type[$type] = $desc['long'];
  $frm->add_select_field("type", "Catégorie", $avail_type);
  $avail_dept=array();
  foreach($_DPT as $dept=>$desc)
    $avail_dept[$dept] = $desc['long'];
  $frm->add_select_field("departement", "Département", $avail_dept);
  $frm->add_text_area("description", "Description", $cursus->description, 80, 10);
  $frm->add_text_field("nb_all_of", "Nombre d'UV principales", "", false, 2, false, true, " (\"à obtenir\" pour les mineurs, double-étoilées nécessaires pour les filières)");
  $frm->add_text_field("nb_some_of", "Nombre d'UV secondaires", "", false, 2, false, true, " (\"à choisir parmi\" pour les mineurs, simple-étoilées nécessaires pour les filières)");

  $frm->add_submit("savecursus", "Enregistrer & sélectionner les UV");
  $cts->add($frm);

  $site->add_contents($cts);
  $site->end_page();
  exit;
}

/* modification d'une séance existante */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
{
  $cursus = new cursus($site->db, $site->dbrw, intval($_REQUEST['id']));
  if(!$cursus->is_valid())
    $site->redirect("cursus.php");

  /**
   * Informations principales
   */
  $frm = new form("editcursus", "cursus.php?action=save", true, "post", "Informations générales");
  $frm->add_hidden("id", $cursus->id);

  $frm->add_text_field("intitule", "Intitulé", $cursus->intitule, true, 36);
  $frm->add_text_field("responsable", "Responsable", $cursus->responsable, true, 36);
  $avail_type=array();
  foreach($_CURSUS as $type=>$desc)
    $avail_type[$type] = $desc['long'];
  $frm->add_select_field("type", "Type", $avail_type, $cursus->type);
  $avail_dept=array();
  foreach($_DPT as $dept=>$desc)
    $avail_dept[$dept] = $desc['long'];
  $frm->add_select_field("departement", "Département", $avail_dept, $cursus->departement);
  $frm->add_text_area("description", "Description", $cursus->description, 80, 10);
  $frm->add_checkbox("closed", "Cursus fermé", $cursus->closed);

  $frm->add_submit("savecursus", "Enregistrer les modifications");
  $cts->add($frm, true);

  /**
   * UV comprises dans le cursus
   */
  unset($frm);
  $frm = new form("edituvcursus", "cursus.php?action=save", true, "post", "UV faisant partie du cursus");
  $frm->add_hidden("id", $cursus->id);

  $avail_uv = array();
  $all_uv = array();
  $some_uv = array();
  foreach(uv::get_list($site->db, null, $cursus->departement) as $uv){
    if(in_array($uv['id_uv'], $cursus->uv_all_of))
      $all_uv[ $uv['id_uv'] ] = $uv['code']." - ".$uv['intitule'];
    else if(in_array($uv['id_uv'], $cursus->uv_some_of))
      $some_uv[ $uv['id_uv'] ] = $uv['code']." - ".$uv['intitule'];
    else
      $avail_uv[ $uv['id_uv'] ] = $uv['code']." - ".$uv['intitule'];
  }

  $frm->add_text_field("nb_some_of", "Nombre d'UV ".$UV_RELATION[$cursus->type][1], $cursus->nb_all_of, false, 2);
  $frm->add(new selectbox('all_of', 'UV '.$UV_RELATION[$cursus->type][1], $avail_uv, '', 'UV', $all_uv));

  $frm->add_text_field("nb_some_of", "Nombre d'UV ".$UV_RELATION[$cursus->type][0], $cursus->nb_some_of, false, 2);
  $frm->add(new selectbox('some_of', 'UV '.$UV_RELATION[$cursus->type][0], $avail_uv, '', 'UV', $some_uv));

  $frm->add_submit("savecursus", "Enregistrer les modifications");
  $cts->add($frm, true);

  $site->add_contents($cts);
  $site->end_page();
  exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
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
