<?php

/* Copyright 2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "./";

require_once($topdir. "include/site.inc.php");

$site = new site();

echo file_get_contents($topdir."var/cache/stream.php");

if ( file_exists($topdir."var/cache/stream.php") )
  include($topdir."var/cache/stream.php");
else
  $GLOBALS["streaminfo"] = array();

if ( !$GLOBALS["is_using_ssl"] )
{
  echo "sorry, please use ssl";
  exit();
}

$valid = new requete($site->db,
  "SELECT `key` ".
  "FROM `sso_api_keys` ".
  "WHERE `key` = '".mysql_real_escape_string($_REQUEST["key"])."'");

if ( $valid->lines != 1 )
{
  echo "sorry, wrong key";
  exit();
}

$allowed=array("ogg","mp3","title","artist","message");
$updated = array();

foreach ( $allowed as $key )
{
  if ( isset($_REQUEST[$key]) )
  {
    $GLOBALS["streaminfo"][$key] = $_REQUEST[$key];
    $updated[] = $key;
  }
}

echo "thank you. updated: ".implode(", ",$updated);

$GLOBALS["streaminfo"]["updated"] = time();

$stuff = '<? $GLOBALS["streaminfo"] = array(';
foreach ( $GLOBALS["streaminfo"] as $key => $data )
{
  $stuff .= '\''.addcslashes($key,'\'\\').'\'=>\''.addcslashes($data,'\'\\').'\'';
  $stuff .= "\n"; 
}
$stuff .= ');';
$stuff .= "\n?>";

file_put_contents($topdir."var/cache/stream.php",$stuff);

?>