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

if ( !$GLOBALS["streaminfo"]["mp3"] )
{
  echo "<p>Indisponible actuellement</p>";
  exit();  
}

$plug = "wmp";

if (isset($_REQUEST["plug"]))
  $plug = $_REQUEST["plug"];


if ( $plug == "wmp" )
{

echo "<object id=\"wmp\" height=\"34\" width=\"262\" classid =\" CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95\" codebase =\" http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701\" standby=\"ça chauffe...\" type=\"application/x-oleobject\">
							<PARAM NAME=\"Filename\" value=\"".$GLOBALS["streaminfo"]["mp3"]."\">
							<PARAM NAME=\"ShowControls\" VALUE=\"1\">
							<PARAM NAME=\"ShowStatusBar\" VALUE=\"1\">
							<PARAM NAME=\"ShowDisplay\" VALUE=\"TRUE\">
							<PARAM NAME=\"DefaultFrame\" VALUE=\"Slide\">
							<PARAM NAME=\"Autostart\" VALUE=\"1\">
							<PARAM NAME=\"Volume\" value=\"100\">
							<PARAM NAME=\"loop\" value=\"1\">
							<embed 
							width=\"262\" height=\"34\" 
							Name=\"wmp\"
							Autostart=\"1\"
							ShowControls=\"1\"
							ShowDisplay=\"1\"
							ShowStatusBar=\"1\"
							DefaultFrame=\"Slide\"
							type=\"application/x-mplayer2\" 
							pluginspage=\"http://www.microsoft.com/Windows/MediaPlayer/download/default.asp\" 
							src=\"".$GLOBALS["streaminfo"]["mp3"]."\" 
							id=\"wmp\">
							</embed>
							</object>";
}
else
{
  echo "<OBJECT CLASSID=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" CODEBASE=\"http://www.apple.com/qtactivex/qtplugin.cab\" WIDTH=\"140\" HEIGHT=\"22\"><PARAM NAME=\"src\" VALUE=\"".$GLOBALS["streaminfo"]["mp3"]."\" ><PARAM NAME=\"autoplay\" VALUE=\"true\" ><embed autoplay=\"true\" width=\"170\" height=\"22\" name=\"quicktime\" pluginspage=\"http://www.apple.com/quicktime/download/\" type=\"image/x-quicktime\" qtsrc=\"".$GLOBALS["streaminfo"]["mp3"]."\"></embed></OBJECT>";  
  
}


?>