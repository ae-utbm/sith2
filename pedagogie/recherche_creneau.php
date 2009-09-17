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
require_once("include/pedagogie.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/edt_render.inc.php");

$site = new site();
$site->add_js("pedagogie/pedagogie.js");
$site->allow_only_logged_users();

$site->start_page("services", "AE Pédagogie");
$user = new pedag_user($site->db);
$id_utls = array(1827);//,2536,4040,3458);

$lines = array();
$horaires = array('08h00',
                  '09h00',
                  '10h00',
                  '10h15',
                  '11h15',
                  '12h15',
                  '13h00',
                  '14h00',
                  '15h00',
                  '16h00',
                  '16h15',
                  '17h15',
                  '18h15',
                  '19h15',
                  '20h15');
$jours = array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi');
$oqp = array();
foreach($jours as $jour)
{
  $oqp[$jour]=array();
  foreach($horaires as $horraire)
    $oqp[$jour][$horraire]=array('A'=>0,'B'=>0);
}
foreach($id_utls as $id_utl)
{
  $user->load_by_id($id_utl);
  if($user->is_valid())
  {
    if(in_array(SEMESTER_NOW, $user->get_edt_list()))
    {
      $groups = $user->get_groups_detail(SEMESTER_NOW);
      if(!empty($groups))
      {
        foreach($groups as $group)
        {
          $jour  = get_day($group['jour']);
          $debut = substr($group['debut'], 0,5);
          $fin   = substr($group['fin'], 0, 5);
          $sem   = $group['semaine'];
          $add   = 0;
          foreach($oqp[$jour] as $horraire => $_oqp)
          {
            if($horraire==$debut)
              $add = 1;
            if(is_null($sem))
            {
              $_oqp['A']+=$add;
              $_oqp['B']+=$add;
            }
            else
              $_oqp[$sem]+=$add;
            $oqp[$jour][$horraire]=$_oqp;
            if($horraire==$fin)
            {
              $add = 0;
              break;
            }
          }
        }
      }
    }
  }
}

$free = array();
$oqp[$jour][$horraire]=array('A'=>0,'B'=>0);
foreach($oqp as $jour => $horraires)
{
  $startA = false;
  $startB = false;
  $lastA = false;
  $lastB = false;
  foreach($horraires as $horraire => $_oqp)
  {
    if($_oqp['A']==0 && $_oqp['B']==0)
    {
      $lastA=$horraire;
      $lastB=$horraire;
      if(!$startA)
        $startA=$horraire;
      if(!$startB)
        $startA=$horraire;
    }
    if($_oqp['A']==0 && $_oqp['B']!=0)
    {
      $lastA=$horraire;
      if(!$startA)
        $startA=$horraire;
      if($startB && $startB!=$lastB)
      {
        $free[] = array("semaine_seance" =>'B',
                        "hr_deb_seance"  => $startB,
                        "hr_fin_seance"  => $lastB,
                        "jour_seance"    => $jour,
                        "type_seance"    => '',
                        "grp_seance"     => 0,
                        "nom_uv"         => '',
                        "salle_seance"   => '');
        $startB = false;
        $lastB = false;
      }
    }
    elseif($_oqp['A']!=0 && $_oqp['B']==0)
    {
      $lastB=$horraire;
      if(!$startB)
        $startB=$horraire;
      if($startA && $startA!=$lastA)
      {
        $free[] = array("semaine_seance" =>'A',
                        "hr_deb_seance"  => $startA,
                        "hr_fin_seance"  => $lastA,
                        "jour_seance"    => $jour,
                        "type_seance"    => '',
                        "grp_seance"     => 0,
                        "nom_uv"         => '',
                        "salle_seance"   => '');
        $startA = false;
        $lastA = false;
      }
    }
    else
    {
      if($startA && $startA!=$lastA)
      {
        $free[] = array("semaine_seance" =>'A',
                        "hr_deb_seance"  => $startA,
                        "hr_fin_seance"  => $lastA,
                        "jour_seance"    => $jour,
                        "type_seance"    => '',
                        "grp_seance"     => 0,
                        "nom_uv"         => '',
                        "salle_seance"   => '');
        $startA = false;
        $lastA = false;
      }
      if($startB && $startB!=$lastB)
      {
        $free[] = array("semaine_seance" =>'B',
                        "hr_deb_seance"  => $startB,
                        "hr_fin_seance"  => $lastB,
                        "jour_seance"    => $jour,
                        "type_seance"    => '',
                        "grp_seance"     => 0,
                        "nom_uv"         => '',
                        "salle_seance"   => '');
        $startB = false;
        $lastB = false;
      }
    }
  }
}
print_r($oqp);
print_r($free);
exit();
$edt = new edt_img('Créneaux disponibles', $free);
$edt->generate(false);
exit;

?>
