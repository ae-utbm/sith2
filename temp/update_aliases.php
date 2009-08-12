<?php
/* Copyright 2009
 * Simon Lopez <simon dot lopez at ayolo dot org>
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
'SELECT `id_utilisateur`, `nom_utl`, `prenom_utl` FROM `utilisateurs` ORDER by `id_utilisateur` ASC');

$alias = array();
echo "<pre>";
while(list($id,$nom,$prenom)=$req->get_row())
{
  $a=strtolower($prenom{0}.str_replace(' ','',$nom));
  $a = substr($a,0,8);
  if(!isset($alias[$a]))
    $alias[$a]=0;
  else
  {
    $alias[$a]++;
    $a.=$alias[$a];
  }
//echo $nom." ".$prenom." : ".$a."\n";
  new update($site->dbrw,
             'utilisateurs',
             array('alias_utl'=>$a),
             array('id_utilisateur'=>$id));
}
echo "</pre>";
?>
