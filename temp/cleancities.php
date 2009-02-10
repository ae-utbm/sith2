<?php
/* Copyright 2008
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

$req = new requete($site->db,
'SELECT l1.id_ville as id1
       ,l1.nom_ville as nom1
       ,l2.id_ville as id2
       ,l2.nom_ville as nom2
 FROM `loc_ville` l1
     ,`loc_ville` l2
 WHERE
    l1.id_ville!=l2.id_ville
    AND
    SQRT(POW((l2.lat_ville-l1.lat_ville),2)+POW((l2.long_ville-l1.long_ville),2))<1
 LIMIT 10');

echo '<pre>';
while(list($id1,$nom1,$id2,$nom2)=$req->get_row())
  echo "($id1) $nom - ($id2) $nom2\n";
echo '</pre>';
?>
