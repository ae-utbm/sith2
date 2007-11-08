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
require_once($topdir. "include/entities/jeton.inc.php");

$site = new sitelaverie();
$site->allow_only_logged_users("services");
$site->start_page("none","Laverie");
$cts = new contents("Machines à laver de l'AE");

$site->get_rights();

$frm = new form("retourjetons","retour.php",false,"POST","Retour jetons");
$lst = new itemlist("Résultats :");
    
if($_REQUEST['action'] == "retourner")
{
  if(!empty($_REQUEST['id_jeton']))
    $id_jetons[] = $_REQUEST['id_jeton'];
  elseif($_REQUEST['id_jetons'])
  {
    foreach ($_REQUEST['id_jetons'] as $id_jeton)
      $id_jetons[] = $id_jeton;
  }

  foreach($id_jetons as $numjeton)
  {
    $jeton = new jeton($site->db, $site->dbrw);
    $jeton->load_by_id($numjeton);
    $jeton->given_back ();
    $lst->add("Le jeton $jeton->nom a bien été rendu.", "ok");
  }
}
    
/* Liste des jetons empruntés */
$sql = new requete($site->db,
                   "SELECT mc_jeton_utilisateur.id_jeton, 
                   mc_jeton_utilisateur.id_utilisateur, 
                   mc_jeton_utilisateur.prise_jeton,
                   mc_jeton_utilisateur.penalite,
                   mc_jeton.id_jeton,
                   mc_jeton.nom_jeton,
                   DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree,
                   utilisateurs.id_utilisateur,
                   CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
                   FROM mc_jeton_utilisateur
                   INNER JOIN utilisateurs
                   ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
                   LEFT JOIN mc_jeton
                   ON mc_jeton_utilisateur.id_jeton = mc_jeton.id_jeton
                   WHERE mc_jeton_utilisateur.retour_jeton IS NULL
                   ORDER BY duree DESC"); 
    
$table = new sqltable("listeemprunts",
                      "Liste des jetons empruntés",
                      $sql, 
                      "retour.php", 
                      "id_jeton", 
                      array("nom_jeton" => "Jeton",
                            "nom_utilisateur"=>"Utilisateur",
                            "prise_jeton" => "Date d'emprunt",
                            "duree" => "Depuis (jours)",
                            "penalite" => "Pénalité"), 
                      array("retourner" => "Retourner"),
											array("retourner" => "Retourner"), 
                      array("penalite" => array('0' => "Non", '1' => "Oui") ) );
$cts->add($table,true);

$site->add_contents($cts);
$site->end_page();
?>
