<?php
/* Copyright 2011
 * - Antoine Tenart < antoine dot tenart at gmail dot com >
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

/**
 * Mobile Mat'matronch
 */

$topdir = "../";

require_once($topdir. "include/site.inc.php");


$site = new site();
$site->set_mobile(true);
$site->start_page("matmatronch", "MatMaTronch");


$cts = new contents();
$cts->add_title(1, "Mat'Matronch", "mob_title");

if (isset($_REQUEST["simplesearch"])) {
    if (isset($_REQUEST["pattern"])) {
      $pattern = stdentity::_fsearch_prepare_sql_pattern($_REQUEST["pattern"]);
      $pattern = strtr(' ', '|', $pattern);
      echo $pattern;

      $req = new requete($site->db, "SELECT `utilisateurs`.id_utilisateur,
          `utilisateurs`.nom_utl,
          `utilisateurs`.prenom_utl,
          `utilisateurs`.email_utl,
          `utilisateurs`.tel_portable_utl,
          `utl_etu_utbm`.surnom_utbm,
          `utl_etu_utbm`.email_utbm
          FROM `utilisateurs`
          LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.id_utilisateur = `utilisateurs`.id_utilisateur
          WHERE `utilisateurs`.nom_utl REGEXP '".$pattern."' OR
          `utilisateurs`.prenom_utl REGEXP '".$pattern."' OR
          `utl_etu_utbm`.surnom REGEXP '".$pattern."' OR
          `utilisateurs`.tel_portable_utl REGEXP '".$pattern."' OR
          `utilisateurs`.email_utl REGEXP '".$pattern."'
          ORDER BY `utilisateurs`.id_utilisateur DESC
          LIMIT 15");

      $user = new utilisateur($site->db);

      while ($row = $req->get_row()) {
        $exif = @exif_read_data("/var/www/ae/www/ae2/var/img/matmatronch/".$row["id_utilisateur"].".jpg", 0, true);
        $date_prise_vue = $exif["FILE"]["FileDateTime"] ? $exif["FILE"]["FileDateTime"] : '';

        $cts->puts("<div class=\"utl\">");
        $cts->puts("<b>".$row["prenom_utl"]." ".$row["nom_utl"]."</b><br/>");
        $cts->puts("<b>".$row["surnom_utbm"]."</b><br/>");
        $cts->puts("<a href=\"mailto:".$row["email_utl"]."\">".$row["email_utl"]."</a><br/>");
        $cts->puts("<a href=\"tel:".$row["tel_portable_utl"]."\">".$row["tel_portable_utl"]."</a><br/>");
        $cts->puts("<img src=\"/var/img/matmatronch/".$row["id_utilisateur"].".identity.jpg?".$date_prise_vue."\"/>");
        $cts->puts("</div>");
      }
  }
}

$site->add_contents($cts);

$frm = new form("mtmsearch","./matmat.php",true,"POST","Recherche");
$frm->add_text_field("pattern", "Nom, surnom, téléphone ...", "", true);
$frm->add_submit("simplesearch", "Rechercher");

$site->add_contents($frm);


/* Do not cross. */
$site->end_page();

?>
