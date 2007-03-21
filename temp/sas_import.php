<?php
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$dbae = new mysqlae("rw");
$dbsas = new mysql('ubpt', 'ngenceab', 'localhost', 'ubpt');

/*
 * Charge les groupes AE2
 */
$values = array();
$req = new requete($dbae, "SELECT `id_groupe`,`nom_groupe` FROM `groupe` ORDER BY `nom_groupe`");		

while ( list($id,$fname) = $req->get_row() )
	$values[$fname] = $id;
	
$values["ae-membres"] = 10000;
$values["utbm"] = 10001;
$values["ancien_etudiant"] = 10002;
$values["etudiant"] = 10003;

$req = new requete($dbae,
	"SELECT `id_asso`, `nom_unix_asso` " .
	"FROM  `asso`  " .
	"ORDER BY `nom_asso`");	
		
while ( list($id,$fname) = $req->get_row() )
	$values[$fname."-bureau"] = $id+20000;
	
$req = new requete($dbae,
	"SELECT `id_asso`, `nom_unix_asso` " .
	"FROM `asso` " .
	"WHERE `id_asso_parent` IS NOT NULL " .
	"ORDER BY `nom_asso`");	
	
while ( list($id,$fname) = $req->get_row() )
	$values[$fname."-membres"] = $id+30000;

$promo = 1;
while ( isset($values["promo".sprintf("%02d",$promo)."-bureau"]) )
{
	$values["promo".sprintf("%02d",$promo)."-membres"] = $promo+40000;
	$promo++;
}

/*
 * Table de correspondance des groupes
 */
$grpconv =  array("ae" => $values["utbm"],
"admin" => $values["gestion_ae"],
"someadmin" => $values["gestion_ae"],
"promo00" => $values["utbm"],
"promo01" => $values["promo01-membres"],
"promo02" => $values["promo02-membres"],
"promo03" => $values["promo03-membres"],
"promo04" => $values["promo04-membres"],
"promo05" => $values["promo05-membres"],
"promo06" => $values["promo06-membres"],
"promo07" => $values["promo07-membres"],
"bureau01" => $values["promo01-bureau"],
"bureau02" => $values["promo02-bureau"],
"bureau03" => $values["promo03-bureau"],
"bureau04" => $values["promo04-bureau"],
"bureau05" => $values["promo05-bureau"],
"bureau06" => $values["promo06-bureau"],
"bureau07" => $values["promo07-bureau"]);


$req = new requete($dbae,"TRUNCATE TABLE `sas_cat_photos`");
$req = new requete($dbae,"TRUNCATE TABLE `sas_photos`");
$req = new requete($dbae,"TRUNCATE TABLE `sas_personnes_photos`");
/*
 * Import des catégories
 */

$req = new requete($dbsas,"SELECT * FROM `ubpt_context`");
while ( $row = $req->get_row() )
{
	$sql = new insert($dbae,"sas_cat_photos",
		array( 
			'id_catph' => $row["id"],
			'id_catph_parent' => $row["parentid"] ? $row["parentid"]  : null,	
			'id_photo' => $row["highlightphoto"],
			'nom_catph' => $row["nom"]==""?"SAS":utf8_encode($row["nom"]),	
			'date_debut_catph' => gmdate("Y-m-d H:i:s",$row["timed"]+3600),	
			'date_fin_catph' => gmdate("Y-m-d H:i:s",$row["timef"]+3600),	
			'modere_catph' => $row["flags"] & 1,
			'id_utilisateur' => $row["proprietaireid"] == -1 ? 142 : $row["proprietaireid"],	
			'id_groupe' => $grpconv[$row["groupe"]],	
			'id_groupe_admin' => $grpconv[$row["admingroup"]],
			'droits_acces_catph' => $row["droits"] )
		);
}

/*
 * Import des photos
 */

$req = new requete($dbsas,"SELECT * FROM `ubpt_photos`");
while ( $row = $req->get_row() )
{
	$sql = new insert($dbae,"sas_photos",
		array( 
			'id_photo' => $row["id"],	
			'id_catph' => $row["contextid"],	
			'id_utilisateur_photographe' => null,	
			'date_prise_vue' => gmdate("Y-m-d H:i:s",$row["time"]+3600),	
			'modere_ph' => $row["flags"] & 1,	
			'commentaire_ph' => utf8_encode($row["description"]),	
			'incomplet' => ($row["flags"] & 0x10000)?1:0,
			'droits_acquis' => (($row["flags"] & 0x10008)==0x00008)?1:0,
			'couleur_moyenne' => $row["mediumcolor"],
			'classification' => 0,
			'id_utilisateur' => $row["proprietaireid"],	
			'id_groupe' => $row["groupe"] == "" ? $values["gestion_ae"] : $grpconv[$row["groupe"]],	
			'id_groupe_admin' => $grpconv[$row["admingroup"]],	
			'droits_acces_ph' => $row["droits"],
			'supprime_ph' => 0 )
		);
}

/*
 * Import des personnes
 */
$req = new requete($dbsas,"SELECT * FROM `ubpt_personnes`");
while ( $row = $req->get_row() ) //sas_personnes_photos
{
	if ( $row["idutl"] > 0 && $row["id"] > 0)
	$sql = new insert($dbae,"sas_personnes_photos",
		array( 
			'id_photo' => $row["id"],	
			'id_utilisateur' => $row["idutl"],	
			'accord_phutl' => $row["flags"] & 2,	
			'modere_phutl' => $row["flags"] & 4
			 )
		);
}




?>
