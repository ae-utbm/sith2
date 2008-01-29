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
if(is_dir(MAGPIE_CACHE_DIR))
{
  $req = new requete($site->db,"SELECT `url` FROM `planet_flux` WHERE `modere`='1'");
  while ( list($url) = $req->get_row() )
    $rs=fetch_rss($url);

  // Tâche 1 [planet] : nettoyage du cache
  $cache = opendir(MAGPIE_CACHE_DIR);
  while ($file = readdir($cache))
    if ( is_file(MAGPIE_CACHE_DIR.$file) && filemtime(MAGPIE_CACHE_DIR.$file) < (time()-MAGPIE_CACHE_AGE) )
      unlink(MAGPIE_CACHE_DIR.$file);
  closedir($cache);
}

// Tâche 2 [galaxy] : màj, et cycles


require_once($topdir. "include/galaxy.inc.php");

$galaxy = new galaxy($site->db,$site->dbrw);

$galaxy->update();

for($i=0;$i<45;$i++) // Environs 1100 cycles/jours
  $galaxy->cycle();

$galaxy->mini_render($topdir."var/mini_galaxy.png");


?>
