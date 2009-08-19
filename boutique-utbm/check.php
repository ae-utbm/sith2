<?php
$topdir="../";

require_once("include/boutique.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");


$site = new boutique();
$req = new requete($site->db,
"SELECT id_produit, quantite FROM boutiqueut_vendu");
$vendu = array();
while(list($id,$q)=$req->get_row())
{
  if(isset($vendu[$id]))
    $vendu[$id]+=$q;
  else
    $vendu[$id]=$q;
}

$req = new requete($site->db,
"SELECT id_produit, quantite FROM boutiqueut_reapro");
$reapro = array();
while(list($id,$q)=$req->get_row())
{
  if(isset($reapro[$id]))
    $reapro[$id]+=$q;
  else
    $reapro[$id]=$q;
  if(!isset($vendu[$id]))
    $vendu[$id]=0;
}

$req = new requete($site->db,
"SELECT id_produit, stock_global_prod, nom_prod FROM boutiqueut_produits");

while(list($id,$q,$nom)=$req->get_row())
{
  if($id==23)
    continue;
  if($q!=-1 && $q != ($reapro[$id]-$vendu[$id]))
  {
    $fix=$reapro[$id]-$vendu[$id];
    echo "$nom - ($id) <br>";
    echo " >> $q != ".$reapro[$id]."-".$vendu[$id]."($fix)<br>";
    echo " >> stock fixé à : $fix<br>";
    new update($site->dbrw,
               'boutiqueut_produits',
               array('stock_global_prod'=>$fix),
               array('id_produit'=>$id));
  }
}

?>
