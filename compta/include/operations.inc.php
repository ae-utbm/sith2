<?php
/**
 * @file
 */
 

$modes_operation = array(2=>"Espèces",1=>"Chèque",3=>"Virement",4=>"Carte Bancaire");


/**
 * Opération comptable
 */
class operation extends stdentity
{
	
	var $id_classeur;
	var $num;
	var $id_libelle;

	/* type d'opération*/
	var $id_opclb;
	var $id_opstd;
	
	/* étudiant éventuellement remboursé */
	var $id_utilisateur;
	
	/* opération liée, pour les opération jumelles (de compte bancaire à compte bancaire)*/
	var $id_op_liee;
	
	/* bénéficiaire : asso, entreprise ou compte bancaire*/
	var $id_asso;
	var $id_ent;
	var $id_cptasso;
	
	/* informations sur l'opération */
	var $montant;
	var $date;
	var $commentaire;
	var $effectue;
	
	var $mode;
	var $num_cheque;
	

		
	function load_by_id ( $id_op )
	{
		$req = new requete ($this->db, "SELECT *
							FROM `cpta_operation`
							WHERE id_op='".intval($id_op)."'");		
		
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
	
	function _load ( $row )
	{
    $this->id = $row['id_op'];
    $this->id_classeur = $row['id_classeur'];
    $this->num = $row['num_op'];
    $this->id_opclb = $row['id_opclb'];			
    $this->id_opstd = $row['id_opstd'];		
    $this->id_utilisateur = $row['id_utilisateur'];
    $this->id_op_liee = $row['id_op_liee'];
    $this->id_asso = $row['id_asso'];
    $this->id_ent = $row['id_ent'];			
    $this->id_cptasso = $row['id_cptasso'];		
    $this->montant = $row['montant_op'];
    $this->date = strtotime($row['date_op']);
    $this->commentaire = $row['commentaire_op'];
    $this->effectue = $row['op_effctue'];	
    
    $this->mode = $row['mode_op'];
    $this->num_cheque = $row['num_cheque_op'];	
    $this->id_libelle = $row['id_libelle'];	
	}
	
	
	function add_op ( $id_classeur,
					$id_opclb, $id_opstd,
					$id_utilisateur,
					$id_asso, $id_ent, $id_cptasso,
					$montant, $date, $commentaire, $effectue, 
					$mode, $num_cheque,
					$id_libelle = null
					)
	{
	
		$this->id_classeur = $id_classeur;
		$this->id_opclb = $id_opclb;
		$this->id_opstd = $id_opstd;
		$this->id_utilisateur = $id_utilisateur;
		$this->id_asso = $id_asso;
		$this->id_ent = $id_ent?$id_ent:null;
		$this->id_cptasso = $id_cptasso;
		$this->montant = $montant;
		$this->date = $date;
		$this->commentaire = $commentaire;
		$this->effectue = $effectue;
		$this->mode = $mode;
		$this->num_cheque = $num_cheque;	
		$this->id_libelle = $id_libelle;
			 
		$sql = new requete ( $this->db, "SELECT MAX(`num_op`) FROM `cpta_operation` " .
				"WHERE `id_classeur`='".intval($this->id_classeur)."'" );
		
		if ( $sql->lines == 1 )
			list($pnum) = $sql->get_row();
		else
			$pnum = 0;

		$this->num = $pnum + 1;

		$sql = new insert ($this->dbrw,
			"cpta_operation",
			array(
				"id_classeur" => $this->id_classeur,
				"id_opclb" => $this->id_opclb,
				"id_opstd" => $this->id_opstd,
				"id_utilisateur" => $this->id_utilisateur,
				"id_asso" => $this->id_asso,
				"id_ent" => $this->id_ent,
				"id_cptasso" => $this->id_cptasso,
				"num_op" => $this->num,
				"montant_op" => $this->montant,
				"date_op" => date("Y-m-d",$this->date),
				"commentaire_op"=>$this->commentaire,
				"op_effctue" => $this->effectue,
				"mode_op" => $this->mode,
				"num_cheque_op" => $this->num_cheque,
				"id_libelle"=>$this->id_libelle
				
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;

	}
	
	/**
	 * @private
	 */
	function _link ( $id_op )
	{
		$this->id_op_liee = $id_op;
		$req = new update($this->dbrw,
					"cpta_operation",
					array("id_op_liee"=>$id_op),
					array("id_op"=>$this->id));		
	}
	
	/**
	 * Lie une opération avec une autre (opérations jumelles)
	 * @param $op instance deoperation à liér
	 */
	function link_op ( $op )
	{
		if ( $op->id < 1 || $this->id < 1 ) return;
		$op->_link($this->id)	;
		$this->_link($op->id);
	}
	
	/**
	 * Supprime l'opération (et l'opération jumelle)
	 */
	function delete ( )
	{
		if ( $this->id_op_liee )
		{
			$req = new delete($this->dbrw,
						"cpta_operation",
						array("id_op"=>$this->id_op_liee));	
		}
		$req = new delete($this->dbrw,
					"cpta_operation",
					array("id_op"=>$this->id));		
					
    $req = new update($this->dbrw,"cpta_facture",array("id_op"=>null),array("id_op"=>$this->id));
	}
	
	/**
	 * Marque comme faite l'opération (et l'opération jumelle)
	 * @param $done fait(1) ou non fait(0)
	 */	
	function mark_done ( $done = 1 )
	{
		if ( $this->id_op_liee )
		{
			$req = new update($this->dbrw,
						"cpta_operation",
						array("op_effctue"=>$done),
						array("id_op"=>$this->id_op_liee));	
		}
		$req = new update($this->dbrw,
					"cpta_operation",
					array("op_effctue"=>$done),
					array("id_op"=>$this->id));	
		$this->effectue=$done;
	}
	
	function save ( $id_opclb, $id_opstd,
					$id_utilisateur,
					$id_asso, $id_ent, $id_cptasso,
					$montant, $date, $commentaire, $effectue, 
					$mode, $num_cheque,
					$id_libelle = null
					)
	{
	
		$this->id_opclb = $id_opclb;
		$this->id_opstd = $id_opstd;
		$this->id_utilisateur = $id_utilisateur;
		$this->id_asso = $id_asso;
		$this->id_ent = $id_ent;
		$this->id_cptasso = $id_cptasso;
		$this->montant = $montant;
		$this->date = $date;
		$this->commentaire = $commentaire;
		$this->effectue = $effectue;

		$this->mode = $mode;
		$this->num_cheque = $num_cheque;	
		
		$this->id_libelle = $id_libelle;

		$sql = new update ($this->dbrw,
			"cpta_operation",
			array(
				"id_opclb" => $this->id_opclb,
				"id_opstd" => $this->id_opstd,
				"id_utilisateur" => $this->id_utilisateur,
				"id_asso" => $this->id_asso,
				"id_ent" => $this->id_ent,
				"id_cptasso" => $this->id_cptasso,
				"montant_op" => $this->montant,
				"date_op" => date("Y-m-d",$this->date),
				"commentaire_op"=>$this->commentaire,
				"op_effctue" => $this->effectue,
				"mode_op" => $this->mode,
				"num_cheque_op" => $this->num_cheque,
				"id_libelle"=>$this->id_libelle
				
				),
			array(
				"id_op" => $this->id
				)
			);
			
		if ( $this->id_op_liee ) // On met à jour l'opération liée
		{
			$req = new update($this->dbrw,
						"cpta_operation",
						array(
							"montant_op" => $this->montant,
							"date_op" => date("Y-m-d",$this->date),
							"mode_op" => $this->mode,
							"num_cheque_op" => $this->num_cheque,
							"op_effctue" => $this->effectue
						),
						array("id_op"=>$this->id_op_liee));	
		}

	}
	
	function set_libelle($id_libelle)
	{
		$this->id_libelle = $id_libelle;
		
		$sql = new update ($this->dbrw,
			"cpta_operation",
      array("id_libelle" => $this->id_libelle),
			array("id_op" => $this->id)
			);
	}
	
	
}


?>
