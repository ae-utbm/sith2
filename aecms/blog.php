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
          array("blogueur","blog.php?view=bloguer", "Espache blogueur"));
  if( $site->is_user_admin() )
    $tabs[]=array("admin","blog.php?view=admin","Administration");


  $cts->add(new tabshead($tabs,$_REQUEST["view"]));
/*
  $admin = array(0=>CMS_PREFIX."blog",
                 1=>"blog.php",
                 2=>"Blog",
                 4=>array(array('blog.php?bloguer','Bloguer')));
  foreach($site->tab_array as $id=>$tab)
  {
    if($tab[0]==CMS_PREFIX."blog")
    {
      $site->tab_array[$id]=$admin;
      unset($admin);
      break;
    }
  }
  if(isset($admin))
    $site->tab_array[] = $admin;
*/
}

if(isset($_REQUEST['id_entry']))
{
  $cts->add($blog->get_cts_entry($_REQUEST['id_entry'],$site->user));
  $site->add_contents($cts);
  $site->end_page();
}

if(isset($_REQUEST['id_cat']) && $blog->is_cat($_REQUEST['id_cat']))
{
  $page=0;
  if(isset($_REQUEST['id_page']))
    $page=intval($_REQUEST['id_page']);
  $cts->add($blog->get_cts_cat($_REQUEST['id_cat'],$page));
  $site->add_contents($cts);
  $site->end_page();
}
$page=0;
if(isset($_REQUEST['id_page']))
  $page=intval($_REQUEST['id_page']);
$cts->add($blog->get_cts($page));
$site->add_contents($cts);
$site->end_page();

?>
