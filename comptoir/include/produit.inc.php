<?php


/** @file
 *
 * @brief déclaration de la classe produit
 */

/* Copyright 2005
 * - Julien Etelain <julien CHEZ pmad POINT net>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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
 * @addtogroup comptoirs
 * @{
 */

/**
 * Classe gérant un produit
 */
class produit extends stdentity
{

  var $id_type;
  var $id_assocpt;
  var $nom;
  var $prix_vente_barman;
  var $prix_vente;
  var $prix_achat;
  var $meta;
  var $action;
  var $code_barre;
  var $stock_global;

  var $id_file;
  var $description;
  var $description_longue;


  var $id_groupe;
  var $date_fin;
  var $id_produit_parent;

  // Produit à venir retirer si vendu depuis un comptoir "e-boutic", ou expediable par la poste
  /** A venir retiré aux bureaux où cet objet est vendu (boolénn) */
  var $a_retirer;
  /** Envoyable par la poste (booléen) (non disponible pour le moment) */
  var $postable;
  /** Frais de port de l'objet en centimes (non disponible pour le moment) */
  var $frais_port;

  /** etat d'un produit hors commerce, gardé pour archive */
  var $archive;

	var $cl;


  /* Class "amies" pouvant modifier les instances
		- VenteProduit
  */

  /** @brief chargement d'un produit par son identifiant
   *
   * @param id l'identifiant du produit
   *
   */
  function load_by_id ($id)
  {

    /* les SELECT *, ca craint */
    $req = new requete ($this->db, "SELECT * FROM `cpt_produits`
                                    WHERE `id_produit`='".mysql_real_escape_string($id)."'");
		
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;

  }

  /** @brief chargement par code barre
   *
   * @param code_barre le code barre du produit
   *
   */
  function charge_par_code_barre ($code_barre)
  {

    $req = new requete($this->db, "SELECT * FROM `cpt_produits`
                                   WHERE `cbarre_prod` = '".mysql_real_escape_string($code_barre)."'");

		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
  }

  /** @brief ajout d'un produit dans la base
   *
   * @param type le type de produit
   * @param association l'association concern�e
   * @param nom un nom g�n�rique pour le produit vendu
   * @param prix_vente_barman �quivalent du prix coutant
   * @param prix_vente le prix de vente public
   * @param prix_achat prix d'achat brut au fournisseur
   * @param meta meta-action (parametres)
   * @param action action � effectuer � l'achat
   * @param code_barre le code barre du produit
   *
   * @return true si succ�s, false sinon
   *
   */
  function ajout ($id_typeprod,
		  $id_assocpt,
		  $nom,
		  $prix_vente_barman,
		  $prix_vente,
		  $prix_achat,
		  $meta,
		  $action,
		  $code_barre,
		  $stock_global,
		  $id_file,
		  $description,
		  $description_longue,
		  $a_retirer,
		  $postable,
		  $frais_port,
		  $id_groupe=null,
		  $date_fin=null,
		  $id_produit_parent=null)
  {

    $this->id_type = $id_typeprod;
    $this->id_assocpt = $id_assocpt;
    $this->nom = $nom;
    $this->prix_vente_barman = intval($prix_vente_barman);
    $this->prix_vente = intval($prix_vente);
    $this->prix_achat = intval($prix_achat);
    $this->meta = $meta;
    $this->action = intval($action);
    $this->code_barre = $code_barre;
    $this->stock_global = intval($stock_global);
    $this->archive = 0;
    $this->id_file = $id_file;
    $this->description = $description;
    $this->description_longue = $description_longue;
    
    $this->a_retirer = $a_retirer?1:0;    
    $this->postable = $postable?1:0;    
    $this->frais_port = intval(frais_port);  
    
    $this->id_groupe = $id_groupe?$this->id_groupe:null;
    $this->date_fin = $date_fin;
    $this->id_produit_parent = $id_produit_parent;


    $req = new insert ($this->dbrw,
		       "cpt_produits",
		       array("id_typeprod" => $this->id_type,
			     "id_assocpt" => $this->id_assocpt,
			     "nom_prod" => $this->nom,
			     "prix_vente_barman_prod" => $this->prix_vente_barman,
			     "prix_vente_prod" => $this->prix_vente,
			     "prix_achat_prod" => $this->prix_achat,
			     "meta_action_prod" => $this->meta,
			     "action_prod" => $this->action,
			     "cbarre_prod" => $this->code_barre,
			     "stock_global_prod" => $this->stock_global,
			     "prod_archive" => $this->archive,
			     "id_file" => $this->id_file,
			     "description_prod" => $this->description,
			     "description_longue_prod" => $this->description_longue,

			     'frais_port_prod' => $this->frais_port,
			     'postable_prod' => $this->postable,
			     'a_retirer_prod'=> $this->a_retirer,
			     
			     'id_groupe'=>$this->id_groupe,
			     'date_fin_produit'=>is_null($this->date_fin)?null:date("Y-m-d H:i:s",$this->date_fin),
			     'id_produit_parent'=> $this->id_produit_parent	     
			     
			     
			      ));

    if ( !$req )
    	return false;

    $this->id = $req->get_id();

    return true;
  }
  /** @brief modification d'un produit dans la base
   *
   * @param type le type de produit
   * @param association l'association concern�e
   * @param nom un nom g�n�rique pour le produit vendu
   * @param prix_vente_barman �quivalent du prix coutant
   * @param prix_vente le prix de vente public
   * @param prix_achat prix d'achat brut au fournisseur
   * @param meta meta-action (parametres)
   * @param action action � effectuer � l'achat
   * @param code_barre le code barre du produit
   *
   * @return true si succ�s, false sinon
   *
   */
  function modifier ($id_typeprod,
		     $nom,
		     $prix_vente_barman,
		     $prix_vente,
		     $prix_achat,
		     $meta,
		     $action,
		     $code_barre,
		     $stock_global,
		     $id_file,
		     $description,
		     $description_longue,
		     $id_assocpt,
		     $a_retirer,
		     $postable,
		     $frais_port,
		     $id_groupe=null,
		     $date_fin=null,
         $id_produit_parent=null
		     )
  {

    $this->id_type = $id_typeprod;
    $this->nom = $nom;
    $this->prix_vente_barman = intval($prix_vente_barman);
    $this->prix_vente = intval($prix_vente);
    $this->prix_achat = intval($prix_achat);
    $this->meta = $meta;
    $this->action = intval($action);
    $this->code_barre = $code_barre;
    $this->stock_global = intval($stock_global);
    $this->id_file = $id_file;
    $this->description = $description;
    $this->description_longue = $description_longue;
    $this->id_assocpt = $id_assocpt;
    
    $this->a_retirer = $a_retirer?1:0;    
    $this->postable = $postable?1:0;    
    $this->frais_port = intval(frais_port);     
    
    $this->id_groupe = $id_groupe?$this->id_groupe:null;
    $this->date_fin = $date_fin;
    $this->id_produit_parent = $id_produit_parent;

    
    $req = new update ($this->dbrw,
		       "cpt_produits",
		       array("id_typeprod" => $this->id_type,
			     "id_assocpt" => $this->id_assocpt,
			     "nom_prod" => $this->nom,
			     "prix_vente_barman_prod" => $this->prix_vente_barman,
			     "prix_vente_prod" => $this->prix_vente,
			     "prix_achat_prod" => $this->prix_achat,
			     "meta_action_prod" => $this->meta,
			     "action_prod" => $this->action,
			     "cbarre_prod" => $this->code_barre,
			     "stock_global_prod" => $this->stock_global,
			     "id_file" => $this->id_file,
			     "description_prod" => $this->description,
			     "description_longue_prod" => $this->description_longue,
			     
			     'frais_port_prod' => $this->frais_port,
			     'postable_prod' => $this->postable,
			     'a_retirer_prod'=> $this->a_retirer,
			     
			     'id_groupe'=>$this->id_groupe,
			     'date_fin_produit'=>is_null($this->date_fin)?null:date("Y-m-d H:i:s",$this->date_fin),
			     'id_produit_parent'=> $this->id_produit_parent
			     
			      ),
			   array("id_produit" => $this->id));

    if ( !$req )
    	return false;

    return true;
  }
  
  function modifier_typeprod ($id_typeprod)
  {

    $this->id_type = $id_typeprod;

    $req = new update ($this->dbrw,
		       "cpt_produits",
		       array("id_typeprod" => $this->id_type
			      ),
			   array("id_produit" => $this->id));

    if ( !$req )
    	return false;

    return true;
  }
  
  function supprimer ()
  {
    if ( $this->determine_deja_vendu() )
      return false;


    /*
    TODO
    */

    return false;
  }

  /** @brief archivage d'un produit
   *
   * @return true si succ�s, false sinon
   *
   */
  function archiver ()
  {

    $req = new update ($this->dbrw,
		       "cpt_produits",
		       array(
			     "prod_archive" => 1
			      ),
			   array("id_produit" => $this->id));
    if ( !$req )
    	return false;

    $this->archive = 1;
     
		$req = new delete($this->dbrw,"cpt_mise_en_vente",array("id_produit" => $this->id));
     
    return true;
  }

  /** @brief de-archivage d'un produit
   *
   * @return true si succ�s, false sinon
   *
   */
  function dearchiver ()
  {

    $req = new update ($this->dbrw,
		       "cpt_produits",
		       array(
			     "prod_archive" => 0
			      ),
			   array("id_produit" => $this->id));
    if ( !$req )
    	return false;

    $this->archive = 0;

    return true;
  }

  function determine_deja_vendu ()
  {
    $req = new requete ($this->db, "SELECT count(id) FROM `cpt_vendu`
                                    WHERE id_produit='".$this->id."'");
    echo mysql_error();

    list($count) = $req->get_row();

    return $count != 0;
  }

  /*
   * Fonctions priv�es
   */

  /** @brief chargement des donn�es
   *
   * @param un tableau provenant des resultats de mySQL
   * @private
   */
  function _load ($row)
  {
    $this->id = $row['id_produit'];
    $this->id_type = $row['id_typeprod'];
    $this->id_assocpt = $row['id_assocpt'];
    $this->nom = $row['nom_prod'];
    $this->prix_vente_barman = $row['prix_vente_barman_prod'];
    $this->prix_vente = $row['prix_vente_prod'];
    
    $this->prix_achat = $row['prix_achat_prod'];
    $this->meta = $row['meta_action_prod'];
    $this->action = $row['action_prod'];
    $this->code_barre = $row['cbarre_prod'];
    $this->stock_global = $row['stock_global_prod'];
    $this->archive = $row['prod_archive'];
    
    $this->id_file = $row['id_file'];
    $this->description = $row['description_prod'];    
    $this->description_longue = $row['description_longue_prod'];    
    
    $this->a_retirer = $row['a_retirer_prod'];    
    $this->postable = $row['postable_prod'];    
    $this->frais_port = $row['frais_port_prod'];    
    
    $this->id_groupe = $row['id_groupe'];    
    $this->date_fin = is_null($row['date_fin_produit'])?null:strtotime($row['date_fin_produit']);    
    $this->id_produit_parent = $row['id_produit_parent'];    
    
  }
  
  /** @brief obtention d'un prix de vente
   *
   * @param prix_barman (optionnel) selectionne ou pas le prix
   *        avantageux barman ou non
   *
   * @return (int) le prix (en centimes d'euros)
   * @private
   */
  function obtenir_prix ($barman)
  {
    return $barman ? $this->prix_vente_barman : $this->prix_vente;
  }
  
  function can_be_sold ( &$user )
  {
	  if ( !is_null($this->id_groupe) && !$user->is_in_group_id($this->id_groupe) )
	    return false;
	  
	  if ( $this->action == ACTION_CLASS )
	  {
      $this->get_prodclass();
      return $this->cl->can_be_sold($user);
	  }
	  
    return true;
  }
  
  
	function get_prodclass()
	{
		global $topdir;
		
		if ( $this->cl )
			return $this->cl;
		
	  	if ( $this->action != ACTION_CLASS )
	  		return NULL;
	  		
	  	$regs=null;	
	  		
	  	if ( !ereg("^([a-z]+)\((.*)\)$",$this->meta,$regs))
	  		return NULL;
	  		
	  	$class = $regs[1]; // que des lettes minuscules
	  	$param = $regs[2]; // 
	  	
	  	if ( !class_exists($class))
	  	{
		  	if ( !file_exists($topdir."comptoir/include/class/".$class.".inc.php") )
		  		return NULL;
		  		
		  	include($topdir."comptoir/include/class/".$class.".inc.php");
	  	}
	  	
	  	$this->cl = new $class ( $this->db, $this->dbrw, $param );	
	  	
	  	return $this->cl;
	}
  
  
}
?>
