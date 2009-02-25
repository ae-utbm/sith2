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
    
    $this->buffer .= "<p>Indiquez ci-dessous les séances auxquelles vous êtes
      inscrit. Si celle-ci n'est pas présente dans la liste proposée, choisissez
      \"Ajouter une séance\" afin de la créer.</p>";
    
    if(!$uv->extra_loaded)
      $uv->load_extra();
      
    $this->buffer .= "<p>Selon nos informations, les enseignements de cette UV
      sont composés de "
        .$uv->guide['c']."h de Cours, "
        .$uv->guide['td']."h de TD et "
        .$uv->guide['tp']."h de TP (*)</p>";
    
    $this->buffer .= $this->build_uv_choice($uv, GROUP_C);
    $this->buffer .= $this->build_uv_choice($uv, GROUP_TD);
    $this->buffer .= $this->build_uv_choice($uv, GROUP_TP);
    
    $this->buffer .= "<p><i>(*) Si certaines des informations concernant cette UV
      sont incorrectes (détails des séances...), vous pouvez les 
      <a href=\"#\">corriger ici.</a></i></p>";
  }
  
  private function build_uv_choice($uv, $type){
    if($uv->guide[ $_GROUP[$type]['short'] ]){
      $groups = $uv->get_groups_full($type);
      print_r($groups);
      $buffer  = "<div class=\"formrow\">\n";
      $buffer .= "  <div class=\"formlabel\">".$_GROUP[$type]['long']." : </div>\n";
      $buffer .= "  <div class=\"formfield\">\n";
      $buffer .= "    <select name=\"_".$uv->id."_".$_GROUP[$type]['short']."_\">\n";
      $buffer .= "      <option value=\"_none_\">S&eacute;lectionnez votre s&eacute;ance</option>\n";
      foreach($groups as $group){
        $buffer .= "      <option value=".$group['id_group'].">".$_GROUP[$type]['long']." n°".$group['num_groupe']." du ".get_day($group['jour'])." de ".$group['debut']." &agrave; ".$group['fin']." en ".$group['salle']."</option>\n";
      }
      $buffer .= "      <option value=\"_add_\" onclick=\"javascript:alert('Ajout d'une s&eacute;ance');\">Ajouter une s&eacute;ance de ".$_GROUP[$type]['long']."...</option>\n";
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
