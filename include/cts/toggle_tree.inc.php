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
 
require_once($topdir."include/entitieslinks.inc.php"); 
 

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
   */
  var $array;
  var $cur_lvl;

  var $count_id;

  function toggle_tree ($title, $array = null, $buffer)
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
    $this->generate_scripts();

    $this->add_paragraph($this->generate_buffer($array));
  }

  function generate_scripts()
  {
    global $tglnum;

    /* deja defini par un autre toggle tree */
    if ($tglnum > 1)
      return;

    // genere le tableau "arbre" des dépendances
    $script .= "<script language=\"javascript\">\n";
    $script .="function toggle (id_tglnum,id)
    {
      // select next span 
      toHide = document.getElementById(\"tgl\"+id_tglnum+\"_\"+id+\"\");
      imgToChange = document.getElementById(\"tgl\"+id_tglnum+\"_img\"+id+\"\");
      
      if (!toHide.class)
      {
         toHide.class = \"tglon\";
      }

      if (toHide.class == \"tglon\")
      {
         toHide.class = \"tgloff\";
         toHide.style.display = \"none\";
         imgToChange.src = \"/images/fll.png\";
         
      }
 
      else
      {
         toHide.class = \"tglon\";       
         toHide.style.display = \"inline\";
         imgToChange.src = \"/images/fld.png\";
      }
    }\n";

    $script .= "</script>\n";
    $this->buffer .= $script;
  }


  function generate_buffer($array)
  {
    global $topdir;
    global $tglnum;

    if (is_array($array))
      {
	foreach($array as $elem)
	  {
	    $this->add_offset();
	    
	    if (is_array($elem['childs']))
	      $this->buffer .= "<a href=\"javascript:toggle(".$tglnum.", ".++$this->count_id.");\">\n
                                <img id=\"tgl".$tglnum."_img".$this->count_id."\" src=\"" . $topdir . "images/fld.png\" alt=\"\" onclick=\"\"/>\n";

	    else
	      $this->buffer .="<a href=\"javascript:return null;\">";
	  
	    $this->buffer .=  $elem['title'] . "</a><br/>\n";
	    
	    $this->buffer .= "<span id=\"tgl".$tglnum."_" . $this->count_id ."\" class=\"tgloff\">\n";


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
    $this->buffer .= "<img src=\"".$topdir."images/px.gif\" style=\"width: ".$offset."px; height: 1px;\" />";
  }

} 
 
?>