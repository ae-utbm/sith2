<?php
/**
 * @brief Admin de la boutique utbm
 *
 */

/* Copyright 2008
 *
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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

$topdir="../";

require_once("include/boutique.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();


$user = new utilisateur($site->db,$site->dbrw);
if ( isset($_REQUEST["id_utilisateur"]) )
  $user->load_by_id($_REQUEST["id_utilisateur"]);
$site->start_page("services","Administration");
if( $user->is_valid() && $user->type=='srv')
{
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin_utl.php\">Services</a> / Service");
  $cts->add_title(2,$user->get_display_name());

  if($_REQUEST['action']=='centrecout')
  {
    $_cts = new contents("Centre de coût");
    if(isset($_REQUEST['nom_centre_cout']) && !empty($_REQUEST['nom_centre_cout']))
      $req = new insert($site->dbrw,'boutiqueut_centre_cout',array('id_utilisateur'=>$user->id,'centre_cout'=>$_REQUEST['nom_centre_cout']));

    $frm = new form('centrecout','admin_utl.php');
    $frm->add_hidden('id_utilisateur',$user->id);
    $frm->add_hidden('action','centrecout');
    $frm->add_text_field('nom_centre_cout','Centre de coût');
    $frm->add_submit('submit','Ajouter');
    $_cts->add($frm);
    $req = new requete($site->db,'SELECT * FROM boutiqueut_centre_cout WHERE id_utilisateur='.$user->id);
    $_cts->add(new sqltable("ctcouts",
          null,
          $req,
          "admin_utl.php",
          "id_utilisateur",
          array("centre_cout"=>"Centre de coût"),
          array(),
          array(),
          array(),
          true,
          false));
    //on ajoute un centre de cout
  }
  elseif($_REQUEST['action']=='changemdp')
  {
    //on change et on mail
    $_cts=new contents("Mot de passe réinitialisé.");
  }
  elseif($_REQUEST['action']=='centrefinancier')
  {
     $_cts=new contents("Centre financier");
     if(isset($_REQUEST['nom_centre_financier']) && !empty($_REQUEST['nom_centre_financier']))
     {
       $req = new requete($site->db,'SELECT * FROM boutiqueut_service_utl WHERE id_utilisateur='.$user->id);
       if($req->lines==1)
         $req = new update($site->dbrw,'boutiqueut_service_utl',array('centre_financier'=>$_REQUEST['nom_centre_financier']),array('id_utilisateur'=>$user->id));
       else
         $req = new insert($site->dbrw,'boutiqueut_service_utl',array('id_utilisateur'=>$user->id,'centre_financier'=>$_REQUEST['nom_centre_financier']));
     }
     $frm = new form('centrefinan','admin_utl.php');
     $frm->add_hidden('id_utilisateur',$user->id);
     $frm->add_hidden('action','centrefinancier');
     $centre='';
     $req = new requete($site->db,'SELECT centre_financier FROM boutiqueut_service_utl WHERE id_utilisateur='.$user->id);
     if($req->lines==1)
       list($centre)=$req->get_row();
     $frm->add_text_field('nom_centre_cout','Centre de coût',$centre);
     $frm->add_submit('submit','Ajouter');
     $_cts->add($frm);
  }

  if(isset($_cts))
    $cts->add($_cts,true);

  $lst = new itemlist("Actions");
  $lst->add("<a href=\"admin_utl.php?id_utilisateur=".$user->id."&action=centrecout\">Centres de coûts</a>");
  $lst->add("<a href=\"admin_utl.php?id_utilisateur=".$user->id."&action=changemdp\">Changer le mot de passe</a>");
  $lst->add("<a href=\"admin_utl.php?id_utilisateur=".$user->id."&action=centrefinancier\">Centre financier</a>");
  $cts->add($lst,true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
$cts = new contents("<a href=\"admin.php\">Administration</a> / Services");

$req=new requete($site->db,'SELECT id_utilisateur, CONCAT(`prenom_utl`,\' \',`nom_utl`) AS srv FROM utilisateurs WHERE type_utl=\'srv\'');
$cts->add(new sqltable("utls",
          null,
          $req,
          "admin_utl.php",
          "id_utilisateur",
          array("srv"=>"Service"),
          array("edit"=>"Éditer"),
          array(),
          array(),
          true,
          false));
$site->add_contents($cts);
$site->end_page();

?>