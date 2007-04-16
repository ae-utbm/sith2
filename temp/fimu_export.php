<?
	/**
	 * Exportagement de la liste des inscrits au Fimu
	 */

$topdir = "../";
require_once($topdir . "include/site.inc.php");

$site = new site();

$sql = new requete($site->db, "SELECT * FROM fimu_inscr");


while($gnaa = $sql->get_row())
	{
	$i = 0;	
	while($i++ < 26)
		echo($gnaa[$i] . "; ");
	echo "\n";
	}

?>

