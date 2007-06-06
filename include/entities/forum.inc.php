<?php
/* 
 * FORUM2
 *        
 * Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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
 
require_once($topdir."include/entities/basedb.inc.php");
require_once($topdir."include/entities/asso.inc.php");

/**
 * Forum
 */
class forum extends basedb
{

  var $titre;
  
  var $description;
  
  var $categorie;
  
  var $id_forum_parent;
  
  var $id_asso;
  
  var $id_sujet_dernier; 
  var $nb_sujets;
    
  var $ordre;
    
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `frm_forum`
				WHERE `id_forum` = '" .
		       mysql_real_escape_string($id) . "'
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
    $this->id = $row['id_forum'];
    $this->titre = $row['titre_forum'];
    $this->description = $row['description_forum'];
    $this->categorie = $row['categorie_forum'];
    $this->id_forum_parent = $row['id_forum_parent'];
    $this->id_asso = $row['id_asso'];
    $this->id_sujet_dernier = $row['id_sujet_dernier'];
    $this->nb_sujets = $row['nb_sujets_forum'];
    
    $this->id_utilisateur = $row['id_utilisateur'];
    $this->id_groupe = $row['id_groupe'];
    $this->id_groupe_admin = $row['id_groupe_admin'];
    $this->droits_acces = $row['droits_acces_forum'];
    $this->ordre = $row['ordre_forum'];
    $this->modere=1;
  }
  
	function is_admin ( &$user )
	{
		if ( $user->is_in_group("moderateur_forum") || $user->is_in_group("root") )
		  return true;	
		
		if ( !is_null($this->id_asso) )
		  if ( $user->is_asso_role ( $this->id_asso, ROLEASSO_RESPINFO ) )
		    return true;
		
		return parent::is_admin($user);
	}
	
	function is_category()
	{
		return $this->categorie;	
	}
	
	function create ( $titre, $description, $categorie, $id_forum_parent, $id_asso=null, $ordre=0 )
	{
	  $this->titre = $titre;
	  $this->description = $description;
	  $this->categorie = $categorie;
	  $this->id_forum_parent = $id_forum_parent;
	  $this->id_asso = $id_asso;
    $this->id_sujet_dernier = null;
    $this->nb_sujets = 0;
    $this->ordre = $ordre;
    $req = new insert ($this->dbrw,
            "frm_forum", array(
              "titre_forum"=>$this->titre,
              "description_forum"=>$this->description,
              "categorie_forum"=>$this->categorie,
              "id_forum_parent"=>$this->id_forum_parent,
              "id_asso"=>$this->id_asso,
              "id_utilisateur"=>$this->id_utilisateur,
              "id_groupe"=>$this->id_groupe,
              "id_groupe_admin"=>$this->id_groupe_admin,
              "droits_acces_forum"=>$this->droits_acces,
              "id_sujet_dernier"=>$this->id_sujet_dernier,
              "nb_sujets_forum"=>$this->nb_sujets,
              "ordre_forum"=>$this->ordre
            ));
  
		if ( $req )
		{
			$this->id = $req->get_id();
		  return true;
		}
		
		$this->id = null;
    return false;
	}
	
	function update ( $titre, $description, $categorie, $id_forum_parent, $id_asso=null, $ordre=0 )
	{
	  $this->titre = $titre;
	  $this->description = $description;
	  $this->categorie = $categorie;
	  $this->id_forum_parent = $id_forum_parent;
	  $this->id_asso = $id_asso;
    $this->ordre = $ordre;
    $req = new update ($this->dbrw,
            "frm_forum", array(
              "titre_forum"=>$this->titre,
              "description_forum"=>$this->description,
              "categorie_forum"=>$this->categorie,
              "id_forum_parent"=>$this->id_forum_parent,
              "id_asso"=>$this->id_asso,
              "id_utilisateur"=>$this->id_utilisateur,
              "id_groupe"=>$this->id_groupe,
              "id_groupe_admin"=>$this->id_groupe_admin,
              "droits_acces_forum"=>$this->droits_acces,
              "ordre_forum"=>$this->ordre
            ),
            array("id_forum"=>$this->id) );
	}	
	
	function get_sub_forums ( &$user )
	{
	 
    $query = "SELECT frm_forum.*, ".
        "frm_sujet.titre_sujet, ".
        "frm_message.date_message, " .
        "frm_message.id_message, " .
        "utilisateurs.alias_utl AS `nom_utilisateur_dernier_auteur`, " .
        "utilisateurs.id_utilisateur AS `id_utilisateur_dernier` " .
        "FROM frm_forum " .
        "LEFT JOIN frm_sujet ON ( frm_sujet.id_sujet = frm_forum.id_sujet_dernier ) " .
        "LEFT JOIN frm_message ON ( frm_message.id_message = frm_sujet.id_message_dernier ) " .
        "LEFT JOIN utilisateurs ON ( utilisateurs.id_utilisateur=frm_message.id_utilisateur ) " .
        "WHERE " .
        "id_forum_parent='".$this->id."' ";
	 
    if ( !$user->is_valid() )
      $query .= "AND (droits_acces_forum & 0x1) ";
      
    elseif ( !$this->is_admin( $user ) )
    {
      $grps = $user->get_groups_csv();
      $query .= "AND ((droits_acces_forum & 0x1) OR " .
        "((droits_acces_forum & 0x10) AND id_groupe IN ($grps)) OR " .
        "(id_groupe_admin IN ($grps)) OR " .
        "((droits_acces_forum & 0x100) AND frm_forum.id_utilisateur='".$user->id."')) ";
    }
	  $query .= "ORDER BY frm_forum.ordre_forum";
	  
    $req = new requete($this->db,$query);
	  
	  $rows = array();
	  
	  while ( $row = $req->get_row() )
	    $rows[] = $row;
	 
	  return $rows;
	}
	
	function get_sujets ( &$user, $st, $npp )
	{
    $query = "SELECT frm_sujet.*, ".
        "frm_message.date_message, " .
        "frm_message.id_message, " .
        "dernier_auteur.alias_utl AS `nom_utilisateur_dernier_auteur`, " .
        "dernier_auteur.id_utilisateur AS `id_utilisateur_dernier`, " .
        "premier_auteur.alias_utl AS `nom_utilisateur_premier_auteur`, " .
        "premier_auteur.id_utilisateur AS `id_utilisateur_premier`, ";
        
    if ( !$user->is_valid() )
      $query .= "0 AS `nonlu` ";
    elseif( is_null($user->tout_lu_avant))
      $query .= "IF(frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL,1,0) AS `nonlu` ";    
    else
      $query .= "IF((frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ".
                "AND frm_message.date_message > '".date("Y-m-d H:i:s",$user->tout_lu_avant)."' ,1,0) AS `nonlu` ";

    $query .= "FROM frm_sujet " .
        "LEFT JOIN frm_message ON ( frm_message.id_message = frm_sujet.id_message_dernier ) " .
        "LEFT JOIN utilisateurs AS `dernier_auteur` ON ( dernier_auteur.id_utilisateur=frm_message.id_utilisateur ) " .
        "LEFT JOIN utilisateurs AS `premier_auteur` ON ( premier_auteur.id_utilisateur=frm_sujet.id_utilisateur ) ";
        
    if ( $user->is_valid() )
      $query .= "LEFT JOIN frm_sujet_utilisateur ".
                   "ON ( frm_sujet_utilisateur.id_sujet=frm_sujet.id_sujet ".
                   "AND frm_sujet_utilisateur.id_utilisateur='".$user->id."' ) ";
                   
    $query .= "WHERE " .
              "id_forum='".$this->id."' ";
	  $query .= "ORDER BY frm_sujet.type_sujet=2 DESC, frm_message.date_message DESC ";
	  $query .= "LIMIT $st, $npp";
	  
    $req = new requete($this->db,$query);
    
	  $rows = array();
	  
	  while ( $row = $req->get_row() )
	    $rows[] = $row;
	 
	  return $rows;
	}
	
	/**
	 * Met à jour le dernier sujet actif, et le nombre de sujets
	 */
	function update_last_sujet ( )
  {
    if ( $this->categorie )
    {
      $req = new requete($this->db, 
        "SELECT SUM(nb_sujets_forum), MAX(id_sujet_dernier) ".
        "FROM `frm_forum` ".
  		  "WHERE `id_forum_parent` = '". mysql_real_escape_string($this->id) . "' ");
      
      list($this->nb_sujets,$this->id_sujet_dernier) = $req->get_row();
    }
    else
    {
      $req = new requete($this->db, 
        "SELECT frm_sujet.id_sujet ".
        "FROM `frm_sujet` ".
        "INNER JOIN `frm_message` ON ( `frm_sujet`.`id_message_dernier` = `frm_message`.`id_message` ) ".
  		  "WHERE `frm_sujet`.`id_forum` = '". mysql_real_escape_string($this->id) . "' ".
  		  "ORDER BY `date_message` DESC ".
  		  "LIMIT 1");
      
      if ( $req->lines == 0 )
        $this->id_sujet_dernier = null;
      else
        list($this->id_sujet_dernier) = $req->get_row();

      $req = new requete($this->db, 
        "SELECT COUNT(*) ".
        "FROM `frm_sujet` ".
  		  "WHERE `id_forum` = '". mysql_real_escape_string($this->id) . "' ");
  		            
      list($this->nb_sujets) = $req->get_row();
      
    }
    
    $req = new update ($this->dbrw, "frm_forum", 
        array("id_sujet_dernier"=>$this->id_sujet_dernier,"nb_sujets_forum"=>$this->nb_sujets),
        array("id_forum"=>$this->id) );     
  
    if ( !is_null($this->id_forum_parent) )
    {
      $parent = new forum($this->db,$this->dbrw);
      $parent->load_by_id($this->id_forum_parent);
      if ( $parent->is_valid() )
        $parent->update_last_sujet();
    }
    
  }
}

?>
