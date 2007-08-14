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
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/entities/asso.inc.php");

$site = new site();

$site->start_page("none", "La boite à idées");


if(isset($_REQUEST['action']) && $_REQUEST['action'] == "ideas")
{
	$cts = new contents("Idées demandées");
	
	$box = new ideabox($site->db);
	$box->load_by_id(mysql_real_escape_string($_REQUEST['id']));
	
}
else if(isset($_REQUEST['action']) && $_REQUEST['action'] == "new")
{
	if(isset($_REQUEST['magicform']) && $_REQUEST['magicform']['name'] == "newbox")
	{
		$box = new ideabox($site->db, $site->dbrw);
		$box->add();
	}
	
	$cts = new contents("Nouvelle boite à idée");

	$frm = new form("newbox", "ideabox.php?action=new", true, "POST", "boiboite");
	$frm->add_text_field("title", "Question", "", true, 50);
	$frm->add_text_area("desc", "Description", "", 60, 5);
	$frm->add_date_field("start_date", "Date de début", "2007");
	$frm->add_date_field("end_date", "Date de fin");
	$frm->add_entity_select("groupid","Groupe concerné",$site->db,"group", false, "none");
	$frm->add_info("aucun : tout le monde");
	$frm->add_entity_select("assoid","Demandé par",$site->db,"asso", false, "none"); 
	$frm->add_submit("go", "Envoyer");
	
	
	$cts->add($frm, false);

}
else
{

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
}

$site->add_contents($cts);

$site->end_page ();

?>