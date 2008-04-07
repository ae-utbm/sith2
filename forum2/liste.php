<?php

/* Copyright 2008
 * - Remy BURNEY < rburney <point> utbm <at> gmail <dot> com >
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

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/user.inc.php");
require_once($topdir . "include/entities/utilisateur.inc.php");

$site = new site ();
$cts=new contents();


$can_admin=( $site->user->is_in_group("root") || $site->user->is_in_group("moderateur_forum") );

if ( !$site->user->is_in_group("moderateur_forum") )
  $site->error_forbidden("none","group",39);


$site->start_page("none","Administration des forum");

$req = new requete($site->db,
    "SELECT f1.titre_forum as titre_forum, ".
    "f1.description_forum as description_forum, ".
    "f1.categorie_forum as categorie_forum, ".
    "f2.titre_forum as titre_forum_parent ".
    "FROM `frm_forum` f1,`frm_forum` f2  ".
    "WHERE f1.id_forum_parent=f2.id_forum ".
    "ORDER BY f1.id_forum ");
		
  $tbl = new sqltable(
    "listforum", 
    "Liste des forums",
    $req,
    "liste.php", 
    "id_forum", 
    array("titre_forum"=>"Titre","description_forum"=>"Description","categorie_forum"=>"Cat&eacute;gorie","titre_forum_parent"=>"Forum parent"), 
    array(),
    array(),
    array()
    );

$cts->add($tbl,true);


$cts->add_title(2,"Administration du forum");
$lst = new itemlist();
$lst->add("<a href=\"new.php\">Ajouter un sous forum</a>");
$lst->add("<a href=\"liste_ban.php\">Afficher les utilisateurs bannis du forum</a>");
$lst->add("<a href=\"liste.php\">Afficher les forums</a>");

$cts->add($lst);



$site->add_contents($cts);
$site->end_page();

?>
