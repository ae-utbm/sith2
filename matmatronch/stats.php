<?php
/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
require_once($topdir . "include/cts/sqltable.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae") && !$site->user->is_in_group ("matmatronch"))
  error_403();


$site->start_page ("none", "Classement MatMatronch");

$cts = new contents("Classement");

if ( $_REQUEST["action"] == "reset" )
{
  $stats = new requete($site->dbrw, "UPDATE `utl_etu` SET `visites`='0' WHERE `visites`!='0'");
	$cts->add_title(2, "Reset");
	$cts->add_paragraph("Le reset des stats a &eacute;t&eacute; effectu&eacute; avec succ&egrave;s");
}

$cts->add_title(2, "Administration");
$cts->add_paragraph("Le matmatronch vient d'&ecirc;tre &eacute;dit&eacute;, il est temps de remettre les statistiques &agrave; z&eacute;ro :)".
                    "<br /><img src=\"".$topdir."images/actions/delete.png\"><b>ATTENTION CECI EST IRREVERSIBLE</b> : <a href=\"stats.php?action=reset\">Reset !</a>");

$req = new requete($site->db,"SELECT `utl_etu`.`id_utilisateur`, `utl_etu`.`visites`, ".
                             "CONCAT(`utilisateurs`.`nom_utl`,' ',`utilisateurs`.`prenom_utl`) as `nom_utilisateur` ".
                             "FROM `utl_etu` ".
                             "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`utl_etu`.`id_utilisateur` ".
                             "WHERE `utilisateurs`.`utbm_utl`='1' ORDER BY `utl_etu`.`visites` DESC LIMIT 0, 10");


$cts->add(new sqltable("top_full",
                       "Top 10 g&eacute;n&eacute;ral des fiches matmatronch les plus visit&eacute;es", $req, "stats.php",
                       "id_utilisateur",
                       array("=num" => "N°",
                             "nom_utilisateur"=>utf8_encode("Nom & Prénom"),
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
         ),true);

$req = new requete($site->db,"SELECT `utl_etu`.`id_utilisateur`, `utl_etu`.`visites`, ".
                             "CONCAT(`utilisateurs`.`nom_utl`,' ',`utilisateurs`.`prenom_utl`) as `nom_utilisateur` ".
                             "FROM `utl_etu` ".
                             "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`utl_etu`.`id_utilisateur` ".
                             "WHERE `utilisateurs`.`utbm_utl`='1' AND `utilisateurs`.`sexe_utl`='2' ORDER BY `utl_etu`.`visites` DESC LIMIT 0, 10");
$cts->add(new sqltable("top_full",
                       "Top 10 des fiches matmatronch f&eacute;minines les plus visit&eacute;es", $req, "stats.php",
                       "id_utilisateur",
                       array("=num" => "N°",
                             "nom_utilisateur"=>utf8_encode("Nom & Prénom"),
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
         ),true);

$req = new requete($site->db,"SELECT `utl_etu`.`id_utilisateur`, `utl_etu`.`visites`, ".
                             "CONCAT(`utilisateurs`.`nom_utl`,' ',`utilisateurs`.`prenom_utl`) as `nom_utilisateur` ".
                             "FROM `utl_etu` ".
                             "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`utl_etu`.`id_utilisateur` ".
                             "WHERE `utilisateurs`.`utbm_utl`='1' AND `utilisateurs`.`sexe_utl`='1' ORDER BY `utl_etu`.`visites` DESC LIMIT 0, 10");
$cts->add(new sqltable("top_full",
                       "Top 10 des fiches matmatronch masculines les plus visit&eacute;es", $req, "stats.php",
                       "id_utilisateur",
                       array("=num" => "N°",
                             "nom_utilisateur"=>utf8_encode("Nom & Prénom"),
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
         ),true);


$site->add_contents($cts);

$site->end_page ();

?>
