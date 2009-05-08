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
if( $site->is_user_admin() )
  $blog = new blog($site->db,$site->dbrw);
else
  $blog = new blog($site->db);
if(defined('CMS_ALTERNATE'))
  $blog->load($site->asso,CMS_ALTERNATE);
else
  $blog->load($site->asso);
if(!$blog->is_valid())
{
  header("Location: ".$site->pubUrl);
  exit();
}

if ( $blog->is_writer($site->user) )
{

}

if(isset($_REQUEST['id_entry']))
{
  $site->add_contents($blog->get_cts_entry($_REQUEST['id_entry'],$site->user));
  $site->end_page();
}

if(isset($_REQUEST['id_cat']) && $blog->is_cat($_REQUEST['id_cat']))
{
  $page=0;
  if(isset($_REQUEST['id_page']))
    $page=intval($_REQUEST['id_page']);
  $site->add_contents($blog->get_cts_cat($_REQUEST['id_cat'],$page));
  $site->end_page();
}
$page=0;
if(isset($_REQUEST['id_page']))
  $page=intval($_REQUEST['id_page']);
$site->add_contents($blog->get_cts($page));
$site->end_page();

?>
