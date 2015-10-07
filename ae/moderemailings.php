<?php
/* Copyright 2015
 *
 * - Skia < lordbanana25 AT mailoo DOT org >
 *
 * Ce fichier fait partie du site de l'Association des Ãtudiants de
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
require_once($topdir . "include/entities/mailing.inc.php");

$site = new site ();

if (!$site->user->is_in_group ("moderateur_site"))
  $site->error_forbidden("accueil");

$site->start_page ("accueil", "Modération des mailings");

/*suppression via la sqltable */
if ((isset($_REQUEST['id_mailing']))
    && ($_REQUEST['action'] == "modere"))
{
  $news = new mailing ($site->db, $site->dbrw);
  $id = intval($_REQUEST['id_mailing']);
  $news->load_by_id ($id);
  $news->set_valid ();

  $site->add_contents (new contents("Moderation",
            "<p>Modération eff&eacute;ctu&eacute;e avec succ&egrave;s</p>"));
}

/* Evidemment on pourrait mettre de la moderation massive, mais je ne
 * pense pas que ce soit une super idee concernant la qualité de la
 * modération. C'est pourquoi il n'y a pas de batch action possibles
 * dans les formulaires */

/* presentation des mailings en attente de moderation */

$req = new requete($site->db,"SELECT `nvl_nouvelles`.*,
                                     `asso`.`nom_unix_asso`,
                                     `utilisateurs`.`id_utilisateur`,
                                     CONCAT(`utilisateurs`.`prenom_utl`,
                                            ' ',
                                            `utilisateurs`.`nom_utl`) AS `nom_utilisateur`

                              FROM `nvl_nouvelles`
            LEFT JOIN `asso` ON
                                         `asso`.`id_asso` =
                                         `nvl_nouvelles`.`id_asso`
                              INNER JOIN `utilisateurs` ON
                                         `utilisateurs`.`id_utilisateur` =
                                         `nvl_nouvelles`.`id_utilisateur`

                              WHERE `modere_nvl`='0' ORDER BY `date_nvl`");

$req = new requete($site->db,"SELECT `mailing`.`id_mailing`,
                                     CONCAT(`asso`.`nom_unix_asso`,
                                            IF(`mailing`.`nom` = '',
                                                '',
                                                CONCAT('.',`mailing`.`nom`)),
                                            '@utbm.fr') as address,
                                     `asso`.`nom_asso`
                              FROM `mailing`
                              JOIN `asso`
                              ON `mailing`.`id_asso_parent` = `asso`.`id_asso`
                              WHERE `mailing`.`is_valid` = '0'");

$modhelp = new contents("Mod&eacute;ration des mailing",
      "<p>Sur cette page, vous pouvez mod&eacute;rer ".
      "les mailing</p>");


$tabl = new sqltable ("modere_mailing",
    "Mailings en attente de mod&eacute;ration",
    $req,
    "moderemailings.php",
    "id_mailing",
    array ("address" => "Adresse",
           "nom_asso" => "Association"),
    array ("modere" => "moderer"),
    array (),
    array ());

$modhelp->add ($tabl);
$site->add_contents ($modhelp);

$site->end_page ();

