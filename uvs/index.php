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
include($topdir. "include/entities/uv.inc.php");


$site = new site();

$site->start_page("services", "AE - Pédagogie");
$cts = new contents("Site de l'AE - Espace Pédagogie");


$cts->add_paragraph("Bienvenue sur la partie Pédagogie du site de l'AE");

if ($site->user->id > 0)
{
  $cts->add_title(1, "Mon parcours pédagogique");
  $cts->add(get_creds_cts($site->user->id, $site->db));

  /* TODO : formulaire d'ajout de résultats */

}

if ($site->user->utbm)
{
  $cts->add_title(1, "Génération d'emploi du temps");
  $cts->add_paragraph("Cette partie permet aux étudiants de l'UTBM de générer leurs emplois
du temps en graphique, et ainsi le partager facilement.");
  
  $lst[] = "<a href=\"./create.php\">Créer un emploi du temps</a>";
  $lst[] = "<a href=\"./edt.php\">Gérer mes emplois du temps</a>";

  $itemlst = new itemlist("edt_lst", false, $lst);
  $cts->add($itemlst);

}

$cts->add_title(1, "Informations sur les UVs");
$cts->add_paragraph("Grâce à cette section, vous pouvez consulter les UVs dispensées à
l'UTBM. Ces informations ont été copiées du <a href=\"http://www.utbm.fr/upload/gestionFichiers/GUIDEUV_1370.pdf\">Guide
officiel des UVs 2006</a>, complétées par les étudiants / pour les étudiants, et aucune garantie n'est donnée quant à la
justesse des informations.");

$lst = array();

$lst[] = "<a href=\"./uvs.php\">guide des UVs format \"site AE\"</a>";
$lst[] = "<a
href=\"http://www.utbm.fr/upload/gestionFichiers/GUIDEUV_1941.pdf\"><b>guide
des UVs officiel</b> (édition 2007 / 2008, format PDF)</a>";

foreach ($departements as $dept)
     $lst[] = "<a href=\"./uvs.php?iddept=".$dept."\">UVs du département $dept</a>";

$itemlst = new itemlist("edt_lst", false, $lst);
$cts->add($itemlst);


$site->add_contents($cts);
$site->end_page();

?>