<?php

/* Copyright 2008
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

$topdir='../../';
require_once($topdir .'include/site.inc.php');
require_once($topdir .'include/cts/sqltable.inc.php');
require_once($topdir .'include/entities/forum.inc.php');
$site = new site();

if ( !$site->user->is_in_group('root')
     && !$site->user->is_in_group('moderateur_forum')
   )
  $site->error_forbidden('none','group',7);

$site->start_page('none','Administration du forum');
$cts = new contents("Administration");
$_REQUEST['view']='updforums';
$tabs = array(array('','forum2/admin/index.php','Accueil'),
              array('users','forum2/admin/users.php','Utilisateurs'),
              array('lstforums','forum2/admin/list_forum.php','Liste des forums'),
              array('addforums','forum2/admin/add_forum.php','Ajout de forum'),
              array('updforums','forum2/admin/update_forum.php','Modifier un forum'),
              array('movforums','forum2/admin/move_forum.php','Déplacer un forum')
             );
$cts->add(new tabshead($tabs,$_REQUEST['view']));

$forum = new forum($site->db,$site->dbrw);

if(isset($_REQUEST['id_forum']))
  $forum->load_by_id($_REQUEST["id_forum"]);

if(!$forum->is_valid())
{
  $req = new requete($site->db,
                     'SELECT `forum1`.`id_forum`'.
                     ', `forum1`.`titre_forum` as `admin_forum`'.
                     ', `forum2`.`id_forum` as `id_forum_parent` '.
                     'FROM `frm_forum` as `forum1` '.
                     'LEFT JOIN `frm_forum`as `forum2` '.
                     'ON `forum1`.`id_forum_parent`=`forum2`.`id_forum` '.
                     'ORDER BY `forum2`.`id_forum`, `forum1`.`titre_forum`');
  $cts->add(new treects("Forums",
                        $req,
                        0,
                        "id_forum",
                        "id_forum_parent",
                        "admin_forum",
                        ));
}
else
{
  if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
  {
    
  }
}
$site->add_contents($cts);
$site->end_page();

?>
