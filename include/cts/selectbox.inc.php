<?php
/* Copyright 2005,2006
 * - Manuel Vonthron < manuel dot vonthron at acadis dot org >
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
 * Fourni une interface de sélection d'elements a base de
 * deux <select> multiples
 * Inspiree de `selector` de django-admin
 * @param name
 * @param title
 * @param values array( array('value', 'title') )
 * @param page page de reception du form
 * @param select_title (sera affiche sous la forme '<truc> disponibles')
 * @see /js/site.js var select_box
 */

class selectbox extends stdcontents
{
  public function __construct($name, $title, $values, $page, $select_title=null)
  {
    $this->title = $title;
    $sel_from = $name.'_from';
    $sel_to = $name.'_to';
    $this->buffer = "";
    
    $this->buffer .= "<form name =\"$name\" action=\"$page\" method=\"post\">\n";
    $this->buffer .= "<script type=\"text/javascript\">var sel_from = this.form.$sel_from; var sel_to = this.form.$sel_to;</script>\n";
    
    /* div from */
    if($select_title)
      $this->buffer .= "<h4>".$select_title." disponible(s)</h4>\n";
    $this->buffer .= "<select name=\"$sel_from\" multiple=\"multiple\">\n";
    foreach($values as $val)
      $this->buffer .= "  <option value=\"".$val['value']."\" "
                        ."ondblclick=\"select_box.move(sel_from, sel_to);\">"
                        .$val['title']."</option>\n";
    $this->buffer .= "</select>\n";
    
    /* actions */
    /* @todo trouver qqch de moins moche */
    $this->buffer .=	" <input type=\"button\" value=\"--&gt;\" onclick=\"select_box.move(sel_from, sel_from);\" /> \n";
		$this->buffer .=	" <input type=\"button\" value=\"&lt;--\" onclick=\"select_box.move(sel_to, sel_from);\" /> \n";
			 
    /* div to */
    if($select_title)
      $this->buffer .= "<h4>".$select_title." choisi(es)</h4>\n";
    $this->buffer .= "<select name=\"$sel_to\" multiple=\"multiple\">\n";
    $this->buffer .= "</select>\n";
    
    $this->buffer .= "</form>\n";
  }
}

?>
