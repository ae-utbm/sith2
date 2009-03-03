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
/* recap edt */
$cts = new contents("Pédagogie");
$tab = array();
$edts = $user->get_edt_list();
if(!empty($edts))
{
  foreach($user->get_edt_list() as $edt)
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
$cts2 = new contents("Ajoutez un nouvel emploi du temps   (Étape 1/2)");

$tab = array();
foreach(uv::get_list($site->db) as $uv)
  $tab[ $uv['id_uv'] ] = $uv['code']." - ".$uv['intitule'];

$frm = new form("addedt", "edt.php?action=new", true, "post", "Choix des UV");
$frm->add(new selectbox('uvlist', 'Choisissez les UV de ce nouvel emploi du temps', $tab, '', 'UV'));
/* semestre */
$y = date('Y');
$sem = array();
for($i = $y-2; $i <= $y; $i++){
  $sem['P'.$i] = 'Printemps '.$i;
  $sem['A'.$i] = 'Automne '.$i;
}
$frm->add_select_field("semestre", "Semestre concern&eacute;", $sem, SEMESTER_NOW);
$frm->add_submit("continue", "Passer à l'étape suivante");
$cts2->add($frm);

$site->add_contents($cts2);


/**** ajout d'UV */
$uv = new uv($site->db, $site->dbrw, 0);

$cts = new contents("Détails des UV");
$cts->add_paragraph("Indiquez ci-dessous les séances auxquelles vous êtes
      inscrit. Si celle-ci n'est pas présente dans la liste proposée, choisissez
      \"Ajouter une séance\" afin de la créer.");

$site->add_contents($cts);
$site->add_contents(new add_uv_edt_box($uv));

$site->add_contents(new add_seance_box(0, GROUP_C, SEMESTER_NOW));


$site->end_page();
?>
