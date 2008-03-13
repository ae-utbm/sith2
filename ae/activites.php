<?php

/* Copyright 2008
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
require_once($topdir. "include/cts/sqltable.inc.php");

$site = new site ();
$site->set_side_boxes("right",array(),"nope");
$site->set_side_boxes("left",array(),"nope");

$site->start_page("home","Activités");

$req_poles = new requete($site->db, "SELECT * ".
  "FROM asso ".                                                                                                                    
  "WHERE asso.id_asso_parent = 1 ".
  "ORDER BY nom_asso");

while($pole = $req_poles->get_row())
{
  $req_assos = new requete($site->db, "SELECT asso.id_asso, asso.nom_asso,
      utilisateurs_resp.id_utilisateur as id_utilisateur_resp,
      CONCAT(utilisateurs_resp.nom_utl,' ',utilisateurs_resp.prenom_utl) as nom_utilistateur_resp,
      utilisateurs_tres.id_utilisateur AS id_utilisateur_tres,
      CONCAT(utilisateurs_tres.nom_utl,' ',utilisateurs_tres.prenom_utl) AS nom_utilisateur_tres
    FROM asso
    LEFT JOIN asso_membre AS tbl_resp ON (tbl_resp.id_asso=asso.id_asso AND tbl_resp.role='10' AND tbl_resp.date_fin IS NULL)
    LEFT JOIN asso_membre AS tbl_tres ON (tbl_tres.id_asso=asso.id_asso AND tbl_tres.role='7' AND tbl_tres.date_fin IS NULL)
    LEFT JOIN utilisateurs AS utilisateurs_resp ON tbl_resp.id_utilisateur=utilisateurs_resp.id_utilisateur
    LEFT JOIN utilisateurs AS utilisateurs_tres ON tbl_tres.id_utilisateur=utilisateurs_tres.id_utilisateur
    WHERE asso.id_asso_parent='".$pole['id_asso']."' GROUP BY asso.id_asso");

  $table = new sqltable("", $pole['nom_asso'], $req_assos, "", "",
                        array("asso.nom_asso" => "Activité",
                              "nom_utilisateur_resp" => "Responsable",
                              "nom_utilisateur_tres" => "Trésorier"
                              ),
                        array(), array(), array() );

  $site->add_contents($table);
}

$site->end_page();

?>
