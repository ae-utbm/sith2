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
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/graph.inc.php");
$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$conds = array();

if ( $_REQUEST["debut"] )
  $conds[] = "boutiqueut_debitfacture.date_facture >= '".date("Y-m-d H:i:s",$_REQUEST["debut"])."'";

if ( $_REQUEST["fin"] )
  $conds[] = "boutiqueut_debitfacture.date_facture <= '".date("Y-m-d H:i:s",$_REQUEST["fin"])."'";

if ( $_REQUEST["id_typeprod"] )
  $conds[] = "boutiqueut_produits.id_typeprod='".intval($_REQUEST["id_typeprod"])."'";

if ( $_REQUEST["id_produit"] )
  $conds[] = "boutiqueut_vendu.id_produit='".intval($_REQUEST["id_produit"])."'";

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

$coords=array();
$tics=array();
$i=0;
$strip = round($req->lines/7);

while ( list($unit,$qte,$total,$coutant) = $req->get_row() )
{
  if ( $_REQUEST["mode"] == "day" )
    $unit = date("d/m/y",strtotime($unit));

  if ( $i%$strip && ($i != $req->lines-1) && $i != 0 )
    $tics[$i]="";
  else
    $tics[$i]=$unit;

  $coords[] = array('x'=>$i,'y'=>array($total/100,$qte,$coutant/100));
  $i++;
}

$grfx = new graphic ("Resultats",
        array("c.a.","qte","countant"),
        $coords,false,$tics);

$grfx->png_render();
$grfx->destroy_graph();

?>
