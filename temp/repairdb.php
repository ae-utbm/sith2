<?php

/* Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
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
require_once($topdir. "include/carteae.inc.php");
require_once($topdir. "include/cotisation.inc.php");

$site = new site ();

echo "<h1>AE2: Auto repair</h1>";

echo "<ul>";

$sql = new requete($site->db,"SELECT id_cotisation FROM ae_cotisations LEFT JOIN utilisateurs USING(`id_utilisateur`) WHERE utilisateurs.id_utilisateur IS NULL");

while ( list($id_cotisation) = $sql->get_row() )
{
  $rem = new delete($site->dbrw,"ae_cotisations",array("id_cotisation"=>$id_cotisation));
  echo "<li>Missing user: Cotisation $id_cotisation removed.</li>";
}

$sql = new requete($site->db,"SELECT id_carte_ae FROM ae_carte LEFT JOIN ae_cotisations USING(`id_cotisation`) WHERE ae_cotisations.id_cotisation IS NULL");
while ( list($id_carte_ae) = $sql->get_row() )
{
  $rem = new delete($site->dbrw,"ae_carte",array("id_carte_ae"=>$id_carte_ae));
  echo "<li>Missing cotisation: Card $id_carte_ae removed.</li>";
}

$sql = new requete($site->db,"SELECT ae_cotisations.* FROM ae_cotisations LEFT JOIN ae_carte USING(`id_cotisation`) WHERE ae_carte.id_cotisation IS NULL AND date_fin_cotis > NOW()");
$cotiz = new cotisation($site->db,$site->dbrw);
while ( $row = $sql->get_row() )
{
  $cotiz->_load($row);
  $cotiz->generate_card();
  echo "<li>Missing card for valid cotisation ".$cotiz->id." (user ".$cotiz->id_utilisateur.") : A card added.</li>";
}

echo "</ul>";


?>