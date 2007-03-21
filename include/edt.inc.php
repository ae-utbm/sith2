<?php
/**

 @file Gestion des emplois du temps

 @author COLNAT Laurent <laurent DOT colnat AT utbm DOT fr>
 
 **/

$topdir = "../";

require_once ($topdir . "include/cts/edt.inc.php");

class edt
{
	var $id;
	var $id_utilisateur;
	var $semestre;
	var $branche;
	var $date;

	var $db;
	var $dbrw;
	
	/* Constructeur */
	function edt ($db, $dbrw = false)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id  = -1;
	}

	function load_by_id ($id)
	{
		$req = new requete($this->db,"SELECT `id_edt` FROM `edt` WHERE `id_edt` = '" . mysql_real_escape_string($id) . "' LIMIT 1");

		if (!$req)
		{
			$this->id = -1;
			return false;
		}
		elseif ($req->lines == 1)
			$this->_load($req->get_row());
	}

	function _check_if_exists($semestre, $branche, $id_utl)
	{

		$req = new requete($this->db,"SELECT `id_edt` FROM `edt` WHERE `id_utilisateur` = '" .mysql_real_escape_string($id_utl)."' AND `branche` = '" .mysql_real_escape_string($branche) . "' AND `semestre` = '" . mysql_real_escape_string($semestre) . "' LIMIT 1");

		if ($req)
		{
			if ($req->lines == 1)
			{
				$this->_load($req->get_row());
				return true;
			}
			else
			{
				$this->id = -1;
				return false;
			}
		}
		else
			return false;
	}

	function load_lastest ($id_utl)
	{
		
		$req = new requete($this->db, "SELECT * FROM `edt` WHERE `id_utilisateur`='".mysql_real_escape_string($id_utl)."' ORDER BY `date` DESC LIMIT 1");	

		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}

	function _load($row)
	{
		$this->id = $row['id_edt'];
		$this->branche = $row['branche'];
		$this->semestre = $row['semestre'];
		$this->date = $row['date'];
	}

	function add ($branche, $semestre, $id_utl)
	{
		if(!$this->_check_if_exists($semestre, $branche, $id_utl))
		{
			$this->id_utilisateur = $id_utl;
			$this->branche = $branche;
			$this->semestre = $semestre;
			$this->date = time();
			$req = new insert($this->dbrw,"edt",array(
									"id_utilisateur"=>$this->id_utilisateur,
									"branche"=>$this->branche,
									"semestre"=>$this->semestre,
									"date"=>date("Y-m-d",$this->date)));

			if (!$req)
				$this->id = -1;
			else
				$this->id = $req->get_id();
		}
		else
			$this->id = -2;
	}


	/** Assigne une seance de cours/td/tp a un utilisateur
	  * @param $id_seance l'id de la séance à assigner à l'utilisateur
	  *
	  * @return true si ok, false si faux
	  **/
	function assign_seance_edt ( $id_seance )
	{
		$req = new insert($this->dbrw,"edt_edts",array("id_edt" => $this->id, "id_seance" => intval($id_seance)));

		if (!$req)
			return FALSE;

		return TRUE;
	}

	function unassign_seance_edt ( $id_seance )
	{
		$req = new delete($this->dbrw,"edt_edts",array("id_edt" => $this->id, "id_seance" => intval($id_seance)));

		if (!$req)
			return FALSE;

		return TRUE;
	}
}


?>
