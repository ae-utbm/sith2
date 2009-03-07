<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/selectbox.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->add_js("pedagogie/pedagogie.js");
//$site->allow_only_logged_users();

$site->start_page("services", "AE Pédagogie");
$user = new pedag_user($site->db, $site->dbrw);
$user->load_by_id($site->user->id);

$path = "<a href=\"./\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";
$path .= " / "."<a href=\"./edt.php\"><img src=\"".$topdir."images/icons/16/user.png\" class=\"icon\" /> Emploi du temps </a>";

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new')
{  
  /**
   * creation edt : etape 2 !
   */
  if(isset($_REQUEST['newedtstep1']))
  {
    $path .= " / "."Ajouter un emploi du temps (Étape 2/2)";
    $cts = new contents($path);
  
    $sem = $_REQUEST['semestre'];
    $cts->add_paragraph("Vous ajoutez un emploi du temps pour le semestre <b>$sem</b>");
    $cts->add_paragraph("Pour chacune de vos UV, choisissez à présent
    les séances auxquelles vous êtes inscrit, si la séance n'apparait pas
    dans la liste proposée, c'est que vous êtes le premier à l'entrer 
    sur le site, cliquez alors sur \"Ajouter une séance manquante\" pour 
    poursuivre.");
    
    $frm = new form("newedt", "edt.php?action=save", true, "post", "Ajouter un nouvel emploi du temps   (Étape 2/2)");
    $frm->add_hidden("semestre", $sem);
  
    foreach($_REQUEST['uvlist_to'] as $iduv){
      $uv = new uv($site->db, $site->dbrw, $iduv);
      if($uv->is_valid())
        $frm->add(new add_uv_edt_box($uv, $sem), false, false, false, false, false, true);
    }
    
    $frm->add_submit("newedtstep2", "Enregistrer l'emploi du temps");
    
    $cts->add($frm);
    $site->add_contents($cts);
    $site->end_page();
    exit;  
  }
  /**
   * sinon etape 1 
   */
  else
  {
    $path .= " / "."Ajouter un emploi du temps (Étape 1/2)";
    $cts = new contents($path);
    
    $cts->add_paragraph("Vous pouvez ici créer d'un nouvel emploi du temps
    sur le site de l'AE.");
    $cts->add_paragraph("Choisissez pour commencer les UV auxquelles vous
    vous êtes inscrit (y compris les UV hors emploi du temps), le semestre
    concerné (par défaut il s'agit du semestre courant) et appuyez sur \"Passer
    à l'étape suivante\".");
    $cts->add_paragraph("Notez que vous ne pouvez créer qu'un emploi du 
    temps par semestre, mais vous aurez la possibilité de l'éditer.");
    
    $frm = new form("newedt", "edt.php?action=new", true, "post", "Ajouter un nouvel emploi du temps   (Étape 1/2)");
    $frm->add_hidden("step", "1");
    
    $tab = array();
    foreach(uv::get_list($site->db) as $uv)
      $tab[ $uv['id_uv'] ] = $uv['code']." - ".$uv['intitule'];
      
    $frm->add(new selectbox('uvlist', 'Choisissez les UV de ce nouvel emploi du temps', $tab, '', 'UV'));
    /* semestre */
    $y = date('Y');
    $sem = array();
    for($i = $y-2; $i <= $y; $i++){
      $sem['P'.$i] = 'Printemps '.$i;
      $sem['A'.$i] = 'Automne '.$i;
    }
    $frm->add_select_field("semestre", "Semestre concern&eacute;", $sem, SEMESTER_NOW);
    $frm->add_submit("newedtstep1", "Passer à l'étape suivante");
    $cts->add($frm);
  }
  
  $site->add_contents($cts);
  $site->end_page();
  exit;
}

/**
 * enregistrement effectif du nouvel emploi du temps
 */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
{
  if(!isset($_REQUEST['newedtstep2']))
    $site->redirect('edt.php?action=new');

  if(!check_semester_format($_REQUEST['semestre']))
    $site->redirect('edt.php?action=new');
  else
    $semestre = $_REQUEST['semestre'];
  
  $freq = array(); //tableau des frequences envoyees 
  $seances = array(); //tableau des seances
  foreach($_REQUEST as $arg=>$value){
    if(preg_match("/^freq/", $arg) && ($value == 'A' || $value == 'B')){
      list(, $uv, $type) = explode("_", $arg);
      $freq[$uv][$_GROUP[$type]['short']] = $value;
    }
    
    if(preg_match("/^seance/", $arg) && $value){
      list(, $uv, $type) = explode("_", $arg);
      $seances[$uv][$type] = $value;
    }    
  }
  
  if(empty($seances))
    $site->redirect('edt.php?action=new');
    
  foreach($seances as $iduv=>$types){
    $uv = new uv($site->db, $site->dbrw, $iduv);
    if(!$uv->is_valid())
      continue;
    
    foreach($types as $type => $val){
      if($val == 'add' || $val == 'none')
        continue;
      if($uv->has_group(intval($val), $type)){
        if(isset($freq[$uv->id]) && isset($freq[$uv->id][$type]))
          $semaine = $freq[$uv->id][$type];
        else
          $semaine = null;
          
        $user->join_uv_group($val, $semaine);
      }
    }    
  }
  
  $site->redirect("edt.php?semestre=".$semestre."&action=view");
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
{
  if(!isset($_REQUEST['semestre']))
    $site->redirect('edt.php');
    
  /** confirmation anti boulets */
  if(isset($_REQUEST['sure']) && $_REQUEST['sure'] == 'yes')
  {
    $user->delete_edt($_REQUEST['semestre']);
    //$site->redirect('edt.php');
  }
  else
  {
    $path .= " / "."Suppression emploi du temps ".$_REQUEST['semestre'];
    $cts = new contents($path);
    
    $cts->add_paragraph("<b>Vous vous apprêtez à supprimer l'emploi du temps
    du semestre ".$_REQUEST['semestre'].". Êtes vous absolument sûr ?</b>");
    
    $frm = new form("iwantit", "edt.php?action=delete", true, "post", "");
    $frm->add_hidden("semestre", $_REQUEST['semestre']);
    $frm->add_hidden("sure", "yes");
    $frm->add_submit("send", "Supprimer ".$_REQUEST['semestre']);
    $cts->add($frm);
    
    $cts->add_paragraph("<a href=\"edt.php\">Annuler</a>");
    
    $site->add_contents($cts);
    $site->end_page();
    exit;
  }
  
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'print')
{
  if(isset($_REQUEST['semestre']) && check_semester_format($_REQUEST['semestre']))
    $semestre = $_REQUEST['semestre'];
  else
    $semestre = SEMESTER_NOW;
  
  if(!in_array($semestre, $user->get_edt_list()))
    $site->redirect('edt.php');
  
  require_once ("include/cts/edt_render.inc.php");
  
  $groups = $user->get_groups_detail($semestre);
  if(empty($groups))
    $site->redirect('edt.php');
    
  $lines = array();
  foreach($groups as $group){
    $lines[] = array(
                "semaine_seance" => $group['semaine'],
                "hr_debut_seance" => date("H\hi", $group['debut']),
                "hr_fin_seance" => date("H\hi", $group['fin']),
                "jour_seance" => get_day($group['jour']),
                "type_seance" => $_GROUP[ $group['type'] ]['long'],
                "grp_seance" => $group['num_groupe'],
                "nom_uv" => $group['code'],
                "salle_seance" => $group['salle'] 
               );
  }
  print_r($lines);
  $edt = new edt_img($user->get_display_name()." - ".$semestre, $lines);
  //$edt->generate(false);
  exit;
}



/**
 * Contenu défaut page
 */

/* recap edt */
$cts = new contents($path);
$tab = array();
$edts = $user->get_edt_list();
if(!empty($edts))
{
  foreach($edts as $edt)
  {
    $tab[$edt]['semestre'] = $edt;
    $i=0;
    foreach($user->get_edt_detail($edt) as $uv)
      $tab[$edt]['uv'.++$i] = $uv['code'];
  }
}
$cts->add(new sqltable("edtlist", "Liste de vos emplois du temps", $tab, "edt.php", 'semestre',
                        array("semestre"=>"Semestre",
                              "uv1" => "UV 1",
                              "uv2" => "UV 2",
                              "uv3" => "UV 3",
                              "uv4" => "UV 4",
                              "uv5" => "UV 5",
                              "uv6" => "UV 6",
                              "uv7" => "UV 7"),
                        array("view" => "Voir détails",
                              "print" => "Format imprimable",
                              "edit" => "Éditer",
                              "delete" => "Supprimer"),
                        array()), true);
$cts->add_paragraph("<input type=\"submit\" class=\"isubmit\" "
                    ."value=\"+ Ajouter un emploi du temps\" "
                    ."onclick=\"edt.add();\" "
                    ."name=\"add_edt\" id=\"add_edt\"/>");
$site->add_contents($cts);


/******************/
/**** ajout d'UV */
$uv = new uv($site->db, $site->dbrw, 0);

$cts = new contents("Détails des UV");
$cts->add_paragraph("Indiquez ci-dessous les séances auxquelles vous êtes
      inscrit. Si celle-ci n'est pas présente dans la liste proposée, choisissez
      \"Ajouter une séance\" afin de la créer.");

$site->add_contents($cts);

$site->add_contents(new add_seance_box(0, GROUP_C, SEMESTER_NOW));


$site->end_page();
?>
