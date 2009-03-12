<?php
$topdir = "../";
require_once($topdir. "include/site.inc.php");
$site = new site ();
$req = new requete($site->db,'SELECT `nom_asso`,`email_asso` FROM `asso`');
echo "<pre>";
while(list($asso,$email)=$req->get_row())
  echo $asso.';'.$email."\n";
echo "</pre>";
?>
