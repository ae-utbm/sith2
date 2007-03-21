<?php
/*
 * Last Modification 18 april 2006
 *
 * Created by Laurent COLNAT and Julien Etelain
 *
 */
 
class sondage
{
	var $id;
	var $question;
	var $total;
	var $date;
	var $end_date;
	
	var $db;
	var $dbrw;
	
	
	function sondage($db,$dbrw=false)
	{
		
		$this->db = $db;
		$this->dbrw = $dbrw;	
		
	}	
	
	/** Charge un sondage en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id du sondage
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `sdn_sondage`
				WHERE `id_sondage` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function load_lastest ( )
	{
		$req = new requete($this->db, "SELECT * FROM `sdn_sondage` WHERE `date_fin`>=NOW() ORDER BY date_sondage DESC LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}

	function is_lastest( $id )
	{
		if ($this->id != $id)
			return 0;
		else
			return 1;
	}
	
	function _load ( $row )
	{
		$this->id		= $row['id_sondage'];
		$this->question	= $row['question'];
		$this->total		= $row['total_reponses'];
		$this->date		= $row['date_sondage'];
		$this->end_date		= $row['date_fin'];
	}
	
	function new_sondage ( $question, $end_date )
	{
		$this->question = $question;
		$this->end_date = $end_date;
		$this->date = time();
		$this->total = 0;
		
		$sql = new insert ($this->dbrw,
			"sdn_sondage",
			array(
				"question" => $this->question,
				"total_reponses" => $this->total,
				"date_sondage" => date("Y-m-d H:i:s"),
				"date_fin" => date("Y-m-d",$this->end_date)
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
	}

	/** Met � jour un sondage avec les donn�es en param�tre
	 * @param $id id du sondage
	 * @param $question question du sondage
	 * @param $reponses tableau contenant les r�ponses du sondage
	 * @param $total_reponses nombre total de r�ponses actuel
	 * @param $begin_date date de d�but du sondage
	 * @param $end_date date de fin du sondage
	 */

	function update_sondage ($question, $total_reponses, $begin_date, $end_date)
	{
		$this->question = $question;
		$this->end_date = $end_date;
		
		$sql = new update($this->dbrw,
			"sdn_sondage",
			array(
				"question" => $this->question,
				"total_reponses" => $total_reponses,
				"date_sondage" => $begin_date,
				"date_fin" => date("Y-m-d",$this->end_date)
				),array("id_sondage"=>$this->id)
			);
	}
	
	function update_reponse ($reponse , $num)
	{
		$sql = new requete($this->db,"SELECT `nom_reponse` FROM `sdn_reponse` WHERE `id_sondage`='".mysql_real_escape_string($this->id)."' AND `num_reponse`='".mysql_real_escape_string($num)."'");

		if ( $sql->lines == 0 )
			$this->add_reponse($reponse);
		else
			$sql = new update($this->dbrw,
			"sdn_reponse",
			array("nom_reponse" => $reponse),
			array("id_sondage"=>$this->id,"num_reponse"=>$num)
			);		
	}
	
	function add_reponse ( $reponse )
	{
		$sql = new insert ($this->dbrw,
			"sdn_reponse",
			array(
				"id_sondage" => $this->id,
				"nom_reponse" => $reponse,
				"nb_reponse" => 0
				)
			);
	}
	
	function remove_reponse ( $num )
	{
		$sql = new delete($this->dbrw,
			"sdn_reponse",
			array(
				"id_sondage" => $this->id,
				"num_reponse" => $num
				)
			);
	}
	
	function get_reponses()
	{
		$sql = new requete($this->db, "SELECT `num_reponse`,`nom_reponse` " .
						"FROM `sdn_reponse` " .
						"WHERE id_sondage='".mysql_escape_string($this->id)."' " .
						"ORDER BY `num_reponse`");
		
		$reponses = array();
		
		while ( list($id,$nom) = $sql->get_row() )
			$reponses[$id] = $nom;
			
		return $reponses;
	}
	
	function get_results()
	{
		$sql = new requete($this->db, "SELECT `num_reponse`,`nom_reponse`,nb_reponse " .
						"FROM `sdn_reponse` " .
						"WHERE id_sondage='".mysql_escape_string($this->id)."' " .
						"ORDER BY `num_reponse`");
		
		$resultats = array();
		
		while ( list($id,$nom,$nb) = $sql->get_row() )
			$resultats[$id] = array($nom,$nb);
			
		return $resultats;
	}
	function a_repondu ( $id_utilisateur )
	{
		
		$sql = new requete($this->db, "SELECT * " .
						"FROM `sdn_a_repondu` " .
						"WHERE id_sondage='".mysql_escape_string($this->id)."' " .
						"AND id_utilisateur='".mysql_escape_string($id_utilisateur)."'");

		return ($sql->lines == 1);
		
	}

	/**
	 * Definit la réponse au sondage pour un utilisateur
	 * Si le numéro de reponse est invalide, le vote est compté comme blanc
	 */
	function repondre ( $id_utilisateur, $numrep )
	{		
		
		if ( $this->a_repondu($id_utilisateur) ) return;

		$sql = new insert ($this->dbrw,
			"sdn_a_repondu",
			array(
				"id_sondage" => $this->id,
				"id_utilisateur" => $id_utilisateur,
				"date_reponse" => date("Y-m-d H:i:s")
				)
			);
		
		if ( $numrep ) // vote pas blanc
			$sql = new requete($this->dbrw, "UPDATE `sdn_reponse` " .
					"SET `nb_reponse`=`nb_reponse`+1 " .
					"WHERE id_sondage='".mysql_escape_string($this->id)."' " .
					"AND num_reponse='".mysql_escape_string($numrep)."'");
	
		$sql = new requete($this->dbrw, "UPDATE `sdn_sondage` " .
					"SET `total_reponses`=`total_reponses`+1 " .
					"WHERE id_sondage='".mysql_escape_string($this->id)."'");
		$this->total++;
	}
}
 
 
 
?>
