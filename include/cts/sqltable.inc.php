<?php
/* Copyright 2005,2006
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
 * Table SQL
 */
 
require_once($topdir."include/catalog.inc.php"); 
 
/** Classe permetant de générer un tableau à partir d'un resultat SQL en 
 * y ajoutant des actions et de nombreuses fonctionalités aditionnelles.
 */
class sqltable extends stdcontents
{
	var $id_field;
	var $id_name;
	var $page;
	var $get_page;
	/** Génére une table basé sur une requéte SQL avec actions (supprimer, édtier)
	 * @param $formname Nom du formulaire (@see form)
	 * @param $title Titre
	 * @param $req objet request associé (ou un array(array(field=>value)))
	 * @param $page Page qui va être la cible des actions
	 * @param $id_field Champ qui contient l'id de l'objet
	 * @param $cols colonnes à traiter (id=>Description) (ou id_defaut=>array(Description,id1,id2,id3...))
	 * @param $actions actions sur chaque objet (envoyé à %page%?action=%action%&%id_field%=[id])
	 * @param $batch_actions actions possibles sur plusieurs objets (envoyé à page, les id sont le tableau %id_field%s)
	 * @param $enumerated valeurs des champs énumérés ($enumerated[id] = array(0=>"truc"))
	 **/
	function sqltable ( $formname, $title, $sql, $page, $id_field, $cols, $actions, $batch_actions, $enumerated=array() )
	{
		global $topdir,$wwwtopdir;
		
		$reg = false;
		
		$this->title = $title;
		$this->id_field = $id_field;
		$this->id_name = $id_field;
		$this->page = $page;
		
		if ( strstr($page,"?"))
			$this->get_page = $page."&";
		else
			$this->get_page = $page."?";
		
		if ( is_array($sql) )
		{
			if (count($sql) < 1 )
			{
				$this->buffer = "<p>(vide)</p>\n";
				return;	
			}	
		}
		elseif ( $sql->lines < 1 )
		{
			$this->buffer = "<p>(vide)</p>\n";
			return;	
		}
		
		if ( count($batch_actions) )
		{
			$this->buffer .= "<form name=\"$formname\" action=\"$page\" method=\"POST\">\n";
			$this->buffer .= "<input type=\"hidden\" name=\"magicform[name]\" value =\"$formname\" />\n";
		}
		
		$this->buffer .= "<table class=\"sqltable\">\n";

		if ( count($cols) > 1 )
		{
			$this->buffer .= "<tr class=\"head\">\n";
			if ( count($batch_actions) )
			{
				$this->buffer .= "<th>";
				$this->buffer .= "<input type=\"checkbox\" onclick=\"this.value=setCheckboxesRange('".$formname."', '".$this->id_name."s["."', 0, $sql->lines)\" name=\"".$formname."_all\">";
				$this->buffer .= "</th>\n";
			}
							
			foreach ( $cols as $key => $name )
			{
				if ( is_array($name) ) $name = $name[0];
				$this->buffer .= "<th>".htmlentities($name,ENT_NOQUOTES,"UTF-8")."</th>\n";
			}
			
			foreach ( $actions as $key => $name )
			{
				$this->buffer .= "<th></th>\n";
			}
			
			$this->buffer .= "</tr>\n";
		}
		
		$t=0;
		if ( is_array($sql) ) reset($sql);
		$num = 0;
		$l=1;
		static $num = 0;
		
		while ( is_array($sql) ? (list($rien,$row) = each($sql)) : ($row = $sql->get_row()) )
		{
			
			$t = $t^1;
			
			if ( count($batch_actions) )
			{
				$this->buffer .= "<tr id=\"ln[$num]\" class=\"ln$t\" onMouseDown=\"setPointer('ln$t','$num','click','".$this->id_name."s[','".$formname."');\" onMouseOut=\"setPointer('ln$t','$num','out');\" onMouseOver=\"setPointer('ln$t','$num','over');\">\n";
				$this->buffer .= "<td><input type=\"checkbox\" class=\"chkbox\" name=\"".$this->id_name."s[$num]\" value=\"".$row[$id_field]."\" onClick=\"setPointer('ln$t','$num','click','".$this->id_name."s[','".$formname."');\"/></td>\n";
			}
			else
				$this->buffer .= "<tr id=\"ln[$num]\" class=\"ln$t\" onMouseDown=\"setPointer('ln$t','$num','click');\" onMouseOut=\"setPointer('ln$t','$num','out');\" onMouseOver=\"setPointer('ln$t','$num','over');\">\n";

			$num++;
			foreach ( $cols as $key => $name )
			{
			

			
				if ( is_array($name) )
				{
					for($i=1;$i<count($name);$i++)
						if ( $row[$name[$i]] ) $key = $name[$i];	
				}
				
			  if ( $key == "=num" )
			  {
					$this->buffer .= "<td>";
					$this->buffer .= $l;
					$this->buffer .= "</td>";
			  } 
			  else if ( ($key == "solde") || ($key == "montant") || ($key == "sum") || (ereg("^sum_(.*)$",$key,$reg)) )
				{
					$this->buffer .= "<td class=\"moneycell\">";
					$this->buffer .= sprintf("%01.2f",floatval($row[$key]));
					$this->buffer .= "</td>";
				}
				else
				{
				
				
				$this->buffer .= "<td>";
				
				foreach ( $GLOBALS["entitiescatalog"] as $class => $ent )
				{
					if ( ereg("^".$ent[1]."(.*)$",$key,$reg))	
					{
						$id = $row[$ent[0].$reg[1]];
						if ( $id )
						{
						  if ( $ent[5] )
						  {
						    $ref="sqt".$num.$key;
						    $javascript="id=\"$ref\" onmouseover=\"show_tooltip('$ref','$wwwtopdir','$class','".$id."');\" ".
						      "onmouseout=\"hide_tooltip('$ref');\"";
						  }
						  else
						    $javascript="";
						    
							if ( $ent[3] )
							{
								$this->buffer .= "<a href=\"".$wwwtopdir.$ent[3]."?".$ent[0]."=$id\">";
								$this->buffer .= "<img src=\"".$wwwtopdir."images/icons/16/".$ent[2]."\" class=\"icon\" alt=\"\" $javascript />";
								$this->buffer .= "</a> ";
							}
							else
								$this->buffer .= "<img src=\"".$wwwtopdir."images/icons/16/".$ent[2]."\" class=\"icon\" alt=\"\" $javascript />";
							
						}
					}
					
				}
				
				if ( ereg("^date_(.*)$",$key,$reg))
				{
					if ( $row[$key] )
					{
						$timestamp=strtotime($row[$key]);
						if ( $row[$key] == date("Y-m-d",$timestamp)) // DATE
							$this->buffer .= date("d/m/Y",$timestamp);
						else // DATETIME
							$this->buffer .= date("d/m/Y H:i",$timestamp);
					}
					else
						$this->buffer .= "";
				}
				elseif ( ereg("^stock_(.*)$",$key,$reg))
				{
					if ( $row[$key] == -1 )
						$this->buffer .= "Non limité";
					else
						$this->buffer .= $row[$key];
				}
				elseif ( isset($enumerated[$key]) )
					$this->buffer .= htmlentities($enumerated[$key][$row[$key]],ENT_NOQUOTES,"UTF-8");
				elseif ( ereg("^(.*)_folder$",$key,$reg))
					$this->buffer .= $row[$key];
				else
					$this->buffer .= htmlentities($row[$key],ENT_NOQUOTES,"UTF-8");
		
				$this->buffer .= "</td>\n";
				}
			}
			
			foreach ( $actions as $key => $name )
			{
				$this->buffer .= "<td><a href=\"".$this->generate_hlink($key,$row[$id_field])."\">";
				if ( file_exists( $topdir . "images/actions/" . $key.".png")   )
					$this->buffer .= "<img src=\"".$wwwtopdir . "images/actions/" . $key.".png\" alt=\"".htmlentities($name,ENT_NOQUOTES,"UTF-8")."\" title=\"".htmlentities($name,ENT_NOQUOTES,"UTF-8")."\" class=\"icon\" />";
				else
					$this->buffer .= htmlentities($name,ENT_NOQUOTES,"UTF-8");
				$this->buffer .= "</a></td>\n";
			}			
			$this->buffer .= "</tr>\n";
				$l++;
		}
		
		$this->buffer .= "</table>\n";
		
		if ( count($batch_actions) )
		{
			$this->buffer .= "<p style=\"font-size:90%;\">&nbsp;&nbsp;Pour la s&eacute;lection : <select name=\"action\">\n";
			
			foreach ($batch_actions as $action => $name )
				$this->buffer .= "<option value=\"$action\">".htmlentities($name,ENT_NOQUOTES,"UTF-8")."</option>\n";
			$this->buffer .= "</select>\n<input type=\"submit\" name=\"$formname\" value=\"Valider\" class=\"isubmit\"/>\n</p>\n";
			$this->buffer .= "</form>\n";
		}
		
		
	}
	
	function generate_hlink ( $action, $id )
	{
		if ( !$action )
			return htmlentities($this->get_page.$this->id_name."=".$id);
		else
			return htmlentities($this->get_page.$this->id_name."=".$id."&action=".$action);
	}

	
}


?>
