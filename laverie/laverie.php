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

// En dure en attendant la correction de la base de donnés
$salles = array(6=>"Laverie belfort",8=>"Laverie Sevenans");

$is_admin = $site->user->is_in_group("gestion_machines");

if ( $_REQUEST["page"] == "admin" && $is_admin )
{
  if ( !isset($_REQUEST["id_salle"]) )
  {
    $site->start_page("services","Laverie");
    $cts = new contents("Administration des laveries");
  
  	$lst = new itemlist("Veuillez choisir la laverie à administrer");
  	
  	foreach ( $salles as $id => $nom )
  		$lst->add("<a href=\"laverie.php?page=admin&amp;id_salle=$id\">$nom</a>");
  	$cts->add($lst,true);
	
    $site->add_contents($cts);
    $site->end_page(); 
    exit();
  }
  
  $id_salle = intval($_REQUEST["id_salle"]);
  
  $site->start_page("services","Laverie");
  $cts = new contents("Administration ".$salles[$id_salle]);

  $cts->add(new tabshead(
       array(
        array("","laverie/laverie.php?page=admin&id_salle=$id_salle", "Vente"),
  			array("jt","laverie/laverie.php?page=admin&id_salle=$id_salle&view=jt", "Jetons"),
  			array("pl","laverie/laverie.php?page=admin&id_salle=$id_salle&view=pl", "Plannings"),
  			array("bc","laverie/laverie.php?page=admin&id_salle=$id_salle&view=bc", "Mauvais clients"),
  			array("mc","laverie/laverie.php?page=admin&id_salle=$id_salle&view=mc", "Machines")
  			),$_REQUEST["view"]));	

  if ( $_REQUEST["view"] == "mc" ) // Liste des machines
  {
    
    
    
    
    
  }
  elseif ( $_REQUEST["view"] == "bc" ) // Mauvais clients
  {
    
    
    
    
    
  }
  elseif ( $_REQUEST["view"] == "pl" ) // Plannings
  {
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
    
    $date = getDate();
                
    if($date['wday'] == 0)
      $days_left = 0; 
    else
      $days_left = 7 - $date['wday'];
    
    $current_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left, $date['year']);
    $next_week_start = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +1, $date['year']);
    $next_week_end = mktime(0, 0, 0, $date['mon'], $date['mday'] + $days_left +8, $date['year']);
    
    $frm = new form("autoplanning", "laverie.php?page=admin&id_salle=$id_salle&view=pl",false,"POST","Generer les plannings pour toutes les machines (hors HS)");
    $frm->add_hidden("action","autoplanning");
    $frm->add_datetime_field("date_debut","Date de début",$next_week_start);
    $frm->add_datetime_field("date_fin","Date de fin",$next_week_end);
    $frm->add_submit("valid","Valider");
    $frm->allow_only_one_usage();
    $cts->add($frm,true);
    
    
  }
  elseif ( $_REQUEST["view"] == "jt" ) // Jetons
  {
    
    
    
    
    
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
            $caddie[$jeton->type] = array(1, $vente_lav);
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
      }      
    }
    
    if ( $_REQUEST["action"] == "gouser" && $user->is_valid() )
    {
      $sql = new requete($site->db,"
        SELECT id_creneau, debut_creneau, lettre, type
        FROM mc_creneaux
        INNER JOIN mc_machines ON mc_creneaux.id_machine = mc_machines.id
        WHERE mc_creneaux.id_utilisateur = ".$user->id."
        AND fin_creneau >= '".date("Y-m-d H:i:s")."'
        AND debut_creneau <= '".date("Y-m-d H:i:s",time()+(48*24*60*260))."'
        AND id_jeton IS NULL
        AND mc_machines.id_salle = '".$id_salle."'
        ORDER BY debut_creneau");      
      
      if ( $sql->lines == 0 )
      {
        $cts->add_paragraph("Pas de créneau dans les prochaines 48 heures.");
      }
      else
      {
        $blabla = array("laver"=>"Jeton lavage","secher"=>"Jeton sechage");
        $frm = new form("vendre","laverie.php?page=admin&id_salle=$id_salle",true,"POST","Jetons");
        if ( $ErreurVente )
          $frm->error($ErreurVente);
        $frm->add_hidden("action","vendre");
        $frm->add_hidden("id_utilisateur",$user->id);
        while ( $row = $sql->get_row() )
        {
          $frm->add_text_field("jeton[".$row["id_creneau"]."]",$blabla[$row["type"]]." pour ".date("d/m/Y H:i",strtotime($row["debut_creneau"]))." (".$row["lettre"].")" );
        }
        $frm->add_submit("valid","Valider");
        $cts->add($frm,true);
      }
    }
    
    $frm = new form("gouser","laverie.php?page=admin&id_salle=$id_salle",false,"POST","Jetons pour l'utilisateur");
    if ( $Erreur )
      $frm->error($Erreur);
    $frm->add_hidden("action","gouser");
    $frm->add_entity_smartselect ( "id_utilisateur", "Utilisateur", $user );
    $frm->add_submit("search","Rechercher");
    $cts->add($frm,true);
    
  }
  
  $site->add_contents($cts);
  $site->end_page(); 
    
  exit();
}

if ( $_REQUEST["action"] == "reserver" )
{
  $machine = new machine($site->db,$site->dbrw);

  $user = $site->user;

  if ( $is_admin && isset($_REQUEST["id_utilisateur"]) )
  {
    $user = new utilisateur($site->db);
    $user->load_by_id($_REQUEST["id_utilisateur"]);  
  }

  $machine->load_by_id_creneau($_REQUEST["id_creneau1"],$debut);
  $machine->take_creneau($_REQUEST["id_creneau1"],$user->id);
  
  if ( isset($_REQUEST["id_creneau2"]) )
  {
    $machine->load_by_id_creneau($_REQUEST["id_creneau2"],$debut);
    $machine->take_creneau($_REQUEST["id_creneau2"],$user->id);
  }
}

if ( $_REQUEST["page"] == "reserver" )
{
  if ( strpos($_REQUEST["id_creneau"],",") !== false )
  {
    list($id_creneau1,$id_creneau2) = explode(",",$_REQUEST["id_creneau"]);
  }
  else
  {
    $id_creneau1 = $_REQUEST["id_creneau"];
    $id_creneau2 = null;
  }
  
  $machine1 = new machine($site->db);
  $machine2 = new machine($site->db);
  
  $machine1->load_by_id_creneau($id_creneau1,$debut1);
  
  if ( !is_null($id_creneau2) )
    $machine2->load_by_id_creneau($id_creneau2,$debut2);
  
  $site->start_page("services","Laverie");
  $cts = new contents("Machines à laver de l'AE");

  $cts->add_title(2,"Confirmation de la reservation");

  $blabla = array("laver"=>"de la machine à laver","secher"=>"du sèche linge");

  $cts->add_paragraph("Reservation ".$blabla[$machine1->type]." ".$machine1->lettre." le ".date("d/m/Y",$debut1)." à partir de ".date("H:i",$debut1));

  if ( !is_null($id_creneau2) )
    $cts->add_paragraph("et reservation ".$blabla[$machine2->type]." ".$machine2->lettre." le ".date("d/m/Y",$debut2)." à partir de ".date("H:i",$debut2));

  $frm = new form("reserver","laverie.php",false);
  $frm->add_hidden("action","reserver");
  $frm->add_hidden("id_creneau1",$id_creneau1);
  if ( !is_null($id_creneau2) )
    $frm->add_hidden("id_creneau2",$id_creneau2);
    
  if ( $is_admin )
    $frm->add_entity_smartselect ( "id_utilisateur", "Reserver pour", $site->user );
    
  $frm->add_submit("valid","Confirmer");
  $cts->add($frm);


  $site->add_contents($cts);
  $site->end_page();
  
  exit(); 
}
elseif ( $_REQUEST["action"] == "searchmc" )
{
  $site->start_page("services","Laverie");
  $cts = new contents("Machines à laver de l'AE");
    
  if ( $_REQUEST["operation"] ==  3 )
  {
    $sql = 
    "SELECT 
     CONCAT(cl.id_creneau,',',cs.id_creneau) AS id_creneau,
     cl.debut_creneau, SUBTIME(cl.fin_creneau,'00:00:01') AS fin_creneau,
     'Selectionner' AS texte
     FROM mc_creneaux AS cl
     INNER JOIN mc_machines AS ml ON ( cl.id_machine = ml.id AND ml.type='laver' )
     INNER JOIN mc_creneaux AS cs ON ( cs.debut_creneau = cl.fin_creneau )
     INNER JOIN mc_machines AS ms ON ( cs.id_machine = ms.id AND ms.type='secher' )
     WHERE ml.loc='".mysql_real_escape_string($_REQUEST["id_salle"])."'
     AND ms.loc='".mysql_real_escape_string($_REQUEST["id_salle"])."'";
    
    $pl = new weekplanning ( "Selectionner un creneau", $site->db, $sql, "id_creneau", "cl.debut_creneau", "cl.fin_creneau", "texte", "laverie.php?action=searchmc&operation=".$_REQUEST["operation"]."&id_salle=".$_REQUEST["id_salle"], "laverie.php?page=reserver", "GROUP BY cl.debut_creneau" );
    $cts->add($pl,true);    
  }
  else
  {
    $type = $_REQUEST["operation"] ==  1 ? 'laver' : 'secher';
    
    $sql = 
    "SELECT 
     id_creneau,
     debut_creneau, SUBTIME(fin_creneau,'00:00:01') as fin_creneau,
     'Selectionner' AS texte
     FROM mc_creneaux
     INNER JOIN mc_machines ON ( mc_creneaux.id_machine = mc_machines.id  )
     WHERE mc_machines.type='".mysql_real_escape_string($type)."'
     AND mc_machines.loc='".mysql_real_escape_string($_REQUEST["id_salle"])."'";    
     
    $pl = new weekplanning ( "Selectionner un creneau", $site->db, $sql, "id_creneau", "debut_creneau", "fin_creneau", "texte", "laverie.php?action=searchmc&operation=".$_REQUEST["operation"]."&id_salle=".$_REQUEST["id_salle"], "laverie.php?page=reserver" );
    $cts->add($pl,true);     
  }
    

    
  $frm = new form("searchmc","laverie.php",false,"POST","Nouvelle recherche");
  $frm->add_hidden("action","searchmc");
  $frm->add_select_field("id_salle","Lieu",$salles);
  $frm->add_select_field("operation","Machines désirées",array(3=>"Lavage et sechage",1=>"Lavage seulement",2=>"Sechage seulement"));
  $frm->add_submit("search","Rechercher un créneau");
  $cts->add($frm,true);
    
  $site->add_contents($cts);
  $site->end_page();
  
  exit(); 
}


$site->start_page("services","Laverie");
$cts = new contents("Machines à laver de l'AE");

$frm = new form("searchmc","laverie.php",false,"POST","Reserver un creneau");
$frm->add_hidden("action","searchmc");
$frm->add_select_field("id_salle","Lieu",$salles);
$frm->add_select_field("operation","Machines désirées",array(3=>"Lavage et sechage",1=>"Lavage seulement",2=>"Sechage seulement"));
$frm->add_submit("search","Rechercher un créneau");
$cts->add($frm,true);

$sql = new requete($site->db,"
      SELECT debut_creneau, fin_creneau, lettre, type, mc_machines.loc AS id_salle, nom_jeton
      FROM mc_creneaux
      INNER JOIN mc_machines ON mc_creneaux.id_machine = mc_machines.id
      LEFT JOIN mc_jeton ON mc_creneaux.id_jeton = mc_jeton.id_jeton
      WHERE mc_creneaux.id_utilisateur = ".$site->user->id."
      AND fin_creneau >= NOW()
      ORDER BY debut_creneau");

$tbl = new sqltable("lstcrfutur",
  "Liste des créneaux réservés",
  $sql,
  "index.php",
  "id",
  array(
    "debut_creneau" => "Début du créneau",
    "fin_creneau" => "Fin du créneau",
    "lettre" => "Lettre",
    "type" => "Type de la machine",
    "id_salle" => "Lieu",
    "nom_jeton" => "Jeton à utiliser"),
  array("delete" => "Annuler la réservation"),
  array(),
  array("type"=>$GLOBALS['types_machines'],"id_salle"=>$salles) );

$cts->add($tbl,true);

if ( $is_admin )
  $cts->add_paragraph("<a href=\"?page=admin\">Administration</a>");

$site->add_contents($cts);
$site->end_page(); 

?>