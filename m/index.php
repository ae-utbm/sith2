<?php
/* Copyright 2011
 * - Antoine Tenart < antoine dot tenart at gmail dot com >
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

/**
 * Home page for the mobile version
 */

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/newsflow.inc.php");


$site = new site();
$site->set_mobile(true);

/**
 * So we can put it in prod
 * TODO : remove me
 */
if(!$GLOBALS["taiste"]) header("HTTP/1.0 404 Not Found");


$site->start_page("acceuil", "Bienvenue");

if(!$site->user->is_valid()) {
  /**
   * For the moment, mobile version is only available for logged users
   */
  $frm = new form("connect",$topdir."connect.php",true,"POST","Connexion");
  $frm->add_select_field("domain",
      "Connexion",
      array("utbm"=>"UTBM / Assidu",
            "id"=>"ID",
            "autre"=>"E-mail",
            "alias"=>"Alias"));
  $frm->add_text_field("username","Utilisateur","","",20,true,true,null,false,35);
  $frm->add_password_field("password","Mot de passe","","",20);
  $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", true );
  $frm->add_submit("connect","Se connecter");
  $frm->add_hidden("mobile");
  $site->add_contents($frm);

  /* Come back here after connexion completed */
  $_SESSION['session_redirect'] = "m/";   /* Oh, a diplodocus ! Shhh !! */

  $site->end_page();
  exit(0);
}


/**
 * Display news on the home page
 */
//$site->add_contents("<h1>Accueil</h1>");
$site->add_contents(new newsfront($site->db));


/* Do not cross. */
$site->end_page();

?>
