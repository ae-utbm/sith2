<?php

/* Copyright 2010
 * - Mathieu Briand < briandmathieu at hyprua dot ord >
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

$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$site = new site ();
$asso = new asso($site->db,$site->dbrw);
$asso->load_by_id($_REQUEST["id_asso"]);
$user = new utilisateur($site->db);

if (!$asso->is_valid())
{
  $site->error_not_found();
  exit();
}

if ( !$site->user->is_in_group("gestion_ae")&&!$asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU))
  $site->error_forbidden("presentation","role","bureau");

$site->start_page("presentation", "Inscrits aux mailings-lists " . $asso->nom);

$cts = new contents($asso->nom);

$cts->add(new tabshead($asso->get_tabs($site->user),"mebs"));

$subtabs = array();
$subtabs[] = array("mailing","asso/mailing.php?id_asso=".$asso->id,"Mailing aux membres");
$subtabs[] = array("trombino","asso/membres.php?view=trombino&id_asso=".$asso->id,"Trombino (membres actuels)");
$subtabs[] = array("vcards","asso/membres.php?action=getallvcards&id_asso=".$asso->id,"Télécharger les vCard (membres actuels)");
$subtabs[] = array("anciens","asso/membres.php?view=anciens&id_asso=".$asso->id,"Anciens membres");

$cts->add(new tabshead($subtabs,"mailing","","subtab"));

if ( $asso->is_mailing_allowed() )
{
  if (in_array($_REQUEST['action'], array("subscribe", "subscribes", "unsubscribe", "unsubscribes")))
  {
    $ml = mysql_real_escape_string($_REQUEST['ml']);

    if (substr($ml, 0, strrpos($ml, ".")) == $asso->nom_unix)
    {
      if ($_REQUEST['action'] = "subscribe")
      {
        $email = str_replace(' [dot] ', '.', str_replace(' [at] ', '@', $_REQUEST['email']));
        $email = mysql_real_escape_string($email);
        die($ml.' '.$email);
        $asso->_ml_subscribe ($this->db, $ml, $email);
      }
      if ($_REQUEST['action'] = "subscribes")
      {
        foreach($_REQUEST['emails'] as $email)
        {
          $email = str_replace(' [dot] ', '.', str_replace(' [at] ', '@', $email));
          $email = mysql_real_escape_string($email);
          die($ml.' '.$email);
          $asso->_ml_subscribe ($this->db, $ml, $email);
        }
      }
      if ($_REQUEST['action'] = "unsubscribe")
      {
        $email = str_replace(' [dot] ', '.', str_replace(' [at] ', '@', $_REQUEST['email']));
        $email = mysql_real_escape_string($email);
        die($ml.' '.$email);
        $asso->_ml_unsubscribe ($this->db, $ml, $email);
      }
      if ($_REQUEST['action'] = "unsubscribes")
      {
        print_r($_REQUEST);
        foreach($_REQUEST['emails'] as $email)
        {
          $email = str_replace(' [dot] ', '.', str_replace(' [at] ', '@', $email));
          $email = mysql_real_escape_string($email);
          die($ml.' '.$email);
          $asso->_ml_unsubscribe ($this->db, $ml, $email);
        }
      }
    }
  }

  // On affiche les inscrits à la ml
  foreach($asso->get_exist_ml() as $ml)
  {
    $user_ids = array();
    $asso_user_ids = array();

    // On détermine le groupe d'utilisateurs associés, si possible
    $asso_ml = true;
    if (substr($ml, -7) == ".bureau")
      $role = ROLEASSO_MEMBREBUREAU;
    elseif (substr($ml, -10) == ".benevoles")
      $role = ROLEASSO_MEMBREACTIF;
    elseif (substr($ml, -8) == ".membres")
      $role = false;
    else
      $asso_ml = false;

    // On récupère la liste des membres correspondant
    if ($asso_ml)
    {
      $req = new requete($site->db,
        "SELECT `id_utilisateur` " .
        "FROM `asso_membre` " .
        "WHERE `date_fin` IS NULL " .
        "AND `id_asso`='".$asso->id."' " .
        ($role ? "AND `asso_membre`.`role` >= '".$role."' " : "").
        "ORDER BY `role` DESC, `desc_role`", true);

      while($row = $req->get_row())
        $asso_user_ids[] = $row['id_utilisateur'];
    }

    // On traite les membres réellement inscrits
    foreach($asso->get_subscribed_email($ml) as $email)
    {
      $user->load_by_email($email);

      if ($user->is_valid())
        $user_ids[] = $user->id;

      if (!$user->is_valid())
        $tab_foreign[] = array(
          "email" => str_replace('.',' [dot] ',str_replace('@',' [at] ',$email)),
          "id_utilisateur" => null,
          "nom_utilisateur" => "",
          );
      elseif(!in_array($user->id, $asso_user_ids))
        $tab_nonmember[] = array(
          "email" => str_replace('.',' [dot] ',str_replace('@',' [at] ',$email)),
          "id_utilisateur" => $user->id,
          "nom_utilisateur" => $user->prenom." ".$user->nom,
          );
      else
        $tab_member[] = array(
          "email" => str_replace('.',' [dot] ',str_replace('@',' [at] ',$email)),
          "id_utilisateur" => $user->id,
          "nom_utilisateur" => $user->prenom." ".$user->nom,
          );
    }

    // On récupère la liste des membres non inscrits
    if ($asso_ml)
    {
      $req = new requete($site->db,
        "SELECT `utilisateurs`.`id_utilisateur`, " .
        "CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur` " .
        "FROM `asso_membre` " .
        "INNER JOIN `utilisateurs` USING(`id_utilisateur`) ".
        "WHERE `asso_membre`.`date_fin` IS NULL " .
        "AND `asso_membre`.`id_asso`='".$asso->id."' " .
        ($role ? "AND `asso_membre`.`role` >= '".$role."' " : "").
        "AND `id_utilisateur` NOT IN (".implode(', ', $user_ids).") ".
        "ORDER BY `asso_membre`.`role` DESC, `asso_membre`.`desc_role`,`utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl` ", true);

      while($row = $req->get_row())
        $tab_nonml[] = array(
          "id_utilisateur" => $row['id_utilisateur'],
          "nom_utilisateur" => $row['nom_utilisateur'],
          );
    }

    $cts2 = new contents($ml);

    $cts2->add(new sqltable("mldiff_".$ml, "membres inscrits à la mailing-list",
      $tab_member, "mldiff.php?id_asso=".$asso->id."&ml=".$ml, "id_utilisateur",
      array("nom_utilisateur"=>"Utilisateur", "email"=>"Email"),
      array(), array(), array()
    ), true);

    $cts2->add(new sqltable("mldiff_".$ml, "membres du club non inscrits à la mailing-list",
      $tab_nonml, "mldiff.php?id_asso=".$asso->id."&ml=".$ml, "id_utilisateur",
      array("nom_utilisateur"=>"Utilisateur", "email"=>"Email"),
      array("subscribe"=>"Inscrire à la mailing-list"),
      array("subscribes"=>"Inscrire à la mailing-list"),
      array()
    ), true);

    $cts2->add(new sqltable("mldiff_".$ml, "non membres inscrits à la mailing-list",
      $tab_nonmember, "mldiff.php?id_asso=".$asso->id."&ml=".$ml, "email",
      array("nom_utilisateur"=>"Utilisateur", "email"=>"Email"),
      array("unsubscribe"=>"Désinscrire de la mailing-list"),
      array("unsubscribes"=>"Désinscrire de la mailing-list"),
      array()
    ), true);

    $cts2->add(new sqltable("mldiff_".$ml, "non membres inscrits à la mailing-list et sans compte ae",
      $tab_foreign, "mldiff.php?id_asso=".$asso->id."&ml=".$ml, "email",
      array("email"=>"Email"),
      array("unsubscribe"=>"Désinscrire de la mailing-list"),
      array("unsubscribes"=>"Désinscrire de la mailing-list"),
      array()
    ), true);

    $cts->add($cts2, true);
  }
}

$site->add_contents($cts);
$site->end_page();

?>
