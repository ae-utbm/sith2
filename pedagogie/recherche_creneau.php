<?php

/* Copyright 2010
 * - Mathieu Briand < briandmathieu AT hyprua DOT org >
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

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();
$site->allow_only_logged_users();
$site->start_page("none", "Recherche de crénaux libres communs");

$cts = new contents("Recherche de crénaux libres communs");

$frm = new form ("crenauxcommuns", "recherche_creneau.php", false, "POST", "Recherche de crénaux libres communs");

$utilisateur = new utilisateur($site->db);
$nbutil = 0;
if (isset($_REQUEST['id_utilisateur']))
{
  foreach($_REQUEST['id_utilisateur'] as $id)
  {
    $utilisateur->load_by_id($id);
    if ($utilisateur->is_valid())
    {
      $frm->add_entity_smartselect("id_utilisateur[".$i."]","Utilisateur", $utilisateur, true);
      $utilisateurs[] = $utilisateur->id;
      $nbutil++;
    }
  }
}

if ($nbutil == 0)
{
  $frm->add_entity_smartselect("id_utilisateur[".$i."]","Utilisateur", $site->user, true);
  $utilisateurs[] = $site->user->id;
  $nbutil++;
}

$utilisateur = new utilisateur($site->db);
$frm->add_entity_smartselect("id_utilisateur[".$nbutil."]","Utilisateur", $utilisateur, true);
$frm->add_submit("valid","Générer");

$site->add_contents($frm);

$param = "?id_utilisateurs=".serialize($site->user->id);
$image = new image("Créneaux communs", "recherche_creneau_img.php".$param);
$site->add_contents($image);

$site->end_page();

?>
