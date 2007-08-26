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

require_once("jobuser_etu.inc.php");

class annonce extends stdentity
{	
	var $id;
	var $id_client;
	var $nom_client;
	var $winner;
	var $titre;
	var $job_type;
	var $desc;
	var $profil;
	var $divers;
	var $start_date;
	var $duree;
	var $nb_postes;
	var $indemnite;
	var $ville;
	var $type_contrat;
	
	var $applicants;
	var $applicants_fullobj;
		
  function load_by_id($id)
  {
  	$sql = new requete($this->db, "SELECT `job_annonces`.*, 
																		DATE_FORMAT(`job_annonces`.`start_date`, '%e-%c-%Y') as `s_date`,
																		CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_client` 
																		FROM `job_annonces` 
																		LEFT JOIN `utilisateurs`
																		ON `job_annonces`.`id_client` = `utilisateurs`.`id_utilisateur`
																		WHERE `id_annonce` = $id LIMIT 1");
  	$line = $sql->get_row();
  	
  	$this->id = $id;
  	$this->id_client = $line['id_client'];
  	$this->nom_client = $line['nom_client'];
  	$this->winner = $line['id_select_etu'];
  	$this->titre = $line['titre'];
  	$this->job_type = $line['job_type'];
  	$this->desc = $line['desc'];
  	$this->profil = $line['profil'];
  	$this->divers = $line['divers'];
  	$this->start_date = $line['s_date'];
  	$this->duree = $line['duree'];
  	$this->nb_postes = $line['nb_postes'];
  	$this->indemnite = $line['indemnite'];
  	$this->ville = $line['ville'];
  	$this->type_contrat = $line['type_contrat'];

  }

  function load_applicants()
  {
  	$this->applicants = array();
  	
  	$sql = new requete($this->db, "SELECT `id_etu`, `comment` FROM `job_annonces_etu` WHERE `id_annonce` = $this->id AND `relation` = 'apply'");
  	while($line = $sql->get_row())
  		$this->applicants[] = $line;
  		
  	return count($this->applicants);  	
  }
  
  function load_applicants_fullobj()
  {
  	$this->applicants = array();
  	 
  	$sql = new requete($this->db, "SELECT `id_etu`, `comment` FROM `job_annonces_etu` WHERE `id_annonce` = $this->id AND `relation` = 'apply'");
  	while($line = $sql->get_row())
  	{
  		$this->applicants[] = $line;
  		
  		$etu = new jobuser_etu($this->db);
  		$etu->load_by_id($line['id_etu']);
  		$this->applicants_fullobj[] = $etu;
  	}

  	return count($this->applicants);
  }

  function is_provided()
  {
  	return $winner;
  }

  function get_client()
  {
  	return $id_client;
  }

  function apply_to($etu, $comment = null)
  {
  	if( !($etu instanceof jobuser_etu) ) exit("NIET !");
  	
  	$sql = new insert($this->dbrw, 
  										"job_annonces_etu",
  										array(
  											"id_annonce" => $this->id,
  											"id_etu" => $etu->id,
  											"relation" => "apply",
  											"comment" => mysql_real_escape_string($comment)
  											)
  										);
  	
  	if($sql)
			return $sql->get_id();
		else
			return false;
  }
  
  function reject($etu)
  {
  	if( !($etu instanceof jobuser_etu) ) exit("NIET !");
  	
  	$sql = new insert($this->dbrw, 
  										"job_annonces_etu",
  										array(
  											"id_annonce" => $this->id,
  											"id_etu" => $etu->id,
  											"relation" => "reject",
  											"comment" => null
  											)
  										);
  	
  	if($sql)
			return true;
		else
			return false;
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
		$this->titre = mysql_real_escape_string($titre);
		$this->job_type	= $job_type;
		$this->desc = mysql_real_escape_string($desc);
		$this->divers = mysql_real_escape_string($divers);
		$this->profil = mysql_real_escape_string($profil);
		$this->start_date = $start_date;
		$this->duree = mysql_real_escape_string($duree);
		$this->nb_postes = mysql_real_escape_string($nb_postes);
		$this->indemnite = mysql_real_escape_string($indemnite);
		$this->ville = $ville;
		$this->type_contrat = $type_contrat;

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
			$this->id = false;

		return $this->id;
  }

}

?>
