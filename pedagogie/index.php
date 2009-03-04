<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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
require_once("include/pedagogie.inc.php");
require_once("include/cts/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");

$site = new site();
//$site->allow_only_logged_users();

$site->start_page("services", "AE - Pédagogie");

$user = new pedag_user($site->db, $site->dbrw, $site->user->id);

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";
$path .= "/" . " Accueil";
$cts = new contents($path);
$cts->add_paragraph("Bienvenue");
$site->add_contents($cts);

$cts = new contents("Résumé de votre parcours");
  $lst = new itemlist(false);
  $lst->add("Vous venez de ");
  $lst->add("Vous avez obtenu ".$user->get_nb_uv_result(RESULT_EQUIV)." UV en équivalence");
  $lst->add("bla bla");
$cts->add($lst);
$site->add_contents($cts);

$site->add_contents(new semestre_box());

$site->end_page();
?>
