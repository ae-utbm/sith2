<?php
/*
 * Created on 22 d�c. 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class budget
{
	var $id;
	var $id_classeur;
	var $nom;
	var $total;
	var $date;
	var $valide;
	
	var $db;
	var $dbrw;
	
	function budget ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
		$this->id = -1;
	}
	
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `cpta_budget`
				WHERE `id_budget` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	

	}
	
	function _load ( $row )
	{
		$this->id = $row['id_budget'];
		$this->id_classeur = $row['id_classeur'];
		$this->nom = $row['nom_budget'];
		$this->total = $row['total_budget'];
		$this->date = strtotime($row['date_budget']);
		$this->valide = $row['valide_budget'];
	}
	
	function new_budget ( $id_classeur, $nom )
	{
		
		$this->id_classeur = $id_classeur;
		$this->nom = $nom;
		$this->date = time();
		$this->valide = 0;
		$this->total = 0;
		
		$sql = new insert ($this->dbrw,
			"cpta_budget",
			array(
				"id_classeur" => $this->id_classeur,
				"nom_budget" => $this->nom,
				"total_budget" => $this->total,
				"date_budget"=> date("Y-m-d H:i",$this->date),
				"valide_budget"=> $this->valide
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
		
	}
	
	function add_line ( $id_opclb, $montant, $description )
	{
		$sql = new insert ($this->dbrw,
			"cpta_ligne_budget",
			array(
				"id_budget" => $this->id,
				"id_opclb" => $id_opclb,
				"montant_ligne" => $montant,
				"description_ligne"=> $description
				)
			);
	}
	
	function remove_line ( $num )
	{
		$sql = new delete($this->dbrw,
			"cpta_ligne_budget",
			array(
				"id_budget" => $this->id,
				"num_lignebudget" => $num
				));
	}

}





?>
