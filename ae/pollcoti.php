<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
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

$req = new requete($site->dbrw,"UPDATE `ae_carte` SET `etat_vie_carte_ae`='".CETAT_EXPIRE."' " .
		"WHERE `date_expiration` <= NOW() AND `etat_vie_carte_ae`<".CETAT_EXPIRE."");
		
$req = new requete($site->dbrw,"UPDATE `utilisateurs` SET `ae_utl`='1' " .
		"WHERE `ae_utl`='0' AND EXISTS(SELECT * FROM `ae_cotisations` " .
			"WHERE `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
			"AND `date_fin_cotis` > NOW())");
			
$req = new requete($site->dbrw,"UPDATE `utilisateurs` SET `ae_utl`='0' " .
		"WHERE `ae_utl`='1' AND NOT EXISTS(SELECT * FROM `ae_cotisations` " .
			"WHERE `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
			"AND `date_fin_cotis` > NOW())");
			

?>
