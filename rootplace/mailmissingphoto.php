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
require_once($topdir. "include/entities/files.inc.php");
require_once($topdir. "include/entities/folder.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
	error_403();

$site->start_page("none","Administration");

$cts = new contents("<a href=\"./\">Administration</a> / Envoie de mail / Photos manquantes");

$lst = new itemlist();

$req = new requete($site->db, 
		"SELECT `utilisateurs`.`id_utilisateur`, email_utl  ".
		"FROM `utilisateurs` " .
		"INNER JOIN `utl_etu_utbm` ON `utilisateurs`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur` ".
		"WHERE utbm_utl=1 AND ancien_etudiant!=1 AND role_utbm='etu'");	
$count=0;
while ( $row = $req->get_row() )
{
	if ( !file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
	{
    $lst->add($row['email_utl']);
    $count++;
	}
}

$lst->add("$count photos manquantes sur un total de ".$req->lines);

$cts->add($lst);
$site->add_contents($cts);

$site->end_page();
?>