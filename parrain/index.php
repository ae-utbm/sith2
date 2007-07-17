<?php
/** @file
 *
 * @brief Page d'inscription au pré-parrainage pour les nouveaux
 *
 */

/* Copyright 2007
 * - Julien Ehrhart <julien POINT ehrhart CHEZ utbm POINT fr>
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

include($topdir. "include/site.inc.php");

$site = new site();

$site->start_page("services", "Pré-parrainage");

// Commenté en attendant de savoir si on passe par une procédure d'inscription classique
/*
if (!$site->user->is_valid())
{
  error_403();
}
*/

$cts = new contents("Pré-parrainage",
		    "Sur cette page, vous allez pouvoir ".
		    "vous inscrire pour le pré-parrainage.");

$cts->add_title(2,"Informations");

$cts->add_paragraph("Le module de pré-parrainage est actuellement en cours de réalisation. Merci de revenir prochainement.");

$site->add_contents($cts);

$site->end_page();

?>
