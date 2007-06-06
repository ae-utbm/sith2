<?php

/* Copyright 2005,2006
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir="../";
require_once("include/comptoirs.inc.php");
require_once($topdir. "include/cts/user.inc.php");

$site = new sitecomptoirs(true);
$site->comptoir->ouvrir($_REQUEST["id_comptoir"]);
if ( $site->comptoir->id < 1 )
{
	header("Location: index.php");
	exit();	
}

if ( $site->comptoir->type != 2 )
	error_403("forbiddenmode");

if ( !$site->comptoir->set_operateur($site->user) )
	error_403();

include("frontend.inc.php");

?>