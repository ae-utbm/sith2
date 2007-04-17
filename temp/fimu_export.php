<?
/*
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Expires: 0");
header("Pragma: no-cache");
header("Content-disposition: attachment; filename=fimu_inscr.xls");
*/
	/**
	 * Exportagement de la liste des inscrits au Fimu
	 */

$topdir = "../";
require_once($topdir . "include/site.inc.php");

$site = new site();

$sql = new requete($site->db, "SELECT fimu_inscr.id_utilisateur, 
						utilisateurs.prenom_utl, 
						utilisateurs.nom_utl,
						utilisateurs.date_naissance_utl,
						utilisateurs.addresse_utl,
						utilisateurs.ville_utl,
						utilisateurs.cpostal_utl,
						utilisateurs.tel_portable_utl,
						utilisateurs.email_utl,
						fimu_inscr.*,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
					FROM fimu_inscr 
					LEFT JOIN utilisateurs 
					ON fimu_inscr.id_utilisateur = utilisateurs.id_utilisateur");

 //fimu_inscr.*, nom_utl, prenom_utl, date_naissance_utl, addresse_utl, ville_utl, cpostal_utl, tel_portable_utl, email_utl

print("Prénom; Nom; Bordel; a ; couette; \n");

while($gnaa = $sql->get_row())
	{
	$i = 0;	
	while($i++ < 36)
		print_r ($gnaa[$i] . "; ");
	echo "\n";
	}

exit;


?>

