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

define('P2G', 2.596921296);
define('D2R', (M_PI/180));
define('G2R', (M_PI/200));
define('R2D', (180/M_PI));
define('R2G', (200/M_PI));
define('PHIO0', (52*G2R));

define('L0', 0.9215573613);
define('R0', 5999695.768);
define('X0', 600000);
define('Y0', 200000);
define('E2', 0.00680348765);
define('AA', 6378249.2);

/**
 * Entité permettant de gérer des points geographiques.
 *
 * Ne s'utilise pas en soit, mais est destiné à être etendue par d'autres
 * entités de type "lieu" ...
 *
 * Tout geopoint peut être considéré comme un lieu.
 *
 * @see geo_degrees_to_radians
 * @see geo_radians_to_degrees
 * @see include/geo.inc.php
 * @see lieu
 * @ingroup stdentity
 * @author Julien Etelain
 */
abstract class geopoint extends stdentity
{
  /** Id de la ville où se situe le point, peut être NULL */
  var $id_ville;
  /** Latitude N en radians, peut être NULL */
  var $lat;
  /** Longitude E en radians, peut être NULL */
  var $long;
  /** Eloignement (en radians?), peut être NULL */
  var $eloi;
  /** Type du point, sa classe */
  var $type;
  /** Nom du point, son titre */
  var $nom;
  
  /**
   * Charge un geopoint dans sa classe d'origine en lecture seule.
   * @param $db Lien à la base de donnée en lecture seule
   * @param $id Id du geopoint
   * @param $type Type du geopoint si connu, ou forcé (lieu par exemple)
   * @return une instance du geopoint (classe du geopoint) ou null en cas d'erreur
   */
  static function autoload_by_id ( $db, $id, $type=null )
  {
    global $topdir;
    
    if ( is_null($type) )
    {
      $req = new requete($db, 
        "SELECT type_geopoint ".
        "FROM `geopoint` ".
        "WHERE `id_geopoint` = '" . mysql_real_escape_string($id) . "' ".
        "LIMIT 1");
      
      if ( $req->lines != 1 )
        return null;
      
      list($type) = $req->get_row();
    }
    
    if ( !class_exists($type) 
         && isset($GLOBALS["entitiescatalog"][$type][5]) 
         && $GLOBALS["entitiescatalog"][$type][5] )
      require_once($topdir."include/entities/".$GLOBALS["entitiescatalog"][$type][5]);
      
    if ( class_exists($type) )
  		$item = new $type($db);
    else
      return null;
    
    $item->load_by_id($id);
    
    return $item;
  }
  
  /**
   * Charge les données du point geographique depuis une ligne SQL
   * @param $row Ligne SQL
   * @see stdentity::_load
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
      
    if ( is_null($conds) || !is_array($conds) )
      $conds = array();
      
    $conds["type_geopoint"] = $class;
      
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
      
    $class = get_class($this);
      
    if ( $class != "lieu" )
    {
      if ( is_null($conds) || !is_array($conds) )
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
  
  function get_kml_placemark()
  {
    $buffer .= "<Placemark id=\"ae_utbm_fr_geopoint_".$this->id_geopoint."\">";
    $buffer .= "<name>".htmlspecialchars($this->nom)."</name>";
    $buffer .= "<description>".htmlspecialchars($this->get_html_extended_info())."</description>";
    $buffer .= "<Point>";
    $buffer .= "<coordinates>".sprintf("%.12F",$this->long*360/2/M_PI).",".
      sprintf("%.12F",$this->lat*360/2/M_PI)."</coordinates>";
    $buffer .= "</Point>";
    $buffer .= "</Placemark>";
    return $buffer;
  }

  function L2W()
  {
    $e=sqrt(E2);
    $n0=sin(PHI0);
    $y = $this->lat - 2000000;
    $x = $this->long;
    $lng = atan(($x - X0) / (R0 + Y0 - $y)) / $n0;
    $r = sqrt(pow(($x - X0), 2) + pow(($y - Y0 - R0), 2));
    $l = -log($r / (R0 * exp($n0 * L0))) / $n0;
    $lat = 2 * atan(exp($l)) - M_PI / 2;
    for($i=1 ; $i<=4 ; $i++)
    {
      $lat = pow(((1 + $e * sin($lat)) / (1 - $e * sin($lat))), ($e / 2));
      $lat = 2 * atan($lat * exp($l)) - M_PI / 2;
    }
    $lng = $lng * R2G;
    $lat = $lat * R2G;
    $lng = ($lng + P2G) * G2R;
    $lat = $lat * G2R;
    $n = AA / sqrt(1 - E2 * pow(sin($lat), 2));
    $x = $n * cos($lat) * cos($lng);
    $y = $n * cos($lat) * sin($lng);
    $z = ($n * (1 - E2)) * sin($lat);
    $x = $x - 168;
    $y = $y - 60;
    $z = $z + 320;
    $e = sqrt(E2);
    $p = sqrt(pow($x,2) + pow($y,2));
    $lng = 2 * atan($y / ($x + $p));
    $lat = 0;
    for($i=1 ; $i<=5 ; $i++)
    {
      $n = AA / sqrt(1 - E2 * pow(sin($lat), 2));
      $lat = atan(($z + $n * E2 * sin($lat)) / $p);
    }
    $lng = $lng * r2d;
    $lat = $lat * r2d;
    return array('long' => $lng, 'lat' => $lat);
  }
  
}



?>
