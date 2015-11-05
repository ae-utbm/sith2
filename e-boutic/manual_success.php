<?php
/**
 * @brief La reponse automatique generee par les serveurs
 * de la soge.
 *
 */

/* Copyright 2015
 * Skia <lordbanana25 AT mailoo DOT org>
 *
 * Ce fichier fait partie du site de l'Association des ï¿½tudiants de
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

$topdir = "../";

require_once ($topdir . "include/site.inc.php");
require_once ("./include/answer.inc.php");

/* fonction rapide de log des retours */
function log_success ($message)
{
  $rep = ($GLOBALS['taiste']) ? "taiste" : "ae2";
  $fp = fopen("/var/www/".$rep."/e-boutic/.autorep.log", "a+");
  fwrite($fp, date("Y\-m\-d H\:i") . " " . $message . "\n");
  fclose($fp);
}

$site = new site(new mysqlae(), new mysqlae("rw"));
if (!$site->user->is_in_group("root"))
  $site->error_forbidden("accueil");

/* Ca, c'est cense etre obligatoire
 * (doc sogenactif)
 */
if (!isset($_POST['DATA']))
{
  log_success("Erreur : pas de variable DATA postée");
  die();
}

log_success("Variable DATA postée: ".$_POST['DATA']);

/* else : les serveurs sogenactif ont bien posté ce
   qu'on attendait                                   */

$success = new answer (new mysqlae(), new mysqlae("rw"));
$success->set_answer($_POST['DATA']);
/* si erreur ne venant pas de nous */
if ($success->code != 0)
{
  log_success("Code de retour sogenactif erroné : " . $success->code . ".");
  /* meme remarque que précédemment */
  die();
}

/* else : on a un retour correct à ce niveau la */
$ret = $success->register_order ();
if ($ret == true)
  log_success("Enregistrement des donnees de l'achat effectue avec succes.");
else
  log_success("Erreur appel answer->register_order ().");
die();

?>
