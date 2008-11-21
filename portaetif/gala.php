<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

$topdir = "../";
include($topdir. "include/site.inc.php");
include("include/log.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae") && !$site->user->is_in_group ("portaetif"))
  $site->error_forbidden();
$site->add_css("css/gala.css");
$site->set_side_boxes("left",array());
$site->set_side_boxes("right",array());

if ( $_REQUEST["action"] == "getpass" )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  if ( $user->id > 0 )
  {
    $sql = 'SELECT * FROM places_gala WHERE id_utilisateur='.$user->id;
/*    $req = new requete($site->db,$sql);
    if ( $req->lines>0 )
    {
      
    }
    else*/
      $Erreur = "Aucune place en stock pour vous.";
  }
  $Erreur = "Try again please :/";
}

$cts = new contents("Bienvenue au gala de prestige 2008 de l'UTBM");
$frm = new form("getpass","gala.php",true,"POST","Gala");
$frm->add_info("Bienvenue au Gala de Prestige 2008 de l'UTBM");
$frm->add_info("Veuillez entrer votre nom ci-dessous pour pouvoir retirer votre place :");
$frm->add_hidden("action","getpass");
if ( $Erreur ) $frm->error($Erreur);
$frm->add_user_fieldv2("id_utilisateur","");
$frm->add_submit("get","Retirer des places");
$cts->add($frm,true);
$cts->puts("<script type='text/javascript'>userselect_toggle('id_utilisateur');</script>");

/* c'est tout */
$site->add_contents($cts);

$site->end_page();

?>
