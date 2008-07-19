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
 
/**
 * Permet d'afficher un carte (de goolge maps).
 *
 * @author Julien Etelain
 * @ingroup display_cts
 */
class gmap extends stdcontents
{
  var $name;

  /* google map api key */
  var $key = "__GMAP_KEY__";

  var $markers = array();
  var $paths=array();

  function gmap ( $name )
  {
    $this->name = $name;
    
  }

  function add_marker ( $name, $lat, $long, $draggable=false, $dragend=null )
  {
    $this->markers[] = array("name"=>$name,"lat"=>$lat, "long"=>$long, "draggable"=>$draggable, "dragend"=>$dragend );
  }
  
  function add_geopoint ( &$g )
  {
    $this->add_marker($g->nom,$g->lat,$g->long );
  }  
  
  function add_path ( $name, $latlongs, $color="ff0000" )
  {
    $this->paths[] = array("name"=>$name,"latlongs"=>$latlongs, "color"=>$color );
  }
  
  function add_geopoint_path ( $name, $geopoints, $color="ff0000" )
  {
    $latlongs=array();
    foreach ($geopoints as $g)
    {
      $latlongs[] = array("lat"=>$g->lat, "long"=>$g->long);
    }
    $this->add_path($name,$latlongs, $color);
  }  

  function html_render()
  {
    $this->buffer .= "<div id=\"".$this->name."_canvas\" style=\"width: 500px; height: 300px\"></div>";
    
    
    $this->buffer .= "
    <script src=\"http://www.google.com/jsapi?key=".$this->key."\" type=\"text/javascript\"></script>
    <script type=\"text/javascript\">\n";
    
    //
    $this->buffer .="google.load(\"maps\", \"2\");\n";
    $this->buffer .="var ".$this->name.";\n";
    
    foreach ( $this->markers as $marker )
      $this->buffer .= "var ".$marker["name"].";\n";

    foreach ( $this->paths as $path )
      $this->buffer .= "var ".$path["name"].";\n";

    $this->buffer .="function initialize() {\n";
    $this->buffer .= $this->name." = new google.maps.Map2(document.getElementById(\"".$this->name."_canvas\"));\n";
        
        
    $first = true;
    
    foreach ( $this->markers as $marker )
    {
      $this->buffer .= "var ".$marker["name"]."_point = new google.maps.LatLng(".sprintf("%.12F",$marker['lat']*360/2/M_PI).", ".sprintf("%.12F",$marker['long']*360/2/M_PI).");\n";
      
      
      if ( $first )
      {
        $this->buffer .= $this->name.".setCenter(".$marker["name"]."_point, 15);\n";
        $first = false;
      }
      
      if ( $marker["draggable"] )
      {
        $this->buffer .= "var ".$marker["name"]." = new google.maps.Marker(".$marker["name"]."_point, {draggable: true});\n";
        if ( !is_null($marker["dragend"]) )
          $this->buffer .= "google.maps.Event.addListener(marker, \"dragend\", ".$marker["dragend"]." );\n";
      }
      else
        $this->buffer .= $marker["name"]."= new google.maps.Marker(".$marker["name"]."_point);\n";
      
      $this->buffer .= $this->name.".addOverlay(".$marker["name"].");\n";
      
    }

    foreach ( $this->paths as $path )
    {
      $points=array();
      foreach( $path["latlongs"] as $point )
      {
        //$points[] = "@".sprintf("%.12F",$point['lat']*360/2/M_PI).", ".sprintf("%.12F",$point['long']*360/2/M_PI);
        $points[] = "new google.maps.LatLng( ".sprintf("%.12F",$point['lat']*360/2/M_PI).", ".sprintf("%.12F",$point['long']*360/2/M_PI).")";
        $this->buffer.=$this->name.".addOverlay(new google.Marker(new google.maps.LatLng( ".sprintf("%.12F",$point['lat']*360/2/M_PI).",".sprintf("%.12F",$point['long']*360/2/M_PI).")));\n"; 
        if ( $first )
        {
          $this->buffer .= $this->name.".setCenter(new google.maps.LatLng(".sprintf("%.12F",$point['lat']*360/2/M_PI).", ".sprintf("%.12F",$point['long']*360/2/M_PI)."), 15);\n";
          $first = false;
        }        
      }

      $this->buffer .= "var ".$path["name"]."points = new Array(".implode(", ",$points).");\n"; 
      $this->buffer .= $path["name"]."= new google.maps.Directions(map);\n";
      $this->buffer .= "google.maps.Event.addListener(".$path["name"].",\"error\", function() { alert(\"Directions Failed: \"+".$path["name"].".getStatus().code); });\n";
      $this->buffer .= $path["name"].".load(".$path["name"]."points, {getSteps:true});\n";
//      $this->buffer .= $this->name.".addOverlay(".$path["name"].");\n";
    }

    $this->buffer .= $this->name.".addControl(new google.maps.SmallMapControl());\n";
    $this->buffer .= $this->name.".addControl(new google.maps.MapTypeControl());\n";

    $this->buffer .= "
    }
    
    google.setOnLoadCallback(initialize);
    document.onunload=GUnload;
    
    </script>";  
    
    
    return $this->buffer;
  }


}



?>
