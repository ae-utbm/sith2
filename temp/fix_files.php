<?php
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$dbrw = new mysqlae("rw");

$sql = new requete($dbrw,"SELECT id_file, nom_fichier_file FROM d_file WHERE id_file > 12 AND id_file < 90");
while ( list($id,$nom) = $sql->get_row() )
{
	$up = new requete($dbrw,"UPDATE `d_file` 
						SET `nom_fichier_file` = '".mysql_real_escape_string(utf8_encode($nom))."'
						WHERE `id_file` = '$id'");
}

echo "pour info : ".mysql_client_encoding();
?>