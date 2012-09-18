<?php
/* Copyright 2012
 * - Antoine Ténart < antoine dot tenart at utbm dot fr >
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
require_once($topdir. "include/entities/utilisateur.inc.php");

$site = new site ();

if (!$site->user->is_in_group ("root"))
  $site->error_forbidden ("none", "group", 7);

$site->start_page ("none", "Fix accounts");

$cts = new contents ("<a href=\"./\">Administration</a> / Fix accounts");

$req = new requete ($site->db, "SELECT `id_utilisateur`
                     FROM `utilisateurs`
                     WHERE `utbm_utl` = '1'
                     AND `id_utilisateur` NOT IN
                     (SELECT `id_utilisateur` FROM `utl_etu_utbm`)");

$l = $req->lines;
if ($req->lines > 0) {
  while ($row = $req->get_row ()) {
    new insert ($site->dbrw,
        "utl_etu_utbm",
        array ("id_utilisateur" => $row['id_utilisateur']));
  }
}

$cts->add_paragraph ($l . " comptes fixés.");

$site->add_contents ($cts);
$site->end_page ();

?>
