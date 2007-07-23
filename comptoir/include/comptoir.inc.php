<?php

/**
 * @file
 */
 
/*
 *	Classe comptoir.
 *
 * Cette classe a �t� con�ue pour r�sister au mieux aux erreurs des autres
 * modules. La validit� de toutes les donn�es est v�rifi�e au chargement.
 * A chaque op�ration la validit� des donn�es soumises est v�rifi�e.
 *
 * Lors de l'utilisation de classe, v�rifiez toujours les codes de sortie
 * et les valeurs renvoy�es.
*/

/* Copyright 2005,2006
 * - Julien Etelain <julien CHEZ pmad POINT net>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

require_once($topdir . "comptoir/include/facture.inc.php");

function first ( $array )
{
	reset($array);
	return current($array);
}
/** 
 * @addtogroup comptoirs
 * @{
 */

/**
 * Classe gérant un comptoir
 */
class comptoir extends stdentity
{
	/* Informations comptoir */
	var $nom;
	var $id_assocpt;
	var $groupe_vendeurs;
	var $groupe_admins;
	var $type;
	var $id_salle;

	/* Informations de session */
	var $operateurs;
	var $panier;
	var $client;
	var $prix_barman;
	var $mode;


	/** @brief chargement du comptoir
	 *
	 * @param id l'id du comptoir
	 *
	 */
	function load_by_id ($id)
	{

		$req = new requete($this->db,"SELECT *
							 FROM `cpt_comptoir`
							 WHERE `id_comptoir`='".intval($id)."'");

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
		$this->id = $row['id_comptoir'];
		$this->nom = $row['nom_cpt'];
		$this->id_assocpt = $row['id_assocpt'];
		$this->groupe_vendeurs = $row['id_groupe_vendeur'];
		$this->groupe_admins = $row['id_groupe'];
		$this->type = $row['type_cpt'];
		$this->id_salle = $row['id_salle'];
	}

	/** @brief ajout d'un comptoir
	 *
	 * @param nom le nom g�n�rique du comptoir
	 * @param id_assocpt l'id de l'association concern�e
	 * @param groupe_vendeurs l'id du groupe d�signant les vendeurs
	 * @param groupe_admins l'id du groupe d�signant les admins du comptoir
	 *
	 * @return true en cas de succ�s, false sinon
	 */
	function ajout ($nom, $id_assocpt, $groupe_vendeurs, $groupe_admins, $type, $id_salle)
	{

		$this->nom = $nom;
		$this->id_assocpt = $id_assocpt;
		$this->groupe_vendeurs = $groupe_vendeurs;
		$this->groupe_admins = $groupe_admins;
		$this->type = $type;
		$this->id_salle = $id_salle>0?$id_salle:null;
		
		$req = new insert ($this->dbrw,
					 "cpt_comptoir",
					 array("nom_cpt" => $this->nom,
					 "id_assocpt" => $this->id_assocpt,
					 "id_groupe_vendeur" => $this->groupe_vendeurs,
					 "id_groupe" => $this->groupe_admins,
					 "type_cpt"=>$this->type,
					 "id_salle"=>$this->id_salle
						));

		if ( !$req )
			return false;

		$this->id = $req->get_id();

		return true;
	}

	/** @brief modification d'un comptoir
	 *
	 * @param nom le nom g�n�rique du comptoir
	 * @param id_assocpt l'id de l'association concern�e
	 * @param groupe_vendeurs l'id du groupe d�signant les vendeurs
	 * @param groupe_admins l'id du groupe d�signant les admins du comptoir
	 *
	 * @return true en cas de succ�s, false sinon
	 */
	function modifier ($nom, $id_assocpt, $groupe_vendeurs, $groupe_admins,$type,$id_salle)
	{

		$this->nom = $nom;
		$this->id_assocpt = $id_assocpt;
		$this->groupe_vendeurs = $groupe_vendeurs;
		$this->groupe_admins = $groupe_admins;
		$this->type = $type;
		$this->id_salle = $id_salle>0?$id_salle:null;
		$sql = new update($this->dbrw,
					"cpt_comptoir",
					array("nom_cpt" => $nom,
					"id_assocpt" => $id_assocpt,
					"id_groupe_vendeur" => $groupe_vendeurs,
					"id_groupe" => $groupe_admins,
					 "type_cpt"=>$this->type,
					 "id_salle"=>$this->id_salle
					 ),
					array("id_comptoir" => $this->id));

		return ($sql->lines == 1) ? true : false;
	}

	/** @brief ouverture d'un comptoir
	 *
	 * @param id l'identifiant du comptoir
	 *
	 * @return true en cas de succ�s, false sinon
	 */
	function ouvrir ($id)
	{
		$this->load_by_id($id);

		if ( $this->id < 0 )
			return false;

		$this->operateurs = array();
		$this->panier = array();

		$this->client = new utilisateur($this->db,$this->dbrw);

		/* Par s�curit� */
		$this->client->id = -1;
		/* par d�faut on paye au prix normal */
		$this->prix_barman = false;
		/* Si il n'y a pas d'entr�e, on a fini */
		if (!isset($_SESSION["Comptoirs"][$this->id]))
			return true;

		/* chargement des op�rateurs */
		foreach($_SESSION["Comptoirs"][$this->id]["operateurs"] as $uid)
		{
			$Op = new utilisateur ($this->db,$this->dbrw);
			$Op->load_by_id ($uid);
			if (($Op->id > 0) && $Op->is_in_group_id($this->groupe_vendeurs))
			{
				$this->operateurs[] = $Op;
				
				// Met à jour l'entrée de tracking de chaque barmen
				$req = new requete ($this->dbrw,
					 "UPDATE `cpt_tracking` SET `activity_time`='".date("Y-m-d H:i:s")."'
					  WHERE `activity_time` > '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
					  AND `closed_time` IS NULL
					  AND `id_utilisateur` = '".mysql_real_escape_string($Op->id)."'
					  AND `id_comptoir` = '".mysql_real_escape_string($this->id)."'");
					  
				if ( $req->lines == 0 ) // rien n'a été affecté, donc on re-crée une entrée
				{
          $req = new insert ($this->dbrw,
             "cpt_tracking",
             array(
             "id_utilisateur" => $Op->id,
             "id_comptoir" => $this->id,
             "logged_time" => date("Y-m-d H:i:s"),
             "activity_time" => date("Y-m-d H:i:s"),
             "closed_time" => null
              ));
				}
					  
		  }
		}

		/* Si il n'y a pas client, on a fini */
		if (!$_SESSION["Comptoirs"][$this->id]["client"])
			return true;

		/* chargement du client */
		$this->client->load_by_id($_SESSION["Comptoirs"][$this->id]["client"]);

		/* L'utilisatveur n'existe pas... probablement une erreur
		 * de passage de param�tre */
		if ($this->client->id < 0)
			return false;


		if ( isset($_SESSION["Comptoirs"][$this->id]["mode"]) ) // On est en mode spécial
		{
			$this->mode = $_SESSION["Comptoirs"][$this->id]["mode"];
			
			if ( $this->mode == "book" )
				foreach($_SESSION["Comptoirs"][$this->id]["panier"] as $pid)
				{
					$bk = new livre($this->db);
					$bk->load_by_id($pid);
					if ( $bk->id > 0 && $bk->id_salle == $this->id_salle )
						$this->panier[] = $bk;
				}

			return true;
		}
		
		/* v�rification pour la tarification */
		if ($_SESSION["Comptoirs"][$this->id]["prix_barman"])
			$this->verifie_prix_barman ();

		/* on parse le panier du client */
		if ( count($_SESSION["Comptoirs"][$this->id]["panier"]) > 0 )
		foreach($_SESSION["Comptoirs"][$this->id]["panier"] as $pid)
		{
			$Prod = new produit ($this->db);
			$Prod->load_by_id ($pid);
			if ($Prod->id > 0)
			{
				$VenteProd = new venteproduit ($this->db,$this->dbrw);
				if ($VenteProd->charge ($Prod,$this))
				{
					$this->panier[] = $VenteProd;
				}
			}
		}
		return true;
	}

	/** @brief fermeture d'un comptoir
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function fermer ()
	{
		unset($_SESSION["Comptoirs"][$this->id]);
	}

	/** @brief ajout d'un operateur dans la liste des op�rateurs
	 *	du comptoir.
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function ajout_operateur ($etudiant)
	{
		if ($etudiant->id < 0)
			return false;

		if (!$etudiant->is_in_group_id($this->groupe_vendeurs))
			return false;

		$this->operateurs[] = $etudiant;

		$_SESSION["Comptoirs"][$this->id]["operateurs"][] = $etudiant->id;

    // crée l'entrée de tracking pour le barman
		$req = new insert ($this->dbrw,
					 "cpt_tracking",
					 array(
					 "id_utilisateur" => $etudiant->id,
					 "id_comptoir" => $this->id,
					 "logged_time" => date("Y-m-d H:i:s"),
					 "activity_time" => date("Y-m-d H:i:s"),
					 "closed_time" => null
						));

		return true;
	}
	
	/** @brief définit le seul operateur du comptoir.
	 * Doit être appelè à chaque instanciation.
	 * 
	 * @return true si succ�s, false sinon
	 *
	 */
	function set_operateur ($etudiant)
	{
		if ($etudiant->id < 0)
			return false;

		if (!$etudiant->is_in_group_id($this->groupe_vendeurs))
			return false;

		$this->operateurs = array($etudiant);

		return true;
	}	
	
	
	
	/** @brief el�ve un operateur de la liste des op�rateurs
	 *	du comptoir.
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function enleve_operateur ($id_etudiant)
	{
	
		$id_etudiant = intval($id_etudiant); // On est jamais trop prudent, m�me si c'est inutile
	
		foreach ( $this->operateurs as $key => $op )
			if ( $id_etudiant == $op->id )
				unset($this->operateurs[$key]);
					
		foreach ( $_SESSION["Comptoirs"][$this->id]["operateurs"] as $key => $id_op )
			if ( $id_etudiant == $id_op )
				unset($_SESSION["Comptoirs"][$this->id]["operateurs"][$key]);	 
				
    // met à jour l'entrée de tracking du barman
    
		$req = new requete ($this->dbrw,
					 "UPDATE `cpt_tracking` SET `closed_time`='".date("Y-m-d H:i:s")."'
					  WHERE `activity_time` > '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
					  AND `closed_time` IS NULL
					  AND `id_utilisateur` = '".mysql_real_escape_string($id_etudiant)."'
					  AND `id_comptoir` = '".mysql_real_escape_string($this->id)."'");
				
		return true;
	}
	
	/** @brief ouverture du panier
	 *
	 * @param client un objet de type client
	 * @param flag_prix_barman (optionel) true si prix barman,
	 *				false sinon
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function ouvre_pannier ($client, $flag_prix_barman = true)
	{
		/* si identifiant client invalide */
		if ($client->id < 0)
			return false;

		if ( !$client->ae )
			return false;

		if ( $client->is_in_group("cpt_bloque") )
			return false;

		/* si pas d'op�rateur sur le comptoir */
		if (!count($this->operateurs))
			return false;

		$this->client = $client;
		$_SESSION["Comptoirs"][$this->id]["client"] = $this->client->id;

		/* v�rification du droit au prix barman */
		if ($flag_prix_barman)
			$this->verifie_prix_barman();
		else
			$this->prix_barman = false;
			
		$_SESSION["Comptoirs"][$this->id]["prix_barman"] = $this->prix_barman;
		
		return true;
	}

	/** @brief annulation du panier
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function annule_pannier ()
	{
		if (!count($this->operateurs))
			return false;
			
		if ( $this->client->id < 0 )
			return false;
			
			
		if ( $this->mode != "book" )

		foreach ($this->panier as $vp)
		{
			$vp->debloquer($this->client,1);
		}			
			
		$this->vider_pour_vente();
		return true;
	}

	/** @brief annulation du dernier produit
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function annule_dernier_produit ()
	{
		if (!count($this->operateurs))
			return false;

		if ($this->client->id < 0)
			return false;

		if ( count($this->panier) == 0 )
			return false;

		$last = count($this->panier) - 1;
		
		if ( $this->mode != "book" )
		$this->panier[$last]->debloquer($this->client,1);

		unset($this->panier[$last]);
		unset($_SESSION["Comptoirs"][$this->id]["panier"][$last]);

		return true;
	}

	/** @brief ajout d'un article dans le panier
	 *
	 * @param prod un objet de type produit ou livre
	 *
	 * @return true si succ�s, false sinon
	 *
	 */
	function ajout_pannier ($prod)
	{
		if (!count($this->operateurs))
			return false;

		if ( !$this->client->is_valid() < 0)
			return false;

		if ( $this->mode == "book" )
		{
			if ( $prod->id <= 0 || $prod->id_salle != $this->id_salle )
				return false;
			
			$this->panier[] = $prod;
			$_SESSION["Comptoirs"][$this->id]["panier"][] = $prod->id;
			
			return true;
		}

    if ( !$prod->can_be_sold($this->client) )
      return;

		if (!$this->client->credit_suffisant($this->calcule_somme () + $prod->obtenir_prix ($this->prix_barman)))
			return false;
			
		$vp = new venteproduit($this->db,$this->dbrw);
		
		if (!$vp->charge($prod,$this))
			return false;
			
		$vp->bloquer($this->client);
			
		$this->panier[] = $vp;

		$_SESSION["Comptoirs"][$this->id]["panier"][] = $prod->id;

		return true;
	}

	/** @brief vente du panier
	 *
	 * @return un tableau associatif de type
	 * ([0] => objet client,
	 *	[1] => tableau d'articles vendus,
	 *	[2] => tableau d'articles non vendus (solde insuffisant ...)),
	 * false sinon
	 *
	 */
	function vendre_panier ()
	{
		if (!count($this->operateurs))
			return false;

		if ($this->client->id < 0)
			return false;

		if ( $this->mode == "book" )
			return false;

		if (!$this->client->credit_suffisant($this->calcule_somme()))
			return false;
			
		$vendeur = first($this->operateurs);
		$client = $this->client;
		$ancien_panier = $this->panier;
		$panier = array();

		foreach ($ancien_panier as $vp)
		{
			$panier[$vp->produit->id][0]++;
			$panier[$vp->produit->id][1] = $vp;
		}

		$debfact = new debitfacture($this->db,$this->dbrw);
		
		if ( !$debfact->debitAE ( $client, $vendeur, $this, $panier, $this->prix_barman ) )
			return false;

		$this->vider_pour_vente();
		
		return array($client,$ancien_panier,array());
	}
	
	/** @brief rechargement des comptes
	 *
	 * @param client un objet de type client
	 * @param type_paiement le type de paiement
	 * @param banque la banque
	 * @param valeur le montant du rechargement
	 * @param association l'identifiant de l'association concern�e
	 *
	 */
	function recharger_compte ($client,
					 $type_paiement,
					 $banque,
					 $valeur,
					 $association)
	{

		if ( !$client->ae )
			return false;

		if ( $client->is_in_group("cpt_bloque") )
			return false;
			
		if (!count($this->operateurs))
			return false;	

		$operateur = first($this->operateurs);
		/* on passe � la fonction membre de client pour le rechargement */
		return $client->crediter ($operateur->id,
						$type_paiement,
						$banque,
						$valeur,
						$association->id,
						$this->id);
	}

	/*
	 * Fonctions "priv�es"
	 * Usage interne
	*/

	/** @brief v�rifie que le client a bien droit au prix barman
	 *
	 */
	function verifie_prix_barman ()
	{
		$this->prix_barman = false;

		foreach ($this->operateurs as $Op)
			if ($this->client->id == $Op->id)
			{
				$this->prix_barman = true;
				return;
			}
	}

	/** @brief Vidage effectif du panier
	 *
	 *
	 */
	function vider_pour_vente ()
	{
		$this->panier = array();
		$this->client = new utilisateur($this->db);
		$this->client->id = -1;
		$this->prix_barman = false;
		$this->mode = null;
		unset($_SESSION["Comptoirs"][$this->id]["panier"]);
		unset($_SESSION["Comptoirs"][$this->id]["prix_barman"]);
		unset($_SESSION["Comptoirs"][$this->id]["client"]);
		unset($_SESSION["Comptoirs"][$this->id]["mode"]);
	}

	/* @brief calcule de la somme du panier
	 *
	 * @param prix_barman true si droit prix barman,
	 *				false sinon
	 *
	 * @return la somme
	 *
	 */
	function calcule_somme ( $prix_barman = false )
	{
		$Somme = 0;
		foreach ( $this->panier as $VenteProd )
		{
			$Somme += $VenteProd->produit->obtenir_prix($this->prix_barman);
		}
		return $Somme;
	}
	
	function switch_to_special_mode ( $mode )
	{
		unset($_SESSION["Comptoirs"][$this->id]["panier"]);
		$this->panier = array();
		$this->mode = $mode;
		$_SESSION["Comptoirs"][$this->id]["mode"] = $mode;
	}
	
	
	
}


?>