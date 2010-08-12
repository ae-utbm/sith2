<?php
/* Copyright 2006
 * - Pierre Mauduit
 * - Benjamin Collet < bcollet at oxynux dot org>
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
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/files.inc.php");

$site = new site ();

if (!$site->user->is_in_group("moderateur_site"))
  error_403 ();


$site->start_page ("none", "Mod&eacute;ration des fichiers");

/* formulaire de modification poste */
if (isset($_REQUEST['mod_file_submit']))
{
  $file = new file ($site->db, $site->dbrw);
  $file->load_by_id ($_REQUEST['id_fichier']);

  /* verif droits */
  if ((!$site->user->is_in_group("moderateur_site"))
      && ($file->auteur != $site->user->id))
    error_403 ();

  if ((!$site->user->is_in_group("moderateur_site"))
      && ($file->modere == true))
    {
      $site->add_contents(new error("erreur",
            "Ce fichier a d&eacute;j&agrave; &eacute;t&eacute; modere, vous ne ".
            "pouvez donc plus y apporter de modifications"));
      $site->end_page ();
      exit();
    }
  /* droits verifies */
  $ret = $file->modify ($_REQUEST['id_fichier'],
      $_REQUEST['titre'],
      $_REQUEST['cat'],
      $_REQUEST['comment'],
      $_REQUEST['public'] == 1 ? 1 : 0);

  if ($ret == true)
    $site->add_contents (new contents ("Modification  du fichier",
               "<p>Fichier modifie avec succes</p>"));
  else
    $site->add_contents (new error ("Modification du fichier",
            "<p>Une erreur est survenue lors de la modification</p>"));
  $site->end_page();
  exit ();
}


/* site de moderation (si rien dans request) */
if (!isset($_REQUEST['action']))
{
  /* explications */
  $site->add_contents (new contents("Modération des fichiers",
            "<p>Sur cette page, vous ".
            "allez pouvoir modérer les fichiers ".
            "selon votre niveau d'acces<br/><br/>".
            "Les modérateurs du site peuvent ".
            "supprimer tout fichier, moderé ou non; ".
            "les utilisateurs ayant posté un fichier".
            " qui n'a pas encore été moderé peuvent ".
            "le supprimer.</p>"));

  /* l'equipe de moderation doit moderer les fichiers en attente */
  if ($site->user->is_in_group ("moderateur_site"))
    {
      /* selection fichiers non moderes */
      $sql = "SELECT `fichiers`.*
                     , `fichiers_cat`.*
                     , `utilisateurs`.`id_utilisateur`
                     , CONCAT(`utilisateurs`.`prenom_utl`, ' ',
                              `utilisateurs`.`nom_utl`) as nom_utilisateur

              FROM `fichiers`
              INNER JOIN `fichiers_cat` ON `fichiers_cat`.`id_catfch` =
  `fichiers`.`id_catfch`
              LEFT JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur` =
  `fichiers`.`id_utilisateur`
              WHERE `modere_fichier` = 0";

      $req = new requete($site->db, $sql);
      /* liste pour la moderation */
      $tbl = new sqltable("filemod_list",
        "Fichiers en attente de moderation",
        $req,
        "moderefichiers.php?filemod=1",
        "id_fichier",
        array("titre_fichier"=>"Titre",
        "nom_utilisateur"=>"Auteur",
        "mime_fichier"=>"Type",
        "commentaire_fichier"=>"Commentaire"),
        array("done" => "Accepter",
        "delete" => "Supprimer"),
        array("done" => "Accepter",
        "delete" => "Supprimer"),
        array());
      $site->add_contents ($tbl);
    }


  /* liste globale des fichiers selon le niveau d'acces */
  $sql = "SELECT `fichiers`.*, `fichiers_cat`.*  " .
    "FROM `fichiers` " .
    "INNER JOIN `fichiers_cat` ON `fichiers`.`id_catfch` = ".
    "`fichiers_cat`.`id_catfch` ";
  $titre = "Tous les fichiers";

  /* un auteur peut modifier ses fichiers, avant moderation */
  if (!$site->user->is_in_group ("moderateur_site"))
    {
      $sql = $sql ." WHERE `id_utilisateur` = ". $site->user->id .
  " AND `modere_fichier` = 1";
      $titre = "Vos fichiers en attente de moderation";
    }

  $sql .= " ORDER BY `fichiers`.`date_fichier` DESC";


  $req = new requete($site->db, $sql);

  /* liste complete des fichiers */
  $tbl = new sqltable("file_list",
          $titre,
          $req,
          "moderefichiers.php?filelist=1",
          "id_fichier",
          array("titre_fichier"=>"Titre",
          "mime_fichier"=>"Type",
          "commentaire_fichier"=>"Commentaire"),
          array("edit" => "Editer", "delete" => "Supprimer"),
          array("delete" => "Supprimer"),
          array());

  $site->add_contents ($tbl);
}



/* actions sur la page */
else
{
  /* edition */
  if ($_REQUEST['action'] == "edit")
    {
      $file = new file ($site->db, $site->dbrw);
      $file->load_by_id ($_REQUEST['id_fichier']);

      /* verif droits */
      if ((!$site->user->is_in_group("moderateur_site"))
    && ($file->auteur != $site->user->id))
  error_403 ();

      if ((!$site->user->is_in_group("moderateur_site"))
    && ($file->modere == true))
  $site->add_contents(new error("erreur",
              "Ce fichier a d&eacute;j&agrave; &eacute;t&eacute; mod&eacute;r&eacute;, vous ne ".
              "pouvez donc plus y apporter de modifications"));
      /* modification autorisee */
      else
  {
    $mod = new form ("mod_file",
         "moderefichiers.php",
         true,
         "Modification d'un fichier");
    $mod->add_hidden ("id_fichier", $file->id);
    $mod->add_text_field ("titre",
        "titre du fichier",
        $file->titre);
    $mod->add_checkbox ("public",
            "statut public",
            $file->public == 1 ? true : false);
    /* categories */
    $req = new requete ($site->db,
            "SELECT * FROM `fichiers_cat`");
    while ($rs = $req->get_row ())
      $cats[$rs[0]] = $rs[1];

    $mod->add_select_field ("cat",
          "categorie",
          $cats,
          $file->id_cat);
    $mod->add_text_area ("comment",
             "Commentaires",
             $file->comment,
             80,
             5,
             false);
    $mod->add_submit("mod_file_submit",
         "Modifier");
    $site->add_contents ($mod);
  }
    }

  /* suppression */
  if ($_REQUEST['action'] == "delete")
    {
      $file = new file ($site->db, $site->dbrw);
      /* il peut y avoir plusieurs fichiers a supprimer d'un coup */
      if (isset($_REQUEST['id_fichier']))
  $ids[] =  $_REQUEST['id_fichier'];
      else
  $ids = $_REQUEST['id_fichiers'];

      foreach ($ids as $id)
  {
    $file->load_by_id ($id);

    /* verification droits */
    if ((!$site->user->is_in_group("moderateur_site"))
        && ($file->auteur != $site->user->id))
      error_403 ();

    if ((!$site->user->is_in_group("moderateur_site"))
        && ($file->modere == true))
      $site->add_contents(new error("erreur",
            "Ce fichier a d&eacute;j&agrave; &eacute;t&eacute; mod&eacute;r&eacute;, vous ne ".
            "pouvez donc plus le suppimer"));
    else
      {
        $ret = $file->delete_file ();
        if ($ret == true)
    $site->add_contents (new contents ("Suppression du fichier",
               "<p>Fichier supprime avec succes</p>"));
        else
    $site->add_contents (new error ("Suppression du fichier",
            "<p>Une erreur est survenue lors de ".
            "la suppression</p>"));
      }
  }
      $site->end_page();
      exit ();
    }
  /* moderation des fichiers par l'equipe de moderation (premiere liste) */
  if ($_REQUEST['filemod'] == "1")
    {
      $file = new file($site->db, $site->dbrw);
      if (!$site->user->is_in_group ("moderateur_site"))
  error_403 ();
      if (isset($_REQUEST['id_fichier']))
  $ids[] =  $_REQUEST['id_fichier'];
      else
  $ids = $_REQUEST['id_fichiers'];

      foreach ($ids as $id)
  {
    $file->load_by_id($id);
    $title = $file->titre;

    if ($_REQUEST['action'] == "delete")
      {
        $ret = $file->delete_file();

        if ($ret == true)
    $site->add_contents (new contents ("Suppression du fichier",
               "<p>Fichier $title supprime avec succes</p>"));
        else
    $site->add_contents (new error ("Suppression du fichier",
            "<p>Une erreur est survenue lors de ".
            "la suppression de $title</p>"));
      }
    /* accepte en moderation */
    if ($_REQUEST['action'] == "done")
      {
        $ret = $file->modere();
        if ($ret == true)
    $site->add_contents (new contents ("Moderation du fichier",
               "<p>Fichier $title modere avec succes</p>"));
        else
    $site->add_contents (new error ("Modereation du fichier",
            "<p>Une erreur est survenue lors de ".
            "la moderation de $title</p>"));

      }
  }
    }
}


$site->end_page ();

?>
