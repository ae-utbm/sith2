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

$GLOBALS["entitiescatalog"]["typeproduit"]   = array ( "id_typeprod", "nom_typeprod", "typeprod.png", "boutique-utbm/admin.php", "boutiqueut_type_produit");
$GLOBALS["entitiescatalog"]["produit"]       = array ( "id_produit", "nom_prod", "produit.png", "boutique-utbm/admin.php", "boutiqueut_produits" );

$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$produit = new produit($site->db,$site->dbrw);

$site->start_page('adminbooutique',"Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Stock");
$frm = new form('stock','stock.php',true);
$frm->add_datetime_field("date","Date et heure souhaitée");
$frm->add_submit("valid","Voir");
$cts->add($frm);

$req = new requete($site->db,
  'SELECT id_produit, nom_prod, stock_global_prod '.
  'FROM boutiqueut_produits '.
  'WHERE stock_global_prod!=-1'
);
$lst=array();
if(isset($_REQUEST['date']) && !empty($_REQUEST['date']))
  $cts->add_title(2,'Stock au '.date("d/m/Y H:i",$_REQUEST["date"]));
else
  $cts->add_title(2,'Stock au '.date("d/m/Y H:i"));
while(list($id,$nom,$stock)=$req->get_row())
{
  if(isset($_REQUEST['date']) && !empty($_REQUEST['date']))
  {
    $lim = date("Y-m-d H:i",$_REQUEST["date"]);
    $req2 = new requete($site->db,
                        'SELECT SUM(quantite) as qu '.
                        'FROM boutiqueut_vendu '.
                        'INNER JOIN boutiqueut_debitfacture USING(id_facture) '.
                        'WHERE id_produit='.$id.' '.
                        'AND date_facture\''.$lim.'\' '.
                        'AND ((ready=1 AND etat_facture=1) OR (ready=0 AND etat_facture=1)) ');
    if($req2->lines==1)
    {
      list($add)=$req2->get_row();
      if(!is_null($add))
        $stock=$stock+$add;
    }
    $req2 = new requete($site->db,
                       'SELECT SUM(quantite) as qu '.
                       'FROM boutiqueut_reapro '.
                       'WHERE id_produit='.$id.' '.
                       'AND date_reapro>\''.$lim.'\'');
    if($req2->lines==1)
    {
      list($add)=$req2->get_row();
      if(!is_null($add))
        $stock=$stock-$add;
    }
    $lst[]=array('id_produit'=>$id,'nom'=>$nom,'stock'=>$stock);
  }
  else
  {
    $req2 = new requete($site->db,
                        'SELECT SUM(quantite) as qu '.
                        'FROM boutiqueut_vendu '.
                        'INNER JOIN boutiqueut_debitfacture USING(id_facture) '.
                        'WHERE id_produit='.$id.' '.
                        'AND ((ready=1 AND etat_facture=1) OR (ready=0 AND etat_facture=1)) ');
    if($req2->lines==1)
    {
      list($add)=$req2->get_row();
      if(!is_null($add))
        $stock=$stock+$add;
    }
    $lst[]=array('id_produit'=>$id,'nom'=>$nom,'stock'=>$stock);
  }
}
$cts->add(new sqltable('stock','Stock',$lst,'admin.php', 'id_produit',array('nom'=>'Produit','stock'=>'Stock'),array(),array(),true,false));

if(!isset($_REQUEST['date']))
{
  $fdate = date("d/m/Y");
  $req = new requete($site->db,
                     'SELECT '.
                     'IF(mode_paiement=\'UT\', IF(u.type_utl=\'srv\',\'Facture interne\',\'À régler\'), IF(mode_paiement=\'CH\',\'Chèque\',\'Espèce\')) AS mode '.
                     ', SUM(montant_facture)/100 AS total '.
                     'FROM boutiqueut_debitfacture '.
                     "LEFT JOIN utilisateurs u USING(id_utilisateur) ".
                     'WHERE ready=1 '.
                     'AND date_facture > \''.$date.' 00:00:00\' '.
                     'AND date_facture < \''.$date.' 23:59:59\' '.
                     'GROUP BY mode_paiement');
  if($req->lines>0)
  {
    $lst=array();
    while(list($type,$total)=$req->get_row())
      $lst[]=array('type'=>$type,'total'=>sprintf('%.2f €',$total));
    $cts->add(new sqltable('stock','Ventes du '.date("d/m/Y"),$lst,'stock.php', 'type',array('type'=>'Mode de paiement','total'=>'Total'),array(),array(),true,false),true);
  }
}
else
{
  $date = date("Y-m-d",$_REQUEST["date"]);
  $fdate = date("d/m/Y",$_REQUEST["date"]);
  $req = new requete($site->db,
                     'SELECT '.
                     'IF(mode_paiement=\'UT\', IF(u.type_utl=\'srv\',\'Facture interne\',\'À régler\'), IF(mode_paiement=\'CH\',\'Chèque\',\'Espèce\')) AS mode '.
                     ', SUM(montant_facture)/100 AS total '.
                     'FROM boutiqueut_debitfacture '.
                     "LEFT JOIN utilisateurs u USING(id_utilisateur) ".
                     'WHERE ready=1 '.
                     'AND date_facture > \''.$date.' 00:00:00\' '.
                     'AND date_facture < \''.$date.' 23:59:59\' '.
                     'GROUP BY mode_paiement');
  if($req->lines>0)
  {
    $lst=array();
    while(list($type,$total)=$req->get_row())
      $lst[]=array('type'=>$type,'total'=>sprintf('%.2f €',$total));
    $cts->add(new sqltable('stock','Ventes du '.$fdate,$lst,'stock.php', 'type',array('type'=>'Mode de paiement','total'=>'Total'),array(),array(),true,false),true);
  }
}

$site->add_contents($cts);
$site->end_page();

?>
