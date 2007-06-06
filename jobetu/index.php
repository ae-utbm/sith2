<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/board.inc.php");
require_once("include/jobetu.inc.php");
require_once("include/jobuser_etu.inc.php");



$site = new site();
$site->start_page("services", "AE Job Etu");


$header = new contents("Bienvenue sur AE Job Etu");
$header->add_paragraph("Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum diam augue, vestibulum sit amet, hendrerit in, vehicula et, libero. Aliquam nisl elit, vehicula sed, porttitor ac, tempor aliquam, risus. Donec consectetuer sagittis turpis. Aenean elit mauris, tincidunt ac, tristique vitae, porttitor et, lacus. Nam non leo in augue dignissim euismod. Vivamus consectetuer. Praesent adipiscing. Vestibulum laoreet semper diam. Aliquam varius arcu ac lectus. Sed ultrices risus vitae massa. Integer suscipit aliquet turpis. Pellentesque varius libero in velit dapibus consectetuer. Phasellus fermentum. Sed a neque vitae diam tristique lacinia. In hac habitasse platea dictumst. Curabitur vel nisi. Maecenas quis nunc a erat pellentesque consequat.");
$header->add_paragraph("Lisez les CGU : http://ae.utbm.fr/article.php?name=legals-jobetu-cgu");
$site->add_contents($header);


$link_etu = new contents("Vous êtes étudiant ?");

if($site->user->is_in_group('jobetu_etu'))
	$link_etu->add_paragraph("<a href='board_etu.php'>Accédez à votre tableau de bord</a>");
else
	$link_etu->add_paragraph("Activez votre compte !");

$link_client = new contents("Vous êtes un particulier, une entreprise ?");

if($site->user->is_in_group('jobetu_client'))
	$link_client->add_paragraph("<a href='board_etu.php'>Accédez à votre tableau de bord</a>");
else
	$link_client->add_paragraph("<a href='depot.php'>Passez votre annonce !</a>");


$board = new board();
	$board->add($link_client, true);
	$board->add($link_etu, true);
$site->add_contents($board);

$site->end_page();

?>
