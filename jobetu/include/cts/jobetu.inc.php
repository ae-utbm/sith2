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

	class apply_annonce_box extends stdcontents
	{
		function apply_annonce_box($annonce)
		{
			$this->buffer .= "<p>Gnaa !</p>";
		}
			
	}

	class annonce_box extends stdcontents
	{
		function annonce_box($annonce)
		{
			if( !($annonce instanceof annonce) ) exit("Namého ! mauvaise argumentation mon bonhomme ! :)");
			
			$this->buffer .= "<div class=\"annonce_table\">\n";
	
			$this->buffer .= "<div class=\"header\">\n";
			$this->buffer .= "<div class=\"num\">";
	  	$this->buffer .= "n°".$annonce->id;
	  	$this->buffer .= "</div>\n";
	  		
	  	$this->buffer .= "<div class=\"title\">";
	  	$this->buffer .= $annonce->titre;
	  	$this->buffer .= "</div>\n";
	  		
	  	$this->buffer .= "<div class=\"icons\">";
	  	$this->buffer .= "<a href=\"../article.php?name=jobetu-help\" title=\"Aide\"><img src=\"../images/actions/info.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=edit&id=".$annonce->id."\" title=\"Editer l'annonce\"><img src=\"../images/actions/edit.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=close&id=".$annonce->id."\" title=\"Clore cette annonce\"><img src=\"../images/actions/lock.png\" /></a>";
	  	$this->buffer .= "</div>\n";
	  	$this->buffer .= "</div>\n";
	  		
	  	$this->buffer .= "<div class=\"content\">\n";
	  	
	  	/** Candidatures ******************************************************/
	  	$this->buffer .= "Il y a `".count($annonce->applicants)."` candidature(s) pour votre annonce :\n";
	  	$n = 1; // Compteuràlacon

	  	foreach($annonce->applicants_fullobj as $usr)
	  	{
				$usr->load_all_extra();
	  		$this->buffer .= "<div class=\"apply_table\">\n";
  				$this->buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_".$n."');\">";
  				$this->buffer .= $usr->prenom." ".$usr->nom." (département ".$usr->departement.")";
  				$this->buffer .= "</div>\n";
  					
  				$this->buffer .= "<div id=\"applicant_".$n."\" class=\"apply_content\">";

  				
  				$this->buffer .= "gnaa";
  				$this->buffer .= "</div>\n";

	  			$this->buffer .= "</div>\n";
	  			$n++;
	  	}
	  	
	  	$this->buffer .= "</div>\n";
  			
  		$this->buffer .= $annonce->desc;
  		$this->buffer .= "</div>\n";
  		
  		 		  		
  		$this->buffer .= "</div>\n";
			
		}

	}

	class jobtypes_select_field extends form
	{
		function jobtypes_select_field(&$jobetu, $name, $title, $value = false, $required = true, $enabled = true)
		{
			if( !($jobetu instanceof jobetu) ) return -1;
			if(empty($jobetu->job_types)) $jobetu->get_job_types();
			
			parent::form($name, false);
			  	
	//  	if ( $frm->autorefill && $_REQUEST[$name] ) $value = $_REQUEST[$name];	
			$this->buffer .= "<div class=\"formrow\">";
			$this->_render_name($name,$title,$required);
		
			
			$this->buffer .= "<div class=\"formfield\">$prefix";
			$this->buffer .= "<select name=\"$name\" ";
			
			$this->buffer .= ">\n";
	
			foreach ( $jobetu->job_types as $key => $item )
			{
					$this->buffer .= "<option value=\"$key\"";
				if(!($key%100))
					$this->buffer .= " disabled style=\"background: #D8E7F3; color: #000000; font-weight: bold;\"";
				if ( $value == $key )
					$this->buffer .= " selected=\"selected\"";
				if(!($key%100))
					$this->buffer .= ">".htmlentities($item,ENT_NOQUOTES,"UTF-8")."</option>\n";
				else
					$this->buffer .= ">&nbsp;&nbsp;&nbsp;&nbsp;".htmlentities($item,ENT_NOQUOTES,"UTF-8")."</option>\n";
			}
	
			$this->buffer .= "</select></div>\n";
			$this->buffer .= "</div>";
		}
	}
	
?>
