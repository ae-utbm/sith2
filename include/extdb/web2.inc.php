<?php
/** @file
 *
 * @brief Connexion et obtention d'informations sur des applications
 * Web2.0 distantes (flickr, facebook, ...)
 *
 */

/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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


require_once($topdir . "include/extdb/xml.inc.php");


$flickr_api_key = "FLICKR_API_KEY";

class flickr_info
{
  var $user;
  var $flickr_id;

  function flickr_info(&$user, $user_id)
  {
    $this->user = $user;

    $xmlcts = file_get_contents("http://api.flickr.com/services/rest/?method=flickr.people.findByUsername".
				"&api_key=".$flickr_api_key."&username=" . $user_id);

    $xml = new u007xml($xmlcts);
  }
}





?>