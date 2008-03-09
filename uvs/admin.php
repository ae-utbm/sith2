<?php
  /** @file
   *
   * @brief Page d'administration de la partie pédagogique du site de
   * l'AE.
   * Cette page a pour vocation :
   *
   * - De modérer les commentaires jugés abusifs et/out marqués comme
   *   supprimés
   * - De modifier les séances de l'emploi du temps
   * - Autres actions relatives à l'administration (à définir).
   *
   */

  /* Copyright 2008
   * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
   * along with this program; if not, write to the Free Sofware
   * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
   * 02111-1307, USA.
   */

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/uv.inc.php");


$site = new site();
$site->add_box("uvsmenu", get_uvsmenu_box() );
$site->set_side_boxes("left",array("uvsmenu", "connexion"));

$site->start_page("services", "AE - Pédagogie - Modération");

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";
$path .= "/" . " Accueil";
$cts = new contents($path);


$cts->add_paragraph("Modération de la partie pédagogie");

// vérification d'usage

/** @todo : selon Zoror, un groupe spécifique à la modération de la
 * partie pédagogie serait pertinant. Je (pedrov) ne prends pas de
 * décision la dessus.
 */
if (! $site->is_in_group("gestion_ae"))
  {
    $site->error_forbidden();
  }


require_once($topdir . "include/cts/uvcomment.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

// page selon la "subsection"
// modification des séances
if ($_REQUEST['sub'] == 'modseance')
  {

    // formulaire posté
    if (isset($_REQUEST['modsubmit']))
      {
        $cts->add_paragraph("<pre>" . print_r($_REQUEST, true) . "</pre>");
      }

    // on sait ce qu'on doit modifier
    if (isset($_REQUEST['id_seance']))
      {
        $idseance = intval($_REQUEST['id_seance']);
        $req = "SELECT * FROM `edu_uv_groupe` WHERE id_uv_groupe = $idseance";
        if ($req->lines != 1)
          {
            $cts->add_paragraph("<b>Erreur : séance introuvable.</b>");
          }
        else
          {
            $res = $req->get_row();

            $frm = new form('modseance', './admin.php?sub=modseance', true);
            $frm->add_select_field('mod_typegrp', 'Type de séance',
                                   array('C' => 'cours', "TD" => "TD", "TP" => "TP"),
                                   $res['type_grp'], "", true);
            $frm->add_text_field('mod_numgrp', 'Numéro de groupe',
                                 true, $res['numero_grp']);

            $frm->add_text_field('mod_hdebgrp', 'Heure de début',
                                 true, $res['heure_debut_grp']);

            $frm->add_text_field('mod_hfingrp', 'Heure de fin',
                                 true, $res['heure_fin_grp']);

            $frm->add_select_field('mod_jourgrp', 'Jour', $jour);
            $frm->add_select_field('modfreqgrp', 'Fréquence',
                                   array('1' => 'Hebdomadaire', '2' => 'Bimensuelle'),
                                   $res['frequence_grp'], "", true);

            $frm->add_text_field('mod_sallegrrp', 'Salle',
                                 true, $res['salle_grp']);
            $frm->add_entity_smartselect('mod_lieu', 'Lieu', new lieu($site->db), false, true);
            $frm->add_submit('modsubmit', 'Modifier');
            $cts->add($frm);
          }

      }
    // sinon, il faut chercher
    /**
     * @todo : implémenter une recherche par code d'UV ? (autres, des idées ?)
     */
    else
      {
        $cts->add_title(1, "Modification des séances horaires");
        $cts->add_paragraph("Veuillez entrer l'identifiant de séance qui vous a été ".
                            "communiqué dans le formulaire ci-dessous.");

        $frm = new form('searchseance', './admin.php?sub=modseance', true);
        $frm->add_text_field('id_seance', 'Identifiant de séance', true);
        $frm->add_submit('searchseance_sbmt', 'Rechercher');
        $cts->add($frm);
      }
  }
// modération des commentaires
else if ($_REQUEST['sub'] == 'modcomments')
  {


  }



$cts->add_title(1, "Modération");

$cts->add_paragraph("Cette partie du site est réservée à la modération ".
                    "de la partiepédagogie. Elle vous permet de modifier".
                    " une séance horaire d'UV qui n'aurait pas été saisie".
                    " correctement par un utilisateur, de modérer les ".
                    "commentaires jugés abusifs et/ou supprimés.");

$lst = array("<a href=\"./admin.php?&sub=modseance\">Modification des séances</a>",
             "<a href=\"./admin.php?&sub=modcomments\">Modération des commentaires</a>");

$cts->add(new itemlist("actions", false, $lst));

$site->add_contents($cts);

$site->end_page();


?>