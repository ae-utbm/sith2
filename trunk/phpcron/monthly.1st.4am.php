<?php
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

$topdir=$_SERVER['SCRIPT_FILENAME']."/../";
require_once($topdir. "include/site.inc.php");

$site = new site ();

echo "==== ".date("d/m/Y")." ====\n";



?>
