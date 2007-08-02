<?
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");
require_once($topdir."include/galaxy.inc.php");

$dbrw = new mysqlae("rw");

$galaxy = new galaxy($dbrw,$dbrw);

if ( isset($_REQUEST["init"]) )
{
  echo "INITIALISATION : ";
  $st = microtime(true);
  $galaxy->init();
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
}

if ( isset($_GET["rand"]) )
  $galaxy->rand();
  
$cycles=10;

if ( isset($_GET["cycles"]) )
   $cycles = intval($_GET["cycles"]);
   
for($i=0;$i<$cycles;$i++)
{
  echo "CYCLE : ";
  $st = microtime(true);  
  $galaxy->cycle(!isset($_REQUEST["bypasscollision"]));
  echo "done in ".round(microtime(true)-$st,2)." sec<br/>\n";
}

if ( isset($_REQUEST["render"]) )
{
  echo "RENDER : ";
  $st = microtime(true);
  $galaxy->render();
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  echo "<br/><br/><img src=\"galaxy_temp.png\" />";
}

?>