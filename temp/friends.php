<?php

class map
{
  
  var $personnes;  
  var $wires;
  
  var $gx;
  var $gy;
  
  function map ()
  {
    $this->personnes = array();
    $this->wires = array();
    
    $this->gx = 0;
    $this->gy = 0;
  }
  
  
  function poll ()
  {
    foreach($this->personnes as $per )
      $per->pre_poll();
      
    foreach($this->personnes as $per )
      $per->do_poll();      
  }      
    
  function echo_infos ()
  {
    echo "listing\n";
    foreach($this->personnes as $per )
      echo $per->id." (".$per->x.",".$per->y.")\n";     
  }
 
  function dim ()
  {
    $min_x=null;
    $max_x=null;
    $min_y=null;
    $max_y=null;
    
    foreach($this->personnes as $per )
    {
      if ( is_null($min_x) )
      {
        $min_x = $per->x;  
        $max_x = $per->x;  
        $min_y = $per->y;  
        $max_y = $per->y;  
      }
      else
      {
        
        if ( $min_x > $per->x )
          $min_x = $per->x;  
        elseif ( $max_x < $per->x )
          $max_x = $per->x; 
          
        if ( $min_y > $per->y )
          $min_y = $per->y;  
        elseif ( $max_y < $per->y )
          $max_y = $per->y;       
          
      }      
    }
    return array( $min_x,  $min_y, $max_x, $max_y);
  } 
  
  function draw ()
  {
    $tx=50;
    
    $dim = $this->dim();
    
    $top_x = floor($dim[0]-1);
    $top_y = floor($dim[1]-1);
    $bottom_x = ceil($dim[2]+1);
    $bottom_y = ceil($dim[3]+1);
    
    $width = ($bottom_x-$top_x)*$tx;
    $height = ($bottom_y-$top_y)*$tx;
    
    $img = imagecreate($width,$height);
    
    $bg = imagecolorallocate($img, 255, 255, 255);
    $textcolor = imagecolorallocate($img, 0, 0, 255);
    $wirecolor = imagecolorallocate($img, 255, 0, 0);
    
    foreach($this->personnes as $per )
    {
      $per->ix = round(($per->x-$top_x)*$tx);
      $per->iy = round(($per->y-$top_y)*$tx);
    }
    
    foreach($this->wires as $wire )
    {
      imageline ($img, $wire->p1->ix, $wire->p1->iy, $wire->p2->ix, $wire->p2->iy, $wirecolor );
    }
    
    foreach($this->personnes as $per )
    {
      imagefilledellipse ($img, $per->ix, $per->iy, $tx/2, $tx/2, $textcolor );
      imagestring($img, 1, $per->ix, $per->iy,  $per->nom, $bg);
    }    
    
    //      

    
    imagepng($img,"friends_temp.png");
    imagedestroy($img);
  }
 
}

class personne
{
  var $id;
  var $nom;
  var $x;  
  var $y;
  
  var $wires;
  
  var $map;
  
  var $dx;  
  var $dy;
  
  
  function personne ( $id, $nom, &$map)
  {
    $this->id= $id;
    $this->nom= $nom;
    $this->x = $map->gx;
    $this->y = $map->gy;
    
    $map->gx += 20;
    $map->gy++;
    
    if ( $map->gx > 20 )
      $map->gx = 0;
     
    $this->map = &$map;
    $this->wires = array();
    $map->personnes[$this->id] = &$this;
  }
  
  function pre_poll ()
  {
    $this->dx = 0;
    $this->dy = 0;  
    //echo "pre_poll()\n";
    foreach ( $this->wires as $wire )
    {
      list($mx,$my) = $wire->get_delta($this);
      $this->dx += $mx;
      $this->dy += $my;
    }
  }
  
  function do_poll ()
  {
    $this->x += $this->dx;
    $this->y += $this->dy;  
  }
  

  
}


class wire
{
  
  var $p1;
  var $p2;
  var $tension;  
  
  function wire ( &$p1, &$p2, $tension )
  {
    if ( isset($p1->wires[$p2->id]) )
      return;
      
    if ( isset($p2->wires[$p1->id]) )
      return;
    
    $p1->wires[$p2->id] = &$this;
    $p2->wires[$p1->id] = &$this;
    $this->p1 = &$p1;
    $this->p2 = &$p2;
    $this->tension = $tension;
    $p1->map->wires[] = &$this;
  }
  
  function get_length()
  {
    return sqrt(pow($this->p1->x-$this->p2->x,2)+pow($this->p1->y-$this->p2->y,2));
  }
  
  function get_minimal_length()
  {
    //return 1/$this->tension;
    return 1;
  }
  
  function get_delta(&$p)
  {
    $len = $this->get_length();
    $min_len = $this->get_minimal_length();
    
    //echo "len=".$len." min=".$min_len;
    
    if ( $len == $min_len )
      return array ( 0, 0 );
    
    $f = ($len-$min_len)*$this->tension/50;
    
    $dx = ($this->p1->x-$this->p2->x);
    $dy = ($this->p1->y-$this->p2->y);
    
    if ( $dx > 1 )
    {
      $dx = 1;
      $dy = $dy/$dx;
    }
    
    if ( $dy > 1 )
    {
      $dy = 1;
      $dx = $dx/$dy;
    }
    
    if ( $p === $this->p1 )
    {
      $dx = -$dx;  
      $dy = -$dy;
    }
    
    
    
    return array( $dx*$f, $dy*$f );
  }
  
}


$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$db = new mysqlae();;

$map = new map();

$req1 = new requete($db, "SELECT p1.id_utilisateur, alias_utlFROM `sas_personnes_photos` AS `p1`
INNER JOIN utilisateurs ON ( p1.id_utilisateur= utilisateurs.id_utilisateur )JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photoAND p1.id_utilisateur != p2.id_utilisateur )GROUP BY p1.id_utilisateur");

while ( $row = $req2->get_row() )
  new personne($row['id_utilisateur'],$row['alias_utl'],$map);


echo count($map->personnes)." utilisateurs dans la moulinette\n";

$req2 = new requete($db, "SELECT COUNT( * ) as c, p1.id_utilisateur as u1, p2.id_utilisateur as u2FROM `sas_personnes_photos` AS `p1`JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photoAND p1.id_utilisateur != p2.id_utilisateur )GROUP BY p1.id_utilisateur, p2.id_utilisateur");

while ( $row = $req1->get_row() )
  new wire($map->personnes[$row['u1']],$map->personnes[$row['u2']],$row['c']);
  
echo count($map->wires)." liens dans la moulinette\n";


$step = 300;
if ( isset($_GET["step"]) )
   $step = intval($_GET["step"]);

for($i=0;$i<$step;$i++)
  $map->poll();
  
$map->draw();

echo "<br/><br/><img src=\"friends_temp.png\" />";

/*
$map = new map();

$a = new personne(1,"a",$map);
$b = new personne(2,"b",$map);
$c = new personne(3,"c",$map);

$c->y = 20;

new wire ($a, $b, 1);
new wire ($c, $b, 1);
new wire ($a, $c, 1);
*/
?>