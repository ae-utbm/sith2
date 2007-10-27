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

#define("SVN_PATH","/var/lib/svn/");
define("SVN_PATH","/var/lib/");
define("PRIVATE_SVN","svn/");
define("PUBLIC_SVN","svn-public/");


$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
$site = new site ();

if ( !$site->user->is_in_group("root") )
  error_403();

function private_svn ()
{
  $list = array();

  if ($dh = opendir(SVN_PATH.PRIVATE_SVN))
  {
    while (($file = readdir($dh)) !== false)
    {
      if ( $file != "." && $file != ".." && is_dir(SVN_PATH.PRIVATE_SVN.$file) )
      {
        $list[] = array("name"=>$file );
      }
    }
    closedir($dh);
  }

  return $list;
}


$sha=hash("sha1","test");
print_r($sha);

$private = private_svn();
asort($private);

$site->start_page("none","Administration");
$cts = new contents("<a href=\"./\">Administration</a> / AECMS");
$cts->add_paragraph($private);
$cts->add(new sqltable("svn_private",
                       "Liste des SVN privés",
                       $private,
                       "svn.php",
                       "type",
                       array("name"=>"Nom"),
                       array(),
                       array(),
                       array()
                      ),true);

$site->add_contents($cts);
$site->end_page();

?>
