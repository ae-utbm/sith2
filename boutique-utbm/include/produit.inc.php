<?php


/** @file
 *
 * @brief déclaration de la classe produit
 */

/* Copyright 2005,2006,2007,2008
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
 * Classe gérant un produit
 * @see venteproduit
 * @see comptoir
 * @see debitfacture
 * @ingroup comptoirs
 */
class produit extends stdentity
{

  /** Id du type de produit */
  var $id_type;
  /** Nom du produit */
  var $nom;
  /** Prix de vente pour les services (en centimes) */
  var $prix_vente_service;
  /** Prix de vente public (en centimes) */
  var $prix_vente;
  /** Prix d'achat (à titre indicatif) (en centimes) */
  var $prix_achat;
  /** Stock global du produit, -1 si non limité */
  var $stock_global;
  /** Id du fichier utilisé pour la vignette du produit */
  var $id_file;
  /** Description succinte du produit */
  var $description;
  /** Description complète du porduit */
  var $description_longue;
  /** Date de fin de vente du produit (timestamp) @todo à implémenter */
  var $date_fin;
  /** Id du produit parent (null si aucun) si non null, alors ce produit est une
   *  déclinaisaon du produit parent */
  var $id_produit_parent;
  /** A venir retiré aux bureaux où cet objet est vendu (boolénn) */
  var $a_retirer;
  /** etat d'un produit hors commerce, gardé pour archive */
  var $archive;

  /** Cache de l'instance de la classe associée
   * @see get_prodclass
   * @private
   */
  var $cl;


  function get_extra_info (&$user)
  {
    return '';
  }

  /* Class "amies" pouvant modifier les instances
    - VenteProduit
  */

  /**
   * Charge un produit en fonction de son id
   * En cas d'erreur, l'id est définit à null
   * @param $id id du produit
   * @return true en cas du succès, false sinon
   */
  function load_by_id ($id)
  {
    $req = new requete ($this->db, "SELECT * FROM `boutiqueut_produits`
                                    WHERE `id_produit`='".mysql_real_escape_string($id)."'");
    if ( $req->lines == 1 )
    {
      $this->_load($req->get_row());
      return true;
    }
    $this->id = null;
    return false;
  }

  /**
   * Crée un produit
   *
   * Voir documentation des champs de la classe pour la signification des
   * paramètres.
   *
   * @return true si succès, false sinon
   */
  function ajout ($id_typeprod,
      $nom,
      $prix_vente_service,
      $prix_vente,
      $prix_achat,
      $stock_global,
      $id_file,
      $description,
      $description_longue,
      $a_retirer,
      $date_fin=null,
      $id_produit_parent=null
      )
  {

    $this->id_type = $id_typeprod;
    $this->nom = $nom;
    $this->prix_vente_service = intval($prix_vente_service);
    $this->prix_vente = intval($prix_vente);
    $this->prix_achat = intval($prix_achat);
    $this->stock_global = intval($stock_global);
    $this->archive = 0;
    $this->id_file = $id_file;
    $this->description = $description;
    $this->description_longue = $description_longue;

    $this->a_retirer = $a_retirer?1:0;

    $this->date_fin = $date_fin?$date_fin:null;
    $this->id_produit_parent = $id_produit_parent;

    $req = new insert ($this->dbrw,
           "boutiqueut_produits",
           array("id_typeprod" => $this->id_type,
           "nom_prod" => $this->nom,
           "prix_vente_prod_service" => $this->prix_vente_service,
           "prix_vente_prod" => $this->prix_vente,
           "prix_achat_prod" => $this->prix_achat,
           "stock_global_prod" => $this->stock_global,
           "prod_archive" => $this->archive,
           "id_file" => $this->id_file,
           "description_prod" => $this->description,
           "description_longue_prod" => $this->description_longue,

           'a_retirer_prod'=> $this->a_retirer,

           'date_fin_produit'=>is_null($this->date_fin)?null:date("Y-m-d H:i:s",$this->date_fin),
           'id_produit_parent'=> $this->id_produit_parent
            ));

    if ( !$req )
      return false;

    $this->id = $req->get_id();

    return true;
  }

  /**
   * Modifie le produit
   *
   * Voir documentation des champs de la classe pour la signification des
   * paramètres.
   *
   * @return true si succès, false sinon
   */
  function modifier ($id_typeprod,
         $nom,
         $prix_vente_service,
         $prix_vente,
         $prix_achat,
         $stock_global,
         $id_file,
         $description,
         $description_longue,
         $a_retirer,
         $date_fin=null,
         $id_produit_parent=null
         )
  {

    $this->id_type = $id_typeprod;
    $this->nom = $nom;
    $this->prix_vente_service = intval($prix_vente_service);
    $this->prix_vente = intval($prix_vente);
    $this->prix_achat = intval($prix_achat);
    $this->stock_global = intval($stock_global);
    $this->id_file = $id_file;
    $this->description = $description;
    $this->description_longue = $description_longue;

    $this->a_retirer = $a_retirer?1:0;

    $this->date_fin = $date_fin?$date_fin:null;
    $this->id_produit_parent = $id_produit_parent;

    $req = new update ($this->dbrw,
           "boutiqueut_produits",
           array("id_typeprod" => $this->id_type,
           "nom_prod" => $this->nom,
           "prix_vente_prod_service" => $this->prix_vente_service,
           "prix_vente_prod" => $this->prix_vente,
           "prix_achat_prod" => $this->prix_achat,
           "stock_global_prod" => $this->stock_global,
           "id_file" => $this->id_file,
           "description_prod" => $this->description,
           "description_longue_prod" => $this->description_longue,

           'a_retirer_prod'=> $this->a_retirer,

           'date_fin_produit'=>is_null($this->date_fin)?null:date("Y-m-d H:i:s",$this->date_fin),
           'id_produit_parent'=> $this->id_produit_parent
            ),
         array("id_produit" => $this->id));

    if ( !$req )
      return false;

    return true;
  }

  /**
   * Modifie le type du produit
   * @param $id_typeprod Id du type de produit
   * @return true si succès, false sinon
   */
  function modifier_typeprod ($id_typeprod)
  {

    $this->id_type = $id_typeprod;

    $req = new update ($this->dbrw,
           "boutiqueut_produits",
           array("id_typeprod" => $this->id_type
            ),
         array("id_produit" => $this->id));

    if ( !$req )
      return false;

    return true;
  }

  /**
   * Supprime le produit (s'il n'a jamais été vendu)
   * @return true si succès, false sinon
   */
  function supprimer ()
  {
    if ( $this->determine_deja_vendu() )
      return false;

    new delete($this->dbrw,"boutiqueut_produits",array("id_produit" => $this->id));

    return false;
  }

  /**
   * Archivage d'un produit :
   * - le retire de la vente dans tous les comptoirs
   * - le marque comme archivé
   *
   * @return true si succès, false sinon
   */
  function archiver ()
  {

    $req = new update ($this->dbrw,
           "boutiqueut_produits",
           array(
           "prod_archive" => 1
            ),
         array("id_produit" => $this->id));
    if ( !$req )
      return false;

    $this->archive = 1;

    return true;
  }

  /**
   * De-archivage d'un produit : enlève le marquage "archivé"
   *
   * @return true si succès, false sinon
   */
  function dearchiver ()
  {

    $req = new update ($this->dbrw,
           "boutiqueut_produits",
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
    $req = new requete ($this->db, "SELECT count(id) FROM `boutiqueut_vendu`
                                    WHERE id_produit='".$this->id."'");

    list($count) = $req->get_row();

    return $count != 0;
  }

  function _load ($row)
  {
    $this->id = $row['id_produit'];
    $this->id_type = $row['id_typeprod'];
    $this->nom = $row['nom_prod'];
    $this->prix_vente_service = $row['prix_vente_prod_service'];
    $this->prix_vente = $row['prix_vente_prod'];

    $this->prix_achat = $row['prix_achat_prod'];
    $this->stock_global = $row['stock_global_prod'];
    $this->archive = $row['prod_archive'];

    $this->id_file = $row['id_file'];
    $this->description = $row['description_prod'];
    $this->description_longue = $row['description_longue_prod'];

    $this->a_retirer = $row['a_retirer_prod'];

    $this->date_fin = is_null($row['date_fin_produit'])?null:strtotime($row['date_fin_produit']);
    $this->id_produit_parent = $row['id_produit_parent'];
  }

  /**
   * Determine le prix de vente pour un utilisateur
   *
   * @param $prix_barman true si l'utilisateur a droit au prix barman, false sinon
   * @param $user utilisateur à qui le pdoruit va être vendu (intsance de utilisateur)
   * @return le prix (en centimes d'euros)
   */
  function obtenir_prix ($user)
  {
    if($user->is_valid() && $user->type!="srv")
      return $this->prix_vente;
    elseif($user->type=="srv")
      return $this->prix_vente_service;
    else
      return $this->prix_vente;
  }

  /**
   * Détermine si le produit peut être vendu à un utilisateur.
   * Verifie que l'utilisateur fait partie du groupe cible (si définit).
   * Fait appel à la classe associée au produit si disponible.
   *
   * @param $user Utilisateur (instance de utilisateur)
   * @return true si le produit peut être vendu à $user, false sinon
   * @see get_prodclass
   */
  function can_be_sold ( &$user )
  {
    return true;
  }
}

?>
