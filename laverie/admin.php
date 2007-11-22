<?php

/* Copyright 2007
 * - Benjamin Collet < bcollet AT oxynux DOT org >
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
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

define("ID_ASSO_LAVERIE", 84);
define("GRP_BLACKLIST", 29);
define("CPT_MACHINES", 8);
define("JET_LAVAGE", 224);
define("JET_SECHAGE", 225);

$topdir = "../";
require_once($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/planning.inc.php");
require_once($topdir. "include/cts/user.inc.php");

require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/jeton.inc.php");
require_once($topdir. "include/entities/machine.inc.php");

require_once($topdir. "comptoir/include/comptoirs.inc.php");
require_once($topdir. "comptoir/include/venteproduit.inc.php");

$site = new site();

$site->allow_only_logged_users();

if ( !$site->user->is_in_group("gestion_machines") )
  $site->error_forbidden("services","group");
  
// En dure en attendant la correction de la base de donnés
$salles = array(6=>"Laverie belfort",8=>"Laverie Sevenans");

if ( !isset($_REQUEST["id_salle"]) )
{
  $site->start_page("services","Laverie");
  $cts = new contents("Administration des laveries");

	$lst = new itemlist("Veuillez choisir la laverie à administrer");
	
	foreach ( $salles as $id => $nom )
		$lst->add("<a href=\"admin.php?id_salle=$id\">$nom</a>");
	$cts->add($lst,true);

  $site->add_contents($cts);
  $site->end_page(); 
  exit();
}

$id_salle = intval($_REQUEST["id_salle"]);

$site->start_page("services","Laverie");
$cts = new contents("<a href=\"laverie.php\">Laverie</a> / <a href=\"admin.php\">Administration</a> / ".$salles[$id_salle]);

$cts->add(new tabshead(
      array(
      array("","laverie/admin.php?id_salle=$id_salle", "Vente"),
			array("jt","laverie/admin.php?id_salle=$id_salle&view=jt", "Jetons"),
			array("pl","laverie/admin.php?id_salle=$id_salle&view=pl", "Plannings"),
			array("bc","laverie/admin.php?id_salle=$id_salle&view=bc", "Mauvais clients"),
			array("mc","laverie/admin.php?id_salle=$id_salle&view=mc", "Machines")
			),$_REQUEST["view"]));	

// Traitement des actions
if ( $_REQUEST["action"] == "autoplanning" )
{
  $machine = new machine($site->db,$site->dbrw);
  $req = new requete($site->db,"SELECT * FROM mc_machines WHERE loc='$id_salle'");
  while ( $row = $req->get_row() )
  {
    $machine->_load($row);
    $machine->create_all_creneaux_between($_REQUEST["date_debut"],$_REQUEST["date_fin"],3600);
  }
  
  $cts->add_title(2,"Generation des plannings");
  $cts->add_paragraph("Plannings générés");
}
elseif ( $_REQUEST["action"] == "retjetons" )
{
  $cts->add_title(2,"Retour des jetons");
  
  $jetons = explode(" ",$_REQUEST["data"]); 
  $jeton = new jeton($site->db,$site->dbrw);
  
  foreach ( $jetons as $nom_jeton )
  {
    $nom_jeton = trim($nom_jeton);
    if ( !empty($nom_jeton) )
    {
      if ( $jeton->load_by_nom_and_salle($nom_jeton,$_REQUEST["type"],$id_salle) )
        $jeton->given_back();
      else
        $cts->add_paragraph("Jeton $nom_jeton inconnu.");
    }
  }
  
  $cts->add_paragraph("Fait.");
}
elseif ( $_REQUEST["action"] == "newjetons" )
{
  $cts->add_title(2,"Ajout des jetons");
  
  $jetons = explode(" ",$_REQUEST["data"]); 
  $jeton = new jeton($site->db,$site->dbrw);
  
  foreach ( $jetons as $nom_jeton )
  {
    $nom_jeton = trim($nom_jeton);
    if ( !empty($nom_jeton) )
    {
      if ( !$jeton->load_by_nom_and_salle($nom_jeton,$_REQUEST["type"],$id_salle) )
        $jeton->add ( $id_salle, $_REQUEST["type"], $nom_jeton );
      else
        $cts->add_paragraph("Jeton $nom_jeton déjà existant.");
    }
  }
  
  $cts->add_paragraph("Fait.");
}


// Contenu des onglets
if ( $_REQUEST["view"] == "mc" ) // Liste des machines
{
  
  
  
  
  
}
elseif ( $_REQUEST["view"] == "bc" ) // Mauvais clients
{
  
  
  
  
  
}
elseif ( $_REQUEST["view"] == "pl" ) // Plannings
{
  $date = getDate();
              
  if($date['wday'] == 0)
    $days_left = 0; 
  else
    $days_left = 7 - $date['wday'];
  
  $current_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left, $date['year']);
  $next_week_start = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +1, $date['year']);
  $next_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +8, $date['year']);
  
  //TODO: afficher le dernier creneau pour toutes les machines
  //TODO: formulaire de generation de planning par machine
  //TODO: proposer la modification de creneaux
  
  $frm = new form("autoplanning", "admin.php?id_salle=$id_salle&view=pl",false,"POST","Generer les plannings pour toutes les machines (hors HS)");
  $frm->add_hidden("action","autoplanning");
  $frm->add_datetime_field("date_debut","Date de début",$next_week_start);
  $frm->add_datetime_field("date_fin","Date de fin",$next_week_end);
  $frm->add_submit("valid","Valider");
  $frm->allow_only_one_usage();
  $cts->add($frm,true);
  
}
elseif ( $_REQUEST["view"] == "jt" ) // Jetons
{
  
  $frm = new form("retjetons", "admin.php?id_salle=$id_salle&view=jt",false,"POST","Retour de jetons");
  $frm->add_hidden("action","retjetons");
  $frm->add_select_field("type","Type de jetons",$GLOBALS['types_jeton']);
  $frm->add_text_area("data","Numéro des jetons (séparé par des espaces)");
  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);
  
  $frm = new form("newjetons", "admin.php?id_salle=$id_salle&view=jt",false,"POST","Ajouter des jetons");
  $frm->add_hidden("action","newjetons");
  $frm->add_select_field("type","Type de jetons",$GLOBALS['types_jeton']);
  $frm->add_text_area("data","Numéro des jetons (séparé par des espaces)");
  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);
  
  $req = new requete($site->db,"SELECT
    mc_jeton.id_jeton,
    mc_jeton.type_jeton,
    mc_jeton.nom_jeton,
    mc_jeton_utilisateur.prise_jeton,
    CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS nom_utilisateur,
    utilisateurs.id_utilisateur
    FROM mc_jeton
    LEFT JOIN mc_jeton_utilisateur ON ( mc_jeton.id_jeton=mc_jeton_utilisateur.id_jeton AND mc_jeton_utilisateur.retour_jeton IS NULL )
    LEFT JOIN utilisateurs ON (mc_jeton_utilisateur.id_utilisateur=utilisateurs.id_utilisateur )
    WHERE mc_jeton.id_salle=$id_salle 
    ORDER BY type_jeton,nom_jeton ");
  
  $tbl = new sqltable("invjt",
    "Inventaire des jetons",
    $req,
    "index.php",
    "id_jeton",
    array(
      "type_jeton" => "Type",
      "nom_jeton" => "Numéro",
      "nom_utilisateur" => "Emprunté par",
      "prise_jeton" => "depuis le"),
    array(),
    array(),
    array("type_jeton"=>$GLOBALS['types_jeton']) );
    
  $cts->add($tbl,true);
  
}
else // Vente
{
  $user = new utilisateur($site->db,$site->dbrw);
  
  if ( isset($_REQUEST["id_utilisateur"]) )
  {
    $user->load_by_id($_REQUEST["id_utilisateur"]);
    if(!$user->is_valid())
      $Erreur = "Utilisateur inconnu";
    elseif($user->is_in_group("cpt_bloque"))
      $Erreur = "Le compte de cet utilisateur est bloqué !";
    elseif($user->is_in_group("blacklist_machines"))
      $Erreur = "Cet utilisateur n'est pas autorisé à emprunter de jeton !";
    elseif( !$user->ae )
      $Erreur = "Cotisation non renouvelée.";
    if ( $Erreur )
      $user->id = null;
  }
  
  if ( $_REQUEST["action"] == "vendre" && $user->is_valid() )
  {
    
    $machine = new machine($site->db,$site->dbrw);
    $ErreurVente = "";
    
    $jetons = array();
    
    foreach ( $_REQUEST["jeton"] as $id_creneau => $nom_jeton )
    {
      $nom_jeton = trim($nom_jeton);
      
      if ( !empty($nom_jeton) )
      {
        $machine->load_by_id_creneau ( $id_creneau, $debut );
        
        $jeton = new jeton($site->db,$site->dbrw);
        $jeton->load_by_nom_and_salle($nom_jeton,$machine->type,$id_salle);
        
        if ( !$jeton->is_valid() )
          $ErreurVente .= "Jeton $nom_jeton non trouvé. ";
        else
          $jetons[$id_creneau] = $jeton;
      }
    }
    
    if ( !empty($ErreurVente) )
      $_REQUEST["action"] = "gouser";
    else // Fait payer
    {
      $caddie = array();
      foreach ( $jetons as $jeton )
      {
        if ( !isset($caddie[$jeton->type]) )
        {
          $vp = new venteproduit($site->db, $site->dbrw);
          if ( $jeton->type == "laver" )
            $vp->load_by_id(JET_LAVAGE, CPT_MACHINES);
          else
            $vp->load_by_id(JET_SECHAGE, CPT_MACHINES);
          $caddie[$jeton->type] = array(1, $vp);
        }
        else
          $caddie[$jeton->type][0]++;
      }
      
      $cpt = new comptoir ($site->db, $site->dbrw);
      $cpt->load_by_id (CPT_MACHINES);
      $debit = new debitfacture($site->db, $site->dbrw);
      if ( !$debit->debitAE($user, $site->user, $cpt, $caddie, false) )
        $ErreurVente .= "Solde insuffisent";
    }
    
    if ( !empty($ErreurVente) )
      $_REQUEST["action"] = "gouser";
    else // Fait le bordel avec les jetons
    {
      foreach ( $jetons as $id_creneau => $jeton )
      {
        $jeton->borrow_to_user($user->id);
        $machine->load_by_id_creneau ( $id_creneau, $debut );
        $machine->affect_jeton_creneau( $id_creneau, $user->id, $jeton->id );
      }
      $user->id = null;
    }      
  }
  
  if ( $_REQUEST["action"] == "gouser" && $user->is_valid() )
  {
    $cts->add_title(2,$user->get_html_link());
    
    $sql = new requete($site->db,"
      SELECT id_creneau, debut_creneau, lettre, type
      FROM mc_creneaux
      INNER JOIN mc_machines ON mc_creneaux.id_machine = mc_machines.id
      WHERE mc_creneaux.id_utilisateur = ".$user->id."
      AND fin_creneau >= '".date("Y-m-d H:i:s")."'
      AND debut_creneau <= '".date("Y-m-d H:i:s",time()+(48*24*60*260))."'
      AND id_jeton IS NULL
      AND mc_machines.loc = '".$id_salle."'
      ORDER BY debut_creneau");      
    
    if ( $sql->lines == 0 )
    {
      $cts->add_paragraph("Pas de créneau dans les prochaines 48 heures.");
    }
    else
    {
      $blabla = array("laver"=>"Jeton lavage","secher"=>"Jeton sechage");
      $frm = new form("vendre","admin.php?id_salle=$id_salle",true);
      if ( $ErreurVente )
        $frm->error($ErreurVente);
      $frm->add_hidden("action","vendre");
      $frm->add_hidden("id_utilisateur",$user->id);
      while ( $row = $sql->get_row() )
      {
        $frm->add_text_field("jeton[".$row["id_creneau"]."]",$blabla[$row["type"]]." pour ".date("d/m/Y H:i",strtotime($row["debut_creneau"]))." (".$row["lettre"].")" );
      }
      $frm->add_submit("valid","Valider");
      $cts->add($frm);
      
    }
    $user->id = null;
  }
  
  $frm = new form("gouser","admin.php?id_salle=$id_salle",false,"POST","Jetons pour l'utilisateur");
  if ( $Erreur )
    $frm->error($Erreur);
  $frm->add_hidden("action","gouser");
  $frm->add_entity_smartselect ( "id_utilisateur", "Utilisateur", $user );
  $frm->add_submit("search","Rechercher");
  $cts->add($frm,true);
  
  $frm = new form("searchmc","laverie.php",false,"POST","Reserver un creneau");
  $frm->add_hidden("action","searchmc");
  $frm->add_hidden("fallback","admin");
  $frm->add_hidden("id_salle",$id_salle);
  $frm->add_select_field("operation","Machines désirées",array(3=>"Lavage et sechage",1=>"Lavage seulement",2=>"Sechage seulement"));
  $frm->add_submit("search","Rechercher un créneau");
  $cts->add($frm,true);

  $cts->add_paragraph("<a href=\"laverie.php\">Reservation d'un creneau</a>");
}

$site->add_contents($cts);
$site->end_page(); 
    
?>