<?php
/* Copyright 2006
 * - Julien Etelian <julien CHEZ pmad POINT net>
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

$topdir = "./";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/newsflow.inc.php");

$site = new site ();

$day = strtotime($_REQUEST["day"]);

$site->start_page("accueil", "le ". date("d/m/Y", $day));

$event = new requete ($site->db,
                      "SELECT `nvl_dates`.`id_nouvelle` FROM `nvl_dates` " .
                      "INNER JOIN `nvl_nouvelles` on `nvl_nouvelles`.`id_nouvelle`=`nvl_dates`.`id_nouvelle`" .
                      "WHERE " .
                      "((`nvl_dates`.`date_debut_eve` <= '" . date("Y-m-d",$day+24*60*60) ." 05:59:59') AND " .
                      "(`nvl_dates`.`date_fin_eve` >= '" . date("Y-m-d",$day) ." 06:00:00') AND
			(`nvl_nouvelles`.`modere_nvl` > 0))");

while (list($id) = $event->get_row())
  $ids[] = $id;

if (count($ids))
  $site->add_contents(new newsflow ("le ".date("d/m/Y", $day),
                                    $site->db, "id_nouvelle IN (".implode(",",$ids).")",
                                    "events.php?day=" . date("Y-m-d", $day)));

$site->end_page();

?>
