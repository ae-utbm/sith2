<?
/* Copyright 2007
 *
 * - Julien Etelain < julien at pmad dot net >
 *
 * "AE Recherche & Developpement" : Galaxy
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

if ( isset($_REQUEST["update"]) )
{
  echo "UPDATE : ";
  $st = microtime(true);
  $galaxy->update();
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
}

if ( isset($_GET["rand"]) )
  $galaxy->rand();

if ( isset($_GET["optimize"]) )
{
  echo "OPTIMIZE : ";
  $st = microtime(true);
  $galaxy->optimize();
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
}

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

if ( isset($_REQUEST["minirender"]) )
{
  echo "MINI-RENDER : ";
  $st = microtime(true);
  $galaxy->mini_render("../var/mini_galaxy.png");
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  echo "<br/><br/><img src=\"../var/mini_galaxy.png\" />";
}

if ( isset($_REQUEST["render"]) )
{
  echo "RENDER : ";
  $st = microtime(true);
  $galaxy->render("../var/galaxy.png");
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  echo "<br/><br/><img src=\"../var/galaxy.png\" />";
}

?>
