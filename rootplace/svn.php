<?php

/* Copyright 2007
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/svn.inc.php");


$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);


/*
if( isset($_REQUEST["action"]) && $_REQUEST["action"]=="adduser" && !empty($_REQUEST["login"]) && !empty($_REQUEST["pass"]) && !empty($_REQUEST["id_utilisateur"]))
{
  $req = new requete($site->db,"SELECT `id_utilisateur` FROM `svn_login` WHERE `login_svn` = '".$_REQUEST["login"]."'");
  if($req->lines==0)
  {
    new delete($site->dbrw,"svn_login",array("id_utilisateur"=>$_REQUEST["id_utilisateur"]));
    new insert($site->dbrw,"svn_login",array("id_utilisateur"=>$_REQUEST["id_utilisateur"],"login_svn"=>$_REQUEST["login"]));
    exec("/usr/bin/htpasswd -sb ".SVN_PATH.PASSWORDFILE." ".$_REQUEST["login"]." ".$_REQUEST["pass"]);
  }
  else
  {
    list($id_user)=$req->get_row();
    if($_REQUEST["id_utilisateur"] == $id_user)
    {
      exec("/usr/bin/htpasswd -sb ".SVN_PATH.PASSWORDFILE." ".$_REQUEST["login"]." ".$_REQUEST["pass"]);
    }
  }
}*/


$tabs = array(array("","rootplace/svn.php","Depots"),array("user","rootplace/svn.php?view=user","Utilisateurs"));
$site->start_page("none","Administration / SVN");
$cts = new contents("<a href=\"./\">Administration</a> / SVN");
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if(isset($_REQUEST["id_depot"]))
{
  $svn = new svn_depot($site->db,$site->dbrw);
  if ( !$svn->load_by_id($_REQUEST["id_depot"]) )
  {
    unset($_REQUEST);
    $site->error_not_found("depot svn");
  }
    
  if(isset($_REQUEST["action"]) )
  {
    if( isset($_REQUEST["mode"]) && $_REQUEST["mode"]=="user")
    {
      $user = new utilisateur($site->db,$site->dbrw);
      /*$user->load_by_id($_REQUEST["id_utilisateur"]);
      if ( !$user->is_valid() )
        $site->error_not_found("matmatronch");*/
    }
      
    if ( $_REQUEST["action"].$_REQUEST["mode"] == "edituser" )
    {
      if( isset($_REQUEST["commit"]) && in_array($_REQUEST["right"],$svn->valid_rights))
      {
        $user->load_by_id($_REQUEST["id_utilisateur"]);
        if ( $user->is_valid() )
        {
          $svn->del_user_access($user);
          $svn->update_user_access($user,$_REQUEST["right"]);
          unset($_REQUEST["action"]);
        }
      }
    }
    elseif( $_REQUEST["action"].$_REQUEST["mode"] == "deleteuser" )
    {
      $user->load_by_id($_REQUEST["id_utilisateur"]);
      if ( $user->is_valid() )
      {
        $svn->del_user_access($user);
      }
    }
    elseif($_REQUEST["action"].$_REQUEST["mode"] == "adduser" )
    {
      if( isset($_REQUEST["commit"]) && isset($_REQUEST["right"]) )
      {
        $user->load_by_id($_REQUEST["id_utilisateur"]);
        if ( $user->is_valid() && in_array($_REQUEST["right"],$svn->valid_rights) )
          if ( !empty($site->user->alias) && preg_match("#^([a-z0-9][a-z0-9\-\._]+)$#i",$site->user->alias) )
            $svn->add_user_access($user,$_REQUEST["right"]);
        unset($_REQUEST["action"]);
      }
    }
    elseif($_REQUEST["action"] == edit)
    {
      if( in_array($_REQUEST["type"],$svn->type_depot) )
        $svn->change_repo_type($_REQUEST["type"]);
    }
  }

  if($svn)
  {    
    if ( isset($_REQUEST["action"] && isset($_REQUEST["mode"] && $_REQUEST["action"].$_REQUEST["mode"] == "edituser" )
    {
      $req = new requete($site->db,"SELECT `right` FROM `svn_member_depot` WHERE `id_depot`='".$svn->id."' AND `id_utilisateur`='".$user->id."'");
      if($req->lines==1)
        list($right)=$req->get_row();
      else
        $right="";
      $frm = new form("changeuser","svn.php?id_depot=".$svn->id,false,"post","Modification des droits de ".$user->prenom." ".$user->nom." :");
      $frm->add_hidden("action","edit");
      $frm->add_hidden("mode","user");
      $frm->add_hidden("commit","valid");
      $frm->add_hidden("id_utilisateur",$user->id);
      $frm->add_select_field("right","Droits",array(""=>"","r"=>"Lecture","rw"=>"Ecriture"),$right);
      $frm->add_submit("valid","Valider");
      $cts->add($frm,true);
    }
    else
    {
      $frm = new form("adduser", "svn.php?id_depot=".$svn->id,false,"post","Ajouter un utilisateur");
      $frm->add_hidden("action","add");
      $frm->add_hidden("mode","user");
      $frm->add_hidden("commit","valid");
      $frm->add_user_fieldv2("id_utilisateur","Utilisateur :");
      $frm->add_select_field("right","Droits",array(""=>"","r"=>"Lecture","rw"=>"Ecriture"));
      $frm->add_submit("valid","Valider");
      $cts->add($frm,true);
    }
    
    $cts2 = new contents("Information sur le dépot");
    $cts2->add_paragraph("<b>Dépot : ".$svn->nom."</b><br />type : ".$svn->type);
    $req2 = new requete($site->db,"SELECT * FROM `svn_member_depot` WHERE `id_depot`='".$svn->id."'");
    $cts2->add(new sqltable("svn_member_depot",
                           "Membres du dépot",
                           $req2,
                           "svn.php?id_depot=".$svn->id."&mode=user",
                           "id_utilisateur",
                           array("id_utilisateur"=>"id_utilisateur","right"=>"Droits"),
                           array("edit"=>"Modifier","delete"=>"Enlever"),
                           array(),
                           array()
                          ));
    $cts->add($cts2);
    
    $frm = new form("editdepot", "svn.php?id_depot=".$svn->id,false,"post","Modifier le dépot");
    $frm->add_hidden("action","edit");
    $frm->add_select_field("type","Droits",array("public"=>"Publique","private"=>"Privé","aeinfo"=>"Équipe info"),$svn->type);
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
  else
  {
    $req = new requete($site->db,"SELECT * FROM `svn_depot`");
    $cts->add(new sqltable("svn_private",
                           "Liste des SVN",
                           $req,
                           "svn.php",
                           "id_depot",
                           array("nom"=>"Nom","type"=>"Type"),
                           array("detail"=>"Détail"),
                           array(),
                           array()
                         ));
  }
}
else
{
  $req = new requete($site->db,"SELECT * FROM `svn_depot`");
  $cts->add(new sqltable("svn_private",
                         "Liste des SVN",
                         $req,
                         "svn.php",
                         "id_depot",
                         array("nom"=>"Nom","type"=>"Type"),
                         array("detail"=>"Détail"),
                         array(),
                         array()
                        ));
}


$site->add_contents($cts);

$site->end_page();

?>
