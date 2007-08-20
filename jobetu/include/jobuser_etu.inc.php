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
		if(!empty($this->annonces)) $this->annonces = null;
	   // if( is_jobetu_etu() )
      {
      	$sql = new requete($this->db, "SELECT id_annonce FROM job_annonces WHERE job_type IN ('".implode('\', \'', $this->competences)."')", false);
      	     	
        while($line = $sql->get_row())
			    $this->annonces[] = $line[0];
	    }
	}

}

?>
