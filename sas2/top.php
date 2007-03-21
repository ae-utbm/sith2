<?php
/* Copyright 2004-2006
 * - Julien Etelain < julien at pmad dot net >
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
require_once("include/sas.inc.php");
require_once($topdir."include/cts/gallery.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir. "include/page.inc.php");
require_once($topdir. "include/assoclub.inc.php");
$site = new sas();


$site->start_page("sas","SAS: Le classement");


$cts = new contents("SAS : Le classement");


$req = new requete ($site->db, "SELECT COUNT(sas_photos.id_photo) as `count`, `utilisateurs`.`id_utilisateur`, " .
    "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
    "FROM sas_photos " .
    "INNER JOIN utilisateurs ON sas_photos.id_utilisateur=utilisateurs.id_utilisateur " .
    "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
    "GROUP BY sas_photos.id_utilisateur " .
    "ORDER BY count DESC LIMIT 30");


$lst = new itemlist("Les meilleurs contributeurs (30)");
$n=1;
while ( $row = $req->get_row() )
{
  $lst->add("$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] )." (".$row['count']." photos)");
  $n++;
}

$cts->add($lst,true);

$req = new requete ($site->db, "SELECT COUNT(sas_personnes_photos.id_photo) as `count`, `utilisateurs`.`id_utilisateur`, " .
    "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
    "FROM sas_personnes_photos " .
    "INNER JOIN utilisateurs ON sas_personnes_photos.id_utilisateur=utilisateurs.id_utilisateur " .
    "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
    "GROUP BY sas_personnes_photos.id_utilisateur " .
    "ORDER BY count DESC");

$lst = new itemlist("Les plus photographi&eacute;s (tous)");
$n=1;
while ( $row = $req->get_row() )
{
  $lst->add("$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] )." (".$row['count']." photos)");
  $n++;
}

$cts->add($lst,true);

$site->add_contents($cts);
$site->end_page();

?>