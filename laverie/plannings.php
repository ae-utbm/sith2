<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet AT oxynux DOT org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
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

define("ID_ASSO_LAVERIE", 84);

$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "laverie/include/laverie.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/planning.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/entities/jeton.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");

$site = new sitelaverie();
$site->allow_only_logged_users("services");
$site->start_page("none","Laverie");
$cts = new contents("Machines à laver de l'AE");

$site->get_rights();

$now = date("Y-m-d H:i:s",time());

$date = getDate();
            
if($date['wday'] == 0)
  $days_left = 0; 
else
  $days_left = 7 - $date['wday'];

$current_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left, $date['year']);
$next_week_start = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +1, $date['year']);
$next_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +8, $date['year']);

$lst = new itemlist("Resultats :");

if($_REQUEST['action'] == "creneaux")
{
  $sql = new requete($site->db,
	                   "SELECT *,
                      CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur,
                      pl_gap.id_gap AS id_gap
                      FROM pl_gap
                      LEFT JOIN pl_gap_user ON pl_gap_user.id_gap = pl_gap.id_gap
                      LEFT JOIN utilisateurs ON pl_gap_user.id_utilisateur = utilisateurs.id_utilisateur
                      WHERE pl_gap.id_planning = '".$_REQUEST['id_planning']."'
                      ORDER BY pl_gap.start_gap");

  $table = new sqltable("listecreneaux",
                        "Liste des créneaux",
                        $sql,
                        "plannings.php?id_planning=".$_REQUEST['id_planning'],
                        "id_gap",
                        array("start_gap" => "Début",
                              "end_gap" => "Fin",
                              "nom_utilisateur" => "Réservé par"),
                        array("modifier_reservation"=> "Modifier la réservation"),
                        array() );

  $cts->add($table, true);
}
elseif($_REQUEST['action'] == "modifier_reservation")
{
  $sql = new requete($site->db,
	                   "SELECT *, pl_gap_user.id_utilisateur AS id_util_old
                      FROM pl_gap
                      LEFT JOIN pl_gap_user ON pl_gap.id_gap = pl_gap_user.id_gap
                      WHERE pl_gap.id_gap = ".$_REQUEST['id_gap']);
  $row = $sql->get_row();

  $cts->add_paragraph("<strong>Début du créneau :</strong> ".$row['start_gap']);
  $cts->add_paragraph("<strong>Fin du créneau :</strong> ".$row['end_gap']);
  /* Faire fonctionner l'autorefill (pas franchement urgent) */
  $frm = new form("modifierreservation","plannings.php?action=do_modifier_reservation",true,"POST","Modifier une réservation");
  $frm->add_user_fieldv2("id_util","Réservé par",$row['id_util_old'],true);
  $frm->add_hidden("id_old",$row['id_util_old']);
  $frm->add_hidden("id_gap",$_REQUEST['id_gap']);
  $frm->add_hidden("id_planning",$_REQUEST['id_planning']);
  $frm->add_submit("valid","Valider");
  $frm->allow_only_one_usage();
  $cts->add($frm,true);
}
elseif($_REQUEST['action'] == "do_modifier_reservation")
{
  $planning = new planning($site->db,$site->dbrw);
  $planning->load_by_id($_REQUEST['id_planning']);
  if($_REQUEST['id_old'] != NULL)
    $planning->remove_user_from_gap($_REQUEST['id_gap'],$_REQUEST['id_old']);
  if($_REQUEST['id_util'] != 0)
    $planning->add_user_to_gap($_REQUEST['id_gap'],$_REQUEST['id_util']);
  header( 'Location: plannings.php?id_planning='.$_REQUEST['id_planning'].'&action=creneaux');
}
elseif($_REQUEST['action'] == "creer_planning")
{
  if($_REQUEST['date_fin'] <= $next_week_end)
  {
    $planning = new planning($site->db,$site->dbrw);
    $planning->add(ID_ASSO_LAVERIE,$_REQUEST['id'],'1',$_REQUEST['date_debut'],$_REQUEST['date_fin'],'0');
     
    $date_temp_start = $planning->start_date;
    while ($date_temp_start <= $planning->end_date - 3600)
    {
      $date_temp_end = $date_temp_start + 3600;
      $planning->add_gap(date("Y-m-d H:i:s",$date_temp_start),date("Y-m-d H:i:s",$date_temp_end));
      $date_temp_start = $date_temp_end;
    }
    header( 'Location: index.php?view=plannings' );
  }
  else
  {
    header( 'Location: plannings.php?id='.$_REQUEST['id'].'&action=ajouter_planning' );
  }
}
elseif($_REQUEST['action'] == "ajouter_planning")
{
  $sql = new requete($site->db,
	                   "SELECT * FROM mc_machines
                     WHERE mc_machines.id = ".$_REQUEST['id']);
  $row = $sql->get_row();
   
  $lieu = new lieu($site->db);
  $lieu->load_by_id($row['loc']);
      
  $frm = new form("ajoutplanning", "plannings.php?action=creer_planning",false,"POST","Ajouter un planning");
  $cts->add_paragraph("<strong>Lieu :</strong> ".$lieu->get_html_link());
  $cts->add_paragraph("<strong>Type de machine :</strong> machine à ".$row['type']);
  $cts->add_paragraph("<strong>Identifiant :</strong> ".$row['lettre']);
  $cts->add_paragraph("Vous ne pouvez ajouter un planning que pour la semaine en cours et la semaine suivante.");
  $frm->add_datetime_field("date_debut","Date de début",$next_week_start);
  $frm->add_datetime_field("date_fin","Date de fin",$next_week_end);
  $frm->add_hidden("id",$_REQUEST['id']);
  $frm->add_submit("valid","Valider");
  $frm->allow_only_one_usage();
  $cts->add($frm,true);
}
else
{
  if($_REQUEST['action'] == "supprimer")
  {
    $planning = new planning($site->db,$site->dbrw);
    $planning->load_by_id( $_REQUEST['id_planning'] );
    $planning->remove();
    $lst->add("Le planning a bien été supprimé");
  }
  
  $cts->add($lst);

  $sql = new requete($site->db,
	                   "SELECT * FROM pl_planning
                     INNER JOIN mc_machines ON pl_planning.name_planning = mc_machines.id
                     INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
                     WHERE pl_planning.id_asso = '".ID_ASSO_LAVERIE."'
                     AND pl_planning.end_date_planning > '".$now."'
                     ORDER BY pl_planning.start_date_planning,mc_machines.lettre, mc_machines.type");
 
  $table = new sqltable("listeplannings",
                        "Liste des plannings",
                        $sql,
                        "plannings.php",
                        "id_planning",
                        array("lettre" => "Lettre",
                              "type" => "Type",
                              "nom_lieu" => "Lieu",
                              "start_date_planning" => "Début",
                              "end_date_planning" => "Fin"),
                        array("creneaux" => "Voir les créneaux",
												      "supprimer" => "Supprimer"),
                        array(),
                        array("type" => $GLOBALS['types_jeton']) );

  $cts->add($table, true);
  
  $sql = new requete($site->db,
	                   "SELECT * FROM mc_machines
                     INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
                     WHERE mc_machines.hs = 0
                     ORDER BY mc_machines.lettre,mc_machines.type");

  $table = new sqltable("listmachine",
                        "Liste des machines en service",
                        $sql,
                        "plannings.php",
                        "id",
                        array("lettre" => "Lettre",
                              "type" => "Type de la machine",
                              "nom_lieu" => "Lieu"),
                        array("ajouter_planning" => "Ajouter un planning"),
                        array(),
                        array("type"=>$GLOBALS['types_jeton'] ) );

  $cts->add($table, true);
}

$site->add_contents($cts);
$site->end_page();
?>
