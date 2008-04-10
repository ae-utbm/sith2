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

$site = new site ();


if ( !$site->user->is_in_group("moderateur_forum") )
  $site->error_forbidden("none","group",7);

	
$site->start_page("none","Administration du forum");

$cts = new contents("Administration");

if(isset($_REQUEST["recherche"]) && 
     isset($_REQUEST["id_recherche"]) &&
     isset($_REQUEST["type_recherche"]))
{

  if($_REQUEST["type_recherche"] == "sujet"){
    $sql = "SELECT `frm_sujet`.`id_sujet`,".
           " `frm_sujet`.`titre_sujet` ,".
           " `frm_forum`.`titre_forum` ,".
           " `utilisateurs`.`alias_utl` ".
           "FROM `frm_sujet`, `utilisateurs`, `frm_forum` ".
           "WHERE `id_sujet` = ".$_REQUEST["id_recherche"]." ".
           "AND `frm_sujet`.`id_forum` = `frm_forum`.`id_forum` ".
           "AND `utilisateurs`.`id_utilisateur` = `frm_sujet`.`id_utilisateur` ;";
    $req = new requete($site->db, $sql);

    if( $req->lines == 0 ){
      $cts->add_paragraph("Aucun sujet ne correspond &agrave; votre recherche !");
    }else{

      $tbl = new sqltable(
              "resrecherche", 
              "Résultat de la recherche d'un sujet",
              $req,
              "liste.php", 
              "id_sujet", 
              array("titre_sujet"=>"Titre du sujet",
                    "titre_forum"=>"Forum concerné",
                    "alias_utl"=>"Utilisateur"), 
              array("edit"=>"Editer","delete"=>"Supprimer"),
              array(),
              array()
              );
    }
    $cts->add($tbl,true);

  }elseif($_REQUEST["type_recherche"] == "forum"){
    $sql = "SELECT f1.titre_forum as titre_forum, ".
           "f1.id_forum as id_forum ,".
           "f1.description_forum as description_forum, ".
           "f2.titre_forum as titre_forum_parent, ".
           "`asso`.nom_asso as nom_asso ".
           "FROM `frm_forum` f1,`frm_forum` f2, `asso`  ".
           "WHERE f1.id_forum_parent=f2.id_forum ".
           "AND `asso`.id_asso = f1.id_asso ".
			     "AND f1.id_forum = ".$_REQUEST["id_recherche"]." ;";
    $req = new requete($site->db, $sql);

    if( $req->lines == 0 ){
      $cts->add_paragraph("Aucun forum ne correspond &agrave; votre recherche !");
    }else{

      $tbl = new sqltable(
              "resrecherche", 
              "Résultat de la recherche d'un forum",
              $req,
              "liste.php", 
              "id_forum", 
              array("titre_forum"=>"Titre du forum",
                    "description_forum"=>"Description du forum",
                    "titre_forum_parent"=>"Forum parent concerné",
                    "nom_asso"=>"Association concernée"), 
              array("edit"=>"Editer","delete"=>"Supprimer"),
              array(),
              array()
              );
    }
    $cts->add($tbl,true);
  }


}



$cts->add_title(2,"Rechercher");
$frm = new form("recherche","admin.php",true,"POST","Recherche");
$frm->add_radiobox_field("type_recherche", 
                         "Recherche d'un ...", 
                         array("sujet"=>"Sujet", "forum"=>"Forum"),
                         "sujet",
                         false,
                         true);

$frm->add_text_field("id_recherche", "Id de l'objet","");
$frm->add_submit("recherche","Rechercher");
$cts->add($frm);


$cts->add_title(2,"Outil");

$lst = new itemlist();
$lst->add("<a href=\"liste.php?action=new\">Ajouter un forum</a>");
$lst->add("<a href=\"liste_ban.php\">Afficher les utilisateurs bannis du forum</a>");
$lst->add("<a href=\"liste.php\">Afficher les forums</a>");

$cts->add($lst);


$site->add_contents($cts);

$site->end_page();

?>
