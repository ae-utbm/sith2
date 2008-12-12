<?php

/* Copyright 2008
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

/**
 * @file
 * Administration de AECMS
 * @ingroup wiki2
 * @author Simon Lopez
 */

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/asso.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

define("AE_ACCOUNTS","/var/www/ae/accounts/");


function list_wikis ()
{
  $list = array();

  if ($dh = opendir(AE_ACCOUNTS))
  {
    while (($file = readdir($dh)) !== false)
      if ( is_dir(AE_ACCOUNTS.$file) && is_dir(AE_ACCOUNTS.$file."/wiki/data/attic/") )
        $list[] = array("unixname"=>$file );
    closedir($dh);
  }
  return $list;
}


$site->start_page("none","Administration");

$baselist = list_wikis();
$list=array();
$asso = new asso($site->db);

foreach($baselist as $row)
{
  $asso->load_by_unix_name($row["unixname"]);
  $row["nom_asso"]=$asso->nom;
  $list[]=$row;
}

$cts = new contents("<a href=\"./\">Administration</a> / IMPORTS WIKI");
$cts->add(new sqltable(
  "wikis",
  "Liste des wikis installés", $list, "import_wiki.php",
  "type",
  array("unixname"=>"Nom","nom_asso"=>"Association/Activité"),
  array(),
  array(),
  array()
  ),true);

$site->add_contents($cts);
$site->end_page();

?>
