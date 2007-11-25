<?php
/* Copyright 2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des 0tudiants de
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
 */
 

require_once($topdir."include/entities/geopoint.inc.php");

class reseaubus extends stdentity
{
  var $nom;
  var $siteweb;
  var $id_reseaubus_parent;
  
}

class lignebus extends stdentity
{
  var $nom;
  var $id_lignebus_parent;
  
}

class arretbus extends geopoint
{
  /**
   * Charge un arret de bus.
   * @param $id Id de l'arret de bus
   * @return false si non trouvé, true si chargé
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * 
        FROM `geopoint`
				WHERE `id_geopoint` = '".mysql_real_escape_string($id)."'
				AND type_geopoint='arretbus'
				LIMIT 1");

    if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
  }
  
  function _load ( $row )
  {
    $this->geopoint_load($row);
  }
  
  /**
   * Creer un nouvel arret de bus
   * @param $id_ville Id de la ville dans le quel l'arret de bus se trouve (null si aucun)
   * @param $nom Nom de l'arret de bus
   * @param $lat Latitude
   * @param $long Longitude
   * @param $eloi Eloignement
   * @return true si crée, false sinon
   */
  function create ( $id_ville,  $nom, $lat, $long, $eloi )
  {
    return $this->geopoint_create ( $nom, $lat, $long, $eloi, $id_ville );        
  }
  
  /**
   * Met à jour les informations relatives à l'arret de bus
   * @param $id_ville Id de la ville dans le quel l'arret de bus se trouve (null si aucun)
   * @param $nom Nom de l'arret de bus
   * @param $lat Latitude
   * @param $long Longitude
   * @param $eloi Eloignement
   */
  function update ( $id_ville, $nom, $lat, $long, $eloi )
  {
    $this->geopoint_update ( $nom, $lat, $long, $eloi, $id_ville );
  }

  /**
   * Supprime l'arret de bus
   */
  function delete ( )
  {
    $this->geopoint_delete();
  }

} 
 
?>