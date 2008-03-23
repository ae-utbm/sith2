<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once ($topdir. "include/entities/asso.inc.php");
require_once ($topdir . "include/pdf/facture_pdf.inc.php");

$site = new site ();

if (!$site->user->ae)
	$site->error_forbidden("none","reserved");

if (!$site->user->utbm)
	$site->error_forbidden("none","reservedutbm");

if (!$site->user->is_valid())
	$site->error_forbidden();

if (!$site->user->is_asso_role(1,2))
	$site->error_forbidden("none",utf8_encode("Accès reservé aux membre du bureau AE"));

$req = new requete($site->db, "SELECT `utilisateurs`.`id_utilisateur`,`utilisateurs`.`nom_utl` AS `nom_utilisateur`,`utilisateurs`.`prenom_utl` AS `prenom` FROM `utilisateurs` INNER JOIN `utl_etu` ON `utilisateurs`.`id_utilisateur`=`utl_etu`.`id_utilisateur` WHERE `utilisateurs`.`ae_utl` = '1' AND (`utl_etu`.`nom_ecole_etudiant`!='UTBM' OR `utilisateurs`.`utbm_utl`='0')");

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
  $pdf->Cell(0, 18, "Automne 2006",'','','R');
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
	  $pdf->Cell(0, 10, " AE R&D - Page " . $page . " - Realised by Lorenzo",0,0,'C');
	  $pdf->AddPage();
	  $page++;
	  $breakpage = false;
	}

      $fill=!$fill;
    }

  $pdf->Cell(array_sum($w),0,'','T');
  $pdf->Output('liste_etus_non_ae_non_utbm.pdf',D);

}


$site->start_page("services","AE - Recherche et Dé¶¥loppement");

$intro = new contents("Edition liste au format pdf");

$intro->add_paragraph("Cliquez <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\">ici</a> pour obtenir le fichier pdf de cette liste : <a href=\"".$_SERVER['SCRIPT_NAME']."?action=getpdf\"><img src=\"".$topdir."/images/pdf.png\"></a>");

$cts = new sqltable(
				"listeinteg", 
				utf8_encode("Liste des utilisateurs extérieurs à l'UTBM non cotisants à l'AE"), $req,$topdir."user.php", 
				"id_utilisateur", 
				array("nom_utilisateur"=>"Nom", "prenom" =>"PrÃ©nom"), 
				array(), array());
				
$site->add_contents($intro);
$site->add_contents($cts);
$site->end_page();


?>