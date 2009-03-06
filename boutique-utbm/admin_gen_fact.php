<?php
/* Copyright 2006
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
require_once("include/boutique.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$site->start_page("boutique", "Suivi" );

if(isset($_REQUEST["id_facture"]))
{
  $fact = new debitfacture($site->db,$site->dbrw);
  if($fact->load_by_id($_REQUEST["id_facture"]))
  {
    $user=new utilisateur($site->db);
    $user->load_by_id($fact->id_utilisateur);
    if(isset($_REQUEST["gen_pdf"]))
    {
      require_once ("include/facture_pdf.inc.php");
      $facturing_infos = array ('name' => "Service Communication",
       'addr' => array(utf8_decode("UTBM"),
           "90010 BELFORT Cedex"),
       'logo' => "http://ae.utbm.fr/images/logo_boutique_ut.jpg");
      $ht=false;
      if($user->is_valid())
      {
        $factured_infos = array (
             'name' => utf8_decode($user->nom)
             . " " .
             utf8_decode($user->prenom),
             'addr' => array(
                 utf8_decode($user->addresse),
                 utf8_decode($ville->cpostal)
                 . " " .
                 utf8_decode($ville->nom)),
             false);
        if($user->type=='srv')
        {
          $factured_infos['srv_obj']              = $fact->objectif;
          $factured_infos['srv_eotp']             = $fact->eopt;
          $factured_infos['srv_contact']          = $fact->contact;
          $factured_infos['srv_centre_financier'] = $fact->centre_financier;
          $factured_infos['srv_centre_cout']      = $fact->centre_cout;
          $ht=true;
        }
        else
        {
          if($fact->mode=='CH')
            $factured_infos['mode_paiement'] = 'Chèque';
          elseif($fact->mode=='LI')
            $factured_infos['mode_paiement'] = 'Liquide';
          else
            $factured_infos['mode_paiement'] = 'À régler';
        }
      }
      else
      {
        $adresse=explode("\n",$fact->adresse);
        $factured_infos = array (
             'name' => utf8_decode($fact->nom)
             . " " .
             utf8_decode($fact->prenom),
             'addr' => $adresse,
             false);
        if($fact->mode=='CH')
          $factured_infos['mode_paiement'] = 'Chèque';
        elseif($fact->mode=='LI')
          $factured_infos['mode_paiement'] = 'Liquide';
        else
          $factured_infos['mode_paiement'] = 'À régler';
      }
      $date_facturation = date("d/m/Y H:i", $fact->date);
      $titre = "Facture boutique UTBM";
      $ref=' '.sprintf('%06d',$fact->id);
      $req = "SELECT * FROM `boutiqueut_vendu`
              INNER JOIN `boutiqueut_produits` USING (`id_produit`)
              WHERE `id_facture` = $fact->id";

      $query = new requete ($site->db, $req);
      $total = 0;
      while ($line = $query->get_row ())
      {
        $lines[] = array('nom' => utf8_decode($line['nom_prod']),
             'quantite' => intval($line['quantite']),
             'prix' => $line['prix_unit'],
             'sous_total' => intval($line['quantite']) * $line['prix_unit']);

        $total += intval($line['quantite']) * $line['prix_unit'];
      }

      $fact_pdf = new facture_pdf ($facturing_infos,
                 $factured_infos,
                 $date_facturation,
                 $titre,
                 $ref,
                 $lines,
                 $ht);

      /* on sort la facture */
      $fact_pdf->renderize ();
      exit();
    }
    else
    {
      $ccts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Détail facture");
      if($_REQUEST["action"]=='prep')
        $fact->set_retire($_REQUEST["id_produit"], $user);
      elseif($_REQUEST["action"]=='preps')
        foreach($_REQUEST["id_produits"] as $id_produit )
          $fact->set_retire($id_produit, $user);
      elseif($_REQUEST["action"]=='retirer')
        $fact->set_etat(0);
      if($fact->ready==1 && $fact->etat==1) // commande à retirer
      {
        $cts = new contents( "Commande à retirer" );
        $cts->add_paragraph("<a href='?id_facture=".$fact->id."&action=retirer'>Marquer comme retirée</a>");
      }
      elseif($fact->ready==0) // commande en cours de préparation
        $cts = new contents( "Commande en attente/cours de préparation" );
      else // commande retirée
        $cts = new contents("Commande retirée");

      $cts->add_paragraph("Facture n° ".$fact->id." du ".date("d/m/Y H:i", $fact->date));
      $cts->add_paragraph("facture au format PDF : <a href=\"?id_facture=".$fact->id."&gen_pdf=1\">ici</a>");
      if($user->type=='srv')
      {
        $cts->add_paragraph('Objectif : '.$fact->objectif);
        $cts->add_paragraph('EOTP : '.(string)$fact->eotp);
        $cts->add_paragraph('Contact : '.$fact->contact);
        $cts->add_paragraph('Centre financier : '.$fact->centre_financier);
        $cts->add_paragraph('Centre de coût : '.$fact->centre_cout);
      }

      $req = new requete($site->db,
           "SELECT id_produit, ".
           "`quantite`, " .
           "`prix_unit`/100 AS `prix_unit`, ".
           "`prix_unit`*`boutiqueut_vendu`.`quantite`/100 AS `total`, ".
           "`nom_prod` " .
           "FROM `boutiqueut_vendu` ".
           "INNER JOIN boutiqueut_produits USING(id_produit) ".
           "WHERE `id_facture` =".$fact->id);
      if($fact->ready==1)
      {
        $cts->add(new sqltable('detailcmd',
                               'Détail de la commande',
                               $req,
                               '',
                               "id_produit",
                               array("nom_prod"=>"Produit","quantite"=>"Quantité","total"=>"Total"),
                               array(),
                               array()));
      }
      else
      {
        $req = new requete($site->db,
           "SELECT id_produit, ".
           "`quantite`, " .
           "`prix_unit`/100 AS `prix_unit`, ".
           "`prix_unit`*`boutiqueut_vendu`.`quantite`/100 AS `total`, ".
           "`nom_prod` " .
           "FROM `boutiqueut_vendu` ".
           "INNER JOIN boutiqueut_produits USING(id_produit) ".
           "WHERE a_retirer_vente IS NOT NULL AND `id_facture` =".$fact->id);
        $cts->add(new sqltable('detailcmd',
                               'Produits en attente de préparation',
                               $req,
                               'admin_gen_fact.php?id_facture='.$_REQUEST["id_facture"],
                               "id_produit",
                               array("nom_prod"=>"Produit","quantite"=>"Quantité","total"=>"Total"),
                               array('prep'=>'Marquer pret'),
                               array('preps'=>'Marquer pret')));
      }
      $ccts->add($cts,true);
      $site->add_contents($ccts);
      $site->end_page();
      exit();
    }
  }
}

$cts = new contents( "Suivi" );

$cts->add_title(2,"Factures");



$months = array();

$req = new requete($site->db, "SELECT SUM(`montant_facture`), " .
    "EXTRACT(YEAR_MONTH FROM `date_facture`) as `month` " .
    "FROM `boutiqueut_debitfacture` " .
    "WHERE `id_utilisateur`='".$user->id."' " .
    "GROUP BY `month` " .
    "ORDER BY `month` DESC");

while ( list($sum,$month) = $req->get_row() )
{
  $report[$month]["depense"] = $sum;
  $months[$month]=$month;
}

if(!empty($report))
{
  rsort($months);

  $cts->add_title(3,"Bilan mensuel");

  $tbl = new table(false,"sqltable");
  $tbl->add_row(array("Mois","Depenses"),"head");
  $t=0;
  foreach( $months as $month )
  {
    $data = $report[$month];
    $t = $t^1;
    $mois = substr($month,4);
    $annee = substr($month,0,4);
    $tbl->add_row(array("$mois / $annee",
      "<a href=\"?page=ALL&amp;month=$month\">".($data["depense"]/100)."</a>"),"ln$t");
  }

  $cts->add($tbl);
}

$req1 = new requete($site->db,
        "SELECT " .
        "`boutiqueut_debitfacture`.`id_facture`, " .
        "`boutiqueut_debitfacture`.`date_facture`, " .
        "`boutiqueut_vendu`.`prix_unit`*`boutiqueut_vendu`.`quantite`/100 AS `total` " .
        "FROM `boutiqueut_vendu` " .
        "INNER JOIN `boutiqueut_produits` ON ".
        "`boutiqueut_produits`.`id_produit` =`boutiqueut_vendu`.`id_produit` " .
        "INNER JOIN `boutiqueut_debitfacture` ON ".
        "`boutiqueut_debitfacture`.`id_facture` =`boutiqueut_vendu`.`id_facture` " .
        "WHERE " .
        "`boutiqueut_debitfacture`.`id_utilisateur` = '".mysql_real_escape_string($user->id) ."' ".
        "GROUP BY `boutiqueut_debitfacture`.`id_facture` ".
        "ORDER BY `boutiqueut_debitfacture`.`date_facture` DESC");

if ( $req2->lines > 0 )
{
  $cts->add_title(3, "Commandes sur facturation");

  $cts->add(new sqltable("eblstae",
          null,
          $req2,
          "suivi.php",
          "id_facture",
          array("id_facture"=>"Numéro de facture",
                "total"=>"Montant",
                "date_facture"=>"Date"),
          array(),
          array(),
          array()));
}
$ccts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Détail facture");
$ccts->add($cts,true);
$site->add_contents($ccts);

$site->end_page();
?>
