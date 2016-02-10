<?php
/* Copyright 2015
 * - Simon Magnin-Feysot < pike at smagnin dot org >
 * Copyright 2006
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
$topdir="../";
require_once("include/comptoirs.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once("include/facture.inc.php");
$site = new sitecomptoirs();

if ( !$site->user->is_valid() )
{
  header("Location: ../403.php?reason=session");
  exit();
}

$site->fetch_proprio_comptoirs();
$comptoirs = array(0=>"-") + $site->proprio_comptoirs;

if ( !count($site->proprio_comptoirs) && !$site->user->is_in_group("gestion_ae") )
  $site->error_forbidden("services");

$site->set_admin_mode();


if (isset($_REQUEST['action']) && $_REQUEST['action']=="pdf")
{
  require_once ($topdir . "include/pdf/facture_pdf.inc.php");
  define('FPDF_FONTPATH', $topdir.'./font/');
  $pdf=new FPDF();
  $pdf->AliasNbPages();
  $pdf->SetAutoPageBreak(false);
  $pdf->AddPage();
  $pdf->SetFont('Arial','B',14);

  $pdf->Image($topdir."./images/Ae-blanc.jpg", 10, 10, 0, 20);
  $pdf->SetFont('Times','BI',22);
  $pdf->Cell(0, 18, date("d/m/Y"),'','','R');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',14);
  $pdf->Cell('', 20, "Liste",'','','C');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',11);

  //Header
  $w=array(40,40,60,20,15,15);

  //Data
  $fill=0;
  $skip=57;
  $height=0;
  $page=1;
  $breakpage = false;

  $conds = array();
  foreach($_POST['conds'] as $value)
  {
    $conds[] = $value;
  }

  $req_= new requete($site->db, "SELECT " .
        "'ae_cotisations'.'id_cotisation'" .
        "'ae_cotisations'.'id_utilisateur'" .
        "'ae_cotisations'.'date_cotis'" .
        "'ae_cotisations'.'date_fin_cotis'" .
        "`ae_cotisations`.`prix_paye_cotis`" .
        "`ae_cotisations`.`id_comptoir`" .
        "`cpt_comptoir`.`id_comptoir`" .
        "`cpt_comptoir`.`nom_cpt`" .
        "'utilisateurs'.'id_utilisateur'" .
        "'utilisateurs'.'nom_utl'" .
        "'utilisateurs'.'prenom_utl'" .
        "FROM `ae_cotisations` " .
        "LEFT JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` =`ae_cotisations`.`id_comptoir` " .
        "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
        "INNER JOIN `utilisateurs` AS `client` ON `ae_cotisations`.`id_utilisateur` =`utilisateur`.`id_utilisateur` " .
        "WHERE " .implode(" AND ",$conds).
        "ORDER BY `ae_cotisations`.`id_cotisations` ASC");

  while ($res = $req_->get_row())
  {
    if(($skip + $height) > 255)
    {
      $breakpage = true;
      $border = 'LRB';
    }
    else if($height == 0)
    {
      //Colors, line width and bold font
      $pdf->SetFillColor(0,0,0);
      $pdf->SetTextColor(255);
      $pdf->SetDrawColor(128,128,128);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('','B');

      $pdf->Cell($w[0],7,"Nom", 1, 0, 'C', 1);
      $pdf->Cell($w[1],7,"Prenom", 1, 0, 'C', 1);
      $pdf->Cell($w[2],7,"Type de cotisations", 1, 0, 'C', 1);
      $pdf->Cell($w[3],7,"Paiement", 1, 0, 'C', 1);
      $pdf->Cell($w[4],7,"Prix", 1, 0, 'C', 1);

      $pdf->Ln();

      //Color and font restoration
      $pdf->SetFillColor(200,200,200);
      $pdf->SetTextColor(0);
      $pdf->SetFont('');

      $height += 7;

      $border = 'LR';
    }
    else
    {
      $border = 'LR';

      $height += 6;
    }

    $pdf->Cell($w[0],6,utf8_decode($res['nom_utl']),$border,0,'L',$fill);
    $pdf->Cell($w[1],6,utf8_decode($res['prenom_utl']),$border,0,'L',$fill);
    $pdf->Cell($w[2],6,utf8_decode($res['type_cotiz']),$border,0,'L',$fill);
    $pdf->Cell($w[3],6,utf8_decode($res['type_payement']),$border,0,'L',$fill);
    $pdf->Cell($w[4],6,utf8_decode($res['prix_paye_cotis']),$border,0,'L',$fill);

    $pdf->Ln();

    if($breakpage == true)
    {
      $skip = 0;
      $height = 0;
      $pdf->Cell(0, 10, "Page " . $page ,0,0,'C');
      $pdf->AddPage();
      $page++;
      $breakpage = false;
    }

    $fill=!$fill;
  }

  $pdf->Cell(array_sum($w),0,'','T');
  $pdf->Output('liste_cotisations_'.date("d-m-Y").'.pdf',D);
}

$site->start_page("services","Comptabilité cotisations");

$cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / Ventes système carte AE/e-boutic");

$frm = new form ("cptacpt","compta.php",true,"POST","Critères de selection");
$frm->add_hidden("action","view");
$frm->add_select_field("mode","Mode", array(""=>"Brut","day"=>"Statistiques/Jour","week"=>"Statistiques/Semaines","month"=>"Statistiques/Mois","year"=>"Statistiques/Année"),$_REQUEST["mode"]);
$frm->add_datetime_field("debut","Date et heure de début");
$frm->add_datetime_field("fin","Date et heure de fin");
$frm->add_select_field("id_comptoir","Comptoir", $comptoirs,$_REQUEST["id_comptoir"]);
$frm->add_entity_select("type_cotis", "Type de cotisation", $site->db, "produit",$_REQUEST["id_produit"],true);
$frm->add_submit("valid","Voir");
$cts->add($frm,true);

if ( $_REQUEST["action"] == "view" && $_REQUEST["mode"] == "" )
{
  $conds = array();
  $comptoir = false;

  if ( $_REQUEST["debut"] )
    $conds[] = "ae_cotisations.date_cotis >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";

  if ( $_REQUEST["fin"] )
    $conds[] = "ae_cotisations.date_cotis <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";

  if ( isset($comptoirs[$_REQUEST["id_comptoir"]]) && $_REQUEST["id_comptoir"] )
  {
    $conds[] = "ae_cotisations.id_comptoir='".intval($_REQUEST["id_comptoir"])."'";
    $comptoir=true;
  }

  if ( $comptoir || $site->user->is_in_group("gestion_ae") )
  {

    if ( $_REQUEST["type de cotisation"] )
      $conds[] = "cpt_vendu.id_assocpt='".intval($_REQUEST["id_assocpt"])."'";

    if ( $_REQUEST["id_typeprod"] )
      $conds[] = "cpt_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";

    if ( $_REQUEST["id_produit"] )
      $conds[] = "cpt_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";
  }

  if ( count($conds) )
  {

    $req = new requete($site->db, "SELECT " .
        "COUNT(`ae_cotisations`.`type_cotis`), " .
        "SUM(`ae_cotisations`.`id_comptoir`), " .
        "SUM(`ae_cotisations`.`prix_paye_cotis`) AS `total`," .
        "FROM `ae_cotisations` " .
        "INNER JOIN `utilisateurs` AS `client` ON `ae_cotisations`.`id_utilisateur` =`client`.`id_utilisateur` " .
        "INNER JOIN `cpt_comptoir` ON `ae_cotisations`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
        "WHERE " .implode(" AND ",$conds).
        "ORDER BY `ae_cotisations`.`date_cotis` DESC");

    list($ln,$qte,$sum,$sumcoutant) = $req->get_row();


    $cts->add_title(2,"Sommes");
    $cts->add_paragraph("Quantitée : $qte unités<br/>" .
        "Chiffre d'affaire: ".($sum/100)." Euros<br/>" .
        "Prix countant total estimé* : ".($sumcoutant/100)." Euros");

    $frm = new form ("cptacptpdf","compta.php",true,"POST","PDF");
    $frm->add_hidden("action","pdf");
    $i=0;
    foreach($conds as $value)
    {
      $frm->add_hidden("conds[".$i."]",$value);
      $i++;
    }
    $frm->add_submit("valid","Générer le PDF");
    $cts->add($frm,true);



    if ( $ln < 1000 )
    {
    $req_= new requete($site->db, "SELECT " .
        "'ae_cotisations'.'id_cotisation'" .
        "'ae_cotisations'.'id_utilisateur'" .
        "'ae_cotisations'.'date_cotis'" .
        "'ae_cotisations'.'date_fin_cotis'" .
        "`ae_cotisations`.`prix_paye_cotis`" .
        "`ae_cotisations`.`id_comptoir`" .
        "`cpt_comptoir`.`id_comptoir`" .
        "`cpt_comptoir`.`nom_cpt`" .
        "'utilisateurs'.'id_utilisateur'" .
        "'utilisateurs'.'nom_utl'" .
        "'utilisateurs'.'prenom_utl'" .
        "FROM `ae_cotisations` " .
        "LEFT JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` =`ae_cotisations`.`id_comptoir` " .
        "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
        "INNER JOIN `utilisateurs` AS `client` ON `ae_cotisations`.`id_utilisateur` =`utilisateur`.`id_utilisateur` " .
        "WHERE " .implode(" AND ",$conds).
        "ORDER BY `ae_cotisations`.`id_cotisations` DESC");

      $cts->add(new sqltable(
        "listresp",
        "Listing", $req, "compta.php",
        "id_facture",
        array(
          "date_cotis"=>"Date de cotis",
          "date_fin_cotis"=>"Date find de cotis",
          "nom_cpt"=>"Lieu",
          "nom_utl"=>"Nom client",
          "prenom_utl"=>"Prénom Client",
          "prix_paye_cotis"=>"Prix payé",
        array(),
        array( )
        ),true);
    }
    $cts->add_paragraph("* ATTENTION: Prix coutant basé sur le prix actuel.");

  }
}
elseif ( $_REQUEST["action"] == "view"  )
{

  $conds = array();
  $comptoir = false;

  if ( $_REQUEST["debut"] )
    $conds[] = "ae_cotisations.date_cotis >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";

  if ( $_REQUEST["fin"] )
    $conds[] = "ae_cotisations.date_cotis <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";

  if ( isset($comptoirs[$_REQUEST["id_comptoir"]]) && $_REQUEST["id_comptoir"] )
  {
    $conds[] = "ae_cotisations.id_comptoir='".intval($_REQUEST["id_comptoir"])."'";
    $comptoir=true;
  }

  if ( $comptoir || $site->user->is_in_group("gestion_ae") )
  {

    if ( $_REQUEST["id_assocpt"] )
      $conds[] = "cpt_vendu.id_assocpt='".intval($_REQUEST["id_assocpt"])."'";

    if ( $_REQUEST["id_typeprod"] )
      $conds[] = "cpt_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";

    if ( $_REQUEST["id_produit"] )
      $conds[] = "cpt_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";
  }

  if ( count($conds))
  {

    if ( $_REQUEST["mode"] == "day" )
      $decoupe = "DATE_FORMAT(`ae_cotisations`.`date_cotis`,'%Y-%m-%d')";
    elseif ( $_REQUEST["mode"] == "week" )
      $decoupe = "YEARWEEK(`ae_cotisations`.`date_cotis`)";
    elseif ( $_REQUEST["mode"] == "year" )
      $decoupe = "DATE_FORMAT(`ae_cotisations`.`date_cotis`,'%Y')";
    else
      $decoupe = "1";

    $req = new requete($site->db, "SELECT " .
        "$decoupe AS `unit`, " .
        "COUNT(`ae_cotisations`.`type_cotis`), " .
        "SUM(`ae_cotisations`.`id_comptoir`), " .
        "SUM(`ae_cotisations`.`prix_paye_cotis`) AS `total`," .
        "FROM `ae_cotisations` " .
        "INNER JOIN `utilisateurs` AS `client` ON `ae_cotisations`.`id_utilisateur` =`client`.`id_utilisateur` " .
        "INNER JOIN `cpt_comptoir` ON `ae_cotisations`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
        "WHERE " .implode(" AND ",$conds)." " .
        "GROUP BY `unit` ".
        "ORDER BY `unit`");

    $tbl = new sqltable("stats",
                        "Statistiques",
                        $req,
                        "",
                        "",
                        array("unit"=>"",
                              "quantite"=>"Quantité",
                              "total"=>"CA",
                              "total_coutant"=>"Coutant"),
                        array(),
                        array(),
                        array());
    $cts->add($tbl,true);

    // $tbl = new table("Tableau");

    // $tbl->add_row(array("","Quantité","CA","Coutant"));

    // while ( list($unit,$qte,$total,$coutant) = $req->get_row() )
    //   $tbl->add_row(array($unit,$qte,$total/100,$coutant/100));

    // $cts->add($tbl,true);


    $cts->add(new image("Graphique","compta.graph.php?mode=".$_REQUEST["mode"]."&".
    "debut=".$_REQUEST["debut"]."&".
    "fin=".$_REQUEST["fin"]."&".
    "id_comptoir=".$_REQUEST["id_comptoir"].),true);

  }


}

$site->add_contents($cts);
$site->end_page();

?>
