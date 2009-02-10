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
'SELECT LEFT(nom_ville,5) as Nom
        , COUNT(*) as Nb
 FROM `loc_ville`
 GROUP BY LEFT(nom_ville,5)
 HAVING COUNT(*)>5');

echo '<pre>';
while(list($nom,$nb)=$req->get_row())
{
  echo $nom."\n";
  $req2 = new requete($site->db,
'SELECT id_ville
      , lat_ville
      , long_ville
      , nom_ville
FROM `loc_ville`
WHERE nom_ville LIKE \''.mysql_real_escape_string($nom).'%\'');

  while(list($id,$lat,$long,$nom2)=$req2->get_row())
    echo " -  (".$id.") ".$nom." : ".geo_radians_to_degrees($lat).", ".geo_radians_to_degrees($long)."\n";

}
echo '</pre>';
?>
