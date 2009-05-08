<?php
/*
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *
 * Copyright 2009
 * - Simon lopez < simon dot lopez at ayolo dot org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

require_once("include/site.inc.php");
require_once("include/blog.inc.php");

$blog = new blog($site->db,$site->dbrw);

if(defined('CMS_ALTERNATE'))
  $blog->load($site->asso,CMS_ALTERNATE);
else
  $blog->load($site->asso);
if(!$blog->is_valid())
{
  header("Location: ".$site->pubUrl);
  exit();
}

$site->start_page ( CMS_PREFIX."blog", "Blog" );
$cts = new contents("un Blog averti en vaut deux...");
if ( $blog->is_writer($site->user) )
{
  $tabs = array(
          array("","blog.php","Le blog"),
          array("blogueur","blog.php?view=bloguer", "Espace blogueur"));
  if( $site->is_user_admin() )
    $tabs[]=array("admin","blog.php?view=admin","Administration");
  $cts->add(new tabshead($tabs,$_REQUEST["view"]));


  if( isset($_REQUEST['view']) )
  {
    if($_REQUEST['view']=='admin')
    {
      /* cas simples */
      if ( $_REQUEST["action"] == "delwriter" )
        if(isset($_REQUEST['id_utilisateur']) )
          $blog->del_writer(new utilisateur($qite->db,null,$_REQUEST['id_utilisateur']));
      elseif( $_REQUEST["action"] == "addwriter" )
        if(isset($_REQUEST['id_utilisateur']) )
          $blog->add_writer(new utilisateur($qite->db,null,$_REQUEST['id_utilisateur']));
      elseif ( $_REQUEST["action"] == "delcat" )
        if(isset($_REQUEST['id_cat']) )
          $blog->del_cat($_REQUEST['id_cat']);
      elseif( $_REQUEST["action"] == "addcat" )
        if(isset($_REQUEST['cat_name']) )
          $blog->add_cat($_REQUEST['cat_name']);
      /* cas multiples */
      elseif ( $_REQUEST["action"] == "delwriters"
              && is_array($_REQUEST["id_utilisateurs"])
              && !empty($_REQUEST["id_utilisateurs"]) )
        foreach($_REQUEST["id_utilisateurs"] as $id )
          $blog->del_writer(new utilisateur($qite->db,null,$id));
      elseif ( $_REQUEST["action"] == "delcats"
              && is_array($_REQUEST["id_cats"])
              && !empty($_REQUEST["id_cats"]) )
        foreach($_REQUEST["id_cats"] as $id )
          $blog->del_cat($id);

      $site->add_contents($cts);
      $site->end_page();
      exit();
    }
    if($_REQUEST['view']=='bloguer')
    {
      $site->add_contents($cts);
      $site->end_page();
      exit();
    }
  }
}

if(isset($_REQUEST['id_entry']))
{
  $cts->add($blog->get_cts_entry($_REQUEST['id_entry'],$site->user));
  $site->add_contents($cts);
  $site->end_page();
  exit();
}

if(isset($_REQUEST['id_cat']) && $blog->is_cat($_REQUEST['id_cat']))
{
  $page=0;
  if(isset($_REQUEST['id_page']))
    $page=intval($_REQUEST['id_page']);
  $cts->add($blog->get_cts_cat($_REQUEST['id_cat'],$page));
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
$page=0;
if(isset($_REQUEST['id_page']))
  $page=intval($_REQUEST['id_page']);
$cts->add($blog->get_cts($page));
$site->add_contents($cts);
$site->end_page();

?>
