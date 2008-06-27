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

$cps = get_cp ($db);

foreach ($cps as $cp)
  $coords[] = get_coords_by_cp ($db, $cp);

$carte = new carto (array());
$carte->add_color ("blue", 0, 0, 255);

foreach ($coords as $coord)
  $cpx[] = $carte->convert_gps_to_px ($coord);

foreach ($cpx as $bleh)
  $carte->draw_circle ($bleh, 5, "blue");

$carte->output ("/var/www/ae/www/var/img/logos/promo04.png");
$carte->destroy ();

/*
 * Recherche des codes postaux d'origine
 *
 */
function get_cp ($db)
{
  $sql = new requete ($db,
          "SELECT `cpostal_parents`
                       FROM `utl_etu` 
                       INNER JOIN `utl_etu_utbm`
                        ON `utl_etu`.`id_utilisateur` = `utl_etu_utbm`.`id_utilisateur`
                       WHERE `cpostal_parents` != ''
                           AND `utl_etu_utbm`.`promo_utbm` = '4'");

  for ($i = 0; $i < $sql->lines; $i++)
  {
    $res = $sql->get_row ();
    if (strlen($res[0]) == 5)
      $cps[] = $res[0];
  }
  return $cps;
}



/*
 * Recherche de coordonnées dans une base
 *
 */
function get_coords_by_cp ($db, $cp)
{
  $cp = intval($cp);

  $sql = new requete ($db,
          "SELECT `lat_ville`, `long_ville`
                          FROM `villes`
                         WHERE `cpostal_ville` = $cp
                         LIMIT 1");
  $res = $sql->get_row();
  return array (rad2deg($res[0]), rad2deg($res[1]));
}
