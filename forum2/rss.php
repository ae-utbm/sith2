<?
/*
 * FORUM2 - flux rss
 *
 * Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/rssforum.inc.php");
$db=new mysqlae();
$user = new utilisateur($db);
if(   isset($_REQUEST['id_utilisateur'])
   && isset($_REQUEST['serviceident'])
   && !empty($_REQUEST['id_utilisateur'])
   && !empty($_REQUEST['serviceident']) )
{
  $user->load_by_service_ident($_REQUEST['id_utilisateur'],$_REQUEST['serviceident']);
}
$rss = new rssfeedforum($db, 40, $user);
$rss->output();


?>
