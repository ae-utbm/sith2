<?
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");
require_once($topdir."include/galaxy.inc.php");

$dbrw = new mysqlae("rw");

$galaxy = new galaxy($dbrw,$dbrw);

if ( isset($_GET["optimize"]) )
{
  // Algorithme d'optimisation du placement des éléments
  
  
  // 1- Trouver un emplacement plus optimal pour les objets liés a un seul autre, qui lui doit être liés à plus d'un objet
  $req = new requete($dbrw,
  "SELECT a.id_star, b.x_star, b.y_star, l.ideal_length_link 
   FROM galaxy_star AS a
   INNER JOIN galaxy_link AS l ON ( l.id_star_a = a.id_star )
   INNER JOIN galaxy_star AS b ON ( l.id_star_b = b.id_star )
   WHERE a.nblinks_star=1 AND b.nblinks_star > 1
   UNION
   SELECT b.id_star, a.x_star, a.y_star, l.ideal_length_link
   FROM galaxy_star AS b
   INNER JOIN galaxy_link AS l ON ( l.id_star_b = b.id_star )
   INNER JOIN galaxy_star AS a ON ( l.id_star_a = a.id_star )
   WHERE b.nblinks_star=1 AND a.nblinks_star > 1");
   
  while ( list($id,$cx,$cy,$l) = $req->get_row() )
  {
    list($nx,$ny) = $galaxy->find_low_density_point($cx-($l*1.5),$cy-($l*1.5),$l*3,$l*3,$id); 
    $nx = sprintf("%.f",$nx);
    $ny = sprintf("%.f",$ny);
    echo "MOVE $id to ($nx, $ny)<br/>\n";
    new requete ( $dbrw, "UPDATE galaxy_star set x_star=$nx, y_star=$ny WHERE id_star=$id");
  }
    
  
  
  
  exit();
}


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