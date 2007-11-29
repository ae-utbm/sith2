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
 
/**
 * @file
 */

class gmap extends stdcontents
{
	var $name;

  var $key = "ABQIAAAAJorv5o0HbErTOq4bRd4i4xQK4V3DXlfwDoXGfM1MfzyVC2ZQMBQlI3Dxb04trtSj-G5duv7flKy7Ag";

	function gmap ( $name )
	{
		$this->name = $name;
		
	}

  function add_marker ( $lat, $long )
  {
    
    
  }

  function html_render()
  {
    $this->buffer="
    <script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=".$this->key."\" type=\"text/javascript\"></script>
    <script type=\"text/javascript\">

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById(\"".$this->name."_canvas\"));
        var center = new GLatLng(37.4419, -122.1419);
        map.setCenter(center, 13);

        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(marker, \"dragstart\", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, \"dragend\", function() {
          marker.openInfoWindowHtml(\"Just bouncing along...\");
        });

        map.addOverlay(marker);

      }
    }
    
    document.onload=initialize;
    document.onunload=GUnload;
    
    </script>";  
    
    $this->buffer .= "<div id=\"".$this->name."_canvas\" style=\"width: 500px; height: 300px\"></div>";
    
    return $this->buffer;
  }


}



?>