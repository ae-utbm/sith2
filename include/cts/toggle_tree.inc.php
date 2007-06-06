<?php
/* Copyright 2007
 * - Pierre Mauduit <Pierre POINT mauduit CHEZ utbm POINT fr>
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
 
require_once($topdir."include/catalog.inc.php"); 
 

$tglnum = 0;


class toggle_tree extends contents
{
  /** Le tableau définissant l'arbre à afficher ;
   *
   * Exemple de structure :
   *
   * un arbre est composé de noeuds définis comme ci dessous :
   * - title : titre du noeud
   * - datas : données concernant la noeud courant
   * - child : les fils (tableau)
   *
   * Ainsi la structure suivante :
   *
   * $array = array(0 => array ("title" => "machin",
   *                            "datas" => null,
   *                            "childs" => array(0 => array("title" => "truc",
   *                                                         "datas" => null, 
   *                                                         "childs" => array(0 => array("title" => "bidule",
   *                                                                                      "datas" => null,
   *                                                                                      "childs" => null)
   *                                                                           
   *                                                                           1 => array("title" => "chouette",
   *                                                                                        ...)))
   *                                              1 => array("title" => "machinchouette" ...)))
   *               1 => array ("title" => "bleh" ...));
   *
   * Donnera :
   * + machin
   * |+ truc
   * ||- bidule
   * ||- chouette
   * |- machinchouette
   * + bleh
   *
   * Voila l'idée de base. Il sera toutefois intéressant d'avoir un 
   * tableau plus chargé, le script pouvant accéder alors aux différents
   * champs de sous-tableau, selon les cas.
   *
   *
   * Note : les noeuds n'ont pas de fonction associée autre que
   * l'enroulement / déroulement.  Les noeuds "fils" (pas de +/-)
   * peuvent associer une fonction javascript en cas de click, via la
   * définition de la clé "jsOnclick";
   *
   */
  var $array;
  var $cur_lvl;

  var $count_id;

  var $collapsed;

  function toggle_tree ($title, $array = null,  $buffer, $collapsed = true)
  {

    global $tglnum;

    if (intval($tglnum) == 0)
      $tglnum = 1;
    else
      $tglnum ++;

    $this->contents($title, $buffer);
    $this->array = $array;
    $this->cur_lvl = 0;
    $this->count_id = 1;
    $this->collapsed = $collapsed;
    $this->add_paragraph($this->generate_buffer($array, $collapsed));

  }


  function generate_buffer($array, $collapsed = true)
  {
    global $topdir;
    global $tglnum;

    if (is_array($array))
      {
	foreach($array as $elem)
	  {
	    $this->add_offset();
	    
	    if (is_array($elem['childs']))
	      {
		if ($collapsed == true)
		  $imgsrc = "\"" .$topdir . "images/fll.png\"";
		else
		  $imgsrc = "\"" .$topdir . "images/fld.png\"";
  
	
		$this->buffer .= "<a href=\"javascript:toggle('".$tglnum."','".++$this->count_id."');\">\n";
		$this->buffer .= "<img id=\"tgl".$tglnum."_img".$this->count_id."\" src=".$imgsrc ."/>\n";
	      }
	    /* si pas d'enfant */
	    else
	      {
		$this->buffer .="   <a href=\"".$elem['jsOnclick']."\">";
	      }
	    $this->buffer .=  $elem['title'] . "</a><br/>\n";
	    if ($collapsed == true)
	      $this->buffer .= "<span id=\"tgl".$tglnum."_" . $this->count_id .
		"\" style=\"display: none;\" class=\"tgloff\">\n";
	    else
	      $this->buffer .= "<span id=\"tgl".$tglnum."_" . $this->count_id .
		"\" style=\"display: inline;\" class=\"tglon\">\n";

	    if (is_array($elem['childs']))
	      {
		$this->cur_lvl ++;
		$this->generate_buffer($elem['childs']);
		$this->cur_lvl --;

	      }
	    $this->buffer .= "</span>\n";
	  }
      }

  }

  function add_offset()
  {
    global $topdir;
    $offset = $this->cur_lvl * 20;
    $this->buffer .= "<img src=\"".$topdir."images/px.gif\" style=\"width: ".$offset."px; height: 1px;\" />\n";
  }

} 
 
?>