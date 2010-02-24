<?php

/* Copyright 2010
 * - Mathieu Briand < briandmathieu AT hyprua DOT org >
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


$site = new site ();

if (!$site->user->is_in_group ("gestion_ae"))
  $site->error_forbidden();

if ($_REQUEST['action'] == "pdf")
{
  print_r($_REQUEST);
  exit();
}

$site->start_page("none", "Génération d'un planning de la semaine");

/* Deuxième formulaire : on choisit les évènements
 */
if ($_REQUEST['action'] == "choix_even")
{
  $firstday = $_REQUEST['date'];

  if (date("N", $firstday) != 1)
    $firstday = strtotime("last Monday", $firstday);
  $lastday = strtotime("next Sunday", $firstday);


  $title = "Planning du ".strftime("%A %d %B", $date)." au ".strftime("%A %d %B", $lastday);

  $frm = new form ("createplaning", "planning.php", false, "POST", "Création d'un planning");
  $frm->add_hidden("action", "pdf");
  $frm->add_hidden("date", $firstday);
  $frm->add_text_field("title", "Titre", $title, true);

  /* Pour chaque jour on permet de choisir parmis la liste des nouvelles
  */
  $date = $firstday;
  do
  {
    /* On ne cherche que dans les nouvelles ponctuelles ou répétitives
     * si elles ont commencées avant le jour concerné, elles doivent se finir après 10h00
     */
    $req = new requete($site->db, "
      SELECT id_nouvelle, titre_nvl, date_debut_eve
      FROM `nvl_dates`
      INNER JOIN `nvl_nouvelles` USING (`id_nouvelle`)
      WHERE
        (
          (date_debut_eve > '".date("Y-m-d", $date)." 00:00'
          AND date_debut_eve < '".date("Y-m-d", $date)." 24:00')
        OR
          (date_debut_eve < '".date("Y-m-d", $date)." 00:00'
          AND date_fin_eve > '".date("Y-m-d", $date)." 10:00')
        )
        AND `type_nvl` IN ( 1, 2 )");

    if ($req->lines > 0)
    {
      $subfrm = new subform("createplaning_".date("N", $date),
                            strftime("%A %d %B", $date));
      while($row = $req->get_row())
      {
        $txt = "";
        $time = strtotime($row['date_debut_eve']);
        if ($time > $date)
          $txt .= date("%G:%i", $date).": ";

        $txt .= $row['titre_nvl'];

        $subfrm->add_checkbox("news[".$row['id_nouvelle']."]", $txt, true);
      }

      $frm->addsub($subfrm);
    }

    $date = strtotime("+1 day", $date);
  } while (date("N", $date) != 1);


  /* On affiches les nouvelles longues
  */
  $req = new requete($site->db, "
    SELECT id_nouvelle, titre_nvl, date_debut_eve, date_fin_eve
    FROM `nvl_dates`
    INNER JOIN `nvl_nouvelles` USING (`id_nouvelle`)
    WHERE date_debut_eve < '".date("Y-m-d", $lastday)." 24:00'
      AND date_fin_eve > '".date("Y-m-d", $firstday)." 00:00'
      AND `type_nvl` = 3");

  if ($req->lines > 0)
  {
    $subfrm = new subform("createplaning_sem", "Toute la semaine");
    while($row = $req->get_row())
    {
      $txt = "";
      $time1 = strtotime($row['date_debut_eve']);
      $time2 = strtotime($row['date_fin_eve']);

      if (($time1 > $firstday ) && ($time2 < $lastday ))
        $txt .= "De ".strftime("%A", $time1)." à ".strftime("%A", $time2)." : ";
      elseif ($time1 > $firstday )
        $txt .= "À partir de ".strftime("%A", $time1)." : ";
      elseif ($time2 < $lastday )
        $txt .= "Jusqu'à ".strftime("%A", $time2)." : ";

      $txt .= $row['titre_nvl'];

      $subfrm->add_checkbox("news[".$row['id_nouvelle']."]", $txt, true);
    }

    $frm->addsub($subfrm);
  }


  $frm->add_submit("valid","Générer");

  $site->add_contents ($frm);
}
/* Premier formulaire : on choisit la date du planning
 */
else
{
  $frm = new form ("createplaning","planning.php",false,"POST","Création d'un planning");
  $frm->add_hidden("action","choix_even");
  $frm->add_date_field("date","Semaine concernée", date(), true);

  $frm->add_submit("valid","Choisir les évènements");

  $site->add_contents ($frm);
}

$site->end_page();

?>
