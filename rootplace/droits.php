<?php

/* Copyright 2007
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
require_once($topdir."include/entities/svn.inc.php");


$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","Administration / Gestion des droits");
$cts = new contents("<a href=\"./\">Administration</a> / Révocation des droits");

$sql = 'SELECT '.
       '      CONCAT(`u`.`prenom_utl`,\' \',`u`.`nom_utl`) AS nom_utilisateur '.
       '      ,COUNT(*) AS nb'.
       'FROM '.
       '      utl_groupe g '.
       'INNER JOIN utilisateurs u '.
       '      USING ( id_utilisateur ) '.
       'LEFT JOIN ae_cotisations c '.
       '      ON c. id_utilisateur=g.id_utilisateur '.
       '      AND c.date_fin_cotis >= NOW() '.
       'WHERE '.
       '      id_cotisation IS NULL '.
       '      AND id_groupe NOT IN ( 7, 20, 25, 39, 42, 45 ) ';
if(isset($_REQUEST['id_groupe']))
  $sql.='      AND id_groupe='.intval($_REQUEST['id_groupe']).' ';
$sql.= 'GROUP BY '.
       '      `u`.`id_utilisateur`'.
       '      ,`u`.`prenom_utl`'.
       '      ,`u`.`nom_utl`';
$req = new requete($site->db,$sql);
$cts->add(new sqltable('bad_rights',
                       'BOUH ! montrons les du doigts !',
                       $req,
                       '',
                       'id_utilisateur',
                       array('nom_utilisateur'=>'Utilisateur',
                             'nb'=>'Occurences'),
                       array(),
                       array(),
                       array()
                      )
         );

$site->add_contents($cts);
$site->end_page();

?>
