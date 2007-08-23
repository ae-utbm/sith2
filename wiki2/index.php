<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 * - Julien Etelain < julien at pmad dot net >
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/wiki.inc.php");

$site = new site();

/* temporairement, si t'es pas logué tu lis pas */
if (!$site->user->id)
  error_403();

$wiki = new wiki($site->db,$site->dbrw);

if ( isset($_REQUEST["name"]) )
  $wiki->load_by_fullpath($_REQUEST["name"]);
else
  $wiki->load_by_id(1);

if ( !$wiki->is_valid() )
{
  $pagepath = $_REQUEST["name"];

  
  $site->start_page ("none", "Page inexistante");
  
  $tabs = array(array("","wiki2/?name=".$pagepath, "Page"),
                array("create","wiki2/?name=".$pagepath."&view=create", "Creer")
               );
               
  $cts = new contents();
  $cts->add(new tabshead($tabs,$_REQUEST["view"]));
  
  
  $cts->add_paragraph("Cette page n'existe pas. <a href=\"wiki2/?name=".$pagepath."&view=create\">La creer</a>","error");
  
  $site->add_contents($cts);
  
  $site->end_page ();
  
  exit();  
}

$pagepath = $wiki->fullpath;


$site->start_page ("none", $wiki->title);

$tabs = array(array("","wiki2/?name=".$pagepath, "Page"),
              array("edit","wiki2/?name=".$pagepath."&view=edit", "Editer"),
              array("refs","wiki2/?name=".$pagepath."&view=refs", "Références"),
              array("hist","wiki2/?name=".$pagepath."&view=hist", "Historique")
             );
             
$cts = new contents();
$cts->add(new tabshead($tabs,$_REQUEST["view"]));

$cts->add_title(1,$wiki->title);

$cts->add($wiki->get_stdcontents());

$site->add_contents($cts);

$site->end_page ();

?>