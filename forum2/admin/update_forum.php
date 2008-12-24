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
require_once($topdir .'include/cts/tree.inc.php');
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

if(isset($_REQUEST['idx_forum']))
  $forum->load_by_id($_REQUEST["idx_forum"]);
elseif(isset($_REQUEST['id_forum']))
  $forum->load_by_id($_REQUEST["id_forum"]);

if(!$forum->is_valid())
{
  $req = new requete($site->db,
                     'SELECT `forum1`.`id_forum` as idx_forum'.
                     ', `forum1`.`titre_forum` as `admin_forum`'.
                     ', `forum2`.`id_forum` as `id_forum_parent` '.
                     'FROM `frm_forum` as `forum1` '.
                     'LEFT JOIN `frm_forum`as `forum2` '.
                     'ON `forum1`.`id_forum_parent`=`forum2`.`id_forum` '.
                     'ORDER BY `forum2`.`id_forum`, `forum1`.`titre_forum`');
  $cts->add(new treects("Forums",
                        $req,
                        0,
                        "idx_forum",
                        "id_forum_parent",
                        "admin_forum"
                        ));
}
else
{
  if(isset($_POST['action']) && $_POST['action']=='update')
  {
    $asso = new asso($site->db);
    $asso->load_by_id($_POST['id_asso']);
    $forum->update(mysql_real_escape_string($_POST['titre']),
                   mysql_real_escape_string($_POST['desc']),
                   mysql_real_escape_string($_POST['cat']),
                   intval($_POST['id_forum_parent']),
                   $asso->id,
                   intval($_POST['ordre']));
    $forum->set_rights($site->user,$_POST['rights'],$_POST['rights_id_group'],$_POST['rights_id_group_admin'],false);
  }
  $frm=new form('updatefrm', '?', true, 'POST', 'Édition');
  $frm->add_hidden("action","update");
  $frm->add_hidden("idx_forum",$forum->id);
  $frm->add_text_field("titre","Titre",$forum->titre,true);
  $frm->add_text_area("desc","Description",$forum->description);
  $frm->add_checbox("categorie","Catégorie",$forum->categorie);
  $frm->add_entity_select("id_forum_parent","Forum Parent", $site->db, "forum", $forum->id_forum_parent,true);
  $frm->add_entity_select("id_asso", "Association", $site->db, "asso",$forum->id_asso,true);
  $frm->add_right_fields($forum,false,$forum->is_admin($site->user));
  $frm->add_text_field("ordre","Ordre", $forum->ordre, true);
  $frm->add_submit("applyedit","Enregistrer");
  $cts->add($frm,true);
}
$site->add_contents($cts);
$site->end_page();

?>
