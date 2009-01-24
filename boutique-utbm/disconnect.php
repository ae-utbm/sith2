<?php
/* Copyright 2005
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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

require_once("include/boutique.inc.php");

$site = new boutique ();

// Supprime la session de la base
$req = new delete($site->dbrw, "site_sessions", array("id_session"=>$_COOKIE['AE2_SESS_ID']) );

// Supprime le cookie (le fait exprier)
setcookie ("AE2_SESS_ID", "", time() - 3600, "/", ".utbm.fr", 0);
unset($_COOKIE['AE2_SESS_ID']);
unset($_SESSION['session_redirect']);

header("Location: http://boutique.utbm.fr/");


?>
