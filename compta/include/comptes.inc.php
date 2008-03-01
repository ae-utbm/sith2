<?php

/* Copyright 2005,2006
 * - Julien Etelain <julien CHEZ pmad POINT net>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

/** 
 * @file
 */


require_once($topdir . "include/entities/asso.inc.php");

/**
 * Compte bancaire
 * @ingroup compta
 */
class compte_bancaire extends stdentity
{
  var $nom;
  
  var $solde;
  var $date_releve;
  var $num;

	/** Charge un compte bancaire en fonction de son id
	 * @param $id Id du compte bancaire
	 */
	function load_by_id ( $id_cptbc )
	{
		$req = new requete ($this->db, "SELECT * FROM `cpta_cpbancaire`
							WHERE id_cptbc='".intval($id_cptbc)."'");
		
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
		$this->id = $row['id_cptbc'];
		$this->nom = $row['nom_cptbc'];
		
		$this->solde = $row['solde_cptbc'];		
		$this->date_releve = is_null($row['date_releve_cptbc'])?null:strtotime($row['date_releve_cptbc']);
		$this->num = $row['num_cptbc'];		
	}
	
  function create ( $nom, $num )
  {
    $this->nom = $nom;
    $this->num = compte_bancaire::standardize_account_number($num);
    $this->solde = null;
    $this->date_releve = null;
    

    $req = new insert ($this->dbrw,
		       "cpta_cpbancaire",
		       array(
		         "nom_cptbc" => $this->nom,
		         "num_cptbc" => $this->num,
		         "solde_cptbc" => $this->solde,
		         "date_releve_cptbc" => $this->date_releve
		       ));

    if (!$req)
      return false;

    $this->id = $req->get_id();

    return true;
  }
  
  function update ( $nom, $num )
  {
    $this->nom = $nom;
    $this->num = compte_bancaire::standardize_account_number($num);
    
    $req = new update ($this->dbrw,
		       "cpta_cpbancaire",
		       array(
		         "nom_cptbc" => $this->nom,
		         "num_cptbc" => $this->num
		       ),
		       array("id_cptbc" => $this->id));

    if ( !$req )
      return false;

    return true;
  }
  
  function import_csv_progeliance ( $data )
  {
    $lines = explode("\n",$data);  
    
    $ignore_before = $this->date_releve;
    
    ereg("^Solde au;([0-9\/]*)$",$lines[3],$regs);
    $this->date_releve=datetime_to_timestamp($regs[1]);
    
    ereg("^Solde;([0-9\, ]*);EUR$",$lines[4],$regs);
    $this->solde=get_prix($regs[1]);
    
    $req = new update ($this->dbrw,
		       "cpta_cpbancaire",
		       array(
		         "solde_cptbc" => $this->solde,
		         "date_releve_cptbc" => date("Y-m-d",$this->date_releve)
		       ),
		       array("id_cptbc" => $this->id));
        
    $row = null;
    
    for ($i=7;$i<count($lines);$i++)
    {
      $cols = explode(";",$lines[$i]);
      
      if ( count($cols) == 7 )
      {
        if ( !is_null($row) )
          $req = new insert ($this->dbrw,"cpta_cpbancaire_lignes",$row);
        
        $time = datetime_to_timestamp($cols[0]);
        
        if ( is_null($ignore_before) || $ignore_before < $time )
          $row=array(
            "id_cptbc"=>$this->id, 
            "date_ligne_cptbc"=>date("Y-m-d",$time),
            "date_valeur_ligne_cptbc"=>date("Y-m-d",datetime_to_timestamp($cols[5])),
            "libelle_ligne_cptbc"=>trim($cols[1]),
            "montant_ligne_cptbc"=>$cols[2]?get_prix($cols[2]):get_prix($cols[3]),
            "devise_ligne_cptbc"=>$cols[4],
            "libbanc_ligne_cptbc"=>trim($cols[6])
          );  
         else
           $row=null;
      }
      elseif ( !is_null($row) )
      {
        if ( isset($row["commentaire_ligne_cptbc"]) )
          $row["commentaire_ligne_cptbc"] .= "\n".trim($cols[1]);
        else
          $row["commentaire_ligne_cptbc"] = trim($cols[1]);
      }
    }
    
    if ( !is_null($row) )
      $req = new insert ($this->dbrw,"cpta_cpbancaire_lignes",$row);
	}
	
	static function standardize_account_number ( $num )
	{
    return ereg_replace("[^0-9]","",$num); 
	}
	
}

/**
 * Compte association (associé à un compte bancaire)
 * @ingroup compta
 */
class compte_asso extends stdentity
{
	var $id_asso;
  var $id_cptbc;
  var $nom;



	/** Charge un compte association en fonction de son id
	 * @param $id Id du compte association
	 */
	function load_by_id ( $id )
	{
		$req = new requete ($this->db, "SELECT *
							FROM `cpta_cpasso` " .
							"INNER JOIN `asso` ON `asso`.`id_asso`=`cpta_cpasso`.`id_asso`
							WHERE `cpta_cpasso`.`id_cptasso`='".intval($id)."'");
							
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
    $this->id = $row['id_cptasso'];
    $this->id_asso = $row['id_asso'];
    $this->id_cptbc = $row['id_cptbc'];
    $this->nom = $row['nom_asso'];
  }


	/** Ajoute un compte association
	 * @param $id_asso Id de l'association
	 * @param $id_cptbc Id du compte bancaire
	 */
	function ajouter ( $id_asso, $id_cptbc )
	{
		$this->id_asso = $id_asso;
		$this->id_cptbc = $id_cptbc;

		$dbrw = new mysqlae ('rw');
		$req = new insert ($dbrw,
			"cpta_cpasso",
			array(
				"id_asso" => $this->id_asso,
				"id_cptbc" => $this->id_cptbc
				)
			);

		if ( !$req )
			return false;

		$this->id = $req->get_id();

		return true;
	}

}

/**
 * Classeur de compta (relatif à un seul compte association)
 * @ingroup compta
 */
class classeur_compta extends stdentity /* table: cpta_classeur */
{
		var $id_cptasso;	var $date_debut_classeur;	var $date_fin_classeur;	var $nom;	var $ferme; // ENUM('0','1')


	/** Charge un classeur en fonction de son id
	 * @param $id Id du classeur
	 */
 	function load_by_id ( $id_classeur )
	{
		$req = new requete ($this->db, "SELECT *
							FROM `cpta_classeur`
							WHERE id_classeur='".intval($id_classeur)."'");

		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
	
	/** Charge le classeur ouvert d'un compte association
	 * @param $id_cptasso Id du compte association
	 */	
 	function load_opened ( $id_cptasso, $not=-1 )
	{
		$req = new requete ($this->db, "SELECT *
							FROM `cpta_classeur`
							WHERE id_cptasso='".intval($id_cptasso)."' AND ferme='0' AND id_classeur!='$not'
							ORDER BY `date_debut_classeur` DESC
							LIMIT 1");

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
		$this->id = $row['id_classeur'];
		$this->id_cptasso = $row['id_cptasso'];
		$this->date_debut_classeur = strtotime($row['date_debut_classeur']);
		$this->date_fin_classeur = strtotime($row['date_fin_classeur']);
		$this->nom = $row['nom_classeur'];
		$this->ferme = $row['ferme'];
	}


 	function ajouter ( $id_cptasso, $date_debut_classeur,
 						$date_fin_classeur, $nom_classeur )
	{
		$this->id_cptasso = $id_cptasso;
		$this->date_debut_classeur = $date_debut_classeur;
		$this->date_fin_classeur = $date_fin_classeur;
		$this->nom = $nom_classeur;
		$this->ferme = false;


		$req = new insert ($this->dbrw,
			"cpta_classeur",
			array(
				"id_cptasso" => $this->id_cptasso,
				"date_debut_classeur" => date("Y-m-d",$this->date_debut_classeur),
				"date_fin_classeur" => date("Y-m-d",$this->date_fin_classeur),
				"nom_classeur" => $this->nom,
				"ferme" => $this->ferme
				)
			);

		if ( !$req )
			return false;

		$this->id = $req->get_id();

		return true;
	}

 	function update ( $date_debut_classeur, $date_fin_classeur, $nom_classeur )
	{
	 
		$this->date_debut_classeur = $date_debut_classeur;
		$this->date_fin_classeur = $date_fin_classeur;
		$this->nom = $nom_classeur;

		$req = new update ($this->dbrw,
			"cpta_classeur",
			array(
				"date_debut_classeur" => date("Y-m-d",$this->date_debut_classeur),
				"date_fin_classeur" => date("Y-m-d",$this->date_fin_classeur),
				"nom_classeur" => $this->nom
				),
			array(
				"id_classeur"=>$this->id
				)
			);
	}

	/** Ferme le classeur
	 * @param $ferme Etat du fermeture
	 */
	function fermer($ferme=true)
	{
		$this->ferme = $ferme;
		
		$req = new update ($this->dbrw,
			"cpta_classeur",
			array(
				"ferme" => $this->ferme
				),
			array(
				"id_classeur"=>$this->id
				)
			);
		
	}

}

?>