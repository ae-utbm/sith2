<?php
/**
 * @file
 * @addtogroup inventaire
 * @{
 */
 
/** 
 * Classe gérant les batiments
 */
class sitebat
{
	var $id;
	var $nom;
	var $fumeur;
	var $convention;
	var $notes;
	// pour utilisation future
	var $id_ville;
	
	var $db;
	var $dbrw;
	
	function sitebat ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	/** Charge un site en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `sl_site`
				WHERE `id_site` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function _load ( $row )
	{
		$this->id			= $row['id_site'];
		$this->nom			= $row['nom_site'];
		$this->fumeur		= $row['site_fumeur'];
		$this->convention	= $row['convention_site'];
		$this->notes			= $row['notes_site'];
		$this->id_ville		= $row['id_ville'];
	}
	
	/** Ajoute un site et le charge dans l'instance
	 * @param $nom Nom du site
	 * @param $fumeur (Booléen) Fumeur ou non
	 * @param $convention (Booléen) Convention de locaux requise
	 * @param $notes Notes (libres)
	 */
	function add ( $nom, $fumeur, $convention, $notes, $id_ville =NULL )
	{
		
		$this->nom			= $nom;
		$this->fumeur		= is_null($fumeur)?false:$fumeur;
		$this->convention	= is_null($convention)?false:$convention;
		$this->notes			= $notes;	
		$this->id_ville		= $id_ville;
		$sql = new insert ($this->dbrw,
			"sl_site",
			array(
				"nom_site" => $this->nom,
				"site_fumeur" => $this->fumeur,
				"convention_site" => $this->convention,
				"notes_site" => $this->notes
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
			
	}
	
} 
 
?>
