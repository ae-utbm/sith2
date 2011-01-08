<?php
/* Copyright 2006
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
$topdir = "./";

require_once($topdir. "include/site.inc.php");

$site = new site ();

$site->user->load_by_id($_REQUEST["id"]);

if ( !$site->user->is_valid() || ($site->user->hash == "valid") || ($site->user->hash != $_REQUEST["hash"]) )
{
  $site->user->id=null;

  $site->start_page("accueil","Erreur");

  if ($site->user->hash == "valid")
    $site->add_contents(new error("Compte déjà validée","Votre compte a déjà été validé. Vous pouvez vous connecter avec vos identifiants. Voir <a href=\"article.php?name=docs:connexion\">Documentation : Connexion</a>"));
  else
    $site->add_contents(new error("Impossible d'activer votre compte","Merci de vérifier le lien dans l'email qui vous a été adressé"));
  $site->end_page();
  exit();
}



$site->user->validate();
$site->connect_user();

$page = $topdir;

/*
 * Le passage de la redirection se fait via la variable de session pour eviter
 * toute redirection non controlée.
 */
if ( $_SESSION['session_redirect'] )
{
  $page = $_SESSION['session_redirect'];
  unset($_SESSION['session_redirect']);
}

header("Location: $page");

?>
