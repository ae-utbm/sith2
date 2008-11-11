<?php

$topdir = "../";

require_once ($topdir. "include/mysql.inc.php");
require_once ($topdir. "include/mysqlae_home.inc.php");
require_once ($topdir. "include/site.inc.php");
require_once ($topdir. "include/cts/sqltable.inc.php");


$site = new site ();

$site->start_page("services","AE - Recherche et Dé¶¥loppement");

$req_prof = new requete($site->db, "SELECT `utilisateurs`.`id_utilisateur`,`utilisateurs`.`nom_utl` AS nom_utilisateur,`utilisateurs`.`prenom_utl` FROM `utl_etu_utbm` INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur` = `utl_etu_utbm`.`id_utilisateur` WHERE `utl_etu_utbm`.`branche_utbm` NOT IN ('GI','GMC','GSP','TC','GSC') AND `utilisateurs`.`ancien_etudiant_utl` != '1' ORDER BY `id_utilisateur` ASC");

/*$mmt_db = new mysql("mmt","istrotte","localhost","matmatronch");

$req_prof = new requete($mmt_db,"SELECT `id`,`nom`,`prenom`,`email` FROM `profs`");*/

if (isset($_REQUEST['action']) && $_REQUEST['action']=="process_one")
{
	$user = new utilisateur($site->db);
	$user->load_by_id($_GET['id_utilisateur']);

	$valid = new contents("<img src=\"".$topdir."images/actions/done.png\">&nbsp;&nbsp;Done !");
	$lst = new itemlist(utf8_encode("Résultats"));
	if ( $user->id > 0 )
	{
		$user->load_all_extra();
		//$insert_db = new mysql("mmt","istrotte","localhost","matmatronch");
		$insert_db_home = new mysqlae(rw);
		$email = $user->email?$user->email:($user->emailutbm?$user->emailutbm:null);
		echo $email;
		$insert_req = new requete($insert_db_home,"INSERT INTO `profs` (`nom`,`prenom`,`sexe`,`email`,`fonction`,`telephone`) VALUES('".mysql_escape_string($user->nom)."', '".mysql_escape_string($user->prenom)."', '" . mysql_escape_string($user->sexe) . "', '".mysql_escape_string($email)."', '".mysql_escape_string($user->departement)."', '".mysql_escape_string($user->telephone)."'");
		$lst->add($user->prenom." ".$user->nom." : OK <img src=\"". $topdir . "images/actions/done.png\">");
	}
	else
	$lst->add($_GET['id_utilisateur']." inconnu <img src=\"".$topdir."images/actions/delete.png\">");


	$valid->add($lst,true);
	/*while($res = $req_prof->get_row())
	{
		$req = new requete(new mysqlae(),"SELECT `id_utilisateur` AS `id` FROM `utilisateurs` WHERE `email_utl` = '" . mysql_real_escape_string($res['email']) . "' LIMIT 1");
		$res_ae = $req->get_row();
		$ids [] = $res_ae['id'];
	}*/
	$site->add_contents($valid);
}
elseif ($_REQUEST['action']=="process_any")
{
	$ids_ae = new contents("IDs correspondants sur le site de l'AE");
	$ids_ae->add_paragraph(print_r($_POST['id_utilisateurs']));
}
$cts = new sqltable(
				"listeinteg",
				"Liste des prof (".$req_prof->lines.")", $req_prof,$_SERVER['REQUEST_URI'],
				"id_utilisateur",
				array("id_utilisateur"=>"ID", "nom_utilisateur" => "Nom","prenom_utl"=>utf8_encode("Prénom")),
				array("process_one"=>"Ajouter aux profs"), array("process_any"=>"Marquer comme profs"));

$site->add_contents($cts);

$process = new form("pr",$_SERVER['REQUEST_URI'],null,"POST","Manual Processing ?");
$process->add_hidden("action","process_manu");
$process->add_text_area("codes","ID &agrave; processer :");
$process->add_submit("valid","Valider");

$site->add_contents($process);
$site->end_page();


?>
