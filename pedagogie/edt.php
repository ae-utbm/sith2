<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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


$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->add_js("pedagogie/pedagogie.js");
//$site->allow_only_logged_users();

$site->start_page("services", "Pédagogie");


/* recap edt */
$cts = new contents();
$sql = new requete($site->db, "SELECT * FROM ");
$cts->add(new sqltable("edtlist", "Liste de vos emplois du temps ", $sql, "edt.php", 'semestre', array("id_annonce"=>"N°", "titre" => "Annonce", "date" => "Déposée le", "nom_utilisateur" => "Par", "etat" => "Etat"), array("detail" => "Détails"), array("detail" => "Détails")));
$site->add_contents($cts);

/**** ajout d'UV */
$uv = new uv($site->db, $site->dbrw);
$uv->load_by_id(0);

$cts = new contents("Détails des UV");
$cts->add_paragraph("Indiquez ci-dessous les séances auxquelles vous êtes
      inscrit. Si celle-ci n'est pas présente dans la liste proposée, choisissez
      \"Ajouter une séance\" afin de la créer.");
      
$site->add_contents($cts);
$site->add_contents(new add_uv_edt_box($uv));

$site->end_page();
?>
