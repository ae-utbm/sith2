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

if ( file_exists($topdir."var/cache/stream") )
  $GLOBALS["streaminfo"] = unserialize(file_get_contents($topdir."var/cache/stream"));

if ( $REQUEST["get"] == "qt.pls" )
{
  echo "[playlist]\n";
  echo "File1=".$GLOBALS["streaminfo"]["mp3"]."\n";
  echo "Title1=\n";
  echo "Length1=-1\n";
  echo "NumberOfEntries=1\n";
  echo "Version=2\n";
  exit();
}


if ( !$GLOBALS["streaminfo"]["mp3"] )
{
  echo "<p>Indisponible actuellement</p>";
  exit();  
}

$plug = "quicktime";

if (isset($_REQUEST["plug"]))
  $plug = $_REQUEST["plug"];


if ( $plug == "wmp" )
{
  echo "<object classid=\"CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95\" 
  codebase=\"http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701\" 
  type=\"application/x-oleobject\">
  							<param name=\"filename\" value=\"".$GLOBALS["streaminfo"]["mp3"]."\">
  							<param name=\"autostart\" value=\"1\">
  							<embed src=\"".$GLOBALS["streaminfo"]["mp3"]."\"
  							autostart=\"1\" type=\"application/x-mplayer2\"
  							pluginspage=\"http://www.microsoft.com/Windows/MediaPlayer/download/default.asp\">
  							</embed></object>";
}
else
{
  echo "<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\"
   codebase=\"http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0\">
  <param name=\"src\" value=\"?get=qt.pls\" />
  <param name=\"autoplay\" value=\"true\" />
  <embed src=\"?get=qt.pls\" type=\"image/x-quicktime\"
    pluginspage=\"http://www.apple.com/quicktime/download/\"
    autoplay=\"true\" qtsrc=\"?get=qt.pls\"></embed></object>";  
  
}


?>