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
 


class typereduction extends stdentity
{
  var $nom;
  var $description;
  var $website;
  
  
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * 
        FROM `pg_typereduction`
				WHERE `id_typereduction` = '".mysql_real_escape_string($id)."'
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
    $this->id = $row['id_typereduction'];
    
    $this->nom = $row['nom_typereduction'];
    $this->description = $row['description_typereduction'];
    $this->website = $row['website_typereduction'];
  }  
  
  function create ( $nom, $description, $website )
  {
    $this->nom = $nom;
    $this->description = $description;
    $this->website = $website;
    
    $req = new insert ( $this->dbrw, "pg_typereduction", 
      array( 
      "nom_typereduction" => $this->nom,
      "description_typereduction" => $this->description,
      "website_typereduction" => $this->website
      ) );
    
    if ( !$req->is_success() )
    {
      $this->id = null;
      return false;
    }
    
	  $this->id = $req->get_id();
    return true;
  }
  
  function update ( $nom, $description, $website )
  {
    $this->nom = $nom;
    $this->description = $description;
    $this->website = $website;

    new update ( $this->dbrw, "pg_typereduction", 
      array( 
      "nom_typereduction" => $this->nom,
      "description_typereduction" => $this->description,
      "website_typereduction" => $this->website
      ),
      array("id_typereduction"=>$this->id) );
  } 
  
  
}

class typetarif extends stdentity
{
  var $nom;
  var $description;
 
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * 
        FROM `pg_typetarif`
				WHERE `id_typetarif` = '".mysql_real_escape_string($id)."'
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
    $this->id = $row['id_typetarif'];
    
    $this->nom = $row['nom_typetarif'];
    $this->description = $row['description_typetarif'];
  }
  function create ( $nom, $description, $website )
  {
    $this->nom = $nom;
    $this->description = $description;
    $this->website = $website;
    
    $req = new insert ( $this->dbrw, "pg_typetarif", 
      array( 
      "nom_typetarif" => $this->nom,
      "description_typetarif" => $this->description
      ) );
    
    if ( !$req->is_success() )
    {
      $this->id = null;
      return false;
    }
    
	  $this->id = $req->get_id();
    return true;
  }
  
  function update ( $nom, $description )
  {
    $this->nom = $nom;
    $this->description = $description;

    new update ( $this->dbrw, "pg_typetarif", 
      array( 
      "nom_typetarif" => $this->nom,
      "description_typetarif" => $this->description
      ),
      array("id_typetarif"=>$this->id) );
  } 
  
}

class service  extends stdentity
{
  var $nom;
  var $description;
  var $website;
  
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * 
        FROM `pg_service`
				WHERE `id_service` = '".mysql_real_escape_string($id)."'
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
    $this->id = $row['id_service'];
    
    $this->nom = $row['nom_service'];
    $this->description = $row['description_service'];
    $this->website = $row['website_service'];
  }
  
  function create ( $nom, $description, $website )
  {
    $this->nom = $nom;
    $this->description = $description;
    $this->website = $website;
    
    $req = new insert ( $this->dbrw, "pg_service", 
      array( 
      "nom_service" => $this->nom,
      "description_service" => $this->description,
      "website_service" => $this->website
      ) );
    
    if ( !$req->is_success() )
    {
      $this->id = null;
      return false;
    }
    
	  $this->id = $req->get_id();
    return true;
  }
  
  function update ( $nom, $description, $website )
  {
    $this->nom = $nom;
    $this->description = $description;
    $this->website = $website;

    new update ( $this->dbrw, "pg_service", 
      array( 
      "nom_service" => $this->nom,
      "description_service" => $this->description,
      "website_service" => $this->website
      ),
      array("id_service"=>$this->id) );
  } 
  
  
}
 
?>