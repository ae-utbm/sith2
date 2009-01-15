<?php

/* Copyright 2006,2008
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

/**
 * @file
 * Gestion des factures (et debits) des comptes AE et de l'eboutic.
 */

/**
 * Classe gérant les factures cartes AE/e-boutic. Elle permet le debit sur les comptes AE.
 * @see venteproduit
 * @ingroup comptoirs
 */
class debitfacture extends stdentity
{

  /** Id du client */
  var $id_utilisateur;
  /** date de la vente */
  var $date;
  /** Mode de paiement AE ou SG */
  var $mode;
  /** montant en centimes */
  var $montant;
  /** Etat */
  var $etat;
  /** Ready */
  var $ready;


  /**
   * Charge la facture en fonction de son ID
   * @param $id Id de la facture
   */
  function load_by_id ( $id)
  {

    $req = new requete($this->db,"SELECT * FROM boutiqueut_debitfacture WHERE id_facture='".intval($id)."'");

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
    $this->id = $row['id_facture'];
    $this->id_utilisateur = $row['id_utilisateur'];
    $this->date = strtotime($row['date_facture']);
    $this->mode = $row['mode_paiement'];
    $this->montant = $row['montant_facture'];
    $this->transacid = $row['transacid'];
    $this->etat = $row['etat_facture'];
    $this->ready = $row['ready'];
  }

  /**
   * Procéde à un debit sur un compte AE
   * @param $client Instance d'utilisateur, le client qui va être débité
   * @param $vendeur Instance d'utilisateur, personne prenant la responsabilité de l'opération
   * @param $comptoir Instance de comptoir, lieu où s'est faite la vente
   * @param $panier Panier, tableau contenant des instances de venteproduit de la forme array(array(quatité,venteproduit))
   * @param $prix_barman Utilise le prix barman ou non (true:prix barman, false: prix publique)
   * @param $etat Etat initial de retrait/expedition. Si commande à expédier ETAT_FACT_A_EXPEDIER, sinon 0
   * @return false en cas de problème (solde insuffisent, erreur sql) sinon true
   * @see venteproduit
   */
  function debit ( $client, $panier, $etat=0 )
  {
    $this->id_utilisateur = $client->id;
    $this->date = time();
    $this->etat = $etat;

    $this->montant = $this->calcul_montant($panier,$client);

    $req = new insert ($this->dbrw,
           "boutiqueut_debitfacture",
           array(
           "id_utilisateur" => $this->id_utilisateur,
           "date_facture" => date("Y-m-d H:i:s",$this->date),
           "montant_facture" => $this->montant,
           "etat_facture" => $this->etat,
           "ready" => 1
         ));

    if ( !$req )
      return false;

    $this->id = $req->get_id();

    $this->traiter_panier($client,$panier, $prix_service);

    $this->send_emails($client);

    return true;
  }

  function send_emails ()
  {
    $body = "Bonjour,
Vous vennez d'effectuer une commande sur la boutique utbm.

Pour suivre l'avancement de votre commande, rendez vous à l'adresse suivante :
http://ae.utbm.fr/boutique-utbm/suivi.php?id_facture=".$this->id."

A chacune des étapes de traitement de votre commande, vous serrez informé par email.

Cordialement,
La boutique utbm";

    $ret = mail($client->email,
                "[boutique utbm] confirmation de commande",
                utf8_decode($body),
                "From: \"La boutique utbm\" <boutique@utbm.fr>\nReply-To: boutique@utbm.fr");

    $body = "Bonjour,
Une commande est en attente de préparation.

Pour consulter la commande, rendez vous à l'adresse suivante :
http://ae.utbm.fr/boutique-utbm/traitement.php?id_facture=".$this->id."

Cordialement,
La boutique utbm";
    $ret = mail("simon.lopez@utbm.fr",
                "[boutique utbm] nouvelle commande",
                utf8_decode($body),
                "From: \"La boutique utbm\" <boutique@utbm.fr>\nReply-To: boutique@utbm.fr");

  }

  /**
   * Calcule le montant d'un panier
   * @param $panier Panier, tableau contenant des instances de venteproduit de la forme array(array(quatité,venteproduit))
   * @param $prix_barman Utilise le prix barman ou non (true:prix barman, false: prix publique)
   * @return le montant en centimes
   */
  function calcul_montant ( $panier, &$client )
  {
    $montant = 0;
    foreach ( $panier as $item )
    {
      list($quantite,$vp) = $item;
      $montant += $quantite * $vp->produit->obtenir_prix($client);
    }

    return $montant;
  }

  /**
   * Modifie l'état de la facture (expedition/retrait)
   * @param $etat Nouvel etat de la facture
   */
  function set_etat ( $etat )
  {
    if ( $this->etat != $etat )
    {
      $this->etat = $etat;
      $req = new update ($this->dbrw,"boutiqueut_debitfacture",array("etat_facture" => $this->etat),array("id_facture" => $this->id));
    }
  }

  function set_ready ( $etat )
  {
    if ( $this->ready != $etat )
    {
      $this->ready = $etat;
      $req = new update ($this->dbrw,"boutiqueut_debitfacture",array("ready" => $this->ready),array("id_facture" => $this->id));
    }
  }


  /**
   * Procède à la "vente" de l'ensemble des produits (Usage strictement interne).
   *
   * - met à jours les stocks
   * - procède aux actions des produits (comme pour les cotisations)
   * - met à jour l'état de retrait/expedition de la facture [si $eboutic est à true]
   * - archive les produits vendus (dans la table boutiqueut_vendu)
   * - marque les produits à retirer/à expédier [si $eboutic est à true]
   *
   * @param $client Utilisateur client (instance de utilisateur)
   * @param $client Utilisateur vendeur (instance de utilisateur) (premier barman, ou client si e-boutic)
   * @param $panier Panier, tableau contenant des instances de venteproduit de la forme array(array(quatité,venteproduit))
   * @param $prix_barman Utilise le prix barman ou non (true:prix barman, false: prix publique)
   * @param $asso_sum Met à jour la comme de contrôle des associations qui vendent les produits (seulement en mode carte AE) (obsolète)
   * @param $eboutic Vente sur un comptoir eboutic, procède aux mises à jour de l'état de retrait/expedition
   * @private
   */
  function traiter_panier ( $client, $panier, $prix_service )
  {
    foreach ( $panier as $item )
    {
      list($quantite,$vp) = $item;
      $a_expedier=NULL;
      $a_retirer=NULL;

      if ( $vp->produit->a_retirer )
      {
        $this->set_etat( 1 );
        $this->set_ready( 0 );
        $a_retirer=1;
      }

      $prix = $vp->produit->obtenir_prix($client);

      $req = new insert ($this->dbrw,
             "boutiqueut_vendu",
             array(
               "id_facture" => $this->id,
               "id_produit" => $vp->produit->id,
               "quantite" => $quantite,
               "prix_unit" => $prix,
               "a_retirer_vente" => $a_retirer
             ));

      $vp->vendu_bloque($client,$prix,$quantite);
    }
  }

  /**
   * Marque un produit de la facture comme retiré
   * @param $id_produit Id du produit
   */
  function set_retire ( $id_produit)
  {
    $req = new update ($this->dbrw,"boutiqueut_vendu",
      array("a_retirer_vente" => 0 ),
      array("id_facture" => $this->id,"id_produit"=>$id_produit));

    $this->recalcul_ready_state();
  }

  /**
   * Met à jour l'état de retrait de la facture
   * @private
   */
  function recalcul_ready_state()
  {
    $req = new requete($this->db, "SELECT COUNT(*) ".
      "FROM `boutiqueut_vendu` " .
      "WHERE `id_facture`='".$this->id."' AND a_retirer_vente='1'");

    list($nb) = $req->get_row();

    if ( $nb == 0 )
    {
      $this->set_etat( 1 );
      $this->set_ready( 1 );
    }
  }


}

/**@}*/
?>
