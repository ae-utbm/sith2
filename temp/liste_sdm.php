<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once ($topdir. "include/assoclub.inc.php");
require_once ($topdir . "include/pdf/facture_pdf.inc.php");

$site = new site ();

if ($site->user->id < 1)
	error_403();

$asso = new asso($site->db,$site->dbrw);

$asso->load_by_id(17);
$conds = array("cpt_vendu.id_assocpt='".$asso->id."'",
               "cpt_produits.id_produit='183'");

$req = new requete($site->db, "SELECT " .
		"`cpt_debitfacture`.`id_facture`, " .
		"`cpt_debitfacture`.`date_facture`, " .
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .
		"`client`.`nom_utl` AS `nom_client`, ".
		"`client`.`prenom_utl` as `prenom_client`, " .
		"`client`.`id_utilisateur` AS `id_utilisateur_client`, " .
		"CONCAT(`vendeur`.`prenom_utl`,' ',`vendeur`.`nom_utl`) as `nom_utilisateur_vendeur`, " .
		"`vendeur`.`id_utilisateur` AS `id_utilisateur_vendeur`, " .			
		"`cpt_vendu`.`quantite`, " .
		"`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
		"`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
		"`cpt_produits`.`prix_achat_prod`*`cpt_vendu`.`quantite`/100 AS `total_coutant`," .
		"`cpt_comptoir`.`id_comptoir`, " .
		"`cpt_produits`.`nom_prod`, " .
		"`cpt_produits`.`id_produit`, " .
		"`cpt_type_produit`.`id_typeprod` " .
		"FROM `cpt_vendu` " .
		"INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
		"INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
		"INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
		"INNER JOIN `cpt_type_produit` ON `cpt_produits`.`id_typeprod` =`cpt_type_produit`.`id_typeprod` " .
		"INNER JOIN `utilisateurs` AS `vendeur` ON `cpt_debitfacture`.`id_utilisateur` =`vendeur`.`id_utilisateur` " .	
		"INNER JOIN `utilisateurs` AS `client` ON `cpt_debitfacture`.`id_utilisateur_client` =`client`.`id_utilisateur` " .
		"INNER JOIN `cpt_comptoir` ON `cpt_debitfacture`.`id_comptoir` =`cpt_comptoir`.`id_comptoir` " .
		"WHERE " .implode(" AND ",$conds).
		"ORDER BY `cpt_debitfacture`.`date_facture` DESC");
	
if (isset($_REQUEST['action']) && $_REQUEST['action']=="getpdf")
{
  define('FPDF_FONTPATH', $topdir.'./font/');

  $pdf=new FPDF();

  $pdf->AliasNbPages();
  $pdf->SetAutoPageBreak(false);
  $pdf->AddPage();
  $pdf->SetFont('Arial','B',14);

  $pdf->Image($topdir."./images/Ae-blanc.jpg", 10, 10, 75);
  $pdf->SetFont('Times','BI',22);
  $pdf->Cell(0, 18, "Semaine de Mars Printemps 2007",'','','R');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',14);
  $pdf->Cell('', 20, "Liste Achat Places E-Boutic",'','','C');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',11);

  //Header
  $w=array(90,60,40);

  //Data
  $fill=0;
  $skip=57;
  $height=0;
  $page=1;
  $breakpage = false;

	while ($res = $req->get_row())
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
	  $pdf->Cell($w[1],7,"Prénom", 1, 0, 'C', 1);
	  $pdf->Cell($w[2],7,"Quantité", 1, 0, 'C', 1);

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

      $pdf->Cell($w[0],6,utf8_decode($res['nom_client']),$border,0,'L',$fill);
      $pdf->Cell($w[1],6,utf8_decode($res['prenom_client']),$border,0,'L',$fill);
      $pdf->Cell($w[2],6,utf8_decode($res['quantite']),$border,0,'L',$fill);

      $pdf->Ln();

      if($breakpage == true)
	{
	  $skip = 0;
	  $height = 0;
	  $pdf->Cell(0, 10, " AE R&D - Page " . $page . " - Realised by Lorenzo & Ayolo",0,0,'C');
	  $pdf->AddPage();
	  $page++;
	  $breakpage = false;
	}

      $fill=!$fill;
    }

  $pdf->Cell(array_sum($w),0,'','T');
  $pdf->Output('liste_eboutic_sdm_P2007.pdf',D);

}


$site->start_page("services","AE - Recherche et Développement");

$intro = new contents("Edition liste au format pdf");

$intro->add_paragraph("Cliquez <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\">ici</a> pour obtenir le fichier pdf de cette liste : <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\"><img src=\"".$topdir."/images/pdf.png\"></a>");

$cts = new sqltable(
	"listresp", 
	"Listing", $req, "ventes.php", 
	"id_facture", 
	array(
		"id_facture"=>"Facture",
		"date_facture"=>"Date",
		"nom_prod"=>"Produit",
		"nom_client"=>"Nom",
		"prenom_client"=>"Prenom",
		"nom_asso"=>"Asso.",
		"quantite"=>"Qte",
		"total"=>"Som.",
		"total_coutant"=>"Coutant*"), 
	array(), 
	array(),
	array());
	
$site->add_contents($intro);
$site->add_contents($cts);
$site->end_page();


?>
