<?php

/*
 * Test cartographie
 *
 *                                      - (c) 2006 pedrov
 */
$topdir = "../";

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");
require_once($topdir . "include/carto.inc.php");


$villes = array ("Strasbourg","Colmar", "Mulhouse", "Belfort", "Vesoul",
		 "Troyes", "Paris", "Chartres", "Le Mans","Rennes", "Vannes");
$db = new mysqlae ();

foreach ($villes as $ville)
{
  $coords[] = get_coords_by_name($db, $ville);
}

$carte = new carto ($coords);
$carte->parse_links (true);


$carte->output ();
$carte->destroy ();


/*
 * Recherche de coordonnées dans une base
 *
 */
function get_coords_by_name ($db, $name)
{
  $name = mysql_real_escape_string ($name);
  $sql = new requete ($db,
		      "SELECT `lat_ville`, `long_ville`
                          FROM `villes`
                         WHERE `nom_ville` = '" . $name . "'
                         LIMIT 1");
  $res = $sql->get_row();
  return array (rad2deg($res[0]), rad2deg($res[1]));
}
