<?php
$topdir="../";
require_once($topdir. "include/site.inc.php");
$site = new site();

$req = new requete($site->dbrw, "UPDATE `site_parametres` SET valeur_param='b:1;' WHERE nom_param='closed'");
exit();

?>
