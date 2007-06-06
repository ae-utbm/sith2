<?php

/** @file
 *
 * @brief Download.
 *
 */

/* Copyright 2006
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Code inspiré de la version 1 du site.
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

$topdir = "./";
require_once($topdir. "include/site.inc.php");


require_once ("./include/mysql.inc.php");
require_once ("./include/mysqlae.inc.php");

$site = new site();

$id = intval($_REQUEST['id']);

/* recuperation des infos */
$sql = "SELECT `nom_fichier`, `mime_fichier` FROM `fichiers` WHERE
                                  `id_fichier` = ".$id;
/* etudiant connecté ? */
if ($site->user->id < 0)
  $sql .= " AND `public_fichier` = 1";

/* fichier modéré ? */
if (!$site->user->is_in_group ("moderateur_site"))
  $sql .= " AND `modere_fichier` = 1";

$sql .= " LIMIT 1";

$req = new requete ($site->db, $sql);

if ($req->lines <= 0)
{
 	header("Location: 403.php");
	exit();
}

$file_info = $req->get_row ();

if (!file_exists ($file_info['nom_fichier']))
{
  header("Location: 404.php");
  exit();
}

$file = $file_info['nom_fichier'];
$mime = $file_info['mime_fichier'];

/* Incrémentation du hit sur le fichier */
$req = new requete($site->dbrw, "UPDATE `fichiers` SET `nb_telecharge_fichier` =
                                 `nb_telecharge_fichier` + 1 WHERE
                                 `id_fichier` = " .
		   $id . " LIMIT 1");

if ($req->lines != 1)
  die("Erreur d'accès à la base\n");

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

header("Content-Type: $mime");
header("Content-Disposition: inline; filename=".basename($file));

@readfile("./files/" .basename($file));

?>
