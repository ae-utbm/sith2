<?php
/* Copyright 2006,2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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
 * @file
 * Permet d'affecter une localisation à un poste. La localisation se fait par
 * salle. Utilisé nottament par les comptoirs.
 * Fonctionne uniquement en HTTPS, redirige automatiquement en HTTPS.
 *
 * @see comptoir/comptoir.inc.php
 * @see salle
 */

// Force le passage en HTTPS, pour éviter le risque d'interception des données
//if ( $_SERVER["REMOTE_ADDR"] != "127.0.1.1" )
if (isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on")
{
	header("Location: https://ae.utbm.fr".$_SERVER["REQUEST_URI"]);
	exit();
}

$GLOBALS["localisation_pv"]="AE2//testusage/";

/**
 * Définit la salle où se trouve le poste client actuel.
 * Fonctionne uniquement en HTTPS, utilise le cookie AE2_LOCALISATION
 *
 * @param $id_salle Id de la salle
 */
function set_localisation ( $id_salle )
{

	$data = array("id_salle"=>$id_salle,"ip"=>$_SERVER["HTTP_X_FORWARDED_FOR"]);

	$data["check"]=md5($GLOBALS["localisation_pv"].$data["id_salle"].$data["ip"]);

	$data = serialize($data);

	setcookie ("AE2_LOCALISATION", $data, time() + 31536000, "/", $_SERVER['HTTP_HOST'], true);
}

/**
 * Détermine la salle où se trouve le poste client actuel.
 * Fonctionne uniquement en HTTPS, utilise le cookie AE2_LOCALISATION
 * @return l'id de la salle où se trouve le poste client actuel ou null
 * si cette information n'est pas définit.
 */
function get_localisation ( )
{
	if ( isset($_COOKIE["AE2_LOCALISATION"]) )
	{
		$data = unserialize($_COOKIE["AE2_LOCALISATION"]);

		if ( $data["check"] != md5($GLOBALS["localisation_pv"].$data["id_salle"].$data["ip"]) )
			return null;

		/* En raison du changement intenpestif des IPS des PCs, on 	oublie cette vérification pour le moment
		 * jusqu'a obtenir des baux dhcp fixess
		if ( $data["ip"] != $_SERVER["HTTP_X_FORWARDED_FOR"])
			return null;*/

		return $data["id_salle"];
	}

	return null;
}


?>
