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

#define("SVN_PATH","/var/lib/svn/");
define("PASSWORDFILE","svn.passwd");
define("SVN_PATH","/var/lib/svn/");
define("PRIVATE_SVN","");
define("PUBLIC_SVN","");


$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
$site = new site ();

if ( !$site->user->is_in_group("root") )
  error_403();

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
}



function private_svn ()
{
  $list = array();

  if ($dh = opendir(SVN_PATH.PRIVATE_SVN))
  {
    while (($file = readdir($dh)) !== false)
    {
      if ( $file != "." && $file != ".." && is_dir(SVN_PATH.PRIVATE_SVN.$file) )
      {
        $list[] = array("name"=>$file );
      }
    }
    closedir($dh);
  }

  return $list;
}

$private = private_svn();
asort($private);


$tabs = array(array("","rootplace/svn.php","Depots"),array("user","rootplace/svn.php?view=user","Utilisateurs"));
$site->start_page("none","Administration");
$cts = new contents("<a href=\"./\">Administration</a> / SVN");
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if ( $_REQUEST["view"]=="user" )
{
  if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete")
  {
    $req = new requete($site->db,"SELECT * FROM `svn_login` WHERE `id_utilisateur`='".$_REQUEST["id_utilisateur"]."'");
    if($req->lines==1)
    {
      list($login,$id)=$req->get_row();
      new delete($site->dbrw,"svn_member_depot",array("svn_login"=>$login));
      new delete($site->dbrw,"svn_login",array("id_utilisateur"=>$_REQUEST["id_utilisateur"]));
    }
  }
  $frm = new form("adduser","svn.php?view=user",false,"post","Créer un user :");
  $frm->add_hidden("action","adduser");
  $frm->add_user_fieldv2("id_utilisateur","Utilisateur");
  $frm->add_text_field("login","Login","",false);
  $frm->add_password_field("pass","Mot de passe","",false);
  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);
  $req = new requete($site->db,"SELECT * FROM `svn_login`");
  $cts->add(new sqltable("svn_login",
                         "Liste des Logins svn",
                         $req,
                         "svn.php?view=user",
                         "id_utilisateur",
                         array("login_svn"=>"Login"),
                         array("delete"=>"Enlever"),
                         array(),
                         array()
                        ));

}

else
{
  if(isset($_REQUEST["id_depot"]))
  {
    if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete")
    {
      if(isset($_REQUEST["svn_login"]))
        new delete($site->dbrw,"svn_member_depot",array("id_depot"=>$_REQUEST["id_depot"],"svn_login"=>$_REQUEST["svn_login"]));
      else
        new delete($site->dbrw,"svn_depot",array("id_depot"=>$_REQUEST["id_depot"]));
    }
    if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "edit")
    {
    }
    $req = new requete($site->db,"SELECT * FROM `svn_depot` WHERE `id_depot`='".$_REQUEST["id_depot"]."'");
    if($req->lines==1)
    {
      list($id,$nom,$type)=$req->get_row();
      $cts->add_paragraph("<b>Dépot : ".$nom."</b><br />type : ".$type);
      $req2 = new requete($site->db,"SELECT * FROM `svn_member_depot` WHERE `id_depot`='".$_REQUEST["id_depot"]."'");
      $cts->add(new sqltable("svn_member_depot",
                             "Membres",
                             $req2,
                             "svn.php?id_depot=".$_REQUEST["id_depot"],
                             "svn_login",
                             array("svn_login"=>"Login","right"=>"Droits"),
                             array("edit"=>"Modifier","delete"=>"Enlever"),
                             array(),
                             array()
                            ));


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

}

$site->add_contents($cts);

$site->end_page();

?>
