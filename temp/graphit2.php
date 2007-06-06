<?
/*
 * tests de graphing normal
 *
 *        - pedrov
 */

$topdir = "../";


require_once ($topdir . "include/graph.inc.php");


for ($i = - pi(); $i < pi() ; $i += 0.001)
{
  $coords[] = array('x' => $i,
		    'y' => array (sin(3 * $i),
				  sin(3 * $i - pi() / 3),
				  sin(3 * $i + pi() / 3)));
}


$graph = new graphic ("3 sinuses",
		      array("sinus 3x",
			    "sinus 3x - pi/3",
			    "sinus 3x + pi/3"),
		      $coords);


$graph->png_render ();



$graph->destroy_graph ();


?>

