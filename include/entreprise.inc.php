<?php

/**
 * @file
 * 
 */
 
/** 
 * Class gérant une entreprise en relation avec l'ae
 */
class entreprise
{
	var $id;
	var $nom;
	var $rue;
	var $ville;
	var $cpostal;
	var $pays;
	var $telephone;
	var $email;
	var $fax;	
	
	var $db;
	var $dbrw;
	
	function entreprise ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	/** Charge une entreprise en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `entreprise`
				WHERE `id_ent` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function _load ( $row )
	{
		$this->id		= $row['id_ent'];
		$this->nom		= $row['nom_entreprise'];
		$this->rue		= $row['rue_entreprise'];
		$this->ville		= $row['ville_entreprise'];
		$this->cpostal	= $row['cpostal_entreprise'];
		$this->pays		= $row['pays_entreprise'];
		$this->telephone	= $row['telephone_entreprise'];
		$this->email		= $row['email_entreprise'];
		$this->fax		= $row['fax_entreprise'];
	}
	
	function add ( $nom,$rue,$ville,$cpostal,$pays,$telephone,$email,$fax)
	{
		if ( !$this->dbrw ) return; // Exits if "Read Only" mode
		
		$this->nom = $nom;
		$this->rue = $rue;
		$this->ville = $ville;
		$this->cpostal = $cpostal;
		$this->pays = $pays;
		$this->telephone = $telephone;
		$this->email = $email;
		$this->fax = $fax;
		
		$sql = new insert ($this->dbrw,
			"entreprise",
			array(
				"nom_entreprise" => $this->nom,
				"rue_entreprise" => $this->rue,
				"ville_entreprise" => $this->ville,
				"cpostal_entreprise" => $this->cpostal,
				"pays_entreprise" => $this->pays,
				"telephone_entreprise" => $this->telephone,
				"email_entreprise" => $this->email,
				"fax_entreprise" => $this->fax
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;		
		
	}
	
	function remove ( )
	{
		if ( !$this->dbrw ) return; // Exits if "Read Only" mode
	
		$sql = new delete ($this->dbrw,
			"entreprise",
			array( 
				"id_ent" => $this->id
				)
			);	
	}	
	
	function save ( $nom,$rue,$ville,$cpostal,$pays,$telephone,$email,$fax)
	{
		
		
		$this->nom = $nom;
		$this->rue = $rue;
		$this->ville = $ville;
		$this->cpostal = $cpostal;
		$this->pays = $pays;
		$this->telephone = $telephone;
		$this->email = $email;
		$this->fax = $fax;
		
		$sql = new update ($this->dbrw,
			"entreprise",
			array(
				"nom_entreprise" => $this->nom,
				"rue_entreprise" => $this->rue,
				"ville_entreprise" => $this->ville,
				"cpostal_entreprise" => $this->cpostal,
				"pays_entreprise" => $this->pays,
				"telephone_entreprise" => $this->telephone,
				"email_entreprise" => $this->email,
				"fax_entreprise" => $this->fax
				),
			array (
				"id_ent" => $this->id
				)
			);
		
		
	}
}

/**
 * Classe gérant un contact dans une entreprise
 */
class contact_entreprise 
{
	var $id;
	var $id_ent;
	var $nom;
	var $telephone;
	var $service;
	var $email;
	var $fax;
	
	var $db;
	var $dbrw;	
	
	function contact_entreprise ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	/** Charge un contact en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `contact_entreprise`
				WHERE `id_contact` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function _load ( $row )
	{
		$this->id		= $row['id_contact'];
		$this->id_ent	= $row['id_ent'];
		$this->nom		= $row['nom_contact'];
		$this->telephone	= $row['telephone_contact'];
		$this->service	= $row['service_contact'];
		$this->email		= $row['email_contact'];
		$this->fax		= $row['fax_contact'];
	}
	
	function add ( $id_ent, $nom, $telephone, $service, $email, $fax )
	{
		$this->id_ent	= $id_ent;
		$this->nom		= $nom;
		$this->telephone	= $telephone;
		$this->service	= $service;
		$this->email		= $email;
		$this->fax		= $fax;
		
		
		$sql = new insert ($this->dbrw,
			"contact_entreprise",
			array(
				"id_ent" => $this->id_ent,
				"nom_contact" => $this->nom,
				"telephone_contact" => $this->telephone,
				"service_contact" => $this->service,
				"email_contact" => $this->email,
				"fax_contact" => $this->fax
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;			
		
	}
	
	function remove ( )
	{
		if ( !$this->dbrw ) return; // Exits if "Read Only" mode
	
		$sql = new delete ($this->dbrw,
			"contact_entreprise",
			array( 
				"id_contact" => $this->id
				)
			);	
	}	
	
} 

 /**
 * Classe gérant un commentaire sur une entreprise
 */
class commentaire_entreprise 
{
	var $id;
	var $id_ent;
	var $id_utilisateur;
	var $id_contact;
	var $date;
	var $commentaire;
	
	var $db;
	var $dbrw;	
	
	
	function commentaire_entreprise ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	/** Charge un commentaire en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `commentaire_entreprise`
				WHERE `id_com_ent` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function _load ( $row )
	{
		$this->id			= $row['id_com_ent'];
		$this->id_ent		= $row['id_ent'];
		$this->id_utilisateur	= $row['id_utilisateur'];
		$this->id_contact	= $row['id_contact'];
		$this->date			= $row['date_com_ent'];
		$this->commentaire	= $row['commentaire_ent'];
	}
	
	function add ( $id_utilisateur, $id_ent, $id_contact, $commentaire )
	{
		if ( !$id_contact )
			$id_contact = NULL;

		$this->id_ent		= $id_ent;
		$this->id_utilisateur	= $id_utilisateur;
		$this->id_contact	= $id_contact;
		$this->date 			= time();
		$this->commentaire	= $commentaire;
		
		$sql = new insert ($this->dbrw,
			"commentaire_entreprise",
			array(
				"id_ent" => $this->id_ent,
				"id_utilisateur" => $this->id_utilisateur,
				"id_contact" => $this->id_contact,
				"date_com_ent" => date("Y-m-d",$this->date),
				"commentaire_ent" => $this->commentaire
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;			
		
	}
	
	function remove ( )
	{
		if ( !$this->dbrw ) return; // Exits if "Read Only" mode
	
		$sql = new delete ($this->dbrw,
			"commentaire_entreprise",
			array( 
				"id_com_ent" => $this->id
				)
			);	
	}	
	
} 
?>
