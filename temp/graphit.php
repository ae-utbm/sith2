<?
/*
 * tests de graphing
 *
 *        - pedrov
 */

$topdir = "../";

require_once ($topdir . "include/graph.inc.php");


for ($i = time() - (24 * 3600 * 7) ; $i < time() ; $i += (24*3600))
{
  $coords[] = array('x' => date("Y-m-d", $i),
		    'y' => rand (0, 5));
}


$graph = new graphic ("clopes par jour",
		      "nb de clopes",
		      $coords,
		      array("%Y-%m-%d", "%d"),
		      array("2006-03-29" => "semaine derniere",
			    "2006-04-03" => "hier",
			    "2006-04-04" => "aujourd'hui"),
		      array(0 => "O clopes : bien",
			    1 => "1 : Mwe ...",
			    2 => "2 : hof ...",
			    3 => "3 : Ca commence a faire la ...",
			    4 => "4 : t'as pris un rateau ou quoi ?",
			    5 => "5 : t'as vu michau ? :-)"));


$graph->png_render ();



$graph->destroy_graph ();


?>

