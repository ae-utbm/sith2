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
  
  var $pgdb;
  
  function ville($db, $dbrw = null, $pgdb = null)
  {
    $this->stdentity ($db, $dbrw);
    $this->pgdb = $pgdb;
  }

  function load_by_id ($id)
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

  /* redéfinition du can_enumerate, spécifique aux recherches de lieux */
  function can_enumerate() { return true; }

  function enumerate ( $null=false, $conds = null )
  {
    $class = get_class($this);
    
    if ( !isset($GLOBALS["entitiescatalog"][$class][4]) )
      return null;
    
		if ( $null ) 
			$values=array(null=>"(aucun)");
		else
			$values=array();
    
		$sql = 
		  "SELECT 
                            `id_ville`
                          , `nom_ville`
                          , `nom_pays`
                   FROM 
                            `loc_ville`
                   INNER JOIN 
                            `loc_pays`
                   USING (`id_pays`) ";
      
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
      $values[$row[0]] = $row[1] . " (" .$row[2] .")";

    /* debug (ca va merder, mais c'est temporaire) */
    print_r($values);

    return $values;
  }

  function load_by_pgid($id)
  {
    if (! $this->pgdb)
      return false;

    $req = new pgrequete($this->pgdb,
			 "SELECT 
                                   AsText(TRANSFORM(the_geom, 4030)) AS coords
                                   , id_loc AS id_ville
                                   , name_loc AS nom_ville
                          FROM
                                   worldloc
                          WHERE
                                   id_loc = ".intval($id));

    $rs = $req->get_all_rows();
    $rs = $rs[0];

    $rs['coords'] = str_replace("POINT(", "", $rs['coords']);
    $rs['coords'] = str_replace(")", "", $rs['coords']);
    list($rs['long_ville'], $rs['lat_ville']) = explode(' ', $rs['coords']);
    
    $this->_load($rs);

    
  }


  function _load ( $row )
  {
    $this->id = $row['id_ville'];
    $this->nom = $row['nom_ville'];
    $this->id_pays = $row['id_pays'];
    $this->cpostal = $row['cpostal_ville'];
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