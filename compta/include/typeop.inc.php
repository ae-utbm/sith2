<?php
/**
 * @file
 * Type des opérations de la compta
 */
 
$types_mouvements = array (1 => "Credit",-1 => "Debit",0 => "Pas de mouvement de fonds");
$types_mouvements_reel = array (1 => "Credit",-1 => "Debit");
 
/**
 * Opération comptable = opération selon le plan comptable
 */
class operation_comptable
{
	var $id;
	var $code;
	var $libelle;
	var $type_mouvement;
		
 	var $db;
	var $dbrw;
	
	function operation_comptable ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
		
	} 		
		
	/** Charge le type d'opération comptable par son id
	 * @param $id Id du type d'opération comptable
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `cpta_op_plcptl`
				WHERE `id_opstd` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
		
	}
	
	function load_by_code ( $code )
	{
		$req = new requete($this->db, "SELECT * FROM `cpta_op_plcptl`
				WHERE `code_plan` = '" . mysql_real_escape_string($code) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
		
	}	
	
	function _load ( $row )
	{
		$this->id = $row['id_opstd'];
		$this->code = $row['code_plan'];
		$this->libelle = $row['libelle_plan'];
		$this->type_mouvement = $row['type_mouvement'];
	}
	

	
}

/** 
 * Type d'opération simplifié pour les clubs (relatif au compte association)
 */
class operation_club
{
	var $id;
	var $id_asso;
	var $id_opstd;
	var $libelle;
	var $type_mouvement;
	
 	var $db;
	var $dbrw;
	
	function operation_club ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
		
	} 	
	/** Charge le type d'opération simplifié par son id
	 * @param $id Id du type d'opération
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `cpta_op_clb`
				WHERE `id_opclb` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
		
	}
	
	function load_or_create ( $id_asso, $code_plan, $libelle=null )
	{
		$opstd = new operation_comptable($this->db);
		$opstd->load_by_code($code_plan);
		
		if ( $libelle )
			$req = new requete($this->db, "SELECT * FROM `cpta_op_clb`
				WHERE `id_asso` = '" . mysql_real_escape_string($id_asso) . "' " .
				"AND `id_opstd` = '" . mysql_real_escape_string($opstd->id) . "' " .
				"AND `libelle_opclb` LIKE '".mysql_real_escape_string($libelle)."' 
				LIMIT 1");
		else
			$req = new requete($this->db, "SELECT * FROM `cpta_op_clb`
				WHERE `id_asso` = '" . mysql_real_escape_string($id_asso) . "' " .
				"AND `id_opstd` = '" . mysql_real_escape_string($opstd->id) . "' 
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return;	
		}
		
		if ( !$libelle )
			$libelle = $opstd->libelle;
		
		$this->new_op_pstd ( $id_asso, $opstd->id, $libelle, $opstd->type_mouvement );
	}
	
	function _load ( $row )
	{
		$this->id = $row['id_opclb'];
		$this->id_asso = $row['id_asso'];
		$this->id_opstd = $row['id_opstd']; 
		$this->libelle = $row['libelle_opclb'];
		$this->type_mouvement = $row['type_mouvement'];
	}	
	
	/** Rattache un type d'opération confome au plan comptable au type d'opératiobn simplifié
	 * @param $id_opstd Id du type d'opération comptable
	 */
	function attach ( $id_opstd )
	{
		$req = new update($this->dbrw,
					"cpta_operation",
					array("id_opstd"=>$id_opstd),
					array("id_opclb"=>$this->id));		
		
		$req = new update($this->dbrw,
					"cpta_op_clb",
					array("id_opstd"=>$id_opstd),
					array("id_opclb"=>$this->id));		
		
		
	}
	
	/** Ajoute un nouveau type d'opération sur le compte asso 
	 * @param $id_asso Id de l'association
	 * @param $libelle Libelle de l'opération
	 * @param $type_mouvement Type de mouvement 
	 */
	function new_op ( $id_asso, $libelle, $type_mouvement )
	{
		$this->id_asso = $id_asso;
		$this->libelle = $libelle;
		$this->type_mouvement = $type_mouvement;
		
		$sql = new insert ($this->dbrw,
			"cpta_op_clb",
			array(
				"id_asso" => $this->id_asso,
				"libelle_opclb" => $this->libelle,
				"type_mouvement" => $this->type_mouvement
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
		
		
	}
	
	/** Ajoute un nouveau type d'opération sur le compte asso connaissant l'opération plan comptable
	 * @param $id_asso Id de l'association
	 * @param $id_opstd Id de l'opération comptable
	 * @param $libelle Libelle de l'opération
	 * @param $type_mouvement Type de mouvement (conforme à l'opération du plan comptable)
	 */
	function new_op_pstd ( $id_asso, $id_opstd, $libelle, $type_mouvement )
	{
		$this->id_asso = $id_asso;
		$this->id_opstd = $id_opstd; 
		$this->libelle = $libelle;
		$this->type_mouvement = $type_mouvement;
		
		$sql = new insert ($this->dbrw,
			"cpta_op_clb",
			array(
				"id_asso" => $this->id_asso,
				"id_opstd" => $this->id_opstd,
				"libelle_opclb" => $this->libelle,
				"type_mouvement" => $this->type_mouvement
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
		
		
	}
	
	/** Remplace le type d'opération par un autre (et supprime ce type)
	 * @param $op Instance de operation_club
	 */
	function replace_and_remove ( $op )
	{
		$req = new update($this->dbrw,
					"cpta_operation",
					array("id_opstd"=>$op->id_opstd,"id_opclb"=>$op->id),
					array("id_opclb"=>$this->id));		
		
		$req = new delete($this->dbrw,
					"cpta_op_clb",
					array("id_opclb"=>$this->id));		
		
		
	}
	
	/** Ajoute un nouveau type d'opération sur le compte asso connaissant l'opération plan comptable
	 * @param $id_asso Id de l'association
	 * @param $id_opstd Id de l'opération comptable
	 * @param $libelle Libelle de l'opération
	 * @param $type_mouvement Type de mouvement (conforme à l'opération du plan comptable)
	 */
	function save ( $id_asso, $id_opstd, $libelle, $type_mouvement )
	{
		$this->id_asso = $id_asso;
		$this->id_opstd = $id_opstd; 
		$this->libelle = $libelle;
		$this->type_mouvement = $type_mouvement;
		
		$sql = new update ($this->dbrw,
			"cpta_op_clb",
			array(
				"id_asso" => $this->id_asso,
				"id_opstd" => $this->id_opstd,
				"libelle_opclb" => $this->libelle,
				"type_mouvement" => $this->type_mouvement
				),
			array(
				"id_opclb" => $this->id
				)
			);

		$req = new update($this->dbrw,
					"cpta_operation",
					array("id_opstd"=>$this->id_opstd),
					array("id_opclb"=>$this->id));
		
	}
	
} 
 
 
?>
