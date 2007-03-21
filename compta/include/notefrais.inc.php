<?php

/**
 * Classe permettant l'edition de notes de frais
 *
 */
class notefrais
{
	var $id;
	
	/** Classeur virtuel de compta où la note est rangée (NULL autorisé) */
	var $id_classeur; 
	
	/** Association imputée */
	var $id_asso;
	
	/** Bénévole emettant la note de frais */
	var $id_utilisateur;
	
	/** Date d'emission de la note de frais */
	var $date;
	
	/** Commentaire */
	var $commentaire;
	
	/** Total (en centimes) (calculé, pour optimisation) */
	var $total;
	
  /** Avance (en centimes) */
	var $avance;
	
	/** Total à payer (calculé, pour optimisation) */
	var $total_payer;	
	
	var $db;
	var $dbrw;
	
	function notefrais ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
		$this->id = null;
	}
	
	
}



?>