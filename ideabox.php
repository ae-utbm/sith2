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

$topdir = "./";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/entities/ideabox.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");

$site = new site();

$site->start_page("none", "La boite à idées");

$cts = new contents("Index des campagnes d'idées");

$cts->add_paragraph("Pour toi public !");

$sql = new requete($site->db, "SELECT `ideabox_campagne`.*,
																			`ideabox_reponses`.`id_cpg`,
																			COUNT(id_cpg) AS `nbideas`
																			FROM `ideabox_campagne` 
																			LEFT JOIN `ideabox_reponses` 
																			ON `ideabox_campagne`.`id` = `ideabox_reponses`.`id_cpg`
																			GROUP BY `ideabox_reponses`.`id_cpg`
																			ORDER BY `date` ASC"
																			);

$table = new sqltable("cpg_list",
											"Liste des boites à idées ouvertes",
											$sql,
											"ideabox.php",
											"id",
											array(
												"title" => "Titre",
												"start_date" => "De",
												"end_date" => "A",
												"nbideas" => "Idées"
												),
											array("ideas" => "Aller voir"),
											array(),
											array()
										);

$cts->add($table, true);
										
$site->add_contents($cts);

$site->end_page ();

?>