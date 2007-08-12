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
			
	  	$this->buffer .= "<div class=\"annonce_table\">";
	
	  	$this->buffer .= "<div class=\"header\">";
	  	$this->buffer .= "<div class=\"num\">";
	  	$this->buffer .= "n°".$annonce->id;
	  	$this->buffer .= "</div>";
	  		
	  	$this->buffer .= "<div class=\"title\">";
	  	$this->buffer .= $annonce->titre;
	  	$this->buffer .= "</div>";
	  		
	  	$this->buffer .= "<div class=\"icons\">";
	  	$this->buffer .= "<a href=\"../article.php?name=jobetu-help\" title=\"Aide\"><img src=\"../images/actions/info.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=edit&id=".$annonce->id."\" title=\"Editer l'annonce\"><img src=\"../images/actions/edit.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=close&id=".$annonce->id."\" title=\"Clore cette annonce\"><img src=\"../images/actions/lock.png\" /></a>";
	  	$this->buffer .= "</div>";
	  	$this->buffer .= "</div>";
	  		
	  	$this->buffer .= "<div class=\"content\">";
	  	
	  	/** Candidatures ******************************************************/
	  	$this->buffer .= "Il y a `".count($annonce->applicants)."` candidature(s) pour votre annonce :";
	  	$n = 1; // Compteuràlacon

	  	foreach($annonce->applicants_fullobj as $usr)
	  	{
	  		//$userinfo = new userinfo($usr, true, false, true, false, true, true);
				$userinfo = new userinfov2($usr);
	  		
	  		$this->buffer .= "<div class=\"apply_table\">";
  					
  				$this->buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_1');\">";
  				$this->buffer .= $usr->prenom." ".$usr->nom." (département ".$usr->branche_utbm.")";
  				$this->buffer .= "</div>";
  					
  				$this->buffer .= "<div id=\"applicant_1\" class=\"apply_content\">";
  				 				
  				$this->buffer .= $userinfo->buffer;
  				$this->buffer .= "gnaa";
  				$this->buffer .= "</div>";

	  			$this->buffer .= "</div>";
	  			$n++;
	  	}
	  	
	  	$this->buffer .= "</div>";
  			
  		$this->buffer .= $annonce->desc;
  		$this->buffer .= "</div>";
  		
  		 		  		
		$buffer .= "</div>";
		}

	}

	
?>
