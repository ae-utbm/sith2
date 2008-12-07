<?php

/** @file
 *
 *
 */
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

/** Conteneur standart
 * @ingroup display_cts
 */
class box_slideshow extends stdcontents
{

  function box_slideshow($delay=5000,$pause=true)
  {
    $this->title = null;
    $this->divid = null;
    $this->cssclass = null;
    $this->slides=array();
    $this->delay=$delay;
    $this->pause=$pause;
    $this->buffer="";
  }

  function add_slide($cts)
  {
    $this->slides[]=$cts;
  }

  function html_render ()
  {
    if(empty($this->slides))
      return "";
    $uid=gen_uid();
    $this->buffer.="<script type=\"text/javascript\">\n";
    $this->buffer.="slideshowboxes['slideshow$uid']=0;\n";
    if($this->pause)
    {
      $this->buffer.="start_slideshow('slideshow$uid', 0, ".(count($this->slides)-1).", ".$this->delay.",1);\n";
      $this->buffer.="<div class='slidebox_pause' id='slideshowonoff$uid'><a href='#' onclick=\"slideshow_onoff('slideshow$uid','slideshowonoff$uid'); return false;\">pause<a/></div>";
    }
    else
      $this->buffer.="start_slideshow('slideshow$uid', 0, ".(count($this->slides)-1).", ".$this->delay.",0);\n";
    $this->buffer.="</script>\n";

    for($i=0;$i<count($this->slides);$i++)
    {
      if($i==0)
        $this->buffer.="<div id='slideshow$uid$i' style=\"display:block\">".$this->slides[$i]->html_render()."</div>\n";
      else
        $this->buffer.="<div id='slideshow$uid$i' style=\"display:none\">".$this->slides[$i]->html_render()."</div>\n";
    }
    return $this->buffer;
  }

}
