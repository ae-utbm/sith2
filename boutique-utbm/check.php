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
"SELECT id_produit, stock_global_prod FROM boutiqueut_produits");

while(list($id,$q)=$req->get_row())
{
  if($q!=-1 && $q != ($reapro[$id]-$vendu[$id]))
  {
    echo "$id - erreur de stock<br>";
    echo " >> $q != ".$reapro[$id]."".$vendu[$id]."<br>";
  }
}

?>
