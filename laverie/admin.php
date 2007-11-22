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
  $cts = new contents("<a href=\"laverie.php\">Laverie</a> / <a href=\"admin.php\">Administration</a>");

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
			
$user = new utilisateur($site->db,$site->dbrw);

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
elseif($_REQUEST['action'] == "blacklist")
{
  if ( !isset($_REQUEST['id_utilisateurs']) )
    $_REQUEST['id_utilisateurs'] = array($_REQUEST['id_utilisateur']);
  
  $cts->add_title(2,"Bloquage");
  
  foreach ( $_REQUEST['id_utilisateurs'] as $id )
  {
    $user = new utilisateur($site->db, $site->dbrw);
    $user->load_by_id($id);
    $user->add_to_group(GRP_BLACKLIST);
    $cts->add_paragraph($user->get_html_link()." a bien été banni de l'usage des machines");
  }
  $user->id = null;
}
elseif($_REQUEST['action'] == "unblacklist")
{
  if ( !isset($_REQUEST['id_utilisateurs']) )
    $_REQUEST['id_utilisateurs'] = array($_REQUEST['id_utilisateur']);  
    
  $cts->add_title(2,"De-bloquage");
  
  foreach ( $_REQUEST['id_utilisateurs'] as $id )
  {
    $user->load_by_id($id);
    $user->remove_from_group(GRP_BLACKLIST);
    $cts->add_paragraph($user->get_html_link()." a bien été débanni de l'usage des machines");
  }
  $user->id = null;
}
elseif($_REQUEST['action'] == "mail_rappel")
{
  if ( !isset($_REQUEST['id_utilisateurs']) )
    $_REQUEST['id_utilisateurs'] = array($_REQUEST['id_utilisateur']);   
    
  $cts->add_title(2,"Mail de rappel");
  
  foreach ( $_REQUEST['id_utilisateurs'] as $id )
  {
    $id = intval($id);

    $user->load_by_id($id);

    $sql = new requete($site->db, "SELECT 
    `mc_jeton_utilisateur`.`id_jeton`
    , `mc_jeton`.`nom_jeton`
    , DATEDIFF(CURDATE(), `mc_jeton_utilisateur`.`prise_jeton`) AS `duree` 
    FROM `mc_jeton` 
    INNER JOIN `mc_jeton_utilisateur` ON `mc_jeton`.`id_jeton` = `mc_jeton_utilisateur`.`id_jeton` 
    WHERE `id_utilisateur` = $id AND mc_jeton_utilisateur.retour_jeton IS NULL");
    /* et si y'a pas de lignes ? */
    if ($sql->lines <= 0)
      continue;

    $body = "Bonjour, 

Vous utilisez le service de machines à laver proposé par l'AE et nous vous en remercions, nous attirons votre attention sur le fait que les jetons vous sont prêtés pour une utilisation des machines dans la journée suivante, ceci afin de permettre une bonne circulation des jetons, garantissant ainsi à tous la possiblité de bénéficier de ce service.

Or vous avez encore en votre possession le(s) jeton(s) suivant(s) : \n";
    
    while ($row = $sql->get_row())
      $body .= "- Jeton n°".$row['nom_jeton'].", emprunté depuis ".$row['duree']." jours \n";
    
    $body .= "\n Afin que tout le monde puisse profiter des machines mises à disposition par l'AE nous vous remercions de bien vouloir utiliser ou rapporter ces jetons dans les plus brefs délais, à défaut de quoi, vous pourriez vous voir bloquer l'accès à ce service.

Merci d'avance

Les responsables machines à laver";

    $mail = mail($user->email, utf8_decode("[AE] Jetons de machines à laver"), utf8_decode($body),
        "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae@utbm.fr");
        
    if ($mail)
      $cts->add_paragraph("Mail de rappel &agrave; ".$user->get_html_link()." : Envoy&eacute;");  
    else
      $cts->add_paragraph("Erreur lors de l'envoi du mail de rappel pour ".$user->get_html_link(),"error");
  }
  $user->id = null;
}

// Contenu des onglets
if ( $_REQUEST["view"] == "mc" ) // Liste des machines
{
  
  
  
  
  
}
elseif ( $_REQUEST["view"] == "bc" ) // Mauvais clients
{
  
  $sql = new requete($site->db, "SELECT mc_jeton_utilisateur.id_jeton,
    mc_jeton_utilisateur.id_utilisateur,
    mc_jeton_utilisateur.retour_jeton,
    COUNT(id_jeton) AS nombre,
    utilisateurs.nom_utl, 
    utilisateurs.prenom_utl, 
    utilisateurs.id_utilisateur,
    CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS nom_utilisateur,
    DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) AS duree
    FROM mc_jeton_utilisateur
    LEFT JOIN utilisateurs 
    ON mc_jeton_utilisateur.id_utilisateur = utilisateurs.id_utilisateur
    WHERE mc_jeton_utilisateur.retour_jeton IS NULL
    AND `retour_jeton` IS NULL
    AND (DATEDIFF(CURDATE(), mc_jeton_utilisateur.prise_jeton) > 10)
    GROUP BY mc_jeton_utilisateur.id_utilisateur
    ORDER BY nombre DESC");


  $table = new sqltable("toploosers",
  "Top des mauvais clients (jetons non rendus depuis plus de 10 jours)",
  $sql,
  "admin.php?id_salle=$id_salle&view=bc",
  "id_utilisateur",
  array(
    "nom_utilisateur"=>"Utilisateur",
    "nombre" => "Nombre",
    "duree" => "Depuis (jours)"
  ),
  array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
  array("mail_rappel"=>"Envoyer mail de rappel", "blacklist" => "Blacklister"),
  array() );

  $cts->add($table, true);

  $sql = new requete($site->db, "SELECT utilisateurs.id_utilisateur, 
    CONCAT(utilisateurs.prenom_utl,' ', utilisateurs.nom_utl) AS nom_utilisateur
    FROM utl_groupe INNER JOIN utilisateurs ON utilisateurs.id_utilisateur = utl_groupe.id_utilisateur 
    WHERE utl_groupe.id_groupe = 29 
    ORDER BY utilisateurs.nom_utl, utilisateurs.prenom_utl");

  $table = new sqltable("blackmember", 
      "Liste des personnes bloquées",
      $sql,
      "admin.php?id_salle=$id_salle&view=bc",
      "id_utilisateur",
      array("nom_utilisateur" => "Utilisateur"),
      array("unblacklist" => "Débloquer"),
      array("unblacklist" => "Débloquer"),
      array()  );

  $cts->add($table, true);

  $frm = new form("blacklist","admin.php?id_salle=$id_salle&view=bc",false,"POST","Bloquer une autre personne");
  $frm->add_hidden("action","blacklist");
  $frm->add_entity_smartselect ( "id_utilisateur", "Utilisateur", $user );
  $frm->add_submit("blacklist","Bloquer");
  $cts->add($frm,true);  
  
  
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

}

$site->add_contents($cts);
$site->end_page(); 
    
?>