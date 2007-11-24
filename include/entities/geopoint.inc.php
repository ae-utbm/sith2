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
 
/**
 * Entité permettant de gérer des points geographiques.
 * Ne s'utilise pas en soit, mais est destiné à être etendue par d'autres
 * entités de type "lieu" ...
 * @see geo_degrees_to_radians
 * @see geo_radians_to_degrees
 * @see include/geo.inc.php
 * @see lieu
 */
class geopoint extends stdentity
{
  var $id_ville;
  var $lat;
  var $long;
  var $eloi;
  var $type;
  var $nom;
  
  /**
   * Charge les données du point geographique depuis une ligne SQL
   * @param $row Ligne SQL
   */
  protected function geopoint_load ( $row )
  {
    $this->id = $row['id_geopoint'];
    $this->id_ville = $row['id_ville'];
    $this->lat = $row['lat_geopoint'];
    $this->long = $row['long_geopoint'];
    $this->eloi = $row['eloi_geopoint'];   
    $this->type = $row['type_geopoint'];
    $this->nom = $row['nom_geopoint'];
  }
  
  /**
   * Créer un point geographique
   * @param $nom Nom du point
   * @param $lat Latitude
   * @param $long Longitude
   * @param $eloi Eloignement
   * @param $id_ville Id de la ville dans le quel le point se trouve (facultatif)
   * @return false en cas d'erreur, true sinon
   * @see geo_degrees_to_radians
   */
  protected function geopoint_create ( $nom, $lat, $long, $eloi=null, $id_ville=null )
  {
    $this->id_ville = $id_ville;
    $this->nom = $nom;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;   
    $this->type = get_class($this);
    
    $req = new insert ( $this->dbrw, "geopoint", 
      array( 
      "id_ville" => $this->id_ville,
      "lat_geopoint" => sprintf("%.12F",$this->lat),
      "long_geopoint" => sprintf("%.12F",$this->long),
      "eloi_geopoint" => sprintf("%.12F",$this->eloi),
      "type_geopoint" => $this->type,
      "nom_geopoint" => $this->nom
      ) );
    
    if ( !$req->is_success() )
    {
      $this->id = null;
      return false;
    }
    
	  $this->id = $req->get_id();
    return true;
  }
  
  /**
   * Enregistre des informations sur le point geographique
   * @param $nom Nom du point
   * @param $lat Latitude
   * @param $long Longitude
   * @param $eloi Eloignement
   * @param $id_ville Id de la ville dans le quel le point se trouve (facultatif)
   * @see geo_degrees_to_radians
   */
  protected function geopoint_update ( $nom, $lat, $long, $eloi=null, $id_ville=null )
  {
    $this->nom = $nom;
    $this->id_ville = $id_ville;
    $this->lat = $lat;
    $this->long = $long;
    $this->eloi = $eloi;     
    
    new update ( $this->dbrw, "geopoint", 
      array( 
      "id_ville" => $this->id_ville,
      "lat_geopoint" => sprintf("%.12F",$this->lat),
      "long_geopoint" => sprintf("%.12F",$this->long),
      "eloi_geopoint" => sprintf("%.12F",$this->eloi),
      "nom_geopoint" => $this->nom
      ),
      array("id_geopoint"=>$this->id) );
  }
  
  /**
   * Supprime les informations sur le point geographique
   */
  protected function geopoint_delete ( )
  {
    new delete($this->dbrw,"geopoint",array("id_geopoint"=>$this->id));
    $this->id = null;
  }  
  
  function can_enumerate()
  {
    return true;
  }
  
  function enumerate ( $null=false, $conds = null )
  {
    $class = get_class($this);
    
		if ( $null ) 
			$values=array(null=>"(aucun)");
		else
			$values=array();
    
		$sql = "SELECT `id_geopoint`,`nom_geopoint` ".
      "FROM `geopoint`";	
      
    if ( $class != "lieu" )
    {
      if ( is_null($conds) || !is_array($conds) )
        $conds = array();
        
      $conds["type_geopoint"] = $class;
    }
      
    if ( !is_null($conds) && count($conds) > 0 )
    {
      $firststatement=true;
      foreach ($conds as $key => $value)
      {
        if( $firststatement )
        {
          $sql .= " WHERE ";
          $firststatement = false;
        }
        else
          $sql .= " AND ";
          
        if ( is_null($value) )
          $sql .= "(`" . $key . "` is NULL)";
        else
          $sql .= "(`" . $key . "`='" . mysql_escape_string($value) . "')";
      }
    }
    
    $sql .= " ORDER BY 2";  
    
		$req = new requete($this->db,$sql);

		while ( $row = $req->get_row() )
		  $values[$row[0]] = $row[1];

    return $values;
  }
  
  function can_fsearch ( )
  {
    return true;
  }
  
  function _fsearch ( $sqlpattern, $limit=5, $count=false, $conds = null )
  {
    if ( $count )
    {
		  $sql = "SELECT COUNT(*) ";
      $limit=null;
    }
    else
		  $sql = "SELECT `id_geopoint`,`nom_geopoint` ";
      
    $sql .= "FROM `geopoint` ".
      "WHERE `nom_geopoint` REGEXP '^$sqlpattern'";	
      
    if ( $class != "lieu" )
    {
      if ( is_null($conds) || !is_array($conds) )
        $conds = array();
        
      $conds["type_geopoint"] = $class;
    }
    
    if ( !is_null($conds) && count($conds) > 0 )
    {
      foreach ($conds as $key => $value)
      {
        $sql .= " AND ";
        if ( is_null($value) )
          $sql .= "(`" . $key . "` is NULL)";
        else
          $sql .= "(`" . $key . "`='" . mysql_escape_string($value) . "')";
      }
    }
    
    $sql .= " ORDER BY 1";
    
    if ( !is_null($limit) && $limit > 0 )
      $sql .= " LIMIT ".$limit;
      
		$req = new requete($this->db,$sql);

    if ( $count )
    {
      list($nb) = $req->get_row();
      return $nb;
    }
    
    if ( !$req || $req->errno != 0 )
      //return null;
      return array(0=>$sql);

    $values=array();
    
		while ( $row = $req->get_row() )
		  $values[$row[0]] = $row[1];

    return $values;
  }
  
  function prefer_list()
  {
    return true;  
  }
  
}



?>