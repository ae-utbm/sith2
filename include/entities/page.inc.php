<?php
/**
 * @file
 */
 
require_once($topdir."include/entities/basedb.inc.php");

/** Classe gérant les pages "wiki" 
 */
class page extends basedb
{
	var $nom;
	var $texte;
	var $date;
	var $titre;
	var $section;

	
	/** Charge une page en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id Id de la page
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `pages`
				WHERE `id_page` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
    }
    
		$this->id = null;	
		return false;
	}
	
	/** Charge une page en fonction de son nom
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $name Nom de la page
	 */
	function load_by_name ( $name )
	{
		$req = new requete($this->db, "SELECT * FROM `pages`
				WHERE `nom_page` = '" . mysql_real_escape_string($name) . "'
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
		$this->id			= $row['id_page'];
		$this->nom			= $row['nom_page'];
		$this->texte			= $row['texte_page'];
		$this->date			= strtotime($row['date_page']);
		$this->titre			= $row['titre_page'];
		$this->section		= $row['section_page'];
		
		$this->id_utilisateur = $row['id_utilisateur'];
		$this->id_groupe = $row['id_groupe_modal'];
		$this->id_groupe_admin = $row['id_groupe'];
		$this->droits_acces = $row['droits_acces_page'];
		$this->modere = $row['modere_page'];
		
	}
	
	/** Génére un stdcontents avec le contenu de la page
	 */
	function get_contents ( )
	{
		$cts = new wikicontents($this->titre,$this->texte);
		$cts->puts("<div class=\"clearboth\"></div>");
		return $cts;
	}
	
	/** Modifie la page actuelle
	 * @param $id_utl Id de l'utilisateur propriètaire
	 * @param $id_groupe Id du groupe
	 * @param $titre Titre de la page
	 * @param $texte Contenu de la page
	 */
	function save ( /*$id_utl, $id_groupe,*/ $titre, $texte, $section )
	{
		//$this->id_utilisateur = $id_utl;
		//$this->id_groupe = $id_groupe;
		$this->texte = $texte;
		$this->titre = $titre;			
		$this->date = time();	
		$this->section = $section;
		$this->modere = true;
		$sql = new update ($this->dbrw,
			"pages",
			array(
				//"id_groupe" => $this->id_groupe,
				//"id_utilisateur" => $this->id_utilisateur,
				"texte_page" => $this->texte,
				"date_page" => date("Y-m-d H:i:s"),
				"titre_page" => $this->titre,
				"section_page"=>$this->section,
				
				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe_modal"=>$this->id_groupe,
				"id_groupe"=>$this->id_groupe_admin,
				"droits_acces_page"=>$this->droits_acces,
				"modere_page"=>$this->modere
				),
			array(
				"id_page" => $this->id
				)
			);
			
	}
	
	/** Ajoute une nouvelle page.
	 * @param $nom Nom de la page
	 * @param $id_utl Id de l'utilisateur propriètaire
	 * @param $id_groupe Id du groupe
	 * @param $titre Titre de la page
	 * @param $texte Contenu de la page
	 */
	function add ( $nom,/* $id_utl, $id_groupe,*/ $titre, $texte, $section )
	{
	
		$this->nom = $nom;
		//$this->id_utilisateur = $id_utl;
		//$this->id_groupe = $id_groupe;
		$this->texte = $texte;
		$this->titre = $titre;		
		$this->date = time();		
		$this->section = $section;
		$this->modere = true;
		
		$sql = new insert ($this->dbrw,
			"pages",
			array(
				"nom_page" => $this->nom,
				//"id_groupe" => $this->id_groupe,
				//"id_utilisateur" => $this->id_utilisateur,
				"texte_page" => $this->texte,
				"date_page" => date("Y-m-d H:i:s"),
				"titre_page" => $this->titre,
				"section_page"=>$this->section,
				
				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe_modal"=>$this->id_groupe,
				"id_groupe"=>$this->id_groupe_admin,
				"droits_acces_page"=>$this->droits_acces,
				"modere_page"=>$this->modere
				
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = null;
		
	}
	
	function is_category()
	{
		return false;	
	}
	
	function is_admin ( &$user )
	{
    if ( $user->is_in_group("moderateur_site") ) return true;
		if ( $user->is_in_group_id($this->id_groupe_admin) ) return true;
		return false;
	}
	
} 
 
 
 
 
 
?>
