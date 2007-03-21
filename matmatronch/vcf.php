<?php
$topdir = "../";
require_once($topdir. "include/site.inc.php");

$site = new site ();
if ( $site->user->id < 1 )
{
 	header("Location: 403.php?reason=session");
	exit(); 	
}

if ( !$site->user->utbm && !$site->user->ae )
{
 	header("Location: 403.php?reason=reservedutbm");
	exit(); 	
}

$user = new utilisateur($site->db,$site->dbrw);
$user->load_by_id($_REQUEST["id_utilisateur"]);	
if ( $user->id < 0 )
{
	header("Location: ../404.php");
	exit();	
}
$user->load_all_extra();
header("Content-Type: text/x-vcard");
header('Content-Disposition: attachment; filename="'.addslashes($user->prenom." ".$user->nom.".vcf").'"');

$user->output_vcard();

?>
