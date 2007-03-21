<?php
$topdir = "./";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/news.inc.php");
$site= new site ();

$site->start_page("accueil","Bienvenue");


$cts = new contents("test");

$frm = new form("test","test.php");
$frm->add_user_fieldv2 ( "ufield", "Utilisateur");


$cts->add($frm);

$site->add_contents($cts);

$site->end_page();


?>