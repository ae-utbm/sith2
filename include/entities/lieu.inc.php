<?php
/*   
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
 
 /**
 * @file
 */
 
 
class lieu extends stdentity
{
  
  var $id_ville;
  var $id_lieu_parent;
  var $nom;
  var $lat;
  var $long;
  var $eloi;
  
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `loc_lieu`
				WHERE `id_lieu` = '" .
		       mysql_real_escape_string($id) . "'
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
    $this->id = $row['id_lieu'];
    $this->id_ville = $row['id_ville'];
    $this->id_lieu_parent = $row['id_lieu_parent'];
    $this->nom = $row['nom_lieu'];
    $this->lat = $row['lat_lieu'];
    $this->long = $row['long_lieu'];
    $this->eloi = $row['eloi_lieu'];    
  }
  
  function create ( $id_ville, $id_lieu_parent, $nom, $lat, $long, $eloi )
  {
    
    if ( strpos($nom,",") !== false ) // La vigule est réservée pour décrire un lieu imbriqué de façon précise (ex: salle, batiment)
      return false;
    
    $this->id_ville = $id_ville;
    $this->id_lieu_parent = $id_lieu_parent;
    $this->nom = $nom;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;

    $req = new insert ($this->dbrw,
            "loc_lieu", array(
              "id_ville"=>$this->id_ville,
              "id_lieu_parent"=>$this->id_lieu_parent,
              "nom_lieu"=>$this->nom,
              "lat_lieu"=>sprintf("%.12F",$this->lat),
              "long_lieu"=>sprintf("%.12F",$this->long),
              "eloi_lieu"=>sprintf("%.12F",$this->eloi)
            ));
  
		if ( $req )
		{
			$this->id = $req->get_id();
		  return true;
		}
		
		$this->id = null;
    return false;
  }
  
  function update ( $id_ville, $id_lieu_parent, $nom, $lat, $long, $eloi )
  {
    $this->id_ville = $id_ville;
    $this->id_lieu_parent = $id_lieu_parent;
    $this->nom = $nom;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;
        
    $req = new update ($this->dbrw,
            "loc_lieu", array(
              "id_ville"=>$this->id_ville,
              "id_lieu_parent"=>$this->id_lieu_parent,
              "nom_lieu"=>$this->nom,
              "lat_lieu"=>sprintf("%.12F",$this->lat),
              "long_lieu"=>sprintf("%.12F",$this->long),
              "eloi_lieu"=>sprintf("%.12F",$this->eloi)
            ),
            array("id_lieu"=>$this->id) );
  }

  function delete ( )
  {
    new delete($this->dbrw,"loc_lieu",array("id_lieu"=>$this->id));
    $this->id = null;
  }


}

?>