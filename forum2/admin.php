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

$site = new site ();


if ( !$site->user->is_in_group('root')  && !$site->user->is_in_group('moderateur_forum') )
  $site->error_forbidden('none','group',7);

$site->start_page('none','Administration du forum');
$cts = new contents('Administration');

$tabs = array(array('','forum2/admin.php','Acuueil'),
              array('add','forum2/admin.php?view=forums','Liste des forums'),
              array('users','forum2/admin.php?view=users','Bans'));
$cts->add(new tabshead($tabs,$_REQUEST['view']));

if($_REQUEST['view']=='forums')
{
  
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
        $user->add_to_group(39);
        $cts->add_paragraph('L\'utilisateur a bien été banni du forum');
      }
    }
    elseif( $_REQUEST['action']=='unban' )
    {
      $user->load_by_id($_REQUEST['id_utilisateur']);
      if(!is_null($user->id))
      {
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
	  $user->remove_from_group(39);
	  $i++;
	}
      }
      $cts->add_paragraph($nb.' utilisateurs ne sont plus bannis du forum.');
    }
  }
  $frm = new form('add','forum2/admin.php?view=add',false,'POST','Bannir un utilisateur');
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
                      'forum2/admin.php?view=users',
                      'id_utilisateur',
                      array('nom_utilisateur'=>'Utilisateur'),
                      array('unban'=>'Enlever le ban'),
                      array('unbans'=>'Enlever le ban'));
  $cts->add($tbl);
}
else
{
  $cts->add_title(2,'Rechercher');
  $frm = new form('recherche','forum2/admin.php',true,'POST','Recherche');
  $frm->add_radiobox_field('type_recherche',
                           'Recherche d\'un ...',
                            array('sujet'=>'Sujet', 'forum'=>'Forum'),
                           'sujet',
                           false,
                           true);
  $frm->add_text_field('id_recherche', 'Id de l\'objet','');
  $frm->add_submit('recherche','Rechercher');
  $cts->add($frm);

  if(isset($_REQUEST['recherche']) && 
     isset($_REQUEST['id_recherche']) &&
     isset($_REQUEST['type_recherche']))
  {

    if($_REQUEST['type_recherche'] == 'sujet')
    {
      $sql = 'SELECT `frm_sujet`.`id_sujet`,'.
             ' `frm_sujet`.`titre_sujet` ,'.
             ' `frm_forum`.`titre_forum` ,'.
             ' `utilisateurs`.`alias_utl` '.
             'FROM `frm_sujet`, `utilisateurs`, `frm_forum` '.
             'WHERE `id_sujet` = '.$_REQUEST['id_recherche'].' '.
             'AND `frm_sujet`.`id_forum` = `frm_forum`.`id_forum` '.
             'AND `utilisateurs`.`id_utilisateur` = `frm_sujet`.`id_utilisateur` ;';
      $req = new requete($site->db, $sql);

      if( $req->lines == 0 )
        $cts->add_paragraph('Aucun sujet ne correspond &agrave; votre recherche !');
      else
      {
        $tbl = new sqltable(
                'resrecherche', 
                'Résultat de la recherche d\'un sujet',
                $req,
                'index.php', 
                'id_sujet', 
                array('titre_sujet'=>'Titre du sujet',
                      'titre_forum'=>'Forum concerné',
                      'alias_utl'=>'Utilisateur'), 
                array('edit'=>'Editer','delete'=>'Supprimer'),
                array(),
                array()
                );
      }
      $cts->add($tbl,true);

    }
    elseif($_REQUEST['type_recherche'] == 'forum')
    {
      $sql = 'SELECT f1.titre_forum as titre_forum, '.
             'f1.id_forum as id_forum ,'.
             'f1.description_forum as description_forum, '.
             'f2.titre_forum as titre_forum_parent, '.
             'a.nom_asso as nom_asso '.
             'FROM `frm_forum` f2,`frm_forum` f1 '.
             'LEFT OUTER JOIN asso a ON f1.id_asso = a.id_asso '.
             'WHERE f1.id_forum_parent=f2.id_forum '.
             'AND f1.id_forum = '.$_REQUEST['id_recherche'].' ;';
      $req = new requete($site->db, $sql);

      if( $req == null || $req->lines == 0 )
        $cts->add_paragraph('Aucun forum ne correspond &agrave; votre recherche !');
      else
      {
        $tbl = new sqltable(
                'resrecherche', 
                'Résultat de la recherche d\'un forum',
                $req,
                'index.php', 
                'id_forum', 
                array('titre_forum'=>'Titre du forum',
                      'description_forum'=>'Description du forum',
                      'titre_forum_parent'=>'Forum parent concerné',
                      'nom_asso'=>'Association concernée'), 
                array('edit'=>'Editer','delete'=>'Supprimer'),
                array(),
                array()
                );
      }
      $cts->add($tbl,true);
    }
  }
}

$site->add_contents($cts);

$site->end_page();

?>
