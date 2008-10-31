<?php
/* Copyright 2007
 * - Simon Lopez < simon dot lopez at ayolo dot org >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

define("WATERMARK", TRUE); // watermark TRUE ou FALSE

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once ($topdir . "include/watermark.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "include/cts/imgloc.inc.php");
require_once ($topdir . "include/watermark.inc.php");

$level=1;

$site = new site ();

$pgconn = new pgsqlae();

$loc = new imgloc(800, $level, $site->db, $pgconn);

$statscotis = new requete($site->db, "SELECT `utl_etu`.`id_ville`
                                      FROM `utl_etu`
                                      INNER JOIN `loc_ville` ON `loc_ville`.`id_ville` = `utl_etu`.`id_ville`
                                      WHERE `utl_etu`.`id_ville` IS NOT NULL");
$idloc=array();
while (list($id_ville) = $statscotis->get_row())
{
  if(!isset($idloc[$id_ville]))
  {
    $idloc[$id_ville]=1;
    $loc->add_location_by_idville($id_ville);
  }
}
$loc->add_context();

$img = $loc->generate_img();
$wm_img = new img_watermark ($img->imgres);
$wm_img->output();

exit();

?>
