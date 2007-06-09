<?php
/*
 * sendfaxnew.php : Test et mise en pratique de la stdentity
 * fax.
 *
 *
 *   <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 *    AE Recherche & Développement, parce que c'est la classe(tm)
 *
 */
$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/fax.inc.php");


$site = new site();

$site->start_page("services","AE - R&D - envoi de fax");

if (isset($_POST['sendfaxsbmt']))
{
  $fax = new fax ($site->db, $site->dbrw);

  $fax->load_by_id($_POST['faxinstanceid']);

  $fax->set_captcha($_POST['captcha']);

  $ret = $fax->send_fax(false);

  if ($ret)
    $cts = new contents("Etat d'envoi du fax",
			"Ca a marché !");
  else
    $cts = new contents("Etat d'envoi du fax",
			"<b>FOIREAIZE !</b>");

  $site->add_contents($cts);
  $site->end_page();
  exit();
}

if (isset($_POST['preparefaxsbmt']))
{
  
  $fax = new fax($site->db, $site->dbrw);
  
  $fax->create_instance($site->user->id,
			$_POST['numdest'],
			$_FILES['mypdf'],
			1 /* ae, quoi */);
  

 
  $cts = new contents("Euh ouais", 
		      "Par contre j'ai besoin que tu me tapes ".
		      "l'image la dans la ptite boite !"); 
  
  $cts->puts("<br/><img src=\"".$fax->imgcaptcha."\" alt=\"captchos\" />");
  
  $frm = new form("sendfax",
		  "sendfax.php",
		  true,
		  "POST");

  $frm->add_hidden("faxinstanceid", $fax->id);

  $frm->add_text_field("captcha",
		       "Captcha : ",
		       "");

  $frm->add_submit("sendfaxsbmt", "Et paf !");

  $site->add_contents($cts);
  $site->add_contents($frm);
  $site->end_page();

  exit();

}	

$frm = new form("preparefax",
		"sendfax.php",
		true,
		"POST");

$frm->add_text_field("numdest","Numéro du destinataire : ");
$frm->add_file_field("mypdf", "Fichier PDF : ", true);
$frm->add_submit("preparefaxsbmt","Valider");

$site->add_contents($frm);

$site->end_page();


?>