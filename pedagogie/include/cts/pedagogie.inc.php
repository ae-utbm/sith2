<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

class add_uv_edt_box extends stdcontents
{
  public function __construct($uv)
  {
    if( !($uv instanceof uv) )
      throw new Exception("Incorrect type");
      
    $this->title = $uv->code." - ".$uv->intitule;
    $code = $uv->code;
    $this->buffer = "";    
    
    if(!$uv->extra_loaded)
      $uv->load_extra();
    print_r($uv);
    $this->buffer .= $this->build_uv_choice($uv, GROUP_C, "c");
    $this->buffer .= $this->build_uv_choice($uv, GROUP_TD, "td");
    $this->buffer .= $this->build_uv_choice($uv, GROUP_TP, "tp");
  }
  
  private function build_uv_choice($uv, $type, $typename){
    if($uv->guide[$typename]){
      $groups = $uv->get_groups_full($type);
      $opt = null;
      
      $buffer  = "<div class=\"formrow\">\n";
      $buffer .= "  <div class=\"formlabel\">".ucfirst($typename)." : </div>\n";
      $buffer .= "  <div class=\"formfield\">\n";
      $buffer .= "    <select name=\"_".$uv->id."_".$typename."_\">\n";
      foreach($groups as $group){
        $buffer .= "      <option value=".$group['id_group'].">".ucfirst($typename)." n°".$group['num_groupe']." du ".get_day($group['jour'])." de ".$group['debut']." &agrave; ".$group['fin']." en ".$group['salle']."</option>\n";
      }
      $buffer .= "      <option value=\"_add_\" onclick=\"javascript:alert('Ajout d'une s&eacute;ance');\"></option>\n";
      $buffer .= "    </select>\n";
      $buffer .= "  </div>\n";
      $buffer .= "</div>\n\n";
    }
    else
      $buffer = null;
      
    return $buffer;
  }
}

class semestre_box extends contents
{
  public function __construct($semestre=SEMESTER_NOW)
  {
    $this->title = $title;
    $this->buffer = "";
  }
}

?>
