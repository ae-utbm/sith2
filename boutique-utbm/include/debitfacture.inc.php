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
  /** Nom utl */
  var $nom=null;
  /** Prénom utl */
  var $prenom=null;
  /** Adresse */
  var $adresse=null;
  /** eotp */
  var $eotp=null;
  var $contact= null;
  var $centre_financier = null;
  var $centre_cout = null;
  /** objectif */
  var $objectif=null;
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
    $this->id               = $row['id_facture'];
    $this->id_utilisateur   = $row['id_utilisateur'];
    $this->nom              = $row['nom'];
    $this->prenom           = $row['prenom'];
    $this->adresse          = $row['adresse'];
    $this->date             = strtotime($row['date_facture']);
    $this->mode             = $row['mode_paiement'];
    $this->montant          = $row['montant_facture'];
    $this->transacid        = $row['transacid'];
    $this->etat             = $row['etat_facture'];
    $this->ready            = $row['ready'];
    $this->objectif         = $row['objectif'];
    $this->eotp             = $row['eotp'];
    $this->contact          = $row['contact'];
    $this->centre_financier = $row['centre_financier'];
    $this->centre_cout      = $row['centre_cout'];
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
  function debit ( $client, $panier, $etat=1, $ready=0 ,$mode='UT',$nom=null,$prenom=null,$adresse=null,$objectif=null,$eotp=null,$centre_cout=null)
  {
/*
ready=1+etat=1 : à retirer
ready=1+etat=0 : retiré
etat=1+ready=0 : en préparation
*/
    $this->id_utilisateur = $client->id;
    $this->date = time();
    $this->etat = $etat;
    $this->ready = $ready;
    $this->nom=$nom;
    $this->prenom=$prenom;
    $this->adresse=$adresse;
    $this->mode=$mode;
    $this->montant = $this->calcul_montant($panier,$client);
    if($client->is_valid() && $client->type=='srv')
    {
      $req = new requete($this->db,'SELECT centre_financier FROM boutiqueut_service_utl WHERE id_utilisateur='.$client->id);
      if($req->lines==1)
      {
        list($cf)           = $req->get_row();
        $this->centre_financier = $cf;
      }
      if(!is_null($eotp) && !empty($eotp))
        $this->eotp=$eotp;
      if(!is_null($objectif) && !empty($objectif))
        $this->objectif=$objectif;
      if(!is_null($centre_cout))
      {
        $req = new requete($this->db,
                           'SELECT centre_cout,contact FROM boutiqueut_centre_cout WHERE id_utilisateur='.
                           $client->id.
                           ' AND centre_cout=\''.mysql_real_escape_string($centre_cout).'\'');
        if($req->lines==1)
        {
          list($cc,$ct)=$req->get_row();
          $this->contact     = $ct;
          $this->centre_cout = $cc;
        }
      }
    }

    $req = new insert ($this->dbrw,
           "boutiqueut_debitfacture",
           array(
           "id_utilisateur"    => $this->id_utilisateur,
           "nom"               => $this->nom,
           "prenom"            => $this->prenom,
           "adresse"           => $this->adresse,
           "date_facture"      => date("Y-m-d H:i:s",$this->date),
           "montant_facture"   => $this->montant,
           "etat_facture"      => $this->etat,
           "mode_paiement"     => $this->mode,
           "ready"             => $this->ready,
           "objectif"          => $this->objectif,
           "eotp"              => $this->eotp,
           "contact"           => $this->contact,
           "centre_financier"  => $this->centre_financier,
           "centre_cout"       => $this->centre_cout
         ));

    if ( !$req )
      return false;

    $this->id = $req->get_id();

    if($etat==0 && $ready==1)//on force le retrait
      $this->traiter_panier($client,$panier, $prix_service,0);
    else
      $this->traiter_panier($client,$panier, $prix_service,1);

    if(!($etat==0 && $ready==1))//non retiré
      $this->send_emails($client);

    return true;
  }

  function send_emails ($client)
  {
    if(!$client->is_valid())
      return;
    $body = "Bonjour,
Vous venez d'effectuer une commande sur la boutique utbm.

Pour suivre l'avancement de votre commande, rendez vous à l'adresse suivante :
http://boutique.utbm.fr/suivi.php?id_facture=".$this->id."

À chacune des étapes de traitement de votre commande, vous serrez informé par email.

Cordialement,
La boutique utbm
--
http://boutique.utbm.fr
Site accessible uniquement depuis le réseau utbm";

    $ret = mail($client->email,
                "[boutique utbm] confirmation de commande",
                utf8_decode($body),
                "From: \"La boutique utbm\" <boutique@utbm.fr>\nReply-To: boutique@utbm.fr");

    $body = "Bonjour,
Une commande est en attente de préparation.

Pour consulter la commande, rendez vous à l'adresse suivante :
http://boutique.utbm.fr/admin_gen_fact.php?id_facture=".$this->id."

Cordialement,
La boutique utbm
--
http://boutique.utbm.fr
Site accessible uniquement depuis le réseau utbm";
    $ret = mail("boutique@utbm.fr",
//    $ret = mail("simon.lopez@ayolo.org",
                "[boutique utbm] nouvelle commande",
                utf8_decode($body),
                "From: \"La boutique utbm\" <boutique@utbm.fr>\nReply-To: boutique@utbm.fr");

  }
  function send_email_ready($client)
  {
    if(!$client->is_valid())
      return;
    $body = "Bonjour,
Votre commande N°".$this->id." sur la boutique utbm est prête.

Vous pouvez venir la retirer à l'accueil de Sevenans aux heures
d'ouvertures suivantes :";

if($client->type=='srv')
{
  $body.="
  - Le matin entre 8h et 12h
  - l'après-midi entre 14h et 16h
";
}
else
{
  $body.="
  - le jeudi après-midi entre 14h et 16h
";
}

$body .="
Cordialement,
La boutique utbm
--
http://boutique.utbm.fr
Site accessible uniquement depuis le réseau utbm";

    $ret = mail($client->email,
                "[boutique utbm] Commande prete",
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
  function traiter_panier ( $client, $panier, $prix_service, $retirer=1 )
  {
    foreach ( $panier as $item )
    {
      list($quantite,$vp) = $item;
      $a_expedier=NULL;
      $a_retirer=NULL;

      if ( $retirer==1 /*&& $vp->produit->a_retirer*/ )
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
  function set_retire ( $id_produit, $client)
  {
    $req = new update ($this->dbrw,"boutiqueut_vendu",
      array("a_retirer_vente" => null ),
      array("id_facture" => $this->id,"id_produit"=>$id_produit));

    $this->recalcul_ready_state($client);
  }

  /**
   * Met à jour l'état de retrait de la facture
   * @private
   */
  function recalcul_ready_state($client)
  {
    $req = new requete($this->db, "SELECT COUNT(*) ".
      "FROM `boutiqueut_vendu` " .
      "WHERE `id_facture`='".$this->id."' AND a_retirer_vente='1'");

    list($nb) = $req->get_row();

    if ( $nb == 0 )
    {
      $this->set_etat( 1 );
      $this->set_ready( 1 );
      $this->send_email_ready($client);
    }
  }


}

/**@}*/
?>
