<?
$topdir = "../";
require_once($topdir . "include/site.inc.php");
$site = new site();

$sql = new requete($site->db, "SELECT fimu_inscr.id_utilisateur, 
					utilisateurs.prenom_utl,
					utilisateurs.nom_utl
				FROM fimu_inscr
				NATURAL JOIN utilisateurs");
while($gnaa = $sql->get_row())
	echo $gnaa[0] . " " . $gnaa[1] . " " . $gnaa[2] ."\n";

exit();
?>
