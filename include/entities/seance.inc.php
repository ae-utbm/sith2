<?php
/** @file Gestion des seances
 *
 *  @author COLNAT Laurent
 */


/** 
 * Classe permetant la gestion des seances
 */
class seance
{

	var $db;
	var $dbrw;

	var $id;
	var $id_uv;
	var $nom_uv;
	var $salle;
	var $hr_deb;
	var $hr_fin;
	var $semaine;
	var $jour;
	var $type;
	var $groupe;

	function seance ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}

	/** Charge un utilisateur en fonction de son id
	 * En cas d'erreur, l'id est défini à -1
	 * @param $id id de l'utilisateur
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `edt_seances` " .
				"WHERE `id_seance` = '" . mysql_real_escape_string($id) . "' " .
				"LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	/** Charge un utilisateur en fonction de son adresse email personnelle, 
	 * ou de son adresse mail utbm.
	 * En cas d'erreur, l'id est défini à -1
	 * @param $id_uv id de l'uv
	 */
	function load_by_uv ( $id_uv )
	{
	
		$req = new requete($this->db, "SELECT `edt_seances`.`id_seance` FROM `edt_seances` " .
				"LEFT JOIN `edt_uv` ON `edt_seances`.`id_uv` = `edt_uv`.`id_uv` " .
				"WHERE `edt_seances`.`id_uv` = '" . mysql_real_escape_string($id_uv) . "'");	
				
		if ( $req->lines > 0 )
		{
			while ( $res = $req->get_row() )
			{
				$ret[] = $res['id_seance'];
			}
			return $ret;
		}
		else
			return FALSE;
	}
	
	/** Charge un utilisateur en fonction de son alias
	 * En cas d'erreur, l'id est défini à -1
	 * @param $alias alias de l'utilisateur
	 */	
	function exists ( $id_uv, $type, $groupe )
	{
		if (empty($groupe))
			$req = new requete($this->db, "SELECT `edt_seances`.`id_seance` FROM `edt_seances` " .
				"WHERE `edt_seances`.`id_uv` = '" . mysql_real_escape_string($id_uv) . "' " .
				"AND `edt_seances`.`type` = '" . mysql_real_escape_string($type) . "' " .
				"AND `edt_seances`.`groupe` IS NULL " . 
				"LIMIT 1");
		else
			$req = new requete($this->db, "SELECT `edt_seances`.`id_seance` FROM `edt_seances` " .
				"WHERE `edt_seances`.`id_uv` = '" . mysql_real_escape_string($id_uv) . "' " .
				"AND `edt_seances`.`type` = '" . mysql_real_escape_string($type) . "' " .
				"AND `edt_seances`.`groupe` = '" . mysql_real_escape_string($groupe) . "' " . 
				"LIMIT 1");

		if (!$req)
			return FALSE;
					
		if ( $req->lines == 1 )
		{
			$res = $req->get_row();
			return $res['id_seance'];
		}
		else
			return FALSE;	
				
	}

	function _load ( $row )
	{
		$this->id      = $row['id_seance'];
		$this->id_uv  = $row['nom'];
		$this->nom_uv = $this->get_nom_uv_by_seance();
		$this->type    = $row['type'];
		$this->groupe  = $row['groupe'];
		$this->salle   = $row['salle'];
		$this->hr_deb  = $row['hr_deb'];
		$this->hr_fin  = $row['hr_fin'];
		$this->semaine = $row['semaine'];
		$this->jour    = $row['jour'];
	}
	
	/**
	 * Sauve les informations de l'utilisateur.
	 * Au vu du nombre d'informations, le passage se fait par les variables de l'objet.
	 */
	function saveinfos ()
	{
		$req = new update($this->dbrw,
		  	"edt_seances",
			array(
				'hr_deb' => $this->hr_deb,
				'hr_fin' => $this->hr_fin,
				'semaine' => $this->semaine,
				'type' => $this->type,
				'groupe' => $this->groupe,
				'id_uv' => $this->id_uv,
				'salle' => $this->salle),
			array( 'id_seance' => $this->id));

		if (!$req)
			return false;

		return true;
	}

	function get_nom_uv_by_seance()
	{
		$req = new requete($this->db,"SELECT `edt_uv`.`nom` " .
									 "FROM `edt_uv` INNER JOIN `edt_seances` ".
									 "ON `edt_seances`.`id_uv` = `edt_uv`.`id_uv` ".
									 "WHERE `edt_seances`.`id_seance` = '". mysql_real_escape_string(intval($this->id)) ."' ".
									 "LIMIT 1");
		if (!$req)
			return FALSE;
		$res = $req->get_row();

		return $res['nom'];
	}

	function get_id_uv_by_seance()
	{
		$req = new requete($this->db,"SELECT `edt_uv`.`id_uv` " . 
								 "FROM `edt_uv` INNER JOIN `edt_seances` ".
								 "ON `edt_seances`.`id_uv` = `edt_uv`.`id_uv` ".
								 "WHERE `edt_seances`.`id_seance` = '" . mysql_real_escape_string(intval($this->id)) . "' ".
			                     "LIMIT 1");

		if (!$req)
			return FALSE;
		$res = $req->get_row();

		return $res['id_uv'];
	}

	function verif_heure($heure)
	{
		$regexp = '/^[0-9]{2}[\-\/\:]{1}[0-9]{2}$/';
		if (preg_match($regexp,trim($heure)) && strlen(trim($heure)) == "5")
			return TRUE;
		else
			return FALSE;
	}

	function comp($hr1,$hr2)
	{
		$comp1 = intval($hr1[0].$hr1[1].$hr1[3].$hr1[4]);
		$comp2 = intval($hr2[0].$hr2[1].$hr2[3].$hr2[4]);

		if ($comp2 <= $comp1)
			return FALSE; 
		else
			return TRUE;
	}

	function erase ()
	{
		$req = new delete ($this->dbrw,"edt_seances",array("id_seance"=>$this->id));

		if (!$req)
			return FALSE;

		return TRUE;
	}

	/* ajoute une seance et retourne son id si c'est bon */
	function add ($hr_deb, $hr_fin, $jour, $semaine, $type, $groupe, $id_uv, $salle)
	{
		if ( !$this->verif_heure($hr_deb) )
			$this->id = -1;
		elseif ( !$this->verif_heure($hr_fin) )
			$this->id = -2;
		elseif ( !$this->comp($hr_deb,$hr_fin) )
			$this->id = -5;
		else
		{
			$this->hr_deb = $hr_deb;
			$this->hr_fin = $hr_fin;
		}

		if ( $salle == "X000" )
			$this->id = -3;
		else
		{
			$this->salle = strtoupper($salle);

			$this->id_uv = $id_uv;
			$this->semaine = $semaine;
			$this->jour = $jour;
			$this->type = $type;
			$this->groupe = $groupe;	

			$req = new insert($this->dbrw,"edt_seances",array("hr_deb" => $this->hr_deb,
														  "hr_fin" => $this->hr_fin,
														  "semaine"=> $this->semaine,
														  "jour"   => $this->jour,
														  "type"   => $this->type,
														  "id_uv"  => $this->id_uv,
														  "groupe" => empty($this->groupe)?null:$this->groupe,	
														  "salle"  => $this->salle));

			if (!$req)
				$this->id = -4;
			else
				$this->id = $req->get_id();

		}
	}
		
}

?>
