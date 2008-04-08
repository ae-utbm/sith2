<?php
$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/phpcron";

/*
 * hourly
 */
$topdir=$_SERVER['SCRIPT_FILENAME']."/../";
define('MAGPIE_CACHE_DIR', '/var/www/ae/www/var/cache/planet/');
define('MAGPIE_CACHE_ON', true);
define('MAGPIE_CACHE_AGE', 50*60); //50minutes pour etre certain d'avoir un truc à jour :)
define('MAGPIE_OUTPUT_ENCODING', "UTF-8");
define('MAX_NUM',20);
define('MAX_SUM_LENGHT',200);

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/lib/magpierss/rss_fetch.inc.php");


$site = new site ();

// Tâche 1 [planet] : mettre à jour le cache

// Tâche 2 [galaxy] : màj, et cycles


require_once($topdir. "include/galaxy.inc.php");

$galaxy = new galaxy($site->db,$site->dbrw);

$galaxy->update();

for($i=0;$i<45;$i++) // Environs 1100 cycles/jours
  $galaxy->cycle();

$galaxy->mini_render($topdir."var/mini_galaxy.png");


?>
