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
 
define("SUJET_NORMAL",1);
define("SUJET_STICK",2);
define("SUJET_ANNONCE",3);
define("SUJET_ANNONCESITE",4);

/**
 * Sujet dans un forum
 */
class sujet extends stdentity
{

  var $id_utilisateur;
  var $id_forum;
  
  var $titre;
  var $soustitre;
  var $type;
  var $icon;
  var $date;
  
  var $id_message_dernier;
  var $nb_messages;
  
  var $date_fin_annonce;

  var $id_utilisateur_moderateur;

  var $id_nouvelle;
  var $id_catph;
  var $id_sondage;

  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `frm_sujet`
				WHERE `id_sujet` = '" .
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

  function _load($row)
  {
    $this->id = $row["id_sujet"];
    $this->id_utilisateur = $row["id_utilisateur"];
    $this->id_forum = $row["id_forum"];
    $this->titre = $row["titre_sujet"];
    $this->soustitre = $row["soustitre_sujet"];
    $this->type = $row["type_sujet"];
    $this->icon = $row["icon_sujet"];
    $this->date = strtotime($row["date_sujet"]);
    $this->id_message_dernier = $row["id_message_dernier"];
    $this->nb_messages = $row["nb_messages_sujet"];
    $this->date_fin_annonce = strtotime($row["date_fin_annonce_sujet"]);
    $this->id_utilisateur_moderateur = $row["id_utilisateur_moderateur"];
    $this->id_nouvelle = $row["id_nouvelle"];
    $this->id_catph = $row["id_catph"];
    $this->id_sondage = $row["id_sondage"];
  }

  function create ( &$forum, $id_utilisateur, $titre, $soustitre=null,
      $type=SUJET_NORMAL,$icon=null,$date_fin_annonce=null,
      $id_nouvelle=null,$id_catph=null,$id_sondage=null   )
  {
  
    /**@TODO: tester droit d'écriture*/
  
    $this->id_utilisateur=$id_utilisateur;
    $this->id_forum=$forum->id;
  
    $this->titre=$titre;
    $this->soustitre=$soustitre;
    $this->type=$type;
    $this->icon=$icon;
    $this->date=time();
  
    $this->id_message_dernier=null;
    $this->nb_messages=0;  
    
    $this->date_fin_annonce=$date_fin_annonce;

    $this->id_utilisateur_moderateur=null;

    $this->id_nouvelle=$id_nouvelle;
    $this->id_catph=$id_catph;
    $this->id_sondage=$id_sondage;  
    
    
    $req = new insert ($this->dbrw,
            "frm_sujet", array(
              "id_utilisateur"=>$this->id_utilisateur,
              "id_forum"=>$this->id_forum,
              "titre_sujet"=>$this->titre,
              "soustitre_sujet"=>$this->soustitre,
              "type_sujet"=>$this->type,
              "icon_sujet"=>$this->icon,
              "date_sujet"=>date("Y-m-d H:i:s",$this->date),
              "id_message_dernier"=>$this->id_message_dernier,
              "nb_messages_sujet"=>$this->nb_messages,
              "date_fin_annonce_sujet"=>date("Y-m-d H:i:s",$this->date_fin_annonce),
              "id_utilisateur_moderateur"=>$this->id_utilisateur_moderateur,
              "id_nouvelle"=>$this->id_nouvelle,
              "id_catph"=>$this->id_catph,
              "id_sondage"=>$this->id_sondage
            ));
  
		if ( $req )
		{
			$this->id = $req->get_id();
		  return true;
		}
		
		$this->id = null;
    return false;
  }
  //      $forum->update_last_sujet(); 

  function update ( $titre, $soustitre=null,
      $type=SUJET_NORMAL,$icon=null,$date_fin_annonce=null,
      $id_nouvelle=null,$id_catph=null,$id_sondage=null   )
  {
  
    $this->titre=$titre;
    $this->soustitre=$soustitre;
    $this->type=$type;
    $this->icon=$icon;
  
    $this->date_fin_annonce=$date_fin_annonce;

    $this->id_utilisateur_moderateur=null;

    $this->id_nouvelle=$id_nouvelle;
    $this->id_catph=$id_catph;
    $this->id_sondage=$id_sondage;  
    
    $req = new update ($this->dbrw,
            "frm_sujet", array(
              "id_forum"=>$this->id_forum,
              "titre_sujet"=>$this->titre,
              "soustitre_sujet"=>$this->soustitre,
              "type_sujet"=>$this->type,
              "icon_sujet"=>$this->icon,
              "date_fin_annonce_sujet"=>date("Y-m-d H:i:s",$this->date_fin_annonce),
              "id_utilisateur_moderateur"=>$this->id_utilisateur_moderateur,
              "id_nouvelle"=>$this->id_nouvelle,
              "id_catph"=>$this->id_catph,
              "id_sondage"=>$this->id_sondage
            ),
            array("id_sujet"=>$this->id) );
  

  }
  
  function move_to ( &$forum_old, &$forum_new )
  {
    if ( $forum_old->id != $sujet->id_forum )
      return;    
      
    $this->id_forum=$forum_new->id;
    
    $req = new update ($this->dbrw,"frm_sujet",array("id_forum"=>$this->id_forum),array("id_sujet"=>$this->id) );
         
    $forum_old->update_last_sujet();
    $forum_new->update_last_sujet();
  }
  
  /**
   * Supprime le sujet et tous les éléments dépendants
   */
  function delete ( &$forum )
  {
    if ( $forum->id != $this->id_forum )
      return;
    
    if ( !$this->dbrw ) return;
    new delete($this->dbrw,"frm_sujet",array("id_sujet"=>$this->id));
    new delete($this->dbrw,"frm_message",array("id_sujet"=>$this->id));
    new delete($this->dbrw,"frm_sujet_utilisateur",array("id_sujet"=>$this->id));
    $this->id = null;
    
    $forum->update_last_sujet();
  }

  /**
   * Met à jour le dernier message posté et le nombre de messages
   */
	function update_last_message ( &$forum )
  {
    $req = new requete($this->db, 
      "SELECT id_message ".
      "FROM `frm_message` ".
		  "WHERE `id_sujet` = '". mysql_real_escape_string($this->id) . "' ".
		  "ORDER BY `date_message` DESC ".
		  "LIMIT 1");
    
    if ( $req->lines == 0 )
    {
      $this->delete($forum);
      return;
    }
    else
      list($this->id_message_dernier) = $req->get_row();
  
    $req = new requete($this->db, 
      "SELECT COUNT(*) ".
      "FROM `frm_message` ".
		  "WHERE `id_sujet` = '". mysql_real_escape_string($this->id) . "' ");
		            
    list($this->nb_messages) = $req->get_row();
              
    $req = new update ($this->dbrw, "frm_sujet", 
        array("nb_messages_sujet"=>$this->nb_messages,"id_message_dernier"=>$this->id_message_dernier),
        array("id_sujet"=>$this->id) );              
  }

  /**
   * Determine l'id du dernier message lu du sujet par un utilisateur
   * @param $id_utilisateur Id de l'utilisateur
   * @return l'id du dernier message lu, ou sinon null si l'utilisateur n'a jamais
   * lu le sujet.
   */
  function get_last_read_message ( $id_utilisateur )
  {
    $req = new requete($this->db, "SELECT id_message_dernier_lu FROM `frm_sujet_utilisateur`
				WHERE `id_sujet` = '".mysql_real_escape_string($this->id) . "'
		    AND `id_utilisateur` = '".mysql_real_escape_string($id_utilisateur) . "'
				LIMIT 1");
				
		if ( $req->lines == 0 )
		  return null;
		
		$row = $req->get_row();
		return $row['id_message_dernier_lu'];
  }

  /**
   * Définit que l'utilisateur a lu le sujet jusqu'a un message (facultatif)
   * @param $id_utilisateur Id de l'utilisateur
   * @param $max_id_message Dernier message su sujet lu par l'utilisateur (si non précisié utilise $this->id_message_dernier)
   */
  function set_user_read ( $id_utilisateur, $max_id_message=null  )
  {
    if ( is_null($max_id_message) )
      $max_id_message = $this->id_message_dernier;
  
    $user_id_message = $this->get_last_read_message($id_utilisateur);
    
    if ( is_null($user_id_message) )
    {
      $req = new insert ($this->dbrw,
            "frm_sujet_utilisateur", 
            array("id_message_dernier_lu"=>$max_id_message,"id_sujet"=>$this->id,"id_utilisateur"=>$id_utilisateur) );    
    }
    elseif ( $max_id_message > $user_id_message )
    {
      $req = new update ($this->dbrw,
            "frm_sujet_utilisateur", 
            array("id_message_dernier_lu"=>$max_id_message),
            array("id_sujet"=>$this->id,"id_utilisateur"=>$id_utilisateur) );
    }
  }

  function get_messages ( &$user, $st, $npp, $order = 'ASC' )
  {
    if ($order != 'ASC')
      $order = 'DESC';
    else
      $order = 'ASC';

    $query = "SELECT frm_message.*, ".
        "utilisateurs.alias_utl, " .
        "utilisateurs.id_utilisateur, " .
        "utilisateurs.signature_utl " .
        "FROM frm_message " .
        "LEFT JOIN utilisateurs ON ( utilisateurs.id_utilisateur=frm_message.id_utilisateur ) " .
        "WHERE id_sujet='".$this->id."' " .
        "ORDER BY frm_message.id_message $order ".
        "LIMIT $st, $npp";

    $req = new requete($this->db,$query);
    
	  $rows = array();
	  
	  while ( $row = $req->get_row() )
	    $rows[] = $row;
	 
	  return $rows;
  }

}

?>
