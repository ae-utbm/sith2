<?php
/* 
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *        
 * Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
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
 
require_once("include/site.inc.php");
require_once($topdir."include/cts/gallery.inc.php");

$site->start_page ( CMS_PREFIX."membres", "Membres" );

$cts = new contents("Membres");

$site->add_css("css/sas.css");

$req = new requete($site->db,
	"SELECT `utilisateurs`.`id_utilisateur`, " .
	"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
	"`asso_membre`.`role`, " .
	"`asso_membre`.`desc_role`, " .
	"`asso_membre`.`date_debut`, " .
	"CONCAT(`asso_membre`.`id_utilisateur`,',',`asso_membre`.`date_debut`) as `id_membership` " .
	"FROM `asso_membre` " .
	"INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`asso_membre`.`id_utilisateur` " .
	"WHERE `asso_membre`.`date_fin` IS NULL " .
	"AND `asso_membre`.`id_asso`='".$site->asso->id."' " .
	"AND `asso_membre`.`role` > '".ROLEASSO_TRESORIER."' ".
	"ORDER BY `asso_membre`.`role` DESC, `asso_membre`.`desc_role`,`utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl` ");
	
$gal = new gallery();
while ( $row = $req->get_row() )
{

  $img = $topdir."images/icons/128/user.png";
  if ( file_exists($topdir."var/img/matmatronch/".$row['id_utilisateur'].".identity.jpg") )
    $img = $topdir."var/img/matmatronch/".$row['id_utilisateur'].".identity.jpg";

  if ( $row['desc_role'] ) 
    $role = $row['desc_role'];
  else
    $role = $GLOBALS['ROLEASSO'][$row['role']];
  
  $gal->add_item(
  "<img src=\"$img\" alt=\"Photo\" height=\"105\">",
  "".htmlentities($row['nom_utilisateur'],ENT_NOQUOTES,"UTF-8")." (".htmlentities($role,ENT_NOQUOTES,"UTF-8").")");
}
$cts->add($gal);

	
$site->add_contents($cts);  
  
$site->end_page();

?>