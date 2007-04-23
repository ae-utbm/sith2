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
					DATEDIFF(NOW(), utilisateurs.date_naissance_utl) AS age,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
					FROM fimu_inscr 
					LEFT JOIN utilisateurs 
					ON fimu_inscr.id_utilisateur = utilisateurs.id_utilisateur");

 //fimu_inscr.*, nom_utl, prenom_utl, date_naissance_utl, addresse_utl, ville_utl, cpostal_utl, tel_portable_utl, email_utl

print("Prénom; Nom; Bordel; a ; couette; \n");

while($gnaa = $sql->get_row())
	{
/*	$i = 0;	
	while($i++ < 36)
		print_r ($gnaa[$i] . "\n");
	echo "\n";
*/
	$buffer = "";
	$buffer .= " ; ";
	$buffer .= $gnaa['nom_utl'];
	$buffer .= "; ";
	$buffer .= $gnaa['prenom_utl'];
	$buffer .= "; ";
	$buffer .= floor($gnaa['age']/365);
	$buffer .= "; ";
	$buffer .= $gnaa['addresse_utl'];
	$buffer .= "; ";
	$buffer .= $gnaa['cpostal_utl'];
	$buffer .= "; ";
	$buffer .= $gnaa['ville_utl'];
	$buffer .= "; ";
	$buffer .= $gnaa['tel_portable_utl'];
	$buffer .= "; ";
	$buffer .= $gnaa['email_utl'];
	$buffer .= "; UTBM; ";

	if($gnaa['choix1_choix'] == "pilote") $buffer .= "1";	
	else if($gnaa['choix2_choix'] == "pilote") $buffer .= "2";
	else $buffer .= "";
	$buffer .= "; ";


	if($gnaa['choix1_choix'] == "accueil") $buffer .= "1";	
	else if($gnaa['choix2_choix'] == "accueil") $buffer .= "2";
	else $buffer .= "";
	$buffer .= "; ";

	if($gnaa['choix1_choix'] == "regisseur") $buffer .= "1";	
	else if($gnaa['choix2_choix'] == "regisseur") $buffer .= "2";
	else $buffer .= "";
	$buffer .= "; ";
	
	if($gnaa['choix1_choix'] == "signaletic") $buffer .= "1";	
	else if($gnaa['choix2_choix'] == "signaletic") $buffer .= "2";
	else $buffer .= "";
	$buffer .= "; ";

	$buffer .= "; ";

	if($gnaa['disp_24']) $buffer .= "X; "; else $buffer .= "; ";
	if($gnaa['disp_25']) $buffer .= "X; "; else $buffer .= "; ";
	if($gnaa['disp_26']) $buffer .= "X; "; else $buffer .= "; ";
	if($gnaa['disp_27']) $buffer .= "X; "; else $buffer .= "; ";
	if($gnaa['disp_28']) $buffer .= "X; "; else $buffer .= "; ";
	if($gnaa['disp_29']) $buffer .= "X; "; else $buffer .= "; ";

	/* Et c'est parti pour une putain de liste de langue dont personne parle les 3/4 */
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "anglais")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "anglais")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "anglais")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "allemand")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "allemand")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "allemand")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "grec")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "grec")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "grec")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "espagnol")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "espagnol")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "espagnol")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "italien")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "italien")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "italien")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";

	if(!strnatcasecmp($gnaa['lang1_lang'], "roumain")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "roumain")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "roumain")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";

	if(!strnatcasecmp($gnaa['lang1_lang'], "chinois")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "chinois")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "chinois")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "russe")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "russe")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "russe")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "portugais")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "portugais")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "portugais")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "lituanien")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "lituanien")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "lituanien")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "bulgar")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "bulgar")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "bulgar")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "turc")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "turc")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "turc")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";
	
	if(!strnatcasecmp($gnaa['lang1_lang'], "arabe")) $buffer .= "1";
	else if(!strnatcasecmp($gnaa['lang2_lang'], "arabe")) $buffer .= "2";
	else if(!strnatcasecmp($gnaa['lang3_lang'], "arabe")) $buffer .= "3";
	else $buffer .= "";
	$buffer .= "; ";

	if($gnaa['permis'] == 'O') $buffer .= "X"; else $buffer .= "";
	$buffer .= "; ";
	if($gnaa['voiture'] == 'O') $buffer .= "X"; else $buffer .= "";
	$buffer .= "; ";
	if($gnaa['afps'] == 'O') $buffer .= "X"; else $buffer .= "";
	$buffer .= "; ";

	$buffer .= $gnaa['poste_preced'] . "; ";
	$buffer .= $gnaa['remarques'] . "; ";	
	
	print($buffer."\n");
	}

exit;


?>

