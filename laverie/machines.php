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

$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "laverie/include/laverie.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");

$site = new sitelaverie();
$site->allow_only_logged_users("services");
$site->start_page("none","Laverie");
$cts = new contents("Machines à laver de l'AE");

$site->get_rights();

$lst = new itemlist("Resultats :");

if(!empty($_REQUEST['id']))
  $ids[] = $_REQUEST['id'];
elseif($_REQUEST['ids'])
{
  foreach ($_REQUEST['ids'] as $id_machine)
  $ids[] = $id_machine;
}

if($_REQUEST['action'] == "hs")
{
  foreach ( $ids as $id )
  {
    $sql = new requete($site->dbrw, "UPDATE mc_machines 
          SET mc_machines.hs = 1
          WHERE mc_machines.id = $id");

    $lst->add("La machine $id a bien été mise hors service","ok");
  }
  /* Cloturer le planning de la machine
   * Champ 'name' du planning = 'id' de la machine (pas la lettre) */
}
  
if($_REQUEST['action'] == "es")
{
  foreach ( $ids as $id )
  {
    $sql = new requete($site->dbrw, "UPDATE mc_machines 
          SET mc_machines.hs = 0
          WHERE mc_machines.id = $id");

    $lst->add("La machine $id a bien été mise en service","ok");
  }
  /* Créer un nouveau planning pour la machine 
   * Champ 'name' du planning = 'id' de la machine (pas la lettre) */
}

if($_REQUEST['action'] == "supprimer")
{
  foreach ( $ids as $id )
  {
    $sql = new requete($site->dbrw, "DELETE FROM mc_machines 
          WHERE mc_machines.id = $id");

    $lst->add("La machine $id  a bien été supprimée","ok");
  }
  /* Cloturer le planning de la machine
   * Champ 'name' du planning = 'id' de la machine (pas la lettre) */
}

if (!empty($_REQUEST["lettre_machine"]) )
{
  $sql = new insert ($site->dbrw,
        "mc_machines",
         array(
           "lettre" => $_REQUEST['lettre_machine'],
           "type" => $_REQUEST['typemachine'],
           "loc" => $_REQUEST['locmachine']) );
}

$cts->add($lst);
    
$frm = new form("ajoutmachine", "index.php?view=machines", false, "POST", "Ajouter une machine");

$frm->add_text_field("lettre_machine", "Lettre de la machine :");
$frm->add_select_field("typemachine", "Type de la machine :", $GLOBALS['types_jeton']);
$frm->add_select_field("locmachine", "Salle concernée :", $GLOBALS['salles_jeton']);
$frm->add_submit("valid","Valider");
$frm->allow_only_one_usage();
$cts->add($frm,true);

/* Liste des machines */
$sql = new requete($site->db, "SELECT * FROM mc_machines
  INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
  WHERE mc_machines.hs = 0
  ORDER BY mc_machines.lettre,mc_machines.type");

$table = new sqltable("listmachinesok",
                      "Liste des machines en service",
                      $sql,
                      "index.php?view=machines",
                      "id",
                      array("lettre" => "Lettre",
                            "type" => "Type de la machine",
                            "nom_lieu" => "Lieu"),
                      array("hs" => "Hors service",
                            "supprimer" => "Supprimer"),
                      array("hs" => "Hors service",
                            "supprimer" => "Supprimer"),
                      array("type"=>$GLOBALS['types_jeton'] ) );

$cts->add($table, true);

$sql = new requete($site->db, "SELECT * FROM mc_machines
                   INNER JOIN loc_lieu ON mc_machines.loc = loc_lieu.id_lieu
                   WHERE mc_machines.hs = 1
                   ORDER BY mc_machines.lettre,mc_machines.type");

$table = new sqltable("listmachineshs",
                      "Liste des machines hors service",
                      $sql,
                      "index.php?view=machines",
                      "id",
                      array("lettre" => "Lettre",
                            "type" => "Type de la machine",
                            "nom_lieu" => "Lieu"),
                      array("es" => "En service",
                            "supprimer" => "Supprimer"),
                      array("es" => "En service",
                            "supprimer" => "Supprimer"),
                      array("type"=>$GLOBALS['types_jeton'] ) );

$cts->add($table, true);

$site->add_contents($cts);
$site->end_page();
?>
