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
			if( !($annonce instanceof annonce) ) exit("Namého ! mauvaise argumentation mon bonhomme ! :)");
  	
			global $topdir;
			
	  	$this->buffer .= "<div class=\"annonce_table\">";
	  		
	  	$this->buffer .= "<div class=\"header\" onClick=\"javascript:on_off('annonce_".$annonce->id."');\">\n";
	  			$this->buffer .= "<div class=\"num\">";
	  				$this->buffer .= "n°".$annonce->id;
	  			$this->buffer .= "</div>\n";
				
	  			$this->buffer .= "<div class=\"title\">";
	  				$this->buffer .= $annonce->titre;
	  			$this->buffer .= "</div>\n";
	  			
	  			$this->buffer .= "<div class=\"icons\">\n";
	  				$this->buffer .= "<a href=\"$topdir"."article.php?page=docs:jobetu:candidats\" title=\"Aide\"><img src=\"../images/actions/info.png\" /> &nbsp;";
	  				$this->buffer .= "<a href=\"board_etu.php?action=reject&id=".$annonce->id."\" title=\"Ne plus me proposer\"><img src=\"../images/actions/delete.png\" /></a>";
	  			$this->buffer .= "</div>\n";
	  		$this->buffer .= "</div>";
	  			
	  		/** Contenu  ************************************************************/
	  			$this->buffer .= "<div id=\"annonce_".$annonce->id."\" class=\"content\"> \n";
	  			$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Demandeur </div> \n <div class=\"desc_content\"> <a href=\"$topdir/user.php?id_utilisateur=$annonce->id_client\"><img src=\"http://ae.utbm.fr/images/icons/16/user.png\" /> ".$annonce->nom_client."</a></div> \n</div>";
	  			$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Type </div> \n <div class=\"desc_content\">".$annonce->nom_type." (". $annonce->nom_main_cat .") </div> \n</div>";
				if( $annonce->start_date != '0/0/0000' )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Date de début </div> \n <div class=\"desc_content\">".$annonce->start_date."</div> \n</div>";
				if( !empty($annonce->indemnite) )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Rémunération (€)</div> \n <div class=\"desc_content\">". $annonce->indemnite ."</div> \n</div>";
				if( $annonce->nb_postes != 1 )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Nombre de postes </div> \n <div class=\"desc_content\">".$annonce->nb_postes."</div> \n</div>";
				if( !empty($annonce->duree) )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Durée </div> \n <div class=\"desc_content\">".$annonce->duree."</div> \n</div>";
				if( !empty($annonce->desc) ) //enfin en théorie ça peut pas l'être
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Description </div> \n <div class=\"desc_content\">".htmlentities($annonce->desc,ENT_NOQUOTES,"UTF-8")."</div> \n</div>";
				if( !empty($annonce->profil) )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Profil recherché </div> \n <div class=\"desc_content\">".htmlentities($annonce->profil,ENT_NOQUOTES,"UTF-8")."</div> \n</div>";
				if( !empty($annonce->divers) )
						$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Autres renseignements </div> \n <div class=\"desc_content\">".htmlentities($annonce->divers,ENT_NOQUOTES,"UTF-8")."</div> \n</div>";

						$this->buffer .= "<br />";
						
	  					$frm = new form("apply_".$annonce->id."", false, true, "POST");
		  					$frm->add_submit("clic", "Se porter candidat");
		  				$this->buffer .= "<div onClick=\"javascript:on_off('apply_".$annonce->id."');\">" . $frm->buffer . "</div>";
		  				
		  				$this->buffer .= "<div id=\"apply_".$annonce->id."\" style=\"display: none;\" class=\"apply_form\">";
		  					$frm = new form("application_".$annonce->id."", "board_etu.php?action=apply", true, "POST");
		  					$frm->puts("<p>Veuillez noter qu'en soumettant votre candidature, vous vous engagez et qu'il ne vous sera pas possible d'annuler cette candidature (sauf raison particulière ou l'acceptation d'une autre offre). Merci donc de ne pas vous porter candidat \"à la légère\".<p> ");
			  				$frm->puts("Ajouter un message à votre candidature <i>(facultatif)</i> :<br />");
			  				$frm->add_hidden("id", $annonce->id);
			  				$frm->add_text_area("comment", false, false, 80, 10);
			  				$frm->add_submit("send", "Envoyer la candidature");
		  				$this->buffer .= $frm->html_render();
		  				
		  				$this->buffer .= "</div>";
	  				  				
	  				$this->buffer .= "</div>";
	  		/************************************************************************/			
	  		$this->buffer .= "</div>";

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
	  	$this->buffer .= "<a href=\"../article.php?name=docs:jobetu:recruteurs\" title=\"Aide\"><img src=\"../images/actions/info.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=edit&id=".$annonce->id."\" title=\"Editer l'annonce\"><img src=\"../images/actions/edit.png\" /></a> &nbsp;";
	  	$this->buffer .= "<a href=\"board_client.php?action=close&id=".$annonce->id."\" title=\"Clore cette annonce\"><img src=\"../images/actions/lock.png\" /></a>";
	  	$this->buffer .= "</div>\n";
	  	$this->buffer .= "</div>\n";
	  		
	  	$this->buffer .= "<div class=\"content\">\n";
	  	
	  	/** Candidatures ******************************************************/
	  	$n = 1; // Compteuràlacon

			if( empty($annonce->applicants) )
			{
				$this->buffer .= "<p>Aucun candidat ne s'est pour l'instant présenté pour répondre à votre offre.</p>";
			}
			else
			{
				$this->buffer .= "<p>Il y a `".count($annonce->applicants)."` candidature(s) pour votre annonce :</p>\n";
				
		  	foreach($annonce->applicants_fullobj as $usr)
		  	{
					$usr->load_all_extra();
		  		$this->buffer .= "<div class=\"apply_table\">\n";
	  				$this->buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_".$n."');\">";
	  				$this->buffer .= $usr->prenom." ".$usr->nom." (département ".strtoupper($usr->departement).")";
	  				$this->buffer .= "</div>\n";
	  					
	  				$this->buffer .= "<div id=\"applicant_".$n."\" class=\"apply_content\">";
	  				$this->buffer .= "<p>Votre annonce à reçu la candidature de cet étudiant :</p>";
	  				$gal = new gallery("Fiche du candidat");
	  				$gal->add_item( new userinfov2($usr) );

						$this->buffer .= "<div style=\"margin: 0 auto;\">\n" . $gal->html_render() . "</div>" ;
						if( !empty($annonce->applicants[$n-1]['comment']) )
							$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Message </div> \n <div class=\"desc_content\">".$annonce->applicants[$n-1]['comment']."</div> \n</div>";
						
						$frm = new form("apply_".$annonce->id."", false, true, "POST");
		  			$frm->add_submit("clic", "Choisir ce candidat");
						$this->buffer .= $frm->html_render(); 
						
	  				$this->buffer .= "</div>\n";
	
		  			$this->buffer .= "</div>\n";
		  			$n++;
		  	}
		  	$this->buffer .= "<p></p>";
			}
	  	
	 // 	$this->buffer .= "</div>\n";
  		$this->buffer .= "<div class=\"desc_row\"> \n<div class=\"desc_label\"> Votre annonce </div> \n <div class=\"desc_content\">".htmlentities($annonce->desc,ENT_NOQUOTES,"UTF-8")."</div> \n</div>";

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
			
			parent::form($name, false, true);
			  	
	  	if ( $frm->autorefill && $_REQUEST[$name] ) $value = $_REQUEST[$name];	
			
			$this->buffer .= "<div class=\"formrow\" style=\"margin-left: -2em; padding-left: -2em\" >";
			$this->_render_name($name,$title,$required);
					
			$this->buffer .= "<div class=\"formfield\">";
			$this->buffer .= "<select name=\"$name\" >\n";
	
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
			$this->buffer .= "</div>\n";

		}
	}
	
	
	class jobtypes_table extends stdcontents
	{
		function jobtypes_table(&$jobetu, $user, $name, $title, $value = false, $required = true, $enabled = true)
		{
			if( !($jobetu instanceof jobetu) ) return -1;
			if( !($user instanceof jobuser_etu) ) return -1;
			if( empty($jobetu->job_types) ) $jobetu->get_job_types();
			if( empty($user->competences) ) $user->load_competences();
	
	  	$l = 1;
	  	$t = 0;
	  	static $num = 1;
	  	$id_name = "id_job";
	  	
	  	$this->buffer .= "<form name=\"$name\" action=\"board_etu.php?view=profil\" method=\"POST\">\n";
	  	$this->buffer .= "<input type=\"hidden\" name=\"magicform[name]\" value =\"$name\" />\n";
	  	$this->buffer .= "<table class=\"sqltable\">\n";
	  	
	  	foreach ( $jobetu->job_types as $key => $item )
			{
				if(!($key%100))
				{
			  	$this->buffer .= "<tr class=\"head\">\n";
			  		$this->buffer .= "<th colspan=\"2\" value=\"$key\">$item</th>";
			  	$this->buffer .= "</tr>\n";
				}
				else
				{
					$t = $t%2;
					
					if( in_array($key, $user->competences) )
						$check = "checked=\"checked\"";
					else
						$check = "";
					
					$this->buffer .= "<tr id=\"ln[$num]\" class=\"ln$t\" onMouseDown=\"setPointer('ln$t','$num','click','".$id_name."s[','".$name."');\" onMouseOut=\"setPointer('ln$t','$num','out');\" onMouseOver=\"setPointer('ln$t','$num','over');\">\n";
					$this->buffer .= "<td><input type=\"checkbox\" class=\"chkbox\" name=\"".$id_name."s[$num]\" value=\"".$key."\" $check onClick=\"setPointer('ln$t','$num','click','".$id_name."s[','".$name."');\"/></td>\n";
						$this->buffer .= "<td>$item</td>\n";
					$this->buffer .= "</tr>";
				
					$l++; $t++; $num++;
				}
			}
	  	$this->buffer .= "</table>\n";
	  	$this->buffer .= "</select>\n<input type=\"submit\" name=\"$formname\" value=\"Enregistrer\" class=\"isubmit\"/>\n</p>\n";
	
	  	$this->buffer .= "</form>\n";	}
	}
	
?>
