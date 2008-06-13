<?
/* Copyright 2007
 * - Manuel Vonthron <manuel DOT vonthron AT acadis DOT org>
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

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/uv.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");


$site = new site();
if(!$site->user->utbm)
  header('Location: index.php');

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'select_uv')
{
  $sql = new insert($site->dbrw, "edu_uv_selection", 
                      array("id_utilisateur"=>$site->user->id,
                            "id_uv"=>$_REQUEST['id_uv']
                            ));
  if($sql->is_success())
    echo "UV sélectionnée !";
  exit;
}


$site->add_box("uvsmenu", get_uvsmenu_box() );
$site->set_side_boxes("left", array("uvsmenu", "connexion"));

$site->start_page("services", "AE Pédagogie - Sélection d'UV");

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";
$path .= "/" . " Sélection d'UV";
$cts = new contents($path);

$sql = new requete($site->db, "SELECT `code_uv`, `intitule_uv`, `id_uv`, `semestre`
                                FROM `edu_uv_selection`
                                NATURAL JOIN `edu_uv`
                                WHERE `id_utilisateur`=".$site->user->id."
                                GROUP BY `semestre`");

$table = new sqltable('uv_quicklist', "Sélection", $sql, "selection.php",
                      "id_uv",
                      array("code_uv" => "Code de l'UV",
                            "intitule_uv" => "Intitulé de l'UV",
                            "semestre" => "Semestre"),
                      array (),
                      array(), array(), false);

$cts->add($table);

$site->add_contents($cts);
$site->end_page();
?>
