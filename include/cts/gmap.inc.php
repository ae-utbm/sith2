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

  var $markers = array();


	function gmap ( $name )
	{
		$this->name = $name;
		
	}

  function add_marker ( $name, $lat, $long, $draggable=false, $dragend=null )
  {
    $this->markers[] = array("name"=>$name,"lat"=>$lat, "long"=>$long, "draggable"=>$draggable, "dragend"=>$dragend );
    
  }

  function html_render()
  {
    $this->buffer .= "<div id=\"".$this->name."_canvas\" style=\"width: 500px; height: 300px\"></div>";
    
    
    $this->buffer .= "
    <script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=".$this->key."\" type=\"text/javascript\"></script>
    <script type=\"text/javascript\">

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById(\"".$this->name."_canvas\"));
      ";
        
        
    $first = true;
    
    foreach ( $this->markers as $marker )
    {
      $this->buffer .= "var ".$marker["name"]."_point = new GLatLng(".sprintf("%.12F",$marker['lat']*360/2/M_PI).", ".sprintf("%.12F",$marker['long']*360/2/M_PI).");\n";
      
      
      if ( $first )
      {
        $this->buffer .= "map.setCenter(".$marker["name"]."_point, 13);\n";
        $first = false;
      }
      
      if ( $marker["draggable"] )
      {
        $this->buffer .= "var ".$marker["name"]." = new GMarker(".$marker["name"]."_point, {draggable: true});\n";
        if ( !is_null($marker["dragend"]) )
          $this->buffer .= "GEvent.addListener(marker, \"dragend\", ".$marker["dragend"]." );\n";
      }
      else
        $this->buffer .= "var ".$marker["name"]."= new GMarker(".$marker["name"]."_point);\n";
      
      $this->buffer .= "map.addOverlay(".$marker["name"].");\n";
      
    }


    $this->buffer .= "map.addControl(new GSmallMapControl());\n";
    $this->buffer .= "map.addControl(new GMapTypeControl());\n";

    $this->buffer .= "   }
    }
    
    initialize();
    document.onunload=GUnload;
    
    </script>";  
    
    
    return $this->buffer;
  }


}



?>