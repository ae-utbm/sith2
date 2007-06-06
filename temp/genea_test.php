<?
$topdir = "../";
require_once ($topdir . "include/genealogie.inc.php");
require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

/* Da3 (586) est l'exemple parfait pour commencer */
/* et parevise (122) pour l'aspect brut du graphe :-D */
/* moi (166), histoire de tripper */
$gene = new genealogie ("\"Genealogie complete AE\"",
			"5.0");

$req = new requete (new mysqlae(),
		    "SELECT   `utilisateurs`.`id_utilisateur`,
                              CONCAT(`utilisateurs`.`prenom_utl`,
                             ' ', `utilisateurs`.`nom_utl`) AS `nom`,
                              `utilisateurs`.`alias_utl` AS `surnom`
                     FROM `utilisateurs`");

for ($i = 0;$i < $req->lines; $i++)
{
  $res = $req->get_row();
  $users[$res['id_utilisateur']] = utf8_decode($res['nom'] . "\\n" .
					       $res['surnom']);
}

$req = new requete (new mysqlae(),
		    "SELECT `id_utilisateur` AS `parrain`,
                            `id_utilisateur_fillot` AS `fillot`
                     FROM `parrains`");

for ($i = 0;$i < $req->lines; $i++)
{
  $res = $req->get_row();
  $filiation[$users[$res['parrain']]] = $users[$res['fillot']];
}


$gene->generate_conf_from_array ($filiation);

/* le nom de cette fonction membre est a revoir */
//$gene->generate ();



?>