<?php

/* Copyright 2008
 * - Benjamin Collet < bcollet at oxynux dot org >
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
 
$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/utilisateur.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);
	
$site->start_page("none","Administration");

if($_REQUEST['action'] == "info" && !empty($_REQUEST['id_log']))
{
  $req = new requete($site->db, "SELECT CONCAT(prenom_utl,' ',nom_utl) AS nom_utilisateur,
                                   id_utilisateur, id_log, time_log, action_log, context_log, description_log
                                 FROM logs
                                 INNER JOIN utilisateurs USING(id_utilisateur)
                                 WHERE id_log='".$_REQUEST['id_log']."'");

  $cts = new contents("<a href=\"./\">Administration</a> / <a href=\"./logs.php\">Logs</a> / Détail d'un évennement");

  $row = $req->get_row();
  
  $list = new itemlist();
  $list->add("<strong>Date :</strong> ".$row['time_log']);
  $list->add("<strong>Contexte :</strong> ".$row['context_log']);
  $list->add("<strong>Utilisateur :</strong> <a href=\"".$topdir."user.php?id_utilisateur=".$row['id_utilisateur']."\">".$row['nom_utilisateur']."</a>");
  $list->add("<strong>Description :</strong> ".$row['description_log']);
  $cts->add($list);
}
else
{
  $cts = new contents("<a href=\"./\">Administration</a> / Logs");

  $cts->add_title("1","Recherche");
  $cts->add_paragraph("Ici, demian, surement.");


  $req = new requete($site->db, "SELECT CONCAT(prenom_utl,' ',nom_utl) AS nom_utilisateur,
                                   id_utilisateur, id_log, time_log, action_log, context_log, description_log
                                 FROM logs 
                                 INNER JOIN utilisateurs USING(id_utilisateur) 
                                 ORDER BY time_log DESC LIMIT 30");

  $cts->add(new sqltable(
    "logs30", 
    "Dernières actions enregistrées", $req, "logs.php", "id_log", 
    array("time_log" => "Date", "nom_utilisateur" => "Utilisateur", "context_log" => "Contexte", "action_log" => "Action"), 
    array("info" => "Détails"), 
    array(),
    array()),true);
} 
$site->add_contents($cts);
 
$site->end_page();

?>
