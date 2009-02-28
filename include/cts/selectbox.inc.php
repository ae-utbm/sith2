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
 * @param values array( key => value )
 * @param page page de reception du form
 * @param select_title (sera affiche sous la forme '<truc> disponibles')
 * @see /js/site.js var select_box
 */

/**
 * @todo pour demain
 * - etendre form plutot que stdcontents
 *  -> le submit en add_submit
 *  -> tout le bordel dans un truc separe
 *  -> et puis voila on pourra rajouter des trucs en plus (au hasard le semestre)
 */
class selectbox extends form
{
  public function __construct($name, $title, $values, $action, $select_title=null)
  {
    $this->form($name, $action, false, "post", $title);
    
    $this->values = $values;
    $this->sel_from = $name.'_from';
    $this->sel_to = $name.'_to';
    $this->select_title = $select_title;
    
    $this->add_selectbox();
  }
  
  private function add_selectbox(){
    $this->buffer .= "<div class=\"selectbox\">\n";
    
    /* div from */
    $this->buffer .= "<div class=\"selectbox_disp\">\n";
    if($this->select_title)
      $this->buffer .= "<h4>".$this->select_title." disponible(s) :</h4>\n";
    $this->buffer .= "<select name=\"$this->sel_from\" id=\"$this->sel_from\" multiple=\"multiple\">\n";
    foreach($this->values as $key => $value)
      $this->buffer .= "  <option value=\"".$key."\" "
                        ."ondblclick=\"select_box.move(select_box.sel_from, select_box.sel_to);\">"
                        .$value."</option>\n";
    $this->buffer .= "</select>\n";
    $this->buffer .= "</div>\n";
    
    /* actions */
    $this->buffer .= "<ul class=\"selectbox_actions\">";
    $this->buffer .= "  <li class=\"ajouter\" onclick=\"javascript:select_box.move(select_box.sel_from, select_box.sel_to);\">&nbsp;</li>";
    $this->buffer .= "  <li class=\"enlever\" onclick=\"javascript:select_box.move(select_box.sel_to, select_box.sel_from);\">&nbsp;</li>";
    $this->buffer .= "</ul>";
    	 
    /* div to */
    $this->buffer .= "<div class=\"selectbox_choix\">\n";
    if($this->select_title)
      $this->buffer .= "<h4>".$this->select_title." choisi(es) :</h4>\n";
    $this->buffer .= "<select name=\"".$this->sel_to."[]\" id=\"$this->sel_to\" multiple=\"multiple\">\n";
    $this->buffer .= "</select>\n";
    $this->buffer .= "</div>\n";
    
    $this->buffer .= "</div>\n";
    $this->buffer .= "<script type=\"text/javascript\">\nwindow.onload = function(e) {\n  select_box.sel_from = document.getElementById('".$this->sel_from."');\n  select_box.sel_to = document.getElementById('".$this->sel_to."');\n};\n</script>\n";
  }
  
  public function html_render(){
    $html = "";

    if ( $this->error_contents )
     $html .= "<p class=\"formerror\">Erreur : ".$this->error_contents."</p>\n";
     
    $html .= "<form action=\"$this->action\" method=\"".strtolower($this->method)."\"".
              " name=\"".$this->name."\" id=\"".$this->name."\"".
              " onsubmit=\"select_box.select_all(select_box.sel_to)\">\n";

    foreach ( $this->hiddens as $key => $value )
      $html .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";

    $html .= "<div class=\"form\">\n";
    
    $html .= $this->buffer;
    
    $html .= "<div class=\"clearboth\"></div>\n";
    $html .= "</div>\n";
    $html .= "</form>\n";
    
    return $html;
  }
}

?>

