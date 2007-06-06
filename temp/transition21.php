<?php

/* Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
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
require_once($topdir. "include/entities/carteae.inc.php");
require_once($topdir. "include/entities/cotisation.inc.php");
require_once($topdir. "include/cts/special.inc.php");

$site = new site ();

header("Content-Type: text/html; charset=utf-8");

$villes=array();

$sql = new requete($site->db,"SELECT nom_ville, id_ville FROM loc_ville");

while ( list($nom,$id) = $sql->get_row() )
  $villes[strtoupper($nom)]=$id;

$pays=array();

$sql = new requete($site->db,"SELECT nom_pays, id_pays FROM loc_pays");

while ( list($nom,$id) = $sql->get_row() )
  $pays[strtoupper($nom)]=$id;
  
$sql = new requete($site->db,"SELECT * FROM utilisateurs 
LEFT JOIN utl_etu USING(id_utilisateur) 
LEFT JOIN utl_etu_utbm USING(id_utilisateur)");

while ( $row = $sql->get_row() )
{
  
  new update($site->dbrw,
              "utilisateurs",
              array(
              'tel_maison_utl' => telephone_userinput($row['tel_maison_utl']),
              'tel_portable_utl' => telephone_userinput($row['tel_portable_utl']),
              'id_ville'=>$villes[strtoupper($row['ville_utl'])],
              'id_pays'=>$pays[strtoupper($row['pays_utl'])]
              ),
              array( 'id_utilisateur' => $row['id_utilisateur']));
  
  if ( $row['etudiant_utl'] || $row['ancien_etudiant_utl'] )
  {
    new update($site->dbrw,
               "utl_etu",
               array(
               'tel_parents' => telephone_userinput($row['tel_parents']),
               'id_ville'=>$villes[strtoupper($row['ville_parents'])],
               'id_pays'=>$pays[strtoupper($row['pays_parents'])]
               ),
               array( 'id_utilisateur' => $row['id_utilisateur']));
  }
  
  if ( $row['utbm_utl'] )
  {
    $role="etu";    $departement="na";

    switch ( $row['branche_utbm'] )
    {
      case "GI": $departement="gi"; break;
      case "GSP": $departement="imap"; break;
      case "GMC": $departement="gmc"; break;
      case "GSC": $departement="gesc"; break;
      case "TC": $departement="tc"; break;
      case "Admini": $role="adm"; break;
      case "Enseig": $role="ens"; break;
    }
    
    new update($site->dbrw,
               "utl_etu_utbm",
               array('role_utbm' => $role,'departement_utbm' => $departement),
               array( 'id_utilisateur' => $row['id_utilisateur']));
  }

}

?>