<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
/*require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");*/


$site = new site();

$site->start_page("services","AE - Recherche et Dé¶¥loppement");

if (isset($_POST['action']) && $_POST['action'] == "add")
{
	$req = new insert($site->dbrw,"uv",array("nom"=>strtoupper(trim($_POST['nom']))));
	if (!$req)
		$cts = new contents("Erreur lors de l'insertion de l'UV ". $_POST['nom']);
	else
		$cts = new contents("<img src=\"".$topdir."images/actions/done.png\">&nbsp;&nbsp;UV ".$_POST['nom'].utf8_encode(" ajoutée avec succès"));
	$site->add_contents($cts);
}	

$frm = new form("adduv","uvs.php",true,"POST",utf8_encode("Ajouter une UV à la base de donnée"));
$frm->add_text_field("nom","Nom de l'UV : ");
$frm->add_hidden("action","add");
$frm->add_submit("ajout","Valider");

$site->add_contents($frm);

$req = new requete($site->db,"SELECT `id`,`nom` FROM uv ORDER BY nom ASC");

$cts = new sqltable(
				"listeuvs", 
				utf8_encode("Liste des UVs dans la base de données"), $req,$topdir."temp/uvs.php", 
				"id", 
				array("id"=>"N°","nom"=>"Nom UV"), 
				array(), array());


$site->add_contents($cts);

$site->end_page();


?>