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
	var $date_depot;
	var $titre;
	var $id_type;
	var $nom_type;
	var $nom_main_cat;
	var $desc;
	var $profil;
	var $divers;
	var $start_date;
	var $duree;
	var $nb_postes;
	var $indemnite;
	var $ville;
	var $type_contrat;
	var $allow_diff;
	var $tel_client;
	var $closed;
	
	var $applicants;
	var $applicants_fullobj;
		
  function load_by_id($id)
  {
  	$sql = new requete($this->db, "SELECT `job_annonces`.*, 
																		DATE_FORMAT(`job_annonces`.`start_date`, '%e/%c/%Y') as `s_date`,
																		CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_client`,
																		IFNULL(`utilisateurs`.`tel_portable_utl`, `utilisateurs`.`tel_maison_utl`) AS num_client,
																		`job_types`.`nom` as `nom_type`
																		FROM `job_annonces` 
																		LEFT JOIN `utilisateurs`
																		ON `job_annonces`.`id_client` = `utilisateurs`.`id_utilisateur`
																		LEFT JOIN `job_types`
																		ON `job_types`.`id_type` = `job_annonces`.`job_type`
																		WHERE `id_annonce` = $id LIMIT 1");
  	$line = $sql->get_row();
  	$this->id = $id;
  	$this->id_client = $line['id_client'];
  	$this->nom_client = $line['nom_client'];
//  	$this->winner = $line['id_select_etu'];  changeage de méthode pour supporter les postes multiples
  	$this->date_depot = $line['date'];
  	$this->titre = $line['titre'];
  	$this->id_type = $line['job_type'];
  	$this->nom_type = $line['nom_type'];
  	$this->desc = $line['desc'];
  	$this->profil = $line['profil'];
  	$this->divers = $line['divers'];
  	$this->start_date = $line['s_date'];
  	$this->duree = $line['duree'];
  	$this->nb_postes = $line['nb_postes'];
  	$this->indemnite = $line['indemnite'];
  	$this->ville = $line['ville'];
  	$this->type_contrat = $line['type_contrat'];
  	$this->allow_diff = $line['allow_diff'];
  	$this->tel_client = $line['num_client'];
  	$this->closed = $line['closed'];
  	
  	/* Sélection du/des étudiant(s) choisis 'if any' */
		$sql = new requete($this->db, "SELECT id_etu FROM `job_annonces_etu` WHERE id_annonce='$this->id' AND relation='selected'");
		if($sql->lines > 0)
		{
			$this->winner = array();
			while($row = $sql->get_row())
			{
				$this->winner[] = $row[0];
			}
		}
		else
			$this->winner = NULL;
  	
  	/* C'est pas beau mais j'arrive pas à le faire en une requete */
  	$sql = new requete($this->db, "SELECT `job_types`.`nom` FROM `job_annonces` LEFT JOIN `job_types` ON `job_types`.`id_type` = ". ($this->id_type - $this->id_type%100) ."");
		$line = $sql->get_row();
		$this->nom_main_cat = $line['nom'];
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
  	if( $this->winner != NULL && count($this->winner) >= $this->nb_postes )
  		return true;
  	else 
  		return false;
  }
  
  function remaining_positions()
  {
  	return ($this->nb_postes - count($this->winner));
  }
  
  function set_winner($winner, $client)
  {
  	/* if($this->nb_postes > 1 && $this->winner )
  	{
  		$array_winner = explode(";", $this->winner);
  		$array_winner[] = $winner->id;
  		if( count( $array_winner ) <= $this->nb_postes )
  			$sql = new update($this->dbrw, "job_annonces", array( "id_select_etu" => implode(";", $array_winner) ), array("id_annonce" => $this->id) );
  	}
  	else
  		$sql = new update($this->dbrw, "job_annonces", array("id_select_etu" => $winner->id), array("id_annonce" => $this->id) );
  	*/
		if( $this->is_provided() )
			return false;
		else
  		$sql = new insert($this->dbrw, "job_annonces_etu", array("id_annonce" => $this->id, "id_etu" => $winner->id, "relation" => "selected" ));
  	
  	/**
  	 * Envois de mails
  	 */
		$genre_client;
		switch( $client->sexe )
		{
			case 1:
				$genre_client = "M."; break;
			case 2:
				$genre_client = "Mme"; break;
		}
  	$genre_etu;
		switch( $etu->sexe )
		{
			case 1:
				$genre_etu = "M."; break;
			case 2:
				$genre_etu = "Mlle"; break;
		}
		
		$tel = telephone_display($this->tel_client);
  	$text_etu = <<<EOF
		Bonjour,

Nous avons le plaisir de vous annoncer que vous avez été sélectionné par $genre_client $client->prenom $client->nom, client de AE Job Etu, pour son annonce "$this->titre" (numéro $this->id).
Cette personne à été incitée à vous contacter, mais si cela devait tarder anormalement, n'hésitez pas à prendre les devants. 
N° de téléphone : $tel
Pour plus de renseignements, consultez sa fiche Matmatronch : http://ae.utbm.fr/user.php?id_utilisateur=$this->id_client
 	
Nous vous remerçions d'utiliser AE Job Etu et vous souhaitons bon courage pour cette nouvelle mission !

L'équipe AE et les responsables d'AE Job Etu	
EOF;

  	
  	$tel = telephone_display($winner->tel_portable);
		$text_client = <<<EOF
			Bonjour,

Vous venez de sélectionner $genre_etu $winner->prenom $winner->nom afin de répondre à votre annonce "$this->titre" (numéro $this->id).
Nous vous incitons à le contacter si cela n'a pas déjà été fait au $tel afin de convenir des modalités d'exécution du contrat.

Lorsque la prestation sera terminée, n'oubliez pas ne clore l'annonce depuis votre tableau de bord : http://ae.utbm.fr/jobetu/board_client.php

Nous vous remerçions de votre confiance et espérons que votre satisfaction sera totale.

L'équipe AE et les responsables d'AE Job Etu

EOF;

		if(!$this->is_provided())
			$text_client .= "PS: il reste désormais ".$this->remaining_positions()." place(s) disponibles pour votre offre.";
		  	
		$mail_etu = mail($winner->email, utf8_decode("[AE JobEtu] Sélection pour l'annonce n°".$this->id), utf8_decode($text_etu), "From: \"AE Job Etu\" <ae-jobetu@utbm.fr>");
		$mail_client = mail($client->email, utf8_decode("[AE JobEtu] Sélection de $winner->prenom $winner->nom pour l'annonce n°".$this->id), utf8_decode($text_client), "From: \"AE Job Etu\" <ae-jobetu@utbm.fr>");
	
		if($mail_etu && $mail_client)
			return true;
		else 
			return false;
  }
  
  function is_closed()
  {
  	return $this->closed;
  }

  function get_client()
  {
  	return $id_client;
  }
  
  function set_closed($eval = NULL, $comment = NULL)
  {
  	$val = "";
  	$comment = mysql_real_escape_string($comment);
  	
  	switch( mysql_real_escape_string($eval) ) //vu qu'on peut pas mettre de '0' dans les radiobox ...
  	{
  		case "bof":
  			$val = -1; break;
  		case "bleh":
  			$val = 0; break;
  		case "yeah":
  			$val = +1; break;
  	}
  	
  	$sql = new update($this->dbrw, "job_annonces", array("closed" => true), array("id_annonce" => $this->id) );
  	
  	if( $val != NULL || $comment != NULL )
  		$sql2 = new insert($this->dbrw, "job_feedback", array("id_annonce" => $this->id, "note_client" => $val, "avis_client" => $comment) );
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
  											"comment" => $comment 
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
  function add($client, $titre, $job_type, $desc, $profil, $divers = null, $start_date = null, $duree = null, $nb_postes = 1, $indemnite = null, $ville = null, $type_contrat = null, $allow_diff = 0 )
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
		$this->allow_diff = $allow_diff;

		$sql = new insert($this->dbrw,
											"job_annonces",
											array(
														"id_client" => $this->id_client,
														"id_select_etu" => $this->id_select_etu,
														"titre" => $this->titre,
														"date" => date("Y-m-d"),
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
														"allow_diff" => $this->allow_diff,
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
