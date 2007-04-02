<?php

/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
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
define("CPT_MACHINES", 8);
define("JET_LAVAGE", 224);
define("JET_SECHAGE", 225);
define("GRP_BLACKLIST", 29);

$topdir="../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/jeton.inc.php");
require_once($topdir. "comptoir/include/comptoirs.inc.php");
require_once($topdir. "comptoir/include/venteproduit.inc.php");
require_once($topdir. "include/localisation.inc.php");

$site = new site ();

if (!$site->user->is_in_group ("gestion_machines") && !$site->user->is_in_group("gestion_ae") )
	error_403();

$site->start_page ("none", "Gestion des jetons machine");

$cts = new contents("Gestion des jetons machine");

$tabs = array(
			array("","ae/jetons.php", "Emprunt jetons"),
			array("retour","ae/jetons.php?view=retour", "Retour jetons"),
			array("listing","ae/jetons.php?view=listing", "Gestion g&eacute;n&eacute;rale"),
			);

$cts->add(new tabshead($tabs,$_REQUEST["view"]));

/* Onglet retour des jetons */
if ($_REQUEST["view"] == "retour")
{

	$frm = new form("retourjetons","jetons.php?view=retour",false,"POST","Retour jetons");
	$lst = new itemlist("Résultats :");
	
	/* Test des valeurs de jetons envoyés et modif dans la base (+ message)*/ 
	if (isset($_REQUEST["numjetons"]) && isset($_REQUEST["typejeton"]))
			{
				$array_jetons = explode(" ", $_REQUEST["numjetons"]);
				foreach($array_jetons as $numjeton)
				{
					$jeton = new jeton($site->db, $site->dbrw);
					$jeton->load_by_nom($numjeton, $_REQUEST["typejeton"]);
					
					if($jeton->id < 0)
					  $lst->add("Erreur pour le jeton n°$numjeton (mauvais type ?)", "ko");
					elseif(!$jeton->is_borrowed())
					  $lst->add("hey ! mais il est pas emprunté $numjeton !", "ko");
					else
					{	
						$jeton->given_back ( $_REQUEST["sallejeton"], $_REQUEST["typejeton"], $numjeton);
						if($jeton->id > -1)
						  $lst->add("$numjeton a bien été marqué comme restitué", "ok");
					}
				}
		
			}
	$frm->add_info("Entrez les numéros des jetons séparés par des espaces :");
	$frm->add_text_area("numjetons","Numéros :");
	$frm->set_focus("numjetons");
	$frm->add_select_field("typejeton", "Type du jeton :", $GLOBALS['types_jeton']);
	$frm->add_submit("valid","Valider");
	$cts->add($lst);
	$cts->add($frm,true);
	
	
}
/* Onglet ajout de jetons, jetons en libertés, mauvais payeurs */
elseif ( $_REQUEST["view"] == "listing" )
{
	/* Actions sur les utilisateurs : mail, blacklist */
	if(isset($_REQUEST['action']))
	  {
	    $lst = new itemlist("Résultats :");

	    if(isset($_REQUEST['id_utilisateur']))
			$ids[] = $_REQUEST['id_utilisateur'];
		elseif($_REQUEST['id_utilisateurs'])
		{

			foreach ($_REQUEST['id_utilisateurs'] as $id_util)
				$ids[] = $id_util;
		}

		
	    if($_REQUEST['action'] == "blacklist")
	      {
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db, $site->dbrw);
		  $user->load_by_id($id);

		  $user->add_to_group(GRP_BLACKLIST);

		}
	      }

	    if($_REQUEST['action'] == "unblacklist")
	      {
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db, $site->dbrw);
		  $user->load_by_id($id);

		  $user->remove_from_group(GRP_BLACKLIST);
		}
	      }
	      	
	    if($_REQUEST['action'] == "mail_rappel")
	      {
		foreach ( $ids as $id )
		{
		  $user = new utilisateur($site->db);

		  $id = intval($id);

		  $user->load_by_id($id);
		  $sql = new requete($site->db, "SELECT 
                                                        mc_jeton_utilisateur.id_jeton
                                                        , mc_jeton.nom_jeton
                                                        , DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree 
			  			 FROM 
                                                        mc_jeton 
						INNER JOIN 
                                                        mc_jeton_utilisateur 
                                                ON 
                                                        mc_jeton.id_jeton = mc_jeton_utilisateur.id_jeton 
						WHERE 
                                                        id_utilisateur = $id 
                                                AND 
                                                        retour_jeton IS NULL");
		  $body = "Bonjour, 

Vous utilisez le service de machines à laver proposé par l'AE et nous vous en remercions, nous attirons votre attention sur le fait que les jetons vous sont prêtés pour une utilisation des machines dans la journée suivante, ceci afin de permettre une bonne circulation des jetons, garantissant ainsi à tous la possiblité de bénéficier de ce service.

Or vous avez encore en votre possession le(s) jeton(s) suivant(s) : \n";
		  
		  while ($row = $sql->get_row())
		    $body .= "- Jeton n°".$row['nom_jeton'].", emprunté depuis ".$row['duree']." jours \n";
		  

		  $body .= "\n Afin que tout le monde puisse profiter des machines mises à disposition par l'AE nous vous remercions de bien vouloir utiliser ou rapporter ces jetons dans les plus brefs délais, à défaut de quoi, vous pourriez vous voir bloquer l'accès à ce service.

Merci d'avance

Les responsables machines à laver";
			  
		  $mail = mail($user->email, utf8_decode("[AE] Jetons de machines à laver"), utf8_decode($body),
                            "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae.info@utbm.fr");
			if ($mail)
				$lst->add("Mail de rappel &agrave; " .$user->prenom. " " .$user->nom. " : Envoy&eacute;","ok");	
			else
				$lst->add("Erreur lors de l'envoi du mail de rappel pour " . $user->prenom . " " . $user->nom ." !","ko");
		  
		}
	      }
	    $cts->add($lst);
	      
	  }


	$cts->add_title(2,"Nombre de jetons");	

	$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton`");
	list($total) = $req->get_row();
	$cts->add_paragraph("Total : $total");
	$req = new requete($site->db,"SELECT COUNT(*) FROM `mc_jeton_utilisateur` WHERE `retour_jeton` IS NULL");
	list($utilises) = $req->get_row();
	$cts->add_paragraph("En circulation : $utilises");
	$freedom = $total - $utilises;
	$cts->add_paragraph("En caisse : $freedom");
	
	
	/* Formulaire d'ajout de jetons */	
	$lst = new itemlist("Résultats :");
	$frm = new form("ajoutjeton", "jetons.php?view=listing", false, "POST", "Ajouter un jeton");
		/* Test des valeurs de jetons envoyés et ajout dans la base (+ message) */
		if (isset($_REQUEST["numjetons"]))
			{
				$array_jetons = explode(" ", $_REQUEST["numjetons"]);
				foreach($array_jetons as $numjeton)
				{
					$jeton = new jeton($site->db, $site->dbrw);
					$jeton->add ( $_REQUEST["sallejeton"], $_REQUEST["typejeton"], $numjeton);
					if($jeton->id > -1)
					  $lst->add("le jeton $numjeton a bien été enregistré", "ok");
				}
		
			}
	$frm->add_info("Entrez les numéros des jetons séparés par des espaces");
	$frm->add_text_area("numjetons", "Numéro ");
	$frm->set_focus("numjetons");
	$frm->add_select_field("typejeton", "Type du jeton :", $GLOBALS['types_jeton']);
	$frm->add_select_field("sallejeton", "Salle concernée :", $GLOBALS['salles_jeton']);
	$frm->add_submit("valid","Valider");
	$frm->allow_only_one_usage();
	$cts->add($lst);
	$cts->add($frm,true);

	/* Liste des jetons empruntés */
	$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton, 
					mc_jeton_utilisateur.id_utilisateur, 
					mc_jeton_utilisateur.prise_jeton,
					mc_jeton.id_jeton,
					mc_jeton.nom_jeton,
					DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree,
					utilisateurs.id_utilisateur,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
					FROM mc_jeton_utilisateur
					INNER JOIN utilisateurs
					ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
					LEFT JOIN mc_jeton
					ON mc_jeton_utilisateur.id_jeton = mc_jeton.id_jeton
					WHERE mc_jeton_utilisateur.retour_jeton IS NULL
					ORDER BY duree DESC
					"); 
	
	$table = new sqltable("listeemprunts",
				"Liste des jetons empruntés",
				$sql, 
				"jetons.php", 
				"id_jeton", 
				array(
					"nom_jeton" => "Jeton",
					"nom_utilisateur"=>"Utilisateur",
					"prise_jeton" => "Date d'emprunt",
					"duree" => "Depuis (jours)"
					), 
				array(), array(), array()
				);
	$cts->add($table,true);

	$sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton,
					mc_jeton_utilisateur.id_utilisateur,
					mc_jeton_utilisateur.retour_jeton,
					COUNT(id_jeton) AS nombre,
					utilisateurs.nom_utl, 
					utilisateurs.prenom_utl, 
					utilisateurs.id_utilisateur,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur
					FROM mc_jeton_utilisateur
					LEFT JOIN utilisateurs 
					ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
					WHERE mc_jeton_utilisateur.retour_jeton IS NULL
					GROUP BY mc_jeton_utilisateur.id_utilisateur
					ORDER BY nombre DESC");


	$table = new sqltable("toploosers",
				"Top des mauvais clients",
				$sql,
				"jetons.php?view=listing",
				"id_utilisateur",
				array(
					"nom_utilisateur"=>"Utilisateur",
					"nombre" => "Nombre"
					),
			      array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
			      array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
			      array()
				);

	$cts->add($table, true);

	$sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur, 
					CONCAT(utilisateurs.prenom_utl,' ', utilisateurs.nom_utl) AS nom_utilisateur 
					FROM utl_groupe INNER JOIN utilisateurs ON utilisateurs.id_utilisateur = utl_groupe.id_utilisateur 
					WHERE utl_groupe.id_groupe = 29 
					ORDER BY utilisateurs.nom_utl, utilisateurs.prenom_utl");

	$table = new sqltable("blackmember", 
			"Liste des personnes bloquées",
			$sql,
			"jetons.php?view=listing",
			"id_utilisateur",
			array("nom_utilisateur" => "Utilisateur"),
			      array("unblacklist" => "Débloquer"),
			      array("unblacklist" => "Débloquer"),
			      array()
			      );

	$cts->add($table, true);
				


		
}
/* Sinon onglet d'emprunt des jetons */
else
{
  $lst = new itemlist("Résultats :");

  /* execution de la demande */
  if (isset($_REQUEST["magicform"]) && $_REQUEST["magicform"]["name"] == "empruntjeton")
  {
    $utl = new utilisateur($site->db, $site->dbrw);
    $utl->load_by_carteae($_REQUEST['numcarteae']);
    
    if($utl->id == -1)
      $error = "Utilisateur inconnu";

    elseif($utl->is_in_group("cpt_bloque"))
      $error = "Le compte de cet utilisateur est bloqué !";
    
    elseif($utl->is_in_group("blacklist_machines"))
      $error = "Cet utilisateur n'est pas autorisé à emprunter de jeton !";

    elseif( !$utl->ae )
      $error = "Cotisation non renouvelée.";

    if(!empty($error))
      $lst->add($error, "ko");
    else
    {
      if(!empty($_REQUEST['numjetlaver']))
	{
	  $jetlav = new jeton($site->db, $site->dbrw);
	  $jetlav->load_by_nom($_REQUEST['numjetlaver'], "laver");
	  
	  if($jetlav->id == -1)
	    $error = "Le jeton de machine à laver est invalide";
	  elseif($jetlav->is_borrowed())
	    $error = "Le jeton de machine à laver ($jetlav->nom) est censé etre emprunté, comment ce fait-ce ?";
	}
      if(!empty($_REQUEST['numjetsecher']))
	{
	  $jetsech = new jeton($site->db, $site->dbrw);
	  $jetsech->load_by_nom($_REQUEST['numjetsecher'], "secher");

	  if($jetsech->id == -1)
	    $error = "Le jeton de seche-linge est invalide";      
	  elseif($jetsech->is_borrowed())
	    $error = "Le jeton de seche-linge ($jetsech->nom) est censé etre emprunté, comment ce fait-ce ?";
	}

      if(!empty($error))
        $cts->add_paragraph("Erreur : $error");
      else
      {
        if($_REQUEST['typedebit'] == "debit_carte")
        {
          $cpt = new comptoir ($site->db, $site->dbrw);
          $cpt->load_by_id (CPT_MACHINES);
	  $caddie = array();
	  
	  if($jetlav)
	    {
	      $vente_lav = new venteproduit($site->db, $site->dbrw);
	      $vente_lav->load_by_id(JET_LAVAGE, CPT_MACHINES);
	      $caddie[] = array(1, $vente_lav);
	    }
	  if($jetsech)
	    {
	      $vente_sech = new venteproduit($site->db, $site->dbrw);
	      $vente_sech->load_by_id(JET_SECHAGE, CPT_MACHINES);
	      $caddie[] = array(1, $vente_sech);
	    }
	  
	  if($caddie)
	    {
	      $debit = new debitfacture($site->db, $site->dbrw);
	      $ok = $debit->debitAE($utl, $site->user, $cpt, $caddie, false);
	    }
	}
        else
          $ok = true;
          
        if ( !$ok )
          $lst->add("Solde insuffisant", "ko");
        else
        {
	  if($jetlav)
	    {
	      $jetlav->borrow_to_user($utl->id);
	      $lst->add("Le jeton n°$jetlav->nom (lavage) a bien ete prêté à $utl->prenom $utl->nom", "ok");
	    }
	  if($jetsech)
	    {
	      $jetsech->borrow_to_user($utl->id);
	      $lst->add("Le jeton n°$jetsech->nom (séchage) a bien ete prêté à $utl->prenom $utl->nom", "ok");
	    }
        }
      }
    }
  }

	$frm = new form("empruntjeton", "jetons.php", false, "POST", "Emprunter un jeton");
	$frm->add_text_field("numcarteae", "Carte AE");
	$frm->set_focus("numcarteae");
	$frm->add_info("ou");
	$frm->add_user_fieldv2("userae", "Utilisateur");
	$frm->add_info("<br />");

	$frm->add_text_field("numjetlaver", "Numéro de jeton lavage");
	$frm->add_text_field("numjetsecher", "Numéro de jeton séchage");
	$frm->add_radiobox_field("typedebit", "Type de débit",  array("debit_carte"=>"Cartes AE", "debit_especes"=>"Espèces"), "debit_carte");

	$frm->add_submit("valid","Valider");
	$frm->allow_only_one_usage();

	$cts->add($lst);
	$cts->add($frm,true);
}

$site->add_contents($cts);

$site->end_page ();

?>
