<?php

/** @todo à implémenter *********************************/


/** @file

 */

/** 
 * @addtogroup comptoirs
 * @{
 */ 
 
/**
 * Classe gérant un compte association
 */
class assocpt
{
	var $id;
		
	var $montant_ventes;
	var $montant_rechargements;
	
	var $db;
	var $dbrw;
	
	function assocpt($db,$dbrw=false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
	}
	
	function load_by_id ( $id )
	{
		
		$req = new requete($this->db,"SELECT * FROM cpt_association WHERE id_assocpt='".intval($id)."'");
		
		if ( $req->lines == 1 )
		{
			$row = $req->get_row();
			$this->id = $row['id_assocpt'];
			$this->montant_ventes = $row['montant_ventes_asso'];
			$this->montant_rechargements = $row['montant_rechargements_asso'];
		}
		else
			$this->id = -1;
		
	}
	
	function add ( $id )
	{
		$req = new insert($this->dbrw,"cpt_association",array("id_assocpt"=>$id));
	}
	
	
} 
 
 
 
?>