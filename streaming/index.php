<?php

/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
 * along with site program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */
 
$topdir = "../";
require_once($topdir. "include/site.inc.php");

/* on défini des variables simples et efficaces */
$rdd=array("title"=>"remise des diplômes",
           "intro"=>"Cette année, afin de faire participer les personnes non-conviées à la cérémonie de la Remise des Diplômes de l'<a href='http://www.utbm.fr'>Université de Technologie de Belfort-Montbéliard</a>, nous avons mis en place un service expérimental de diffusion de la cérémonie en direct.",
           "date"=>"samedi 17 novembre 2007",
           "h-debut"=>"10",
           "h-fin"=>"12",
           "lieu"=>"au centre des expositions AirExpo d'Andelnans (entre Belfort et Sevenans)",
           "remerciements"=>"Grâce au travail d'UTBM-Productions pendant la cérémonie, nous vous offrons en direct les images montées afin de profiter, chez vous, de la cérémonie. Nous remercions aussi le CRI de l'UTBM ainsi que l'ensemble des bénévoles et partenaires.<br /> <img src=\"utprod.png\" alt=\"UTBM Production\" title=\"UTBM Production\" /> <img src=\"logos_rdd.gif\" alt=\"partenaires\ title=\"partenaires\" />",
           "bandeau"=>"bandeau_rdd.png",
           "photo"=>"photo_rdd.jpg"
          );

$event=$rdd;

$site = new site ();

$site->set_side_boxes("left",array());
$site->set_side_boxes("right",array());

if(isset($event["bandeau"]))

$site->start_page("none",$event["title"]);

$cts = new contents("Présentation");
if(isset($event["bandeau"]))
{
  if(isset($event["photo"]))
    $cts->add_paragraph("<div align=\"center\"><img src=\"".$event["bandeau"]."\" alt=\"".$event["title"]."\" title=\"".$event["title"]."\" /><br /><img src=\"".$event["photo"]."\" alt=\"".$event["title"]."\" title=\"".$event["title"]."\" /></div>");
  else
    $cts->add_paragraph("<div align=\"center\"><img src=\"".$event["bandeau"]."\" alt=\"".$event["title"]."\" title=\"".$event["title"]."\" /></div>");
  $cts->add_paragraph(" ");
}
$cts->add_paragraph($event["intro"]);
$cts->add_paragraph("<b>La cérémonie a lieu le ".$event["date"]." de ".$event["h-debut"]."h à ".$event["h-fin"]."h ".$event["lieu"]."</b>.");
$site->add_contents($cts);

$cts = new contents("Regarder la cérémonie en direct");
$cts->add_paragraph("Pour profiter au mieux et de manière plus fiable de cette diffusion, nous vous recommandons l'utilisation du logiciel libre <a href=\"http://www.videolan.org\">VideoLan</a>. Ce logiciel est disponible au <a href=\"http://www.videolan.org/vlc/\">téléchargement</a> pour toutes les plateformes (Windows, Mac OS et Linux compris bien sûr).");
$cts->add_paragraph("Pour les utilisateurs de <b>VideoLan</b>, ouvrez le lien suivant : <b><a href=\"http://ae.utbm.fr/streaming/stream.m3u\">ici</a></b>");
$site->add_contents($cts);

$cts = new contents("Merci à");
$cts->add_paragraph($event["remerciements"]);
$site->add_contents($cts);

$cts = new contents("Propulsé par ...");
$cts->add_paragraph("<img src=\"linux.png\" alt=\"GNU/Linux\" title=\"GNU/Linux\" /> <img src=\"vlc.jpg\" alt=\"VideoLan\" title=\"VideoLan\" /> <img src=\"debian.png\" alt=\"Debian\" title=\"Debian\" />");
$site->add_contents($cts);

$site->end_page();

?>
