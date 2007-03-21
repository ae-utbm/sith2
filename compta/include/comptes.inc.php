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


require_once($topdir . "include/assoclub.inc.php");

/**
 * Compte bancaire
 */
class compte_bancaire /* table: cpta_cpbancaire */
{
  var $id;
  var $nom;

  var $db;
  var $dbrw;

  function compte_bancaire ( $db, $dbrw = false)
  {
    $this->db = $db;
    $this->dbrw = $dbrw;

  }

	/** Charge un compte bancaire en fonction de son id
	 * @param $id Id du compte bancaire
	 */
	function load_by_id ( $id_cptbc )
	{
		$req = new requete ($this->db, "SELECT `nom_cptbc` FROM `cpta_cpbancaire`
							WHERE id_cptbc='".intval($id_cptbc)."'");
		if ( $req->lines < 1 )
			$this->id_cptbc = -1;
		else
		{
			$row = $req->get_row();
			$this->id = intval($id_cptbc);
			$this->nom = $row['nom_cptbc'];
		}
	}
	
	
  function modifier_nom ($nom)
  {
    $this->nom = $nom;

    $req = new update ($this->dbrw,
		       "cpta_cpbancaire",
		       array("nom_cptbc" => $nom),
		       array("id_cptbc" => $this->id));

    if ( !$req )
      return false;

    return true;
  }


  function ajouter ( $nom )
  {
    $this->nom = $nom;


    $req = new insert ($this->dbrw,
		       "cpta_cpbancaire",
		       array("nom_cptbc" => $this->nom));

    if (!$req)
      return false;

    $this->id = $req->get_id();

    return true;
  }
}

/**
 * Compte association (associé à un compte bancaire)
 */
class compte_asso /* table: cpta_cpasso */
{
	var $id;
	var $id_asso;
    var $id_cptbc;
    var $nom;
 	var $db;
	var $dbrw;

	function compte_asso ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;

	}

	/** Charge un compte association en fonction de son id
	 * @param $id Id du compte association
	 */
	function load_by_id ( $id )
	{
		$req = new requete ($this->db, "SELECT `cpta_cpasso`.`id_asso`,`cpta_cpasso`.`id_cptbc`,`asso`.`nom_asso`
							FROM `cpta_cpasso` " .
							"INNER JOIN `asso` ON `asso`.`id_asso`=`cpta_cpasso`.`id_asso`
							WHERE `cpta_cpasso`.`id_cptasso`='".intval($id)."'");

		if ( $req->lines < 1 )
			$this->id = -1;
		else
		{
			$row = $req->get_row();
			$this->id = intval($id);
			$this->id_asso = $row['id_asso'];
			$this->id_cptbc = $row['id_cptbc'];
			$this->nom = $row['nom_asso'];
		}
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
 */
class classeur_compta /* table: cpta_classeur */
{
	var $id;
		var $id_cptasso;	var $date_debut_classeur;	var $date_fin_classeur;	var $nom;	var $ferme; // ENUM('0','1')

 	var $db;
	var $dbrw;

	function classeur_compta ( $db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;

	}

	/** Charge un classeur en fonction de son id
	 * @param $id Id du classeur
	 */
 	function load_by_id ( $id_classeur )
	{
		$req = new requete ($this->db, "SELECT *
							FROM `cpta_classeur`
							WHERE id_classeur='".intval($id_classeur)."'");

		if ( $req->lines < 1 )
			$this->id_classeur = -1;
		else
			$this->_load($req->get_row());
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

		if ( $req->lines < 1 )
			$this->id_classeur = -1;
		else
			$this->_load($req->get_row());
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
