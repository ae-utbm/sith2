<?php
/**
 * @brief Admin de la boutique utbm
 *
 */

/* Copyright 2008
 *
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

$topdir="../";

require_once("include/boutique.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/gallery.inc.php");
require_once($topdir . "include/entities/folder.inc.php");
require_once($topdir . "include/entities/files.inc.php");
require_once($topdir . "include/cts/taglist.inc.php");

$GLOBALS["entitiescatalog"]["typeproduit"]   = array ( "id_typeprod", "nom_typeprod", "typeprod.png", "boutique-utbm/admin.php", "boutiqueut_type_produit");
$GLOBALS["entitiescatalog"]["produit"]       = array ( "id_produit", "nom_prod", "produit.png", "boutique-utbm/admin.php", "boutiqueut_produits" );

function generate_subform_stock ( $nom,$form_n, $stock_n, $stock_value_n, $stock = -1 )
{

 $subfrm=new form ($form_n,false,false,false,$nom);

 $subfrm1=new form ($stock_n,false,false,false,"Non limité");
 $subfrm->add($subfrm1,false,true,($stock==-1),"nlim",true);

 $subfrm2=new form ($stock_n,false,false,false,"Limité à");
 $subfrm2->add_text_field($stock_value_n,"",($stock==-1)?"":$stock);
 $subfrm->add($subfrm2,false,true,($stock!=-1),"lim",true);

 return $subfrm;
}

$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$file = new dfile($site->db, $site->dbrw);
$folder = new dfolder($site->db, $site->dbrw);
$folder->load_by_id(FOLDERID);
$file = new dfile($site->db, $site->dbrw);
$typeprod = new typeproduit($site->db,$site->dbrw);
$produit = new produit($site->db,$site->dbrw);
$produit_parent = new produit($site->db);

if ( isset($_REQUEST["id_typeprod"]) )
  $typeprod->load_by_id($_REQUEST["id_typeprod"]);
if ( isset($_REQUEST["id_produit"]) )
  $produit->load_by_id($_REQUEST["id_produit"]);

if ( $_REQUEST["page"] == "statistiques" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Statistiques");
  // chiffre d'affaire
  $frm = new form ("boutiqueutaboutiqueut","gestion.php?page=statistiques",true,"POST","Critères de selection");
  $frm->add_hidden("action","view");
  $frm->add_select_field("mode","Mode", array(""=>"Brut","day"=>"Statistiques/Jour","week"=>"Statistiques/Semaines","month"=>"Statistiques/Mois","year"=>"Statistiques/Année"),$_REQUEST["mode"]);
  $frm->add_datetime_field("debut","Date et heure de début");
  $frm->add_datetime_field("fin","Date et heure de fin");
  $frm->add_entity_select("id_typeprod", "Type", $site->db, "typeproduit",$_REQUEST["id_typeprod"],true);
  $frm->add_entity_select("id_produit", "Produit", $site->db, "produit",$_REQUEST["id_produit"],true);
  $frm->add_submit("valid","Voir");
  $cts->add($frm,true);

  if($_REQUEST["action"] == "view" && $_REQUEST["mode"] == "" )
  {
    $conds = array();
    if ( $_REQUEST["debut"] )
      $conds[] = "boutiqueut_debitfacture.date_facture >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";
    if ( $_REQUEST["fin"] )
      $conds[] = "boutiqueut_debitfacture.date_facture <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";
    if ( $_REQUEST["id_typeprod"] )
      $conds[] = "boutiqueut_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";
    if ( $_REQUEST["id_produit"] )
      $conds[] = "boutiqueut_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";
    if ( count($conds) )
    {

      $req = new requete($site->db, "SELECT " .
          "COUNT(`boutiqueut_vendu`.`id_produit`), " .
          "SUM(`boutiqueut_vendu`.`quantite`), " .
          "SUM(`boutiqueut_vendu`.`prix_unit`*`boutiqueut_vendu`.`quantite`) AS `total`," .
          "SUM(`boutiqueut_produits`.`prix_achat_prod`*`boutiqueut_vendu`.`quantite`) AS `total_coutant`" .
          "FROM `boutiqueut_vendu` " .
          "INNER JOIN `boutiqueut_produits` ON `boutiqueut_produits`.`id_produit` =`boutiqueut_vendu`.`id_produit` " .
          "INNER JOIN `boutiqueut_type_produit` ON `boutiqueut_produits`.`id_typeprod` =`boutiqueut_type_produit`.`id_typeprod` " .
          "INNER JOIN `boutiqueut_debitfacture` ON `boutiqueut_debitfacture`.`id_facture` =`boutiqueut_vendu`.`id_facture` " .
          "WHERE " .implode(" AND ",$conds).
          "ORDER BY `boutiqueut_debitfacture`.`date_facture` DESC");

      list($ln,$qte,$sum,$sumcoutant) = $req->get_row();

      $cts->add_title(2,"Sommes");
      $cts->add_paragraph("Quantitée : $qte unités<br/>" .
          "Chiffre d'affaire: ".($sum/100)." Euros<br/>" .
          "Prix countant total estimé* : ".($sumcoutant/100)." Euros");
      $cts->add_paragraph("* ATTENTION: Prix coutant basé sur le prix actuel.");
    }
  }
  elseif($_REQUEST["action"] == "view")
  {
    $conds = array();

    if ( $_REQUEST["debut"] )
      $conds[] = "boutiqueut_debitfacture.date_facture >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";

    if ( $_REQUEST["fin"] )
      $conds[] = "boutiqueut_debitfacture.date_facture <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";

    if ( $_REQUEST["id_typeprod"] )
      $conds[] = "boutiqueut_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";

    if ( $_REQUEST["id_produit"] )
      $conds[] = "boutiqueut_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";
    if ( count($conds))
    {
      if ( $_REQUEST["mode"] == "day" )
        $decoupe = "DATE_FORMAT(`boutiqueut_debitfacture`.`date_facture`,'%Y-%m-%d')";
      elseif ( $_REQUEST["mode"] == "week" )
        $decoupe = "YEARWEEK(`boutiqueut_debitfacture`.`date_facture`)";
      elseif ( $_REQUEST["mode"] == "year" )
        $decoupe = "DATE_FORMAT(`boutiqueut_debitfacture`.`date_facture`,'%Y')";
      else
        $decoupe = "DATE_FORMAT(`boutiqueut_debitfacture`.`date_facture`,'%Y-%m')";

      $req = new requete($site->db, "SELECT " .
          "$decoupe AS `unit`, " .
          "SUM(`boutiqueut_vendu`.`quantite`), " .
          "SUM(`boutiqueut_vendu`.`prix_unit`*`boutiqueut_vendu`.`quantite`) AS `total`," .
          "SUM(`boutiqueut_produits`.`prix_achat_prod`*`boutiqueut_vendu`.`quantite`) AS `total_coutant`" .
          "FROM `boutiqueut_vendu` " .
          "INNER JOIN `boutiqueut_produits` ON `boutiqueut_produits`.`id_produit` =`boutiqueut_vendu`.`id_produit` " .
          "INNER JOIN `boutiqueut_type_produit` ON `boutiqueut_produits`.`id_typeprod` =`boutiqueut_type_produit`.`id_typeprod` " .
          "INNER JOIN `boutiqueut_debitfacture` ON `boutiqueut_debitfacture`.`id_facture` =`boutiqueut_vendu`.`id_facture` " .
          "WHERE " .implode(" AND ",$conds)." " .
          "GROUP BY `unit` ".
          "ORDER BY `unit`");

      $tbl = new table("Tableau");

      $tbl->add_row(array("","Quantité","CA","Coutant"));

      while ( list($unit,$qte,$total,$coutant) = $req->get_row() )
        $tbl->add_row(array($unit,$qte,$total/100,$coutant/100));

      $cts->add($tbl,true);


      $cts->add(new image("Graphique","graph.php?mode=".$_REQUEST["mode"]."&".
        "debut=".$_REQUEST["debut"]."&".
        "fin=".$_REQUEST["fin"]."&".
        "id_typeprod=".$_REQUEST["id_typeprod"]."&".
        "id_produit=".$_REQUEST["id_produit"]),true);

    }
  }

  //chiffre 
  //statistiques stocks presques vides
  //statistiques meilleurs ventes sur le dernier mois
  //statistiques meilleurs ventes sur l'année
  //statistiques pires ventes sur le mois
  //statistiques pires ventes sur l'année
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "factures" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Factures");
  $req = new requete($site->db,
         "SELECT ".
         "IF(f.id_utilisateur=0, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS nom_utilisateur ".
         ", IF(f.id_utilisateur=0, -1, f.id_utilisateur) ".
         ", f.date_facture ".
         ", IF(f.mode_paiement='UT', 'Boutique', IF(f.mode_paiement='CH','Chèque','Espèces')) AS mode ".
         ", f.id_facture ".
         ", IF(f.ready=1,IF(f.etat_facture=1,'à retirer','retirée'),'en préparation') AS etat ".
         "FROM boutiqueut_debitfacture f ".
         "LEFT JOIN utilisateurs u USING(id_utilisateur) ".
         "ORDER BY f.id_facture DESC");
  $cts->add(new sqltable(
         "factures",
         "Factures",
         $req,
         "admin_gen_fact.php",
         "id_facture",
         array('date_facture'=>'Date',"nom_utilisateur" => "Utilisateur","mode"=>"Mode de paiement","etat"=>"État"),
         array('info'=>'Détail'),
         array(),
         array()),
         true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "ventes" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Ventes");

  $req = new requete($site->db,
         "SELECT ".
         "IF(f.id_utilisateur=0, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS nom_utilisateur ".
         ", IF(f.id_utilisateur=0, -1, f.id_utilisateur) ".
         ", f.date_facture ".
         ", f.id_facture ".
         ", IF(f.ready=1,'à retirer','en préparation') AS etat ".
         "FROM boutiqueut_debitfacture f ".
         "LEFT JOIN utilisateurs u USING(id_utilisateur) ".
         "WHERE mode_paiement='UT' ".
         "AND f.ready=0 OR (f.ready=1 AND f.etat_facture=1)".
         "ORDER BY f.id_facture DESC");
  $cts->add(new sqltable(
         "factures",
         "Factures",
         $req,
         "admin_gen_fact.php",
         "id_facture",
         array('date_facture'=>'Date',"nom_utilisateur" => "Utilisateur","etat"=>"État"),
         array('info'=>'Détail'),
         array(),
         array()),
         true);


//liste des ventes en attente


  $site->add_contents($cts);
  $site->end_page();
  exit();
}

$site->add_css("css/d.css");
$site->start_page('adminbooutique',"Administration");
$cts = new contents("Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Gestion");
$lst = new itemlist("");
$lst->add("<a href=\"gestion.php?page=statistiques\">Statistiques</a>");
$lst->add("<a href=\"gestion.php?page=factures\">Factures</a>");
$lst->add("<a href=\"gestion.php?page=ventes\">Ventes</a>");
$cts->add($lst,true);
$site->add_contents($cts);
$site->end_page();

?>
