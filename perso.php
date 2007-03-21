<?php
$topdir = "./";
require_once($topdir. "include/site.inc.php");
$site = new site ();
if ( $site->user->id < 1 )
	error_403();
	
if ( $_REQUEST["action"] == "sethome" )
{
	$home = $_REQUEST["home"];
	if ( $home == 1 || $home == 2 )
		$site->user->set_param("homemode",$home);
}	
	
$site->start_page("","Personnalisation");


$cts = new contents("Personnalisation");

/*
$frm = new form("sethome","perso.php",false,"POST","Page d'accueil");
$frm->add_hidden("action","sethome");
$frm->add_radiobox_field ( "home", "Mode d'affichage", array(1=>"AE2: Affichage résumé, groupé par pôle.",2=>"Classic: Nouvelles entièrement dévellopés les unes après les autres."), $site->user->get_param("homemode",1) , null, true, array(1=>"images/personnalisation/home1.jpg",2=>"images/personnalisation/home2.jpg")  );
$frm->add_submit("valid","Enregistrer");
$cts->add($frm,true);
*/
$cts->add_paragraph("Cette fonctionalité n'est plus disponible pour le momement. Merci de votre comprehension.");

$site->add_contents($cts);

	
$site->end_page();
?>
