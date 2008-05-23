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

$topdir='../';

require_once($topdir. 'include/site.inc.php');
require_once($topdir . 'include/cts/sqltable.inc.php');
require_once($topdir. "include/cts/tree.inc.php");

$site = new site ();


if ( !$site->user->is_in_group('root')  && !$site->user->is_in_group('moderateur_forum') )
  $site->error_forbidden('none','group',7);

$site->start_page('none','Administration du forum');
$cts = new contents('Administration');

$tabs = array(array('','forum2/admin.php','Accueil'),
              array('add','forum2/admin.php?view=forums','Liste des forums'),
              array('users','forum2/admin.php?view=users','Bans'));
$cts->add(new tabshead($tabs,$_REQUEST['view']));

if($_REQUEST['view']=='forums')
{
  $req = new requete($site->db,
                     'SELECT `forum1`.`titre_forum` as `admin_forum`'.
                     ', `forum2`.`id_forum` as `id_forum_parent` '.
                     'FROM `frm_forum` as `forum1` '.
                     'LEFT JOIN `frm_forum`as `forum2` ON `forum1`.`id_forum_parent`=`forum2`.`id_forum` '.
                     'ORDER BY `forum2`.`id_forum`, `forum1`.`titre_forum`');
  $cts->add(new treects ( "Forums", $req, 0, "id_forum", "id_forum_parent", "admin_forum" ));
}
elseif($_REQUEST['view']=='users')
{
  if(isset($_REQUEST['action']))
  {
    $user = new utilisateur($site->db,$site->dbrw);
    if ( $_REQUEST['action']=='ban' )
    {
      $user->load_by_id($_REQUEST['id_utilisateur']);
      if( !is_null($user->id) )
      {
        $site->log('Ajout d\'un utilisateur au groupe ban_forum','Ajout de l\'utilisateur '.$user->nom.' '.$user->prenom.' (id : '.$user->id.') au groupe ban_forum (id : 39)','Groupes',$site->user->id);
        $user->add_to_group(39);
        $cts->add_paragraph('L\'utilisateur a bien été banni du forum');
      }
    }
    elseif( $_REQUEST['action']=='unban' )
    {
      $user->load_by_id($_REQUEST['id_utilisateur']);
      if(!is_null($user->id))
      {
        $site->log('Retrait d\'un utilisateur du groupe ban_forum','Retrait de l\'utilisateur '.$user->nom.' '.$user->prenom.' (id : '.$user->id.') du groupe ban_forum (id : 39)','Groupes',$site->user->id);
        $user->remove_from_group(39);
        $cts->add_paragraph('L\'utilisateur a bien été banni du forum');
      }
    }
    elseif( $_REQUEST['action']=='unbans' )
    {
      $i=0;
      foreach($_REQUEST['id_utilisateurs'] as $id_utilisateur )
      {
        $user->load_by_id($id_utilisateur);
        if(!is_null($user->id))
        {
          $site->log('Ajout d\'un utilisateur au groupe ban_forum','Ajout de l\'utilisateur '.$user->nom.' '.$user->prenom.' (id : '.$user->id.') au groupe ban_forum (id : 39)','Groupes',$site->user->id);
          $user->remove_from_group(39);
          $i++;
        }
      }
      $cts->add_paragraph($i.' utilisateurs ne sont plus bannis du forum.');
    }
  }
  $frm = new form('add','admin.php?view=users',false,'POST','Bannir un utilisateur');
  $frm->add_hidden('action','ban');
  $frm->add_user_fieldv2('id_utilisateur','Utilisateur');
  $frm->add_submit('valid','Ajouter');
  $cts->add($frm,true);
  $sql='SELECT `utilisateurs`.`id_utilisateur`, '.
       'CONCAT(`utilisateurs`.`prenom_utl`,\' \',`utilisateurs`.`nom_utl`) as `nom_utilisateur` '.
       'FROM `utl_groupe`, `utilisateurs` '.
       'WHERE `utilisateurs`.`id_utilisateur` = `utl_groupe`.`id_utilisateur` '.
       'AND `utl_groupe`.`id_groupe` = 39 '.
       'ORDER BY `utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl`';
  $req = new requete($site->db,$sql);
  $tbl = new sqltable('bannis',
                      'Utilisateurs bannis du forum',
                      $req,
                      'admin.php?view=users',
                      'id_utilisateur',
                      array('nom_utilisateur'=>'Utilisateur'),
                      array('unban'=>'Enlever le ban'),
                      array('unbans'=>'Enlever le ban'));
  $cts->add($tbl);
}
else
{
}

$site->add_contents($cts);

$site->end_page();

?>
