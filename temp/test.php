<?php
$topdir = "../";
require_once($topdir. "include/site.inc.php");

$site = new site();

if( $site->get_param("backup_server",false))
  echo "Serveur de backup";
else
  echo "Serveur principal";

?>
