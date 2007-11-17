<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once ($topdir. "include/entities/asso.inc.php");
require_once ($topdir . "include/pdf/facture_pdf.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT " .
      "`utilisateurs`.`id_utilisateur`,`utilisateurs`.`nom_utl` AS `nom_utilisateur`,`utilisateurs`.`prenom_utl` AS `prenom`, " .
      "`cpt_vendu`.`quantite`, " .
      "`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
      "`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
      "`cpt_produits`.`nom_prod` " .
      "FROM `cpt_vendu` " .
      "INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit`=`cpt_vendu`.`id_produit` " .
      "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
      "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`cpt_debitfacture`.`id_utilisateur_client` ".
      "WHERE `cpt_vendu`.`a_retirer_vente`='1' ".
      "AND (`cpt_vendu`.`id_produit`='316' OR `cpt_vendu`.`id_produit`='317' OR `cpt_vendu`.`id_produit`='303' OR `cpt_vendu`.`id_produit`='315' OR `cpt_vendu`.`id_produit`='304') " .
      "ORDER BY `utilisateurs`.`nom_utl` ASC");


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
  $pdf->Cell(0, 18, "Automne 2007",'','','R');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',14);
  $pdf->Cell('', 20, "Liste des étudiants extérieurs non cotisants à l'AE",'','','C');
  $pdf->Ln();

  $pdf->SetFont('Arial','B',11);

  //Header
  $w=array(90,90);

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
  //$pdf->Cell($w[2],7,"Surnom", 1, 0, 'C', 1);

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

      $pdf->Cell($w[0],6,utf8_decode($res['nom_utilisateur']),$border,0,'C',$fill);
      $pdf->Cell($w[1],6,utf8_decode($res['prenom']),$border,0,'C',$fill);
      //$pdf->Cell($w[2],6,utf8_decode($res['surnom']),$border,0,'L',$fill);

      $pdf->Ln();

      if($breakpage == true)
{
  $skip = 0;
  $height = 0;
  $pdf->Cell(0, 10, " AE R&D - Page " . $page . "",0,0,'C');
  $pdf->AddPage();
  $page++;
  $breakpage = false;
}

      $fill=!$fill;
    }

  $pdf->Cell(array_sum($w),0,'','T');
  $pdf->Output('liste_place_non-retrait.pdf',D);

}


$site->start_page("services","AE - Recherche et Développement");

$intro = new contents("Edition liste au format pdf");

$intro->add_paragraph("Cliquez <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\">ici</a> pour obtenir le fichier pdf de cette liste : <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\"><img src=\"".$topdir."/images/pdf.png\"></a>");

$cts = new sqltable(
"listeinteg", 
utf8_encode("Liste des utilisateurs extérieurs à l'UTBM non cotisants à l'AE"), $req,$topdir."user.php", 
"id_utilisateur", 
array("nom_utilisateur"=>"Nom", "prenom" =>"Prénom"), 
array(), array());

$site->add_contents($intro);
$site->add_contents($cts);
$site->end_page();


?>
