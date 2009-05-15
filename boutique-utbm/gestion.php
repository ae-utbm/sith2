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
require_once($topdir . "include/entities/folder.inc.php");
require_once($topdir . "include/entities/files.inc.php");

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
if(!$site->user->is_in_group("root") && !$site->user->is_in_group("adminboutiqueutbm"))
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
      $cts->add_paragraph("Quantité : $qte unités<br/>" .
          "Chiffre d'affaire: ".sprintf('%.2f',($sum/100))." Euros<br/>" .
          "Prix coutant total estimé* : ".sprintf('%.2f',($sumcoutant/100))." Euros");
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
        $tbl->add_row(array($unit,$qte,sprintf('%.2f',$total/100),sprintf('%.2f',$coutant/100)));

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
elseif( $_REQUEST["page"] == "stocks" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Stocks");
  $req = new requete($site->db,
    'SELECT id_produit '.
    ', nom_prod '.
    ', stock_global_prod '.
    ', IF(prod_archive=0,\'En vente\',\'Archivé\') as arch '.
    'FROM boutiqueut_produits '.
    'WHERE stock_global_prod!=-1 '.
    'ORDER BY id_produit_parent,id_produit');

  $cts->add(new sqltable(
         "stock",
         "Stocks",
         $req,
         "admin.php",
         "id_produit",
         array('nom_prod'=>'Produit',"stock_global_prod" => "Stock","arch"=>"Etat"),
         array(),
         array(),
         array()),
         true);
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
         "IF(u.id_utilisateur IS NOT NULL, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS nom_utilisateur ".
         ", f.date_facture ".
         ", IF(f.mode_paiement='UT', IF(u.type_utl='srv','Facture interne','À régler'), IF(f.mode_paiement='CH','Chèque','Espèce')) AS mode ".
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
         array('date_facture'=>'Date',"nom_utilisateur" => "Client","mode"=>"Mode de paiement","etat"=>"État"),
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
         "IF(u.id_utilisateur IS NOT NULL, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS nom_utilisateur ".
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
         array('date_facture'=>'Date',"nom_utilisateur" => "Client","etat"=>"État"),
         array('info'=>'Détail'),
         array(),
         array()),
         true);


//liste des ventes en attente


  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "bilan" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Bilan mensuel");
  $frm = new form ("boutiqueutaboutiqueut","gestion.php?page=bilan",true,"POST","Bilan");
  $frm->add_hidden("action","bilan");
  $frm->add_date_field("debut","Date de début",-1,true);
  $frm->add_date_field("fin","Date de fin",-1,true);
  $frm->add_submit("valid","Générer");
  $cts->add($frm,true);

  if($_REQUEST["action"] == "bilan" )
  {
    $conds = array();
    if ( $_REQUEST["debut"] )
      $conds[] = "f.date_facture >= '".date("Y-m-d",$_REQUEST["debut"])."'";
    if ( $_REQUEST["fin"] )
      $conds[] = "f.date_facture <= '".date("Y-m-d",$_REQUEST["fin"])."'";
    if ( count($conds) )
    {
      $req = new requete($site->db,
          "SELECT  f.id_facture
                 , f.date_facture
                 , IF(f.id_utilisateur!=-1, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS client
                 , p.nom_prod
                 , v.quantite AS q
                 , v.prix_unit/100 AS pu
                 , (v.quantite*v.prix_unit)/100 AS total
                 , IF(f.mode_paiement='UT', IF(u.type_utl='srv','Facture interne','À régler'), IF(f.mode_paiement='CH','Chèque','Espèce')) AS mode
          FROM `boutiqueut_vendu` v
          INNER JOIN `boutiqueut_debitfacture` f USING(`id_facture`)
          LEFT JOIN utilisateurs u USING(id_utilisateur)
          INNER JOIN `boutiqueut_produits` p USING(`id_produit`)
          WHERE ".implode(" AND ",$conds)."
          ORDER BY `id_facture` ASC");

      if ($_REQUEST['pdf'])
      {
        require_once ($topdir . "include/pdf/facture_pdf.inc.php");
        define('FPDF_FONTPATH', $topdir.'./font/');
        $pdf=new FPDF('l');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true);
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell('', 20, 'Bilan du '.date("d/m/Y",$_REQUEST["debut"]).' au '.date("d/m/Y",$_REQUEST["fin"]),'','','C');
        $pdf->Ln();

        $pdf->SetFont('Arial','B',11);
        //Header
        $w=array(15,40,70,80,20,25,25);
        $w2=array(205,45,25);
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(128,128,128);
        $pdf->SetLineWidth(.3);
        $pdf->SetFont('','B');
        $pdf->Cell($w[0],7,utf8_decode("N° Fact"), 1, 0, 'C', 1);
        $pdf->Cell($w[1],7,"Date", 1, 0, 'C', 1);
        $pdf->Cell($w[2],7,"Client", 1, 0, 'C', 1);
        $pdf->Cell($w[3],7,"Article", 1, 0, 'C', 1);
        $pdf->Cell($w[4],7,utf8_decode("Quantité"), 1, 0, 'C', 1);
        $pdf->Cell($w[5],7,utf8_decode("P.U. (€)"), 1, 0, 'C', 1);
        $pdf->Cell($w[6],7,utf8_decode("Total (€)"), 1, 0, 'C', 1);
        $pdf->Ln();
        $pdf->SetFillColor(200,200,200);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');
        $_last=-1;
        $_mode=null;
        $_total=0;
        $_gtotal=0;
        $_smode=array();
        $_tva=0;
        while (list($id_facture,$date,$client,$Article,$Quantite,$pu,$total,$mode)=$req->get_row())
        {
          if($id_facture==$_last)
          {
            $pdf->Cell($w[0],6,'','L',0,'L');
            $pdf->Cell($w[1],6,'',0,0,'L');
            $pdf->Cell($w[2],6,'',0,0,'L');
            $pdf->Cell($w[3],6,utf8_decode($Article),'LRBT',0,'L');
            $pdf->Cell($w[4],6,$Quantite,'LRBT',0,'L');
            $pdf->Cell($w[5],6,sprintf('%.2f',$pu),'LRBT',0,'L');
            $pdf->Cell($w[6],6,sprintf('%.2f',$total),'LRBT',0,'L');
            $pdf->Ln();
            $_total=$_total+$total;
          }
          else
          {
            //on ajoute le bilan de la facture
            if(!is_null($_mode))
            {
              $pdf->Cell($w2[0],6,'','L',0,'L');
              $pdf->Cell($w2[1],6,'Paiment :','LRBT',0,'L');
              $pdf->Cell($w2[2],6,utf8_decode($_mode),'LRBT',0,'L');
              $pdf->Ln();
              if($_mode!='Facture interne')
              {
                $pdf->Cell($w2[0],6,'','L',0,'L');
                $pdf->Cell($w2[1],6,'Total :','LRT',0,'L');
                $pdf->Cell($w2[2],6,sprintf('%.2f',$_total),'LRT',0,'L');
                $pdf->Ln();
                $pdf->Cell($w2[0],6,'','LB',0,'L');
                $pdf->Cell($w2[1],6,'Dont TVA :','LRBT',0,'L');
                $pdf->Cell($w2[2],6,sprintf("%.2f",19.6*$_total/(119.6)),'LRBT',0,'L');
                $_tva+=(19.6*$_total/(119.6));
                $pdf->Ln();
              }
              else
              {
                $pdf->Cell($w2[0],6,'','LB',0,'L');
                $pdf->Cell($w2[1],6,'Total :','LRBT',0,'L');
                $pdf->Cell($w2[2],6,sprintf('%.2f',$_total),'LRBT',0,'L');
                $pdf->Ln();
              }
              $_gtotal = $_gtotal+$_total;
              if(!isset($_smode[$_mode]))
                $_smode[$_mode]=$_total;
              else
                $_smode[$_mode]=$_smode[$_mode]+$_total;
            }
            $pdf->Cell($w[0],6,$id_facture,'LRBT',0,'L');
            $pdf->Cell($w[1],6,$date,'LRBT',0,'L');
            $pdf->Cell($w[2],6,utf8_decode($client),'LRBT',0,'L');
            $pdf->Cell($w[3],6,utf8_decode($Article),'LRBT',0,'L');
            $pdf->Cell($w[4],6,$Quantite,'LRBT',0,'L');
            $pdf->Cell($w[5],6,sprintf('%.2f',$pu),'LRBT',0,'L');
            $pdf->Cell($w[6],6,sprintf('%.2f',$total),'LRBT',0,'L');
            $pdf->Ln();
            $_last  = $id_facture;
            $_mode  = $mode;
            $_total = $total;
          }
        }
        if(!is_null($_mode))
        {
          $pdf->Cell($w2[0],6,'','L',0,'L');
          $pdf->Cell($w2[1],6,'Paiment :','LRBT',0,'L');
          $pdf->Cell($w2[2],6,utf8_decode($_mode),'LRBT',0,'L');
          $pdf->Ln();
          if($_mode!='Facture interne')
          {
            $pdf->Cell($w2[0],6,'','L',0,'L');
            $pdf->Cell($w2[1],6,'Total :','LRT',0,'L');
            $pdf->Cell($w2[2],6,sprintf('%.2f',$_total),'LRT',0,'L');
            $pdf->Ln();
            $pdf->Cell($w2[0],6,'','LB',0,'L');
            $pdf->Cell($w2[1],6,'Dont TVA :','LRBT',0,'L');
            $pdf->Cell($w2[2],6,sprintf("%.2f",19.6*$_total/(119.6)),'LRBT',0,'L');
            $pdf->Ln();
            $_tva+=(19.6*$_total/(119.6));
          }
          else
          {
            $pdf->Cell($w2[0],6,'','LB',0,'L');
            $pdf->Cell($w2[1],6,'Total :','LRBT',0,'L');
            $pdf->Cell($w2[2],6,sprintf('%.2f',$_total),'LRBT',0,'L');
            $pdf->Ln();
          }
          $_gtotal = $_gtotal+$_total;
          if(!isset($_smode[$_mode]))
            $_smode[$_mode]=$_total;
          else
            $_smode[$_mode]=$_smode[$_mode]+$_total;

          $pdf->Ln(20);
          $pdf->SetFont('Arial','B',14);
          $pdf->Cell(275,10,utf8_decode('Résumé'),'LRBT',0,'C');
          $pdf->Ln();
          $pdf->SetFont('');
          $pdf->Cell($w2[0],6,'',0,0,'L');
          $pdf->Cell($w2[1],6,'Total :','LRBT',0,'L');
          $pdf->Cell($w2[2],6,sprintf('%.2f',$_gtotal),'LRBT',0,'L');
          if($_tva!=0)
          {
            $pdf->Ln();
            $pdf->Cell($w2[0],6,'',0,0,'L');
            $pdf->Cell($w2[1],6,utf8_decode('TVA').' :','LRBT',0,'L');
            $pdf->Cell($w2[2],6,sprintf('%.2f',$_tva),'LRBT',0,'L');
          }
          foreach($_smode as $mode => $total)
          {
            $pdf->Ln();
            $pdf->Cell($w2[0],6,'',0,0,'L');
            $pdf->Cell($w2[1],6,utf8_decode($mode).' :','LRBT',0,'L');
            $pdf->Cell($w2[2],6,sprintf('%.2f',$total),'LRBT',0,'L');
          }
        }
        $pdf->Output('bilan_'.date("d-m-Y").'.pdf',D);
      }

      $frm = new form ("pdf","gestion.php?page=bilan",true,"POST","PDF");
      $frm->add_hidden("action","bilan");
      $frm->add_hidden("pdf","true");
      $frm->add_hidden('debut',$_REQUEST["debut"]);
      $frm->add_hidden('fin',$_REQUEST["fin"]);
      $frm->add_submit("valid","Générer le PDF");
      $cts->add($frm,true);

      $tbl = new table('Bilan du '.date("d/m/Y",$_REQUEST["debut"]).' au '.date("d/m/Y",$_REQUEST["fin"]),'bilancomptable');
      $tbl->add_row(array('N° fact','Date','Client','Article','Quantité','P.U.','Total'),'headbilan');
      $_last=-1;
      $_mode=null;
      $_total=0;
      $_gtotal=0;
      $_smode=array();
      while(list($id_facture,$date,$client,$Article,$Quantite,$pu,$total,$mode)=$req->get_row())
      {
        if($id_facture==$_last)
        {
          $tbl->add_row(array('','','',$Article,$Quantite,sprintf('%.2f',$pu),sprintf('%.2f',$total).' €'));
          $_total=$_total+$total;
        }
        else
        {
          //on ajoute le bilan de la facture
          if(!is_null($_mode))
          {
            $tbl->add_row(array('','','','','','Paiement :',$_mode),'modefactbilan');
            $tbl->add_row(array('&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','Total :',sprintf('%.2f',$_total).' €'),'totalfactbilan');
            $_gtotal = $_gtotal+$_total;
            if(!isset($_smode[$_mode]))
              $_smode[$_mode]=$_total;
            else
              $_smode[$_mode]=$_smode[$_mode]+$_total;
          }
          $tbl->add_row(array('<b>'.$id_facture.'</b>',$date,$client,$Article,$Quantite,sprintf('%.2f',$pu),sprintf('%.2f',$total)));
          $_last  = $id_facture;
          $_mode  = $mode;
          $_total = $total;
        }
      }
      if(!is_null($_mode))
      {
        $tbl->add_row(array('','','','','','Paiement :',$_mode),'modefactbilan');
        $tbl->add_row(array('&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','Total :',$_total.' €'),'totalfactbilan');
        $_gtotal = $_gtotal+$_total;
        if(!isset($_smode[$_mode]))
          $_smode[$_mode]=$_total;
        else
          $_smode[$_mode]=$_smode[$_mode]+$_total;
        $tbl->add_row(array('','','','','','Total :',sprintf('%.2f',$_gtotal).' €'),'totalbilan');
        foreach($_smode as $mode => $total)
          $tbl->add_row(array('','','','','',$mode.' :',sprintf('%.2f',$total).' €'),'totalmodebilan');
      }
      $cts->add($tbl,true);
    }
  }


  $site->add_contents($cts);
  $site->end_page();
  exit();
}


$site->add_css("css/d.css");
$site->start_page('adminbooutique',"Administration");
$cts = new contents("Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Gestion");
$lst = new itemlist("");
$lst->add("<a href=\"gestion.php?page=bilan\">Bilan mensuel</a>");
$lst->add("<a href=\"stock.php\">Stock (bilan)</a>");
$lst->add("<a href=\"gestion.php?page=stocks\">Stocks (simples)</a>");
$lst->add("<a href=\"gestion.php?page=statistiques\">Statistiques</a>");
$lst->add("<a href=\"gestion.php?page=factures\">Factures</a>");
$lst->add("<a href=\"gestion.php?page=ventes\">Ventes</a>");
$cts->add($lst,true);
$site->add_contents($cts);
$site->end_page();

?>
