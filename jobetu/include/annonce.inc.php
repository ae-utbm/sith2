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


class annonce extends stdentity
{	
		var $id;
		var $id_client;
		var $winner;
		var $titre;
		var $job_type;
		var $desc;
		var $profil;
		var $start_date;
		var $duree;
		var $nb_postes;
		var $indemnite;
		var $ville;
		var $type_contrat;
		
		var $liste;
  
  function load_by_id($id)
  {
  }
  	
  function liste_annonce($condition = "all")
  {
  	$liste = array();
  	/*
  	//ah ben non, case ne prend qu une valeur de type scalaire
  	switch($condition)
  	{
  		case "pourvu":
  			$sql_condition = " WHERE `etu_selected` IS NOT NULL";
  			break;
  		case "societe":
  			$sql_condition = " WHERE client == societe";
  			break;
  		case "particulier":
  			$sql_condition = " WHERE client == particulier";
  			break;
  		case (instanceof jobuser_client):
  			$sql_condition = " WHERE client = " . $condition->id;
  			break;
  		case (instanceof jobuser_etu):
  			$sql_condition = " WHERE etu_selected = " . $condition->id;
  			break;
  	}
  	
  	$sql = new requete($site->db, "SELECT* FROM job_annonces" . $condition)
	*/
  }

  function is_provided()
  {
  }

  function get_client()
  {
  }

  function get_etu()
  {
  }

  function apply_to()
  {
  }

  /**
   * Ajoute une nouvelle annonce
   * @return l'id de l'annonce (+ chargement des infos dans l'objet courant)
   * @param $client objet jobuser_client
   * @param $titre titre de l'annonce 
   */
  function add($client, $titre, $job_type, $desc, $profil, $divers = null, $start_date = null, $duree = null, $divers = null, $nb_postes = 1, $indemnite = null, $ville = null, $type_contrat = null )
  {
		if(!($client instanceof jobuser_client))	return -1;
	 	
		$this->id_client = $client->id;
		$this->id_select_etu = null;
		$this->titre = $titre;
		$this->job_type	= $job_type;
		$this->desc = $desc;
		$this->divers = $divers;
		$this->profil = $profil;
		$this->start_date = $start_date;
		$this->duree = $duree;
		$this->nb_postes = $nb_postes;
		$this->indemnite = $indemnite;
		$this->ville = $ville;
		$this->type_contrat = $type_contrat;
		//print_r($this);
		$sql = new insert($this->dbrw,
											"job_annonces",
											array(
														"id_client" => $this->id_client,
														"id_select_etu" => $this->id_select_etu,
														"titre" => $this->titre,
														"job_type" => $this->job_type,
														"desc" => $this->desc,
														"divers" => $this->divers,
														"profil" => $this->profil,
														"start_date" => $this->start_date,
														"duree" => $this->duree,
														"nb_postes" => $this->nb_postes,
														"indemnite" => $this->indemnite,
														"ville" => $this->ville,
														"type_contrat" => $this->type_contrat,
														"closed" => 0										
											)
											);
		if($sql)
			$this->id = $sql->get_id();
		else
			$this->id = null;

		return $this->id;
  }

}

?>
