<?php
/** @file
 *
 * @brief Page d'accueil de la partie pédagogique du site de l'AE.
 *
 */

/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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

$depts = array('Humas', 'TC', 'GESC', 'GI', 'IMAP', 'GMC', 'EDIM');

$site = new site();

$site->start_page("services", "AE - Pédagogie");
$cts = new contents("Site de l'AE - Espace Pédagogie");


$cts->add_contents("Cette partie du site de l'AE vous permet de vous renseigner sur le
catalogue des UVs en ligne (basé sur le catalogue officiel de l'UTBM).
<br/><br/><br/><br/> <b>Parce que l'AE cherche à occuper
votre vie extra-scolaire, mais aussi scolaire, l'AE lance pour vous AE
- Pedagogie !</b>");

if ($site->user->utbm)
{
  $cts->add_title(1, "Génération d'emploi du temps");
  $cts->add_paragraph("Cette partie permet aux étudiants de l'UTBM de générer leurs emplois
du temps en graphique, et ainsi le partager facilement.");
  
  $lst[] = "<a href=\"./create.php\">Créer un emploi du temps</a>";
  $lst[] = "<a href=\"./create.php\">Gérer mes emplois du temps</a>";

  $itemlst = new itemlist("edt_lst", false, $lst);
}

$cts->add_title(1, "Informations sur les UVs");
$cts->add_paragraph("Grâce à cette section, vous pouvez consulter les UVs dispensées à
l'UTBM. Ces informations ont été copiées du <a href=\"\">Guide
officiel des UVs</a>, et aucune garantie n'est donnée quant à la
justesse des informations.");

$lst = array();

$lst[] = "<a href=\"./uvs.php\">guide des UVs format \"site AE\"</a>";
$lst[] = "<a
href=\"http://www.utbm.fr/upload/gestionFichiers/GUIDEUV_1370.pdf\"><b>guide
des UVs officiel</b> (édition 2006/2007, format PDF)</a>";

foreach ($depts as $dept)
     $lst[] = "<a href=\"./uvs.php?iddept=".$dept."\">UVs du département $dept</a>";

$itemlst = new itemlist("edt_lst", false, $lst);


$site->add_contents($cts);
$site->end_page();
?>