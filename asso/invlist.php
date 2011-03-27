<?php
/* Copyright 2011
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/pdf/inventaire_pdf.inc.php");

$site = new site ();

if ( !$site->user->is_in_group ("gestion_ae") && !$asso->is_member_role ($site->user->id,ROLEASSO_MEMBREBUREAU) )
  $site->error_forbidden ("presentation");

if (isset ($_REQUEST['id_asso'])) {
    $asso = new asso ($site->db);
    $asso->load_by_id ($_REQUEST["id_asso"]);

    if ( $asso->id  < 1 ) {
        $site->error_not_found ("presentation");
        exit();
    }

    $sql = 'SELECT inv_objet.nom_objet AS nom, sl_salle.nom_salle AS lien, inv_objet.date_achat AS date, inv_objet.prix_objet AS prix'
        .' FROM inv_objet LEFT JOIN sl_salle on sl_salle.id_salle=inv_objet.id_salle'
        .' WHERE inv_objet.id_asso='.intval ($asso->id);
    $req = new requete ($site->db, $req);
    $lines = array ();

    while (($line = $req->get_row ()) != null)
        $lines[] = $line;

    $pdf = new inventaire_pdf ('Inventaire club '.$asso->nom, date ('d-m-Y'));
    $pdf->renderize ();
    exit ();
} else if (isset ($_REQUEST['id_salle'])) {

} else if (isset ($_REQUEST['all'])) {

} else {
    $site->error_not_found("presentation");
}

?>