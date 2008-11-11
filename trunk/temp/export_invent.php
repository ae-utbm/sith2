<?php

$topdir = "../";
require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/entities/objet.inc.php");
$site=new site();
$user=new utilisateur($site->db);

echo '<?php
$topdir = "../";
require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/entities/objet.inc.php");
$site=new site();
$user=new utilisateur($site->db);';

$req=new requete($site->db,"SELECT * FROM `inv_emprunt` WHERE `date_demande_emp`>'207-09-22 00:00:00'");
while(list($id_emp,$id_utl,$is_asso,$id_op,$date_demande_emp,$date_prise_emp,$date_retour_emp,$date_debut_emp,$date_fin_emp,$caution,$prix,$ext,$note,$etat) = $req->get_row() )
{
echo '
$user->load_by_id('.$id_utl.');
if(!$user->is_valid())
  echo "pb with : '.$id_emp.'\n";
else
{
  $emp = new emprunt($site->db,$site->dbrw);
  $obj->add_emprunt('.$id_utl.',null,null,'.mktime(0,0,0,9,22,2007).','.mktime(0,0,0,9,23,2007).');';
$req2 = new requete($site->db,"SELECT `inv_objet`.`cbar_objet` FROM `inv_emprunt_objet` INNER JOIN `inv_objet` ON `inv_objet`.`id_objet`=`inv_emprunt_objet`.`id_objet` WHERE `id_emprunt`='".$id_emp."'");
while(list($cbar)=$req2->get_row())
{
echo '
  $obj2 = new objet($site->db);
  $obj2->load_by_cbar("'.$cbar.'");
  $emp->add_object($obj2->id);
  $emp->retrait();
  $emp->full_back();
}';
}
}
echo 'exit();
?>';
exit();
?>

