<?
/*
 * Test cartographie
 *
 *                                      - (c) 2006 pedrov
 */
$topdir = "../";

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");
require_once($topdir . "include/carto.inc.php");


$db = new mysqlae ();

$ville = $_REQUEST['nom'];
$cp = $_REQUEST['cp'];

$coords = get_coords ($db, $ville, $cp);

$carte = new carto (array());
$carte->add_color ("red", 255, 0, 0);
$coords = $carte->convert_gps_to_px ($coords);
$carte->draw_circle ($coords, 8, "red");

$carte->output ();
$carte->destroy ();


/*
 * Recherche de coordonnées dans une base
 *
 */
function get_coords ($db, $name, $cp)
{
  $cp = intval($cp);
  $name = mysql_real_escape_string ($name);

  $sql = new requete ($db,
		      "SELECT `lat_ville`, `long_ville`
                          FROM `villes`
                         WHERE `nom_ville` LIKE '%" . $name . "%'
                         AND `cpostal_ville` = $cp
                         LIMIT 1");
  $res = $sql->get_row();
  return array (rad2deg($res[0]), rad2deg($res[1]));
}