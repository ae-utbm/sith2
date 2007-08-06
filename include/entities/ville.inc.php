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
 
class ville extends stdentity
{
  
  var $nom;
  var $id_pays;
  var $cpostal;
  var $lat;
  var $long;
  var $eloi;

  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `loc_ville`
				WHERE `id_ville` = '" .
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
    $this->id = $row['id_ville'];
    $this->nom = $row['nom_ville'];
    $this->id_pays = $row['id_pays'];
    $this->cpostal = sprintf("%5d", $row['cpostal_ville']);
    $this->lat = $row['lat_ville'];
    $this->long = $row['long_ville'];
    $this->eloi = $row['eloi_ville'];    
  }
  
  function create ( $id_pays, $nom, $cpostal, $lat, $long, $eloi )
  {
    $this->id_pays = $id_pays;
    $this->cpostal = $cpostal;
    $this->nom = $nom;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;
    
    $req = new insert ($this->dbrw,
            "loc_ville", array(
              "nom_ville"=>$this->nom,
              "id_pays"=>$this->id_pays,
              "cpostal_ville"=>$this->cpostal,
              "lat_ville"=>sprintf("%.12F",$this->lat),
              "long_ville"=>sprintf("%.12F",$this->long),
              "eloi_ville"=>sprintf("%.12F",$this->eloi)
            ));
  
		if ( $req )
		{
			$this->id = $req->get_id();
		  return true;
		}
		
		$this->id = null;
    return false;
  }
  
  function update ( $id_pays, $nom, $cpostal, $lat, $long, $eloi )
  {
    $this->id_pays = $id_pays;
    $this->cpostal = $cpostal;
    $this->nom = $nom;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;
        
    $req = new update ($this->dbrw,
            "loc_ville", array(
              "nom_ville"=>$this->nom,
              "id_pays"=>$this->id_pays,
              "cpostal_ville"=>$this->cpostal,
              "lat_ville"=>sprintf("%.12F",$this->lat),
              "long_ville"=>sprintf("%.12F",$this->long),
              "eloi_ville"=>sprintf("%.12F",$this->eloi)              
            ),
            array("id_ville"=>$this->id) );
  }

  function delete ( )
  {
    new delete($this->dbrw,"loc_ville",array("id_ville"=>$this->id));
    $this->id = null;
  }


}

?>