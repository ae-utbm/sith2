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

require_once($topdir. "include/cts/user.inc.php");

class jobuser_client extends utilisateur
{
	var $annonces = array();
	
  function new_particulier( $nom, $prenom, $alias, $email, $password, $droit_image, $date_naissance, $sexe)
  {
  	$this->create_user( $nom, $prenom, $alias, $email, $password, $droit_image, $date_naissance, $sexe);
  	
  }
  
  function new_societe( $nom, $prenom, $nom_ste, $email, $password, $droit_image, $date_naissance, $sexe) //Ne prévoit pas la France de demain :(
  {
  	//En attente du flag sur la table utilisateur
  	$this->create_user( $nom, $prenom, "Société ". $nom_ste, $email, $password, $droit_image, $date_naissance, $sexe);
  }
    
  function connexion($email, $passwd)
  {
  	$this->load_by_email($email);
		if ( !$this->is_password($passwd) )
		{
			header("Location: http://ae.utbm.fr/article.php?name=site-wrongpassoruser");
			exit();
		}
  }

  function is_jobetu_client()
  {
    return $this->is_in_group("jobetu_client");
  }

  
  function load_annonces()
  {
   // if( is_jobetu_client() )
      {
      	$sql = new requete($this->db, "SELECT * FROM job_annonces WHERE id_client = $this->id");
				
        while($line = $sql->get_row())
			    $this->annonces[] = $line;
	    }
  }
  
  
  function add_annonce_box($id_annonce)
  {
  	$annonce = $this->annonces[$id_annonce];
  	
  	$buffer .= "<div class=\"annonce_table\">";
  		
  		$buffer .= "<div class=\"header\">";
  			$buffer .= "<div class=\"num\">";
  				$buffer .= "n°".$annonce['id_annonce'];
  			$buffer .= "</div>";
			
  			$buffer .= "<div class=\"title\">";
  				$buffer .= $annonce['titre'];
  			$buffer .= "</div>";
  			
  			$buffer .= "<div class=\"icons\">";
  				$buffer .= "<a href=\"../article.php?name=jobetu-help\" title=\"Aide\"><img src=\"../images/actions/info.png\" /></a> &nbsp;";
  				$buffer .= "<a href=\"board_client.php?action=edit&id=".$annonce['id_annonce']."\" title=\"Editer l'annonce\"><img src=\"../images/actions/edit.png\" /></a> &nbsp;";
  				$buffer .= "<a href=\"board_client.php?action=close&id=".$annonce['id_annonce']."\" title=\"Clore cette annonce\"><img src=\"../images/actions/lock.png\" /></a>";
  			$buffer .= "</div>";
  		$buffer .= "</div>";
  			
  		$buffer .= "<div class=\"content\">";
  			  				
  			/** Candidatures ******************************************************/

  			$sql = new requete($this->db, "SELECT job_annonces_etu.id_etu,
																							job_annonces_etu.comment,
																							utilisateurs.nom_utl,
																							utilisateurs.prenom_utl,
																							utl_etu_utbm.branche_utbm,
																							CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur
																				FROM job_annonces_etu
																				LEFT JOIN utilisateurs
																				ON job_annonces_etu.id_etu = utilisateurs.id_utilisateur 
																				LEFT JOIN utl_etu_utbm
																				ON job_annonces_etu.id_etu = utl_etu_utbm.id_utilisateur 
																				WHERE id_annonce = '".$annonce['id_annonce']."' AND relation = 'apply'");

  			$buffer .= "Il y a `".$sql->lines."` candidature(s) pour votre annonce :";
  			
  			while($line = $sql->get_row())
  			{
  				$buffer .= "<div class=\"apply_table\">";
  					
  				$buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_1');\">";
  				$buffer .= $line['nom_utilisateur']." (département ".$line['branche_utbm'].")";
  				$buffer .= "</div>";
  					
  				$buffer .= "<div id=\"applicant_1\" class=\"apply_content\">";
  				
  					$usr = new utilisateur($this->db);
  					$usr->load_by_id($line['id_etu']);
  					$userinfo = new userinfo($user, true);
  					$buffer .= $userinfo->buffer;
  				
  				
  				$buffer .= "gnaa";
  				$buffer .= "</div>";

	  			$buffer .= "</div>";
  			}

  			
  				$buffer .= "<div class=\"apply_table\">";
	  					  					
 						$buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_1');\">";
 							$buffer .= "Tatid (LittleDick inc.)";
  					$buffer .= "</div>";
  					
  					$buffer .= "<div id=\"applicant_1\" class=\"apply_content\">";
  						$buffer .= "gnaa";
  					$buffer .= "</div>";
	  					
	  			$buffer .= "</div>";
	  			
	  			/****/

	  			$buffer .= "<div class=\"apply_table\">";
	  				
	  				$buffer .= "<div class=\"apply_title\" onClick=\"javascript:on_off('applicant_2');\">";
		  				$buffer .= "Popol (cf plus haut)";
		  			$buffer .= "</div>";

		  			$buffer .= "<div id=\"applicant_2\" class=\"apply_content\">";
		  				$buffer .= "gnaa";
		  			$buffer .= "</div>";

	  			$buffer .= "</div>";
	  		/**********************************************************************/	
  			$buffer .= "</div>";
  			
  			$buffer .= $annonce['desc'];
  		$buffer .= "</div>";
  		
  		 		  		
		$buffer .= "</div>";
  	
  	return $buffer;
  }
	
}   	
?>