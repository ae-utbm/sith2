<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


class jobetu extends stdentity
{
  var $job_types;
  var $job_main_cat;

  
  /** Récupère les différents types de job
   * renvoie un tableau: array( array("id_type", "cat", "nom") ) dans $this->jobtypes
   */
  function get_job_types()
  {
    $sql = new requete($this->db, "SELECT * FROM job_types ORDER BY id_type ASC");
    
    while($type = $sql->get_row())
    {
			$this->job_types[ $type['id_type'] ] = $type['nom'];
			if(!($type['id_type']%100))
				$this->job_main_cat[ $type['id_type'] ] = $type['nom'];
    }
  }

  /** 
   * Ajoute une liste à choix 'customisée' pour les types de jobs dans un formulaire
   */
  function add_jobtypes_select_field($frm, $name, $title, $value = false, $required = true, $enabled = true)
  {
  	if(!($frm instanceof form)) return -1;
  	if(empty($this->job_types)) $this->get_job_types();
  	
//  	if ( $frm->autorefill && $_REQUEST[$name] ) $value = $_REQUEST[$name];	
		$frm->buffer .= "<div class=\"formrow\">";
		$frm->_render_name($name,$title,$required);
	
		
		$frm->buffer .= "<div class=\"formfield\">$prefix";
		$frm->buffer .= "<select name=\"$name\" ";
		
		$frm->buffer .= ">\n";

		foreach ( $this->job_types as $key => $item )
		{
			$frm->buffer .= "<option value=\"$key\"";
			if(!($key%100))
			$frm->buffer .= " disabled style=\"background: #D8E7F3; color: #000000; font-weight: bold;\"";
			if ( $value == $key )
				$frm->buffer .= " selected=\"selected\"";
			if(!($key%100))
				$frm->buffer .= ">".htmlentities($item,ENT_NOQUOTES,"UTF-8")."</option>\n";
			else
				$frm->buffer .= ">&nbsp;&nbsp;&nbsp;&nbsp;".htmlentities($item,ENT_NOQUOTES,"UTF-8")."</option>\n";
		}

		$frm->buffer .= "</select></div>\n";	
		$frm->buffer .= "</div>";
  }
  
  /**
   * Ajoute un tableau de type sqltable pour les types de jobs
   */
	function add_jobtypes_table($frm, $name, $title, $value = false, $required = true, $enabled = true)
	{
  	if(!($frm instanceof form)) return -1;
  	if(empty($this->job_types)) $this->get_job_types();

  	$l = 1;
  	$t = 0;
  	static $num = 1;
  	$id_name = "id_job";
  	
  	$frm->buffer .= "<table class=\"sqltable\">\n";
  	
  	foreach ( $this->job_types as $key => $item )
		{
			if(!($key%100))
			{
		  	$frm->buffer .= "<tr class=\"head\">\n";
		  		$frm->buffer .= "<th colspan=\"2\" value=\"$key\">$item</th>";
		  	$frm->buffer .= "</tr>\n";
			}
			else
			{
		  	$frm->buffer .= "<tr id=\"ln[$num]\" class=\"ln$t\" onMouseDown=\"setPointer('ln$t','$num','click','".$id_name."s[','".$frm->name."');\" onMouseOut=\"setPointer('ln$t','$num','out');\" onMouseOver=\"setPointer('ln$t','$num','over');\">\n";
					$frm->buffer .= "<td><input type=\"checkbox\" class=\"chkbox\" name=\"".$id_name."s[$num]\" value=\"".$key."\" onClick=\"setPointer('ln$t','$num','click','".$id_name."s[','".$frm->name."');\"/></td>\n";
					$frm->buffer .= "<td>$item</td>\n";
				$frm->buffer .= "</tr>";
			
				$l++; $t++; $num++;
			}
		}
  	$frm->buffer .= "</table>\n";
	}

  /** Ajoute une nouvelle catégorie de job
   * @param $name nom de la catégorie
   * @return num de la nouvelle catégorie, -1 si échec
   * @todo problème de placement !!
   */
  function add_cat_type($nom)
  {
    /* récupération du maximum existant */
    $sql = new requete($this->db, "SELECT MAX(id_type) FROM job_types");
    $cat = $sql->get_row();
    $max = floor($cat[0] / 100);
    
    $sql = new insert($this->dbrw, 
		      "job_types", 
		      array(
			    "id_type" => ($max + 1)*100,
			    "nom" => mysql_real_escape_string($nom)
			    )
		      );
    
    if($sql)
      return ($max + 1)*100;
    else
      return -1;
  }

  /** Ajoute un nouveau type de job (dans une catégorie existante)
   * @param $name nom du type
   * @param $id_cat id de la catégorie associée
   * @return num du nouveau type, -1 si échec
   */

  function add_subtype($nom, $id_cat)
  {
    $sql = new requete($this->db, "SELECT MAX(id_type) FROM job_types WHERE id_type > ".($id_cat - 1)." AND id_type < ".($id_cat + 100)."");
    $max = $sql->get_row();

    $sql = new insert($this->dbrw,
		      "job_types",
		      array(
						    "id_type" => $max[0] + 1,
						    "nom" => mysql_real_escape_string($nom)
			    			));

    if($sql)
      return $id_cat + $max[0] + 1;
    else
      return -1;   
  }

}

