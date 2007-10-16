<?php

/** @file Gestion des associations et clubs
 *
 */
 
/* Copyright 2005-2007
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
 
define("ROLEASSO_PRESIDENT",10); 
define("ROLEASSO_VICEPRESIDENT",9); 
define("ROLEASSO_TRESORIER",7); 
define("ROLEASSO_RESPCOM",5); 
define("ROLEASSO_SECRETAIRE",4); 
define("ROLEASSO_RESPINFO",3); 
define("ROLEASSO_MEMBREBUREAU",2); 
define("ROLEASSO_MEMBREACTIF",1); 
define("ROLEASSO_MEMBRE",0); 

 
$GLOBALS['ROLEASSO'] = array 
( 
	ROLEASSO_PRESIDENT=>"Responsable/président",
	ROLEASSO_VICEPRESIDENT=>"Vice-responsable/Vice-président",
	ROLEASSO_TRESORIER=>"Trésorier",
	ROLEASSO_RESPCOM=>"Responsable communication",
  ROLEASSO_SECRETAIRE=>"Secrétaire",
  ROLEASSO_RESPINFO=>"Responsable informatique",
	ROLEASSO_MEMBREBUREAU=>"Membre du bureau",
	ROLEASSO_MEMBREACTIF=>"Membre actif",
	ROLEASSO_MEMBRE=>"Membre"	
); 
 
$GLOBALS['ROLEASSO100'] = array
 ( 
	(ROLEASSO_PRESIDENT+100)=>"Président",
	(ROLEASSO_VICEPRESIDENT+100)=>"Vice-président",
	(ROLEASSO_TRESORIER+100)=>"Trésorier",
	(ROLEASSO_RESPCOM+100)=>"Responsable communication",
  (ROLEASSO_SECRETAIRE+100)=>"Secrétaire",
  (ROLEASSO_RESPINFO+100)=>"Responsable informatique",
	(ROLEASSO_MEMBREBUREAU+100)=>"Membre du bureau",
	(ROLEASSO_MEMBREACTIF+100)=>"Membre actif",
	(ROLEASSO_MEMBRE+100)=>"Membre",
	ROLEASSO_PRESIDENT=>"Responsable",
	ROLEASSO_VICEPRESIDENT=>"Vice-responsable",
	ROLEASSO_TRESORIER=>"Trésorier",
	ROLEASSO_RESPCOM=>"Responsable communication",
  ROLEASSO_SECRETAIRE=>"Secrétaire",
  ROLEASSO_RESPINFO=>"Responsable informatique",
	ROLEASSO_MEMBREBUREAU=>"Membre du bureau",
	ROLEASSO_MEMBREACTIF=>"Membre actif",
	ROLEASSO_MEMBRE=>"Membre"	
); 

class asso extends stdentity
{

	/* table asso */
	var $id_parent;	
	var $nom;	var $nom_unix;
	var $adresse_postale;
	
	var $email;
	var $siteweb;
	
	var $login_email;
	var $passwd_email; 
	/*
     L'objectif est de conserver les mots de passe des boites mails des clubs (er pourquoi pas d'y acceder en imap).
     cependant conserver en clair les mots de passe dans la bdd ça crain, faudrais une méthode de pseudo cryptage, 
     pour que ls stockage ne se fasse pas en clair...
	 */

	/** Charge une association par son ID
	 * @param $id ID de l'association
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `asso`
				WHERE `id_asso` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	} 
	
	/** Charge une association par son nom unix
	 * @param $name Nom unix de l'association
	 */
	function load_by_unix_name ( $name )
	{
		$req = new requete($this->db, "SELECT * FROM `asso`
				WHERE `nom_unix_asso` = '" . mysql_real_escape_string($name) . "'
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
		$this->id = $row['id_asso'];
		$this->id_parent = $row['id_asso_parent'];		
		$this->nom = $row['nom_asso'];
		$this->nom_unix = $row['nom_unix_asso'];
		$this->adresse_postale = $row['adresse_postale'];
		
		$this->email = $row['email_asso'];		
		$this->siteweb = $row['siteweb_asso'];
		$this->login_email = $row['login_email'];
		$this->passwd_email = $row['passwd_email'];	
	}
	
	/** Crée une nouvelle association
	 * @param $nom			Nom	de l'association
	 * @param $nom_unix		Nom UNIX de l'association
	 * @param $id_parent	ID de l'association parent, false si non applicable
	 */
	function add_asso ( $nom, $nom_unix, $id_parent = null, $adresse_postale="", $email="", $siteweb="", $login_email="", $passwd_email=""  )
	{
		if ( is_null($this->dbrw) ) return; // "Read Only" mode
		
		$this->nom = $nom;
		$this->nom_unix = $nom_unix;
		$this->id_parent = $id_parent;
		$this->adresse_postale = $adresse_postale;
		
		$this->email = $email;
		$this->siteweb = $siteweb;
		$this->login_email = $login_email;
		$this->passwd_email = $passwd_email;
				
		$sql = new insert ($this->dbrw,
			"asso",
			array(
				"id_asso_parent" => $this->id_parent,
				"nom_asso" => $this->nom,
				"nom_unix_asso" => $this->nom_unix,
				"adresse_postale"=>$this->adresse_postale,
				
				"email_asso"=>$this->email,
				"siteweb_asso"=>$this->siteweb,
				"login_email"=>$this->login_email,
				"passwd_email"=>$this->passwd_email
				
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = null;
			
	  if ( $this->nom_unix )
	  {
	    if ( !is_null($this->id_parent) )
		    $this->_ml_create($this->nom_unix."-membres");	
		  $this->_ml_create($this->nom_unix."-bureau");	
	  }
	}
	
	/** Modifie l'association
	 * @param $nom			Nom	de l'association
	 * @param $nom_unix		Nom UNIX de l'association
	 * @param $id_parent	ID de l'association parent, false si non applicable
	 */
	function update_asso ( $nom, $nom_unix, $id_parent = null, $adresse_postale="", $email=null, $siteweb=null, $login_email=null, $passwd_email=null )
	{
	  $old_unix = $this->nom_unix;
	 
		if ( is_null($this->dbrw) ) return; // "Read Only" mode
		
		
    if ( $this->nom_unix != $this->nom_unix )
    {
      if ( !$this->nom_unix )
      {
        if ( !is_null($id_parent) )
		      $this->_ml_create($nom_unix."-membres");	
		      
		    $this->_ml_create($nom_unix."-bureau");
		  }
      else
      {
        if (!is_null($this->id_parent) && is_null($id_parent) )
  		    $this->_ml_remove($this->nom_unix."-membres");
  		  elseif (is_null($this->id_parent) && !is_null($id_parent) )
  		    $this->_ml_create($nom_unix."-membres");
  		  else
		      $this->_ml_rename($this->nom_unix."-membres",$nom_unix."-membres");	
		      
		    $this->_ml_rename($this->nom_unix."-bureau",$nom_unix."-bureau");	        
      }
    }		
		elseif ( $this->nom_unix )
		{
  		if (!is_null($this->id_parent) && is_null($id_parent) )
  		  $this->_ml_remove($this->nom_unix."-membres");
  		elseif (is_null($this->id_parent) && !is_null($id_parent) )
  		  $this->_ml_create($this->nom_unix."-membres");
		}
		
		$this->nom = $nom;
		$this->nom_unix = $nom_unix;
		$this->id_parent = $id_parent;
		$this->adresse_postale = $adresse_postale;
		
		if ( !is_null($email) )
		  $this->email = $email;
		
		if ( !is_null($siteweb) )
		  $this->siteweb = $siteweb;
		
		if ( !is_null($login_email) )
		  $this->login_email = $login_email;
		
		if ( !is_null($passwd_email) )
		  $this->passwd_email = $passwd_email;

		$sql = new update ($this->dbrw,
			"asso",
			array(
				"id_asso_parent" => $this->id_parent,
				"nom_asso" => $this->nom,
				"nom_unix_asso" => $this->nom_unix,
				"adresse_postale"=>$this->adresse_postale,
				"email_asso"=>$this->email,
				"siteweb_asso"=>$this->siteweb,
				"login_email"=>$this->login_email,
				"passwd_email"=>$this->passwd_email
				),
			array ( "id_asso" => $this->id )
			
			);
			


	}
	
	
	/* table asso_membre */
	
	/** Ajoute un membre actuel à l'association
	 * Si l'utilisateur est déjà membre, passe sa participation
	 *  précédente comme ancienne
	 * @param $id_utl		ID de l'utilisateur
	 * @param $date_debut	Date de début (timestamp unix)	 
	 * @param $role			Role 
	 * @param $description	Description du role (vice président, vpi ...)	
	*/
	function add_actual_member ( $id_utl, $date_debut, $role, $description )
	{
		if ( is_null($this->dbrw) ) return; // "Read Only" mode
	
		if ( !$date_debut )
			$date_debut = time();		

		$prevrole = $this->member_role($id_utl);
		
		if ( is_null($prevrole) )
			$this->_ml_all_subscribe_user($id_utl,$role);
		elseif ( $role == $prevrole )
		  return;
		else
		{
			$this->make_former_member($id_utl,$date_debut,true);
	    $this->_ml_all_delta_user($id_utl,$prevrole,$role);
		}
			
		$sql = new insert ($this->dbrw,
			"asso_membre",
			array(
				"id_asso" => $this->id,
				"id_utilisateur" => $id_utl,
				"date_debut" => strftime("%Y-%m-%d", $date_debut),
				"role" => $role,
				"desc_role" => $description
				)
			);	
	}
	
	/** Ajoute un ancien membre de l'association
	 * @param $id_utl		ID de l'utilisateur
	 * @param $date_debut	Date de début (timestamp unix)
	 * @param $date_fin		Date de fin (timestamp unix)
	 * @param $role			Role 
	 * @param $description	Description du role (vice président, vpi ...)
	 */	
	function add_former_member ( $id_utl, $date_debut, $date_fin, $role, $description )
	{
		if ( is_null($this->dbrw) ) return; // "Read Only" mode

		if ( is_null($date_fin)) return;

		$sql = new insert ($this->dbrw,
			"asso_membre",
			array(
				"id_asso" => $this->id,
				"id_utilisateur" => $id_utl,
				"date_debut" => strftime("%Y-%m-%d", $date_debut),
				"date_fin" => strftime("%Y-%m-%d", $date_fin),
				"role" => $role,
				"desc_role" => $description
				)
			);		
	}
	
	/** Passe un membre actuel de l'association comme ancien
	 * @param $id_utl	ID de l'utilisateur
	 * @param $date_fin	Date de fin (timestamp unix) 
	 */
	function make_former_member ( $id_utl, $date_fin, $ignore_ml=false )
	{
		if ( is_null($this->dbrw) ) return; // "Read Only" mode
	
		if ( !$date_fin )
			$date_fin = time();
	
	  if ( !$ignore_ml )
	    $this->_ml_all_unsubscribe_user($id_utl);
	
		$sql = new update ($this->dbrw,
			"asso_membre",
			array(
				"date_fin" => strftime("%Y-%m-%d", $date_fin)
				),
			array( 
				"id_asso" => $this->id,			
				"id_utilisateur" => $id_utl,
				"date_fin" => NULL
				)
			);		
	
	}
	
	/** Determine si un utilisteur est actuellemnt membre de l'association
	 * @param $id_utl	ID de l'utilisateur
	 * @return true si vrai, false sinon
	 */
	function is_member ( $id_utl )
	{
    if ( is_null($id_utl) )
      return false;
	
		$req = new requete($this->db, "SELECT * FROM `asso_membre`
					WHERE `id_asso` = '" . mysql_real_escape_string($this->id) . "'
					AND `id_utilisateur` = '" . mysql_real_escape_string($id_utl) . "'
					AND `date_fin` is NULL
					LIMIT 1");
		
		return ($req->lines == 1);	
	}
	
	/** Determine si un utilisteur est actuellemnt membre de l'association et occupe un poste spécial
	 * @param $id_utl	ID de l'utilisateur
	 * @param $role	Role minimum à occuper
	 * @return true si vrai, false sinon
	 */
	function is_member_role ( $id_utl, $role )
	{
    if ( is_null($id_utl) )
      return false;
	
		$req = new requete($this->db, "SELECT * FROM `asso_membre`
				WHERE `id_asso` = '" . mysql_real_escape_string($this->id) . "'
				AND `id_utilisateur` = '" . mysql_real_escape_string($id_utl) . "'
				AND `date_fin` is NULL AND `role` >= '".mysql_real_escape_string($role)."'
				LIMIT 1");
		
		return ($req->lines == 1);	
	}	
	
	function member_role ( $id_utl )
	{
    if ( is_null($id_utl) )
      return NULL;
	
		$req = new requete($this->db, "SELECT role FROM `asso_membre`
				WHERE `id_asso` = '" . mysql_real_escape_string($this->id) . "'
				AND `id_utilisateur` = '" . mysql_real_escape_string($id_utl) . "'
				AND `date_fin` is NULL
				LIMIT 1");
		
		if ( $req->lines != 1 )
		  return NULL;
		  
		list($role) = $req->get_row();
		
		return $role;	
	}	
	
	
	/** Enlève une 'participation' d'un membre de l'association actuelle
	 * @param $id_utl		ID de l'utilisateur
	 * @param $date_debut	Date de debut de la 'participation' (timestamp unix)
	 */
	function remove_member ( $id_utl, $date_debut )
	{
		if ( is_null($this->dbrw) ) return; // "Read Only" mode
	
	  $prevrole = $this->member_role($id_utl);
	
		$sql = new delete ($this->dbrw,
			"asso_membre",
			array( 
				"id_asso" => $this->id,			
				"id_utilisateur" => $id_utl,
				"date_debut" => strftime("%Y-%m-%d", $date_debut)
				)
			);	
			
	  $newrole = $this->member_role($id_utl);		
		if ( is_null($newrole) )
	    $this->_ml_all_unsubscribe_user($id_utl,$prevrole);
	  elseif ( $newrole != $prevrole ) 
	    $this->_ml_all_delta_user($id_utl,$prevrole,$newrole);
	}
	
	
	
	function get_tabs($user)
	{
		$tabs = array(array("info","asso.php?id_asso=".$this->id, "Informations"));
		
		if ( $user->is_in_group("gestion_ae")|| $this->is_member_role($user->id,ROLEASSO_MEMBREBUREAU) )
		{
			$tabs[] = array("tools","asso/index.php?id_asso=".$this->id,"Outils");
			$tabs[] = array("inv","asso/inventaire.php?id_asso=".$this->id,"Inventaire");
			$tabs[] = array("res","asso/reservations.php?id_asso=".$this->id,"Reservations");
			$tabs[] = array("mebs","asso/membres.php?id_asso=".$this->id,"Membres");
			$tabs[] = array("slds","asso/ventes.php?id_asso=".$this->id,"Ventes");
			$tabs[] = array("cpg","asso/campagne.php?id_asso=".$this->id,"Recrutement");
		}
		else
		{
			$tabs[] = array("mebs","asso/membres.php?id_asso=".$this->id,"Membres");
		}
		$tabs[] = array("files","d.php?id_asso=".$this->id,"Fichiers");
		
		
    $req = new requete($this->db, "SELECT id_catph FROM `sas_cat_photos` " .
        "WHERE `meta_id_asso_catph` = '" . mysql_real_escape_string($this->id) . "' " .
        "AND `meta_mode_catph`='1' LIMIT 1");		
		
    if ( $req->lines == 1 )
    {
      $enr = $req->get_row();
		  $tabs[] = array("photos","sas2/?id_catph=".$enr["id_catph"],"Photos");
		}
		
		return $tabs;
	}
	
	function get_membres_group_id()
	{
	 return $this->id+30000;
	}
	
	function get_bureau_group_id()
	{
	 return $this->id+20000;
	}
	
	
  function get_html_path()
  {
    global $wwwtopdir;
    $path = $this->get_html_link();
    $parent = new asso($this->db);
    $parent->load_by_id($this->id_parent);
    while ( $parent->is_valid() )
    {
      $path = $parent->get_html_link()." / ".$path;
      $parent->load_by_id($parent->id_parent);
    }
    return $path;
  }
  
  function prefer_list()
  {
    return true;  
  }  
  
  function _ml_all_subscribe_user ( $id_utl, $role=null )
  {
    $user = new utilisateur($this->db);
    $user->load_by_id($id_utl);
    
    if ( !$user->is_valid() )
      return;
    
    if ( is_null($role) )
      $role = $this->member_role($user->id);
    
    if ( !is_null($this->id_parent) )
      $this->_ml_subscribe($this->nom_unix."-membres",$user->email);
    
    if ( $role > ROLEASSO_MEMBREACTIF )
      $this->_ml_subscribe($this->nom_unix."-bureau",$user->email);
  }
  
  function _ml_all_unsubscribe_user ( $id_utl, $role=null )
  {
    $user = new utilisateur($this->db);
    $user->load_by_id($id_utl);
    
    if ( !$user->is_valid() )
      return;
    
    if ( is_null($role) )
      $role = $this->member_role($user->id);
      
    if ( !is_null($this->id_parent) )
      $this->_ml_unsubscribe($this->nom_unix."-membres",$user->email);
    
    if ( $role > ROLEASSO_MEMBREACTIF )
      $this->_ml_unsubscribe($this->nom_unix."-bureau",$user->email);
  }
  
  function _ml_all_delta_user ( $id_utl, $oldrole, $newrole )
  {
    $user = new utilisateur($this->db);
    $user->load_by_id($id_utl);
    
    if ( !$user->is_valid() )
      return;

    if ( $oldrole > ROLEASSO_MEMBREACTIF && $newrole <= ROLEASSO_MEMBREACTIF )
      $this->_ml_unsubscribe($this->nom_unix."-bureau",$user->email);
    elseif ( $oldrole <= ROLEASSO_MEMBREACTIF && $newrole > ROLEASSO_MEMBREACTIF )
      $this->_ml_subscribe($this->nom_unix."-bureau",$user->email);
    
  }
    
  static function _ml_subscribe ( $ml, $email )
  {
    //TODO: subscribe $email to $ml
    echo "$ml SUBSCRIBE $email<br/>";
  }
  
  static function _ml_unsubscribe ( $ml, $email )
  {
    //TODO: unsubscribe $email from $ml
    echo "$ml UNSUBSCRIBE $email<br/>";
  }
  
  static function _ml_create ( $ml )
  {
    //TODO: create $ml
    echo "CREATE $ml<br/>";
  }
  
  static function _ml_rename ( $old, $new )
  {
    //TODO: rename mailing $old to $new
    echo "MOVE $old TO $new<br/>";
  }
  
  static function _ml_remove ( $ml )
  {
    //TODO: destroy $ml
    echo "DESTROY $ml<br/>";
  }  
}
 
 
 
 
 ?>
