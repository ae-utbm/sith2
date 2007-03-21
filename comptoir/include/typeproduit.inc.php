<?php

/** @todo à vérifier *********************************/

/** 
 * @addtogroup comptoirs
 * @{
 */

//cpt_typeproduit
/**
 * Classe gérant un type de produit
 */
class typeproduit 
{

	var $id;
	var $nom;
	var $action; // <=> action par défaut lors de l'ajout
	var $id_assocpt; // <=> association par défaut lors de l'ajout

	var $url_logo;
	var $description;

	var $db;
	var $dbrw;
	/* Class "amies" pouvant modifier les instances
		- VenteProduit
	*/

	/** @brief constructeur de la classe
	 *
	 * @param db un objet de type siteae
	 *
	 */
	function typeproduit ($db,$dbrw=false)
	{
		$this->db = $db;
		$this->dbrw=$dbrw;
	}



	function load_by_id ( $id )
	{
	
		$req = new requete($this->db,"SELECT `id_typeprod`, `nom_typeprod`, `action_typeprod`, 
							 `id_assocpt`,`url_logo_typeprod`,`description_typeprod`
							 FROM `cpt_type_produit` 
							 WHERE `id_typeprod`=".intval($id)."");
							 
		if ($req->lines < 1)
			$this->id = -1;
		else
			list ( 
				$this->id, 
				$this->nom,
				$this->action,
				$this->id_assocpt,
				$this->url_logo,
				$this->description				
				) = $req->get_row(); // mouarf...	
	
	
		return false;
	}
	
	
	function ajout ( $nom, $action, $id_assocpt, $url_logo, $description )
	{
		$this->nom = $nom;
		$this->action = $action;
		$this->id_assocpt = $id_assocpt;
		
		$this->url_logo = $url_logo;
		$this->description = $description;		
		
		$req = new insert ($this->dbrw,
					 "cpt_type_produit",
					 array("nom_typeprod" => $this->nom,
						 "action_typeprod" => $this->action,
					 "id_assocpt" => $this->id_assocpt,
					 "url_logo_typeprod" => $this->url_logo,
					 "description_typeprod" => $this->description
					 ));	 	
		echo mysql_error();				 
		if ( !$req )
				return false;
	
		$this->id = $req->get_id();
		
		return true;
	}
	
	function modifier ( $nom, $action, $id_assocpt, $url_logo, $description )
	{
		$this->nom = $nom;
		$this->action = $action;
		$this->id_assocpt = $id_assocpt;	
		$this->url_logo = $url_logo;
		$this->description = $description;
		
		$sql = new update($this->dbrw,
					"cpt_type_produit",
					array("nom_typeprod" => $this->nom,
						 "action_typeprod" => $this->action,
					 "id_assocpt" => $this->id_assocpt,
					 "url_logo_typeprod" => $this->url_logo,
					 "description_typeprod" => $this->description
					 ),
					array("id_typeprod" => $this->id));
					
		if ( !$sql )
			return false;
		
        return true;
	}
	


}



?>
