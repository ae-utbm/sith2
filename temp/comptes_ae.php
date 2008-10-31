<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");
require_once ("../include/graph.inc.php");


$req = new requete (new mysqlae(),
		    "SELECT `id_utilisateur`,
                            `montant_compte` / 100 as `montant`
                     FROM `utilisateurs`");

for ($i = 0; $i < $req->lines; $i++)
{
  $res = $req->get_row ();
  $coords[$i]['x'] = $res['id_utilisateur'];
  $coords[$i]['y'] = $res['montant'];
}

$graph = new graphic ("Comptes AE",
		      "compte AE = f(id utilisateur)",
		      $coords);


$graph->png_render ();

$graph->destroy_graph ();


?>
