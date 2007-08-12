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


class jobuser_etu extends utilisateur
{
	var $competences =  array();
	var $annonces = array();
	
	function is_jobetu_user()
	{
		return $this->is_in_group('jobetu_etu');
	}	
	
	function load_competences()
	{
		$sql = new requete($this->db, "SELECT id_type FROM job_types_etu WHERE id_utilisateur = $this->id");

		while($line = $sql->get_row())
			    $this->competences[] = $line[0];
	}
	
	function update_competences($new_values)
	{
		$add = array_diff($new_values, $this->competences);
		$del = array_diff($this->competences, $new_values);
		
		foreach ($del as $value)
			$sql = new delete($this->dbrw, "job_types_etu", array("id_type" => $value, "id_utilisateur" => $this->id));
		
		foreach ($add as $value)
			$sql = new insert($this->dbrw, "job_types_etu", array("id_type" => $value, "id_utilisateur" => $this->id));
	}
	
	function load_annonces()
	{
		if(empty($this->competences)) $this->load_competences();
	   // if( is_jobetu_etu() )
      {
      	$sql = new requete($this->db, "SELECT * FROM job_annonces WHERE job_type IN ('".implode(', ', $this->competences)."')");
      	     	
        while($line = $sql->get_row())
			    $this->annonces[] = $line;
	    }
	}
	
	function apply_to_annonce($id_annonce)
	{
		
	}
	
	function add_annonce_box($id_annonce)
	{
  	$annonce = $this->annonces[$id_annonce];
  	
  	$buffer .= "<div class=\"annonce_table\">";
  		
  	$buffer .= "<div class=\"header\" onClick=\"javascript:on_off('annonce_1');\">";
  			$buffer .= "<div class=\"num\">";
  				$buffer .= "n°".$annonce['id_annonce'];
  			$buffer .= "</div>";
			
  			$buffer .= "<div class=\"title\">";
  				$buffer .= $annonce['titre'];
  			$buffer .= "</div>";
  			
  			$buffer .= "<div class=\"icons\">";
  				$buffer .= "<img src=\"../images/actions/info.png\" /> &nbsp;";
  				$buffer .= "<a href=\"board_etu.php?action=reject&id=".$annonce['id_annonce']."\" title=\"Ne plus me proposer\"><img src=\"../images/actions/delete.png\" /></a>";
  			$buffer .= "</div>";
  		$buffer .= "</div>";
  			
  		/** Contenu  ************************************************************/
	  		$buffer .= "<div id=\"annonce_1\" class=\"content\">";
  				$buffer .= "Demandeur : ".$annonce['id_client']."<br />";			
  				$buffer .= "Description : ".$annonce['desc']."<br />";
  				$buffer .= "Nombre de postes : ".$annonce['nb_postes']."<br />";
  				$buffer .= "Date de début : ".$annonce['start_date']."<br />";
  				$buffer .= "Rémunération: ".$annonce['indemnite']."<br />";
  				$buffer .= "Durée : ".$annonce['duree']."<br />";

	  				$frm = new form("apply_1", false, true, "POST");
	  					$frm->add_submit("clic", "Se porter candidat");
	  				$buffer .= "<div onClick=\"javascript:on_off('apply_1');\">" . $frm->buffer . "</div>";
	  				
	  				$buffer .= "<div id=\"apply_1\" style=\"display: none;\" class=\"apply_form\">";
		  				$frm = new form("application_1", "board_etu.php?board_etu.php?action=apply", true, "POST");
		  				$frm->puts("Ajouter un message à votre candidature <i>(facultatif)</i> :<br />");
		  				$frm->add_hidden("id_annonce", $annonce['id_annonce']);
		  				$frm->add_text_area("comment", false, false, 80, 10);
		  				$frm->add_submit("send", "Envoyer la candidature");
	  				$buffer .= $frm->buffer;
	  				
	  				$buffer .= "</div>";
  				  				
  				$buffer .= "</div>";
  		/************************************************************************/			
  		$buffer .= "</div>";
  		
  		 		  		
		$buffer .= "</div>";	
		
		return $buffer;
	}

}

?>
