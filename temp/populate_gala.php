<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

new requete($site->dbrw,"TRUNCATE TABLE zzz_places_gala");

$sql = 'SELECT id_utilisateur, id_produit, quantite
FROM `cpt_vendu`
INNER JOIN cpt_debitfacture
USING ( `id_facture` )
INNER JOIN utilisateurs
USING ( id_utilisateur )
WHERE `id_assocpt` =15
AND `a_retirer_vente` =1';

$req = new requete($site->db, $sql);

$cmd=array();

while(list($id,$prod,$q)=$req->get_row())
{
  if(isset($cmd[$id]))
    $cmd[$id]+=$q;
  else
    $cmd[$id]=$q;
}

foreach($cmd as $id => $nb)
  new insert($site->dbrw,
              'zzz_places_gala',
              array('id_utilisateur'=>$id,
                    'quantite'=>$q));

?>
