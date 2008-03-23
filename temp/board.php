<?php

/* Copyright 2006
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
 * along with site program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */
 
$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/board.inc.php");


$site = new site ();

if (!$site->user->id || !$site->user->utbm)
  $site->error_forbidden();

$site->set_side_boxes("left",array());
$site->set_side_boxes("right",array());


$site->start_page("none","Page personelle");

$cts = new contents("Page personelle");

$board = new board();

$subboard = new board();
$subboard->add(new calendar($site->db),true);
$subboard->add($site->get_forum_box(),true);
$board->add($subboard);


$subboard = new board();
$weekly = $site->get_weekly_photo_contents();
if($weekly!=null)
  $subboard->add($site->get_weekly_photo_contents(),true);
else
  $subboard->add(new contents("Pas de photo"),true);
$subboard->add($site->get_anniv_contents(),true);
$board->add($subboard);

$cts->add($board);

$site->add_contents($cts);
    
$site->end_page();  

?>
