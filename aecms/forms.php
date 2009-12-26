<?php
/*
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *
 * Copyright 2010
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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

require_once ('include/site.inc.php');
require_once ('include/form.inc.php');
require_once ($topdir.'include/entities/news.inc.php');

$form = new formulaire ($site->db, $site->dbrw);

if (isset($_REQUEST['id_form']))
  $form->load_by_id ($_REQUEST['id_form']);
else
  $form->load_by_asso ($site->asso->id);

if (!$form->is_valid($site->asso->id))
  $site->error_not_found ('Formulaire');

$Erreur = false;

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'addentry') {
  $Erreur = $form->validate_and_post ();

  if ($Erreur == false) {
    $site->start_page (CMS_PREFIX.'form', 'Participation enregistrée');

    $cts = new contents();

    $cts->add_title (2, 'Merci de votre participation à : '.$form->name);
    $cts->add_paragraph ($form->success_text);

    $site->add_contents ($cts);
    $site->end_page ();

    exit(0);
  }
}

$frm = $form->get_form ('addentry', 'forms.php', $Erreur);

if ($frm == false)
  $site->fatal_partial (CMS_PREFIX.'form');

$site->start_page (CMS_PREFIX.'form', $form->name);

$cts = new contents();
$cts->add_title(2, $form->name);

$cts->add_paragraph ($form->prev_text);
$cts->add ($frm);
$cts->add_paragraph ($form->next_text);

$site->add_contents ($cts);
$site->end_page ();

?>