<?php


/**
 * Classe permettant l'edition de factures
 *
 */
class efact
{
  /** id de la facture, sert de numéro de facture */
	var $id;
	
	/** Classeur virtuel de compta où la facture est rangée */
	var $id_classeur;
	
	/** Activité emettant la facture */
	var $id_asso;
	
	/** Raison sociale de la personne facturée */
	var $nom_facture;
	
	/** Adresse (siège social) de la personne factuée */
	var $adresse_facture;
	
	/** Date d'emission de la facture */
	var $date;
	
	/** Titre de la facture */
	var $titre;
	
	/** Montant total de la facture (calculé, pour optimisation) */
  var $montant;
  
  /** Operation de compta liée (peut être NULL) */
  var $id_op;
  
	var $db;
	var $dbrw;
	
	function efact ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	
		$this->id = null;
	}
	
	
	/**
	 * Ajoute une ligne à la facture
	 * @param $prix_unit Prix unitaire
	 * @param $quantite Quantité
	 * @param $designation Designation
	 */
  function ajoute_ligne ( $prix_unit, $quantite, $designation )
	{
	
	
	}
	
}



?>