<?php
/* Copyright 2007
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
$topdir = "./";
include($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/tag.inc.php");
require_once($topdir. "include/cts/tagcloud.inc.php");

$site = new site ();

$tag = new tag ($site->db,$site->dbrw);

if ( isset($_REQUEST["id_tag"]) )
  $tag->load_by_id($_REQUEST["id_tag"]);
  
if ( $tag->is_valid() )
{
  $site->start_page("presentation",$tag->nom);
  $cts = new contents($tag->nom);
  
  
  
  
  $site->add_contents($cts);
  $site->end_page ();
  exit();  
}

$site->start_page("presentation","Tags");

$cts = new contents("Tags");




$site->add_contents($cts);
$site->end_page ();

?>