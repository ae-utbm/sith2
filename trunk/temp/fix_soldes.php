<?php
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$dbrw = new mysqlae("rw");

$sql = new requete($dbrw,"SELECT id_utilisateur,SUM( `montant_rech` ) FROM `cpt_rechargements` GROUP BY id_utilisateur");
while ( list($id,$sum) = $sql->get_row() )
	$comptes[$id] = $sum;

$sql = new requete($dbrw,"SELECT id_utilisateur_client,SUM( `montant_facture` ) FROM `cpt_debitfacture` WHERE mode_paiement='AE' GROUP BY id_utilisateur_client");
while ( list($id,$sum) = $sql->get_row() )
	$comptes[$id] -= $sum;

$allsoldes = 0;

foreach ( $comptes as $id => $solde )
{
  $allsoldes+=$solde;

	$up = new requete($dbrw,"UPDATE `utilisateurs`
						SET `montant_compte` = '$solde'
						WHERE `id_utilisateur` = '$id'");
}
echo "<pre>";

echo "<b>".$allsoldes."</b>\n";

print_r($comptes);

echo "</pre>";

?>
