<?php
if(!isset($argc))
  exit();
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

$topdir=$_SERVER['SCRIPT_FILENAME']."/../";
require_once($topdir. "include/site.inc.php");

$site = new site ();

echo "==== ".date("d/m/Y")." ====\n";

echo ">> OPTIMZE TABLES\n";
$req = new requete($site->db, 'SHOW TABLES');
while(list($table)=$req->get_row())
  new requete($site->dbrw, 'OPTIMIZE TABLE \''.$table.'\'');



?>
