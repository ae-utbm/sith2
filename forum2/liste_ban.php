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

$can_admin = ( $site->user->is_in_group("root") || $site->user->is_in_group("moderateur_forum") );

if ( !$site->user->is_in_group("moderateur_forum") )
  $site->error_forbidden("none","group",39);


$site->start_page("none","Administration des bans du forum");



/* annulation ban des utilisateurs coches dans la liste */
if ( $_REQUEST["action"]=="delete" && $can_admin)
{
  $cts->add_title(2,"Annulation ban");

  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);

  $nb = 0;
  foreach($_REQUEST["id_utlban"] as $id_utilisateur )
  {
    $user->load_by_id($id_utilisateur);
    if(!is_null($user->id) && $user->is_in_group("ban_forum")){
      $user->remove_from_group(39); // groupe ban_forum
      $nb = $nb + 1;
    }
  }

  if($nb == 0){
    $cts->add_paragraph("Aucun utilisateur n'a &eacute;t&eacute; affect&eacute; par l'annulation de ban du forum.");
  }elseif($nb == 1){
    $cts->add_paragraph($nb." utilisateur n'est maintenant plus banni du forum.");
  }else{
    $cts->add_paragraph($nb." utilisateurs ne sont plus bannis du forum.");
  }

}elseif ( $_REQUEST["action"]=="unban" && $can_admin && $_REQUEST["id_utilisateur"])
{

  $cts->add_title(2,"Annulation ban");
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  if(!is_null($user->id) && $user->is_in_group("ban_forum")){
    $user->remove_from_group(39); // groupe ban_forum
    $cts->add_paragraph("Un utilisateur n'est maintenant plus banni du forum.");
  }else{
    $cts->add_paragraph("Aucun utilisateur n'a &eacute;t&eacute; affect&eacute; par l'annulation de ban du forum.");
  }

}elseif ( $_REQUEST["action"]=="ban" && $can_admin && $_REQUEST["id_utilisateur"])
{
  $cts->add_title(2,"Ban forum");
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  if( $user->id != null && !$user->is_in_group("ban_forum")){
    $user->add_to_group(39); // groupe ban_forum
    $cts->add_paragraph("L'utilisateur &agrave; bien &eacute;t&eacute; banni du forum");
  }else{
  $cts->add_paragraph("Un problème est survenu : l'utilisateur n'a pas pu &ecirc;tre banni du forum car il est d&eacute;j&agrave; banni !");
  }
}



$req = new requete($site->db,
    "SELECT `utilisateurs`.`id_utilisateur`, " .
    "CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur` " .
    "FROM `utl_groupe`, `utilisateurs` ".
    "WHERE `utilisateurs`.`id_utilisateur` = `utl_groupe`.`id_utilisateur` ".
    "AND `utl_groupe`.`id_groupe` = 39 ". 
    "ORDER BY `utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl` ");
		
  $tbl = new sqltable(
    "listban", 
    "Utilisateurs bannis du forum",
    $req,
    "liste_ban.php", 
    "id_utlban", 
    array("nom_utilisateur"=>"Utilisateur"), 
    $can_admin?array("unban"=>"Enlever le ban"):array(), 
    $can_admin?array("unban"=>"Enlever le ban"):array()

    );

$cts->add($tbl,true);

  if ( $can_admin )
  {
    $frm = new form("add","liste_ban.php",false,"POST","Ajouter un ban utilisateur");
    $frm->add_hidden("action","ban");
    if ( $ErreurAdd )
      $frm->error($ErreurAdd);
    $frm->add_user_fieldv2("id_utilisateur","Utilisateur");
    $frm->add_submit("valid","Ajouter");
    $cts->add($frm,true);
}



$cts->add_title(2,"Administration du forum");
$lst = new itemlist();
$lst->add("<a href=\"new.php\">Ajouter un sous forum</a>");
$lst->add("<a href=\"liste_ban.php\">Afficher les utilisateurs bannis du forum</a>");
$lst->add("<a href=\"liste.php\">Afficher les forums</a>");

$cts->add($lst);


$site->add_contents($cts);
$site->end_page();

?>
