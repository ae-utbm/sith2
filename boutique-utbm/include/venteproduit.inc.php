<?php


/** @file
 *
 * @brief définition de la classe venteproduit
 *
 */
/* Copyright 2005-2008
 * - Julien Etelain <julien CHEZ pmad POINT net>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
 *
 * Ce fichier fait partie du site de l'Association des Ã©tudiants de
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

require_once("defines.inc.php");
require_once("produit.inc.php");
require_once("debitfacture.inc.php");

/**
 * Classe gérant la mise en vente des produit
 * @author Julien Etelain
 * @author Pierre Mauduit
 * @author Simon Lopez
 */
class venteproduit extends stdentity
{

  var $produit;

  function load_by_id ( $id_produit )
  {
    $this->produit = new produit($this->db);

    if(!$this->produit->load_by_id($id_produit))
      return false;

    return true;
  }

  /**
   * Non supporté
   */
  function _load($row)
  {

  }

  /** Enlève la mise en vente
   *
   */
  function supprime ()
  {
    $this->produit->archiver();
  }


  /** @brief Charge l'objet à l'aide d'un produit (objet)
   *  et d'un comptoir (objet) et verifie la disponiblité en stock
   *
   * @param produit une référence vers un objet produit (?)
   *
   * @return true si succès, false si le produit n'est pas en vente on n'est plus en stock
   *
   */
  function charge ( $produit )
  {

    if ( !$this->_charge(&$produit) )
      return false;

    if ($produit->stock_global == 0)
    {
      unset($this->produit);
      return false;
    }

    return true;
  }

  /** @brief Charge l'objet à l'aide d'un produit (objet)
   *
   * @param produit une référence vers un objet produit
   * @param comptoir une référence vers un objet comptoir
   *
   * @return true si succès, false sinon
   *
   */
  function _charge ( $produit )
  {

    $req = new requete($this->db,
           "SELECT `boutiqueut_produits`.`stock_global_prod` ".
           "FROM `boutiqueut_produits` ".
           "WHERE `boutiqueut_produits`.`id_produit` = '".intval($produit->id)."' ".
           "AND (`boutiqueut_produits`.date_fin_produit IS NULL OR `boutiqueut_produits`.date_fin_produit>NOW()) ".
           "LIMIT 1");

    if ($req->lines < 1)
      return false;

    list($produit->stock_global) = $req->get_row();

    $this->produit = &$produit;

    return true;
  }


  /** Incremente le stock
   * @private
   */
  function _increment ( $qte=1 )
  {
    if ($this->produit->stock_global != -1 )
    {
      $req = new requete($this->dbrw,
         "UPDATE `boutiqueut_produits` ".
         "SET `stock_global_prod` = `stock_global_prod`+$qte ".
         "WHERE `id_produit` = '".intval($this->produit->id)."' ".
         "LIMIT 1");
      $this->produit->stock_global=$this->produit->stock_global+$qte;
    }
  }

  /** Decremente le stock
   * @return true s'il y a eu decrementation, sinon false
   * @private
   */
  function _decrement ( $qte=1 )
  {
    $altered = false;

    if ($this->produit->stock_global >= $qte)
    {
      $req = new requete($this->dbrw,
         "UPDATE `boutiqueut_produits` ".
         "SET `stock_global_prod` = `stock_global_prod` - $qte ".
         "WHERE `id_produit` = '".intval($this->produit->id)."' ".
         "LIMIT 1");
      $this->produit->stock_global=$this->produit->stock_global-$qte;
      $altered = true;
    }

    return $altered;
  }



  /** Reserve un produit un utilisateur
   *
   * @return false si le produit n'est pas en stock, true si disponible
   */
  function bloquer ( $client, $qte=1 )
  {
    if ($this->produit->stock_global < $qte
        && $this->produit->stock_global != -1)
      return false;
    if ($this->_decrement($qte))
      $this->_delta_verrou($client, $qte);

    return true;
  }

  /** (usage interne UNIQUEMENT)
   * Fait varier le nombre de produits sur le comptoir réservé pour le client
   * @param $delta variation de la réservation (+1 pour réserver un produit, -1 pour enlever la reservation d'un produit)
   * @return variation effective
   * @private
   */
  function _delta_verrou ( $client, $delta )
  {
    $id_client = $client->id;

    if ( !$client->is_valid() )
      $id_client = 0;

    $req = new requete($this->db,
           "SELECT `quantite` FROM `boutiqueut_verrou` ".
           "WHERE `id_produit` = '".intval($this->produit->id)."' ".
           "AND `id_utilisateur` = '".intval($id_client)."' ");

    if ( $req->lines==1 )
    {
      list($qte) = $req->get_row();
      if ( $qte+$delta <= 0 )
      {
        $req = new requete($this->dbrw,
             "DELETE FROM `boutiqueut_verrou` ".
             "WHERE `id_produit` = '".intval($this->produit->id)."' ".
             "AND `id_utilisateur` = '".intval($id_client)."'");

        return abs($qte);
      }
      else
      {
        $req = new requete($this->dbrw,
             "UPDATE `boutiqueut_verrou` SET " .
             "`quantite` = `quantite` + ($delta), `date_res` = NOW() ".
             "WHERE `id_produit` = '".intval($this->produit->id)."' ".
             "AND `id_utilisateur` = '".intval($id_client)."'");
        return abs($delta);
      }

    }
    elseif ( $delta > 0 )
    {
      $req = new insert ($this->dbrw,
                    "boutiqueut_verrou",
                    array(
                      "id_produit" => $this->produit->id,
                      "id_utilisateur" => $id_client,
                      "date_res" => date("Y-m-d H:i:s"),
                      "quantite"=>$delta
                    )
                    );
      return abs($delta);
    }
    return 0;
  }

  /**
   * Enleve la reservation sur le produit
   * A utiliser pour annuler un blocage
   */
  function debloquer ( $client, $qte=1 )
  {
    $res = $this->_delta_verrou($client, -$qte);
    if ( $res != 0 )
      $this->_increment($res);
  }

  /**
   * Procéde aux actions suite à la vente effective d'un produit qui a été bloqué avec bloquer()
   */
  function vendu_bloque ( $client, $qte=1 )
  {
    $this->debloquer($client,$qte);
    $this->_decrement($qte);
  }

}
?>
