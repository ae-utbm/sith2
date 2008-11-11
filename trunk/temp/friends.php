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
    echo "listing<br/>\n";
    foreach($this->personnes as $per )
      echo $per->id." (".$per->x.",".$per->y.")<br/>\n";
  }

  function dim ()
  {
    $min_x=null;
    $max_x=null;
    $min_y=null;
    $max_y=null;

    foreach($this->personnes as $per )
    {
      if ( $per->sum_tension  > 0 )
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
    }
    return array( $min_x,  $min_y, $max_x, $max_y);
  }


  function can_go ( &$me, $x, $y )
  {
    foreach($this->personnes as $per )
    {
      if ( $me !== $per && pow($x-$per->x,2)+pow($y-$per->y,2) < 0.1 )
        return false;
    }


    return true;
  }

  function draw ()
  {
    $tx=100;

    $dim = $this->dim();

    print_r($dim);

    $top_x = floor($dim[0]-1);
    $top_y = floor($dim[1]-1);
    $bottom_x = ceil($dim[2]+1);
    $bottom_y = ceil($dim[3]+1);

    $width = ($bottom_x-$top_x)*$tx;
    $height = ($bottom_y-$top_y)*$tx;

    echo "size= $width x $height<br/>";

    $img = imagecreate($width,$height);

    if ( $img === false )
      return;

    $bg = imagecolorallocate($img, 255, 255, 255);
    $textcolor = imagecolorallocate($img, 0, 0, 0);
    $wirecolor = imagecolorallocate($img, 255, 192, 192);
    $idealwirecolor = imagecolorallocate($img, 192, 255, 192);
    $bullcolor = imagecolorallocate($img, 255, 128, 128);

    foreach($this->personnes as $per )
    {
      $per->ix = round(($per->x-$top_x)*$tx);
      $per->iy = round(($per->y-$top_y)*$tx);
    }

    foreach($this->wires as $wire )
    {
      if ( abs($wire->get_length()- $wire->get_minimal_length()) < 0.1 )
        imageline ($img, $wire->p1->ix, $wire->p1->iy, $wire->p2->ix, $wire->p2->iy, $idealwirecolor );
      else
        imageline ($img, $wire->p1->ix, $wire->p1->iy, $wire->p2->ix, $wire->p2->iy, $wirecolor );
    }

    foreach($this->personnes as $per )
    {
      imagefilledellipse ($img, $per->ix, $per->iy, $tx/3, $tx/3, $bullcolor );
    }

    foreach($this->personnes as $per )
    {
      imagestring($img, 1, $per->ix, $per->iy,  $per->nom, $textcolor);
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

  var $sum_tension;


  function personne ( $id, $nom, &$map)
  {
    $this->id= $id;
    $this->nom= $nom;

    $this->x = $map->gx;
    $this->y = $map->gy;

    $map->gx += 1;
    $map->gy += 0.05;

    if ( $map->gx > 20 )
      $map->gx = 0;

    $this->map = &$map;
    $this->wires = array();
    $map->personnes[$this->id] = &$this;
    $this->sum_tension=0;
  }

  function pre_poll ()
  {
    $this->dx = 0;
    $this->dy = 0;
    //echo "pre_poll() on ".$this->id." (".$this->x.",".$this->y.")<br/>";
    foreach ( $this->wires as $wire )
    {
      list($mx,$my) = $wire->get_delta($this);

      $this->dx += $mx;
      $this->dy += $my;
    }
      //echo "dx=".$this->dx.",dy=".$this->dy."<br/>";
  }

  function do_poll ()
  {
    if ( $this->map-> can_go ( $this, $this->x + $this->dx, $this->y + $this->dy ) )
    {
      $this->x += $this->dx;
      $this->y += $this->dy;
    }
  }



}


class wire
{

  var $p1;
  var $p2;
  var $tension;

  var $len;

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
    $this->len=null;

    $p1->sum_tension += $tension;
    $p2->sum_tension += $tension;
  }

  function get_length()
  {
    return sqrt(pow($this->p1->x-$this->p2->x,2)+pow($this->p1->y-$this->p2->y,2));
  }

  function get_minimal_length()
  {
    return 1+(1/$this->tension);
    //return 1;
  }

  function get_delta(&$p)
  {
    // Vecteur de P1 à P2
    $dx = ($this->p2->x-$this->p1->x);
    $dy = ($this->p2->y-$this->p1->y);

    if ( $dx == 0 && $dy == 0 ) // Vecteur zéro, on est supperposé...
      $l = 0;
    else
    {
      // Longueur du vecteur
      $l = sqrt(pow($dx,2)+pow($dy,2));

      // Vecteur reduit à la longueur 1
      $dx = $dx/$l;
      $dy = $dy/$l;
    }

    // Longueur au "repos"
    $l_repos = $this->get_minimal_length();

    if ( abs($l-$l_repos) < 0.01 )
      return array(0,0); // Ca nous va...

    // Calcul du déplacement à appliquer pour aller vers une position qui nous satisfait plus

    if ( $l < $l_repos ) // On est écrasé, repulsion mais reduite par la tension
      $delta = ($l-$l_repos)/2;// *$p->sum_tension/$this->tension;
    else // On est tendu, attraction proportinelle à la tension, longueur & co
      $delta = ($l-$l_repos)*$this->tension/$p->sum_tension/2;

    if ( $dx == 0 && $dy == 0 ) // Vecteur zéro, on va donner une implusion aléatoire pour aller vers une position satisfaisante
    {
      $dx = mt_rand(-100,100)/100;
      $dy = mt_rand(-100,100)/100;
      $l = sqrt(pow($dx,2)+pow($dy,2));
      $dx = $dx/$l;
      $dy = $dy/$l;
    }

    if ( $p === $this->p2 ) // On n'est pas du point de vue de P1, on inverse donc le signe
    {
      $dx = -$dx;
      $dy = -$dy;
    }

    return array( $dx*$delta, $dy*$delta );
  }

}


$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$db = new mysqlae();;

$map = new map();

$req1 = new requete($db, "SELECT p1.id_utilisateur, alias_utl
FROM `sas_personnes_photos` AS `p1`
INNER JOIN utilisateurs ON ( p1.id_utilisateur= utilisateurs.id_utilisateur )
JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photo
AND p1.id_utilisateur != p2.id_utilisateur )
GROUP BY p1.id_utilisateur");

while ( $row = $req1->get_row() )
{
  new personne($row['id_utilisateur'],$row['alias_utl'],$map);
  //echo "new personne(".$row['id_utilisateur'].",'".$row['alias_utl']."',".'$'."map);<br/>";
}

echo count($map->personnes)." utilisateurs dans la moulinette<br/>";

$req2 = new requete($db, "SELECT COUNT( * ) as c, p1.id_utilisateur as u1, p2.id_utilisateur as u2
FROM `sas_personnes_photos` AS `p1`
JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photo
AND p1.id_utilisateur != p2.id_utilisateur )
GROUP BY p1.id_utilisateur, p2.id_utilisateur");

$tension_max=1;

while ( $row = $req2->get_row() )
{
  if ( $row['c'] > $tension_max )
    $tension_max = $row['c'];

  if ( isset($map->personnes[$row['u1']]) && isset($map->personnes[$row['u2']]) && $row['c'] > 2 )
  {
    new wire($map->personnes[$row['u1']],$map->personnes[$row['u2']],$row['c']);
    //echo "new wire(".'$'."map->personnes[".$row['u1']."],".'$'."map->personnes[".$row['u2']."],".$row['c'].");<br/>";
  }
}

echo count($map->wires)." liens dans la moulinette<br/>";
/*
new personne(1,"Ness",$map);
new personne(4,'YAK',$map);
new personne(5,'Soull',$map);
new personne(6,'Labte',$map);
new personne(8,'roulroul',$map);
new personne(9,'TOM',$map);
new personne(10,'Ofoy\'s',$map);
new personne(13,'Gine',$map);
new personne(14,'Papa',$map);
new personne(15,'',$map);
new personne(18,'Royal',$map);
new personne(22,'Rv',$map);
new personne(26,'Chasso',$map);
new personne(29,'Blondin',$map);
new personne(30,'',$map);
new personne(31,'BATIF',$map);
new personne(32,'Calva',$map);
new personne(33,'Gamote',$map);
new personne(34,'',$map);
new personne(39,'Albert',$map);
new personne(43,'Stesy',$map);
new personne(44,'Aloa',$map);
new personne(49,'GIGA',$map);
new personne(50,'Tanplan',$map);
new personne(52,'Let\'',$map);
new personne(57,'Tourix',$map);
new personne(59,'Lesson',$map);
new personne(60,'Nam',$map);
new personne(61,'toubu',$map);
new personne(63,'',$map);
new personne(66,'Vado',$map);
new personne(68,'Jojo',$map);
new personne(69,'kos_tom',$map);
new personne(70,'',$map);
new personne(72,'Quizz (et Ludy)',$map);
new personne(73,'Four',$map);
new personne(74,'rienbu',$map);
new personne(83,'Water',$map);
new personne(84,'Table',$map);
new personne(85,'BerBitcH',$map);
new personne(86,'Skwick',$map);
new personne(91,'',$map);
new personne(94,'corpace',$map);
new personne(95,'guillaume.dietsch',$map);
new personne(96,'',$map);
new personne(97,'Mc kley',$map);
new personne(98,'Scl (Irresponsable)',$map);
new personne(99,'gNa',$map);
new personne(100,'',$map);
new personne(101,'LesGlandS',$map);
new personne(103,'Ribble',$map);
new personne(108,'chris',$map);
new personne(109,'ETRADITION',$map);
new personne(112,'Tiny',$map);
new personne(113,'dud',$map);
new personne(114,'Zorrier',$map);
new personne(115,'Paulin',$map);
new personne(116,'MatH',$map);
new personne(119,'',$map);
new personne(120,'nicolas.luet',$map);
new personne(121,'',$map);
new personne(122,'Parvise',$map);
new personne(123,'Dyna',$map);
new personne(124,'Webast',$map);
new personne(125,'Proko',$map);
new personne(126,'Afon',$map);
new personne(127,'Finzerb',$map);
new personne(128,'Melb',$map);
new personne(129,'P\'tit Flo',$map);
new personne(130,'Er',$map);
new personne(132,'Issy',$map);
new personne(133,'NIZI',$map);
new personne(134,'Turiste',$map);
new personne(135,'Massif',$map);
new personne(137,'Nar',$map);
new personne(138,'Macolu',$map);
new personne(139,'ZenMaster',$map);
new personne(140,'BO',$map);
new personne(141,'Skol',$map);
new personne(142,'feu',$map);
new personne(144,'Raz',$map);
new personne(145,'sam`',$map);
new personne(147,'Membu',$map);
new personne(150,'LeeLa',$map);
new personne(151,'Skull',$map);
new personne(152,'Keud',$map);
new personne(153,'BlouZ',$map);
new personne(155,'Bacane',$map);
new personne(158,'Euze',$map);
new personne(161,'Jamby',$map);
new personne(162,'Jaqwell',$map);
new personne(163,'Doub',$map);
new personne(164,'Slij',$map);
new personne(165,'Fleurda',$map);
new personne(166,'pedrov',$map);
new personne(167,'Dingue',$map);
new personne(168,'Zai',$map);
new personne(169,'GTsoul',$map);
new wire($map->personnes[1],$map->personnes[5],3);
new wire($map->personnes[1],$map->personnes[22],1);
new wire($map->personnes[1],$map->personnes[72],1);
new wire($map->personnes[1],$map->personnes[113],2);
new wire($map->personnes[1],$map->personnes[116],2);
new wire($map->personnes[1],$map->personnes[134],2);
new wire($map->personnes[1],$map->personnes[135],2);
new wire($map->personnes[1],$map->personnes[138],3);
new wire($map->personnes[1],$map->personnes[141],1);
new wire($map->personnes[1],$map->personnes[145],1);
new wire($map->personnes[1],$map->personnes[150],3);
new wire($map->personnes[1],$map->personnes[151],5);
new wire($map->personnes[1],$map->personnes[167],1);
new wire($map->personnes[1],$map->personnes[169],2);
new wire($map->personnes[4],$map->personnes[5],20);
new wire($map->personnes[4],$map->personnes[26],4);
new wire($map->personnes[4],$map->personnes[33],1);
new wire($map->personnes[4],$map->personnes[34],1);
new wire($map->personnes[4],$map->personnes[99],2);
new wire($map->personnes[4],$map->personnes[101],1);
new wire($map->personnes[4],$map->personnes[114],1);
new wire($map->personnes[4],$map->personnes[122],4);
new wire($map->personnes[4],$map->personnes[124],10);
new wire($map->personnes[4],$map->personnes[134],8);
new wire($map->personnes[4],$map->personnes[135],6);
new wire($map->personnes[4],$map->personnes[138],3);
new wire($map->personnes[4],$map->personnes[141],4);
new wire($map->personnes[4],$map->personnes[142],7);
new wire($map->personnes[4],$map->personnes[147],1);
new wire($map->personnes[4],$map->personnes[151],2);
new wire($map->personnes[4],$map->personnes[165],2);
new wire($map->personnes[4],$map->personnes[167],7);
new wire($map->personnes[4],$map->personnes[169],1);
new wire($map->personnes[5],$map->personnes[1],3);
new wire($map->personnes[5],$map->personnes[4],20);
new wire($map->personnes[5],$map->personnes[14],1);
new wire($map->personnes[5],$map->personnes[18],1);
new wire($map->personnes[5],$map->personnes[31],1);
new wire($map->personnes[5],$map->personnes[33],6);
new wire($map->personnes[5],$map->personnes[73],3);
new wire($map->personnes[5],$map->personnes[98],2);
new wire($map->personnes[5],$map->personnes[99],5);
new wire($map->personnes[5],$map->personnes[100],1);
new wire($map->personnes[5],$map->personnes[116],1);
new wire($map->personnes[5],$map->personnes[121],1);
new wire($map->personnes[5],$map->personnes[122],20);
new wire($map->personnes[5],$map->personnes[134],6);
new wire($map->personnes[5],$map->personnes[135],4);
new wire($map->personnes[5],$map->personnes[138],1);
new wire($map->personnes[5],$map->personnes[141],3);
new wire($map->personnes[5],$map->personnes[142],7);
new wire($map->personnes[5],$map->personnes[151],12);
new wire($map->personnes[5],$map->personnes[162],1);
new wire($map->personnes[5],$map->personnes[164],1);
new wire($map->personnes[5],$map->personnes[165],1);
new wire($map->personnes[5],$map->personnes[167],10);
new wire($map->personnes[5],$map->personnes[169],1);
new wire($map->personnes[9],$map->personnes[39],1);
new wire($map->personnes[9],$map->personnes[60],1);
new wire($map->personnes[10],$map->personnes[14],3);
new wire($map->personnes[10],$map->personnes[26],8);
new wire($map->personnes[10],$map->personnes[31],3);
new wire($map->personnes[10],$map->personnes[32],10);
new wire($map->personnes[10],$map->personnes[33],2);
new wire($map->personnes[10],$map->personnes[44],4);
new wire($map->personnes[10],$map->personnes[57],5);
new wire($map->personnes[10],$map->personnes[60],3);
new wire($map->personnes[10],$map->personnes[72],1);
new wire($map->personnes[10],$map->personnes[73],3);
new wire($map->personnes[10],$map->personnes[98],4);
new wire($map->personnes[10],$map->personnes[99],3);
new wire($map->personnes[10],$map->personnes[100],2);
new wire($map->personnes[10],$map->personnes[101],2);
new wire($map->personnes[10],$map->personnes[103],4);
new wire($map->personnes[10],$map->personnes[130],7);
new wire($map->personnes[10],$map->personnes[132],1);
new wire($map->personnes[10],$map->personnes[134],1);
new wire($map->personnes[10],$map->personnes[142],4);
new wire($map->personnes[10],$map->personnes[150],1);
new wire($map->personnes[10],$map->personnes[151],1);
new wire($map->personnes[10],$map->personnes[165],4);
new wire($map->personnes[13],$map->personnes[33],1);
new wire($map->personnes[13],$map->personnes[130],1);
new wire($map->personnes[13],$map->personnes[162],1);
new wire($map->personnes[14],$map->personnes[5],1);
new wire($map->personnes[14],$map->personnes[10],3);
new wire($map->personnes[14],$map->personnes[18],17);
new wire($map->personnes[14],$map->personnes[26],32);
new wire($map->personnes[14],$map->personnes[31],9);
new wire($map->personnes[14],$map->personnes[32],31);
new wire($map->personnes[14],$map->personnes[33],119);
new wire($map->personnes[14],$map->personnes[57],35);
new wire($map->personnes[14],$map->personnes[59],1);
new wire($map->personnes[14],$map->personnes[60],12);
new wire($map->personnes[14],$map->personnes[73],17);
new wire($map->personnes[14],$map->personnes[83],1);
new wire($map->personnes[14],$map->personnes[85],5);
new wire($map->personnes[14],$map->personnes[91],1);
new wire($map->personnes[14],$map->personnes[98],39);
new wire($map->personnes[14],$map->personnes[99],27);
new wire($map->personnes[14],$map->personnes[100],76);
new wire($map->personnes[14],$map->personnes[101],4);
new wire($map->personnes[14],$map->personnes[103],44);
new wire($map->personnes[14],$map->personnes[113],1);
new wire($map->personnes[14],$map->personnes[114],2);
new wire($map->personnes[14],$map->personnes[120],1);
new wire($map->personnes[14],$map->personnes[122],3);
new wire($map->personnes[14],$map->personnes[123],6);
new wire($map->personnes[14],$map->personnes[124],2);
new wire($map->personnes[14],$map->personnes[125],2);
new wire($map->personnes[14],$map->personnes[135],1);
new wire($map->personnes[14],$map->personnes[141],21);
new wire($map->personnes[14],$map->personnes[145],2);
new wire($map->personnes[14],$map->personnes[151],7);
new wire($map->personnes[14],$map->personnes[165],2);
new wire($map->personnes[14],$map->personnes[167],1);
new wire($map->personnes[15],$map->personnes[22],2);
new wire($map->personnes[15],$map->personnes[91],2);
new wire($map->personnes[15],$map->personnes[96],3);
new wire($map->personnes[15],$map->personnes[100],1);
new wire($map->personnes[15],$map->personnes[122],4);
new wire($map->personnes[18],$map->personnes[5],1);
new wire($map->personnes[18],$map->personnes[14],17);
new wire($map->personnes[18],$map->personnes[26],2);
new wire($map->personnes[18],$map->personnes[32],5);
new wire($map->personnes[18],$map->personnes[33],21);
new wire($map->personnes[18],$map->personnes[44],1);
new wire($map->personnes[18],$map->personnes[52],1);
new wire($map->personnes[18],$map->personnes[57],7);
new wire($map->personnes[18],$map->personnes[98],8);
new wire($map->personnes[18],$map->personnes[99],8);
new wire($map->personnes[18],$map->personnes[100],15);
new wire($map->personnes[18],$map->personnes[101],3);
new wire($map->personnes[18],$map->personnes[103],2);
new wire($map->personnes[18],$map->personnes[122],1);
new wire($map->personnes[18],$map->personnes[123],4);
new wire($map->personnes[18],$map->personnes[138],1);
new wire($map->personnes[18],$map->personnes[141],6);
new wire($map->personnes[18],$map->personnes[142],1);
new wire($map->personnes[18],$map->personnes[151],5);
new wire($map->personnes[18],$map->personnes[165],1);
new wire($map->personnes[22],$map->personnes[1],1);
new wire($map->personnes[22],$map->personnes[15],2);
new wire($map->personnes[22],$map->personnes[33],1);
new wire($map->personnes[22],$map->personnes[83],1);
new wire($map->personnes[22],$map->personnes[114],1);
new wire($map->personnes[22],$map->personnes[115],1);
new wire($map->personnes[22],$map->personnes[116],1);
new wire($map->personnes[22],$map->personnes[122],2);
new wire($map->personnes[22],$map->personnes[138],1);
new wire($map->personnes[22],$map->personnes[150],1);
new wire($map->personnes[22],$map->personnes[161],1);
new wire($map->personnes[26],$map->personnes[4],4);
new wire($map->personnes[26],$map->personnes[10],8);
new wire($map->personnes[26],$map->personnes[14],32);
new wire($map->personnes[26],$map->personnes[18],2);
new wire($map->personnes[26],$map->personnes[31],6);
new wire($map->personnes[26],$map->personnes[32],19);
new wire($map->personnes[26],$map->personnes[33],36);
new wire($map->personnes[26],$map->personnes[44],8);
new wire($map->personnes[26],$map->personnes[57],25);
new wire($map->personnes[26],$map->personnes[60],6);
new wire($map->personnes[26],$map->personnes[73],13);
new wire($map->personnes[26],$map->personnes[83],4);
new wire($map->personnes[26],$map->personnes[85],2);
new wire($map->personnes[26],$map->personnes[98],7);
new wire($map->personnes[26],$map->personnes[99],4);
new wire($map->personnes[26],$map->personnes[100],12);
new wire($map->personnes[26],$map->personnes[101],4);
new wire($map->personnes[26],$map->personnes[103],12);
new wire($map->personnes[26],$map->personnes[112],2);
new wire($map->personnes[26],$map->personnes[114],1);
new wire($map->personnes[26],$map->personnes[115],1);
new wire($map->personnes[26],$map->personnes[122],1);
new wire($map->personnes[26],$map->personnes[130],20);
new wire($map->personnes[26],$map->personnes[132],5);
new wire($map->personnes[26],$map->personnes[135],1);
new wire($map->personnes[26],$map->personnes[141],21);
new wire($map->personnes[26],$map->personnes[142],8);
new wire($map->personnes[26],$map->personnes[145],1);
new wire($map->personnes[26],$map->personnes[151],15);
new wire($map->personnes[26],$map->personnes[165],8);
new wire($map->personnes[26],$map->personnes[169],2);
new wire($map->personnes[30],$map->personnes[83],1);
new wire($map->personnes[31],$map->personnes[5],1);
new wire($map->personnes[31],$map->personnes[10],3);
new wire($map->personnes[31],$map->personnes[14],9);
new wire($map->personnes[31],$map->personnes[26],6);
new wire($map->personnes[31],$map->personnes[32],12);
new wire($map->personnes[31],$map->personnes[33],10);
new wire($map->personnes[31],$map->personnes[44],5);
new wire($map->personnes[31],$map->personnes[57],3);
new wire($map->personnes[31],$map->personnes[60],3);
new wire($map->personnes[31],$map->personnes[61],2);
new wire($map->personnes[31],$map->personnes[73],11);
new wire($map->personnes[31],$map->personnes[94],1);
new wire($map->personnes[31],$map->personnes[98],4);
new wire($map->personnes[31],$map->personnes[99],3);
new wire($map->personnes[31],$map->personnes[100],4);
new wire($map->personnes[31],$map->personnes[101],6);
new wire($map->personnes[31],$map->personnes[103],6);
new wire($map->personnes[31],$map->personnes[112],2);
new wire($map->personnes[31],$map->personnes[122],2);
new wire($map->personnes[31],$map->personnes[125],3);
new wire($map->personnes[31],$map->personnes[130],4);
new wire($map->personnes[31],$map->personnes[132],1);
new wire($map->personnes[31],$map->personnes[135],1);
new wire($map->personnes[31],$map->personnes[142],3);
new wire($map->personnes[31],$map->personnes[145],1);
new wire($map->personnes[31],$map->personnes[153],9);
new wire($map->personnes[31],$map->personnes[165],3);
new wire($map->personnes[32],$map->personnes[10],10);
new wire($map->personnes[32],$map->personnes[14],31);
new wire($map->personnes[32],$map->personnes[18],5);
new wire($map->personnes[32],$map->personnes[26],19);
new wire($map->personnes[32],$map->personnes[31],12);
new wire($map->personnes[32],$map->personnes[33],21);
new wire($map->personnes[32],$map->personnes[44],14);
new wire($map->personnes[32],$map->personnes[57],33);
new wire($map->personnes[32],$map->personnes[60],13);
new wire($map->personnes[32],$map->personnes[73],29);
new wire($map->personnes[32],$map->personnes[83],6);
new wire($map->personnes[32],$map->personnes[85],7);
new wire($map->personnes[32],$map->personnes[94],2);
new wire($map->personnes[32],$map->personnes[98],11);
new wire($map->personnes[32],$map->personnes[99],10);
new wire($map->personnes[32],$map->personnes[100],19);
new wire($map->personnes[32],$map->personnes[101],1);
new wire($map->personnes[32],$map->personnes[103],5);
new wire($map->personnes[32],$map->personnes[109],1);
new wire($map->personnes[32],$map->personnes[112],1);
new wire($map->personnes[32],$map->personnes[122],4);
new wire($map->personnes[32],$map->personnes[125],4);
new wire($map->personnes[32],$map->personnes[130],11);
new wire($map->personnes[32],$map->personnes[132],6);
new wire($map->personnes[32],$map->personnes[141],5);
new wire($map->personnes[32],$map->personnes[142],5);
new wire($map->personnes[32],$map->personnes[151],5);
new wire($map->personnes[32],$map->personnes[153],5);
new wire($map->personnes[32],$map->personnes[162],2);
new wire($map->personnes[32],$map->personnes[165],5);
new wire($map->personnes[33],$map->personnes[4],1);
new wire($map->personnes[33],$map->personnes[5],6);
new wire($map->personnes[33],$map->personnes[10],2);
new wire($map->personnes[33],$map->personnes[13],1);
new wire($map->personnes[33],$map->personnes[14],119);
new wire($map->personnes[33],$map->personnes[18],21);
new wire($map->personnes[33],$map->personnes[22],1);
new wire($map->personnes[33],$map->personnes[26],36);
new wire($map->personnes[33],$map->personnes[31],10);
new wire($map->personnes[33],$map->personnes[32],21);
new wire($map->personnes[33],$map->personnes[44],5);
new wire($map->personnes[33],$map->personnes[50],1);
new wire($map->personnes[33],$map->personnes[57],39);
new wire($map->personnes[33],$map->personnes[60],10);
new wire($map->personnes[33],$map->personnes[61],1);
new wire($map->personnes[33],$map->personnes[70],1);
new wire($map->personnes[33],$map->personnes[73],17);
new wire($map->personnes[33],$map->personnes[83],2);
new wire($map->personnes[33],$map->personnes[85],9);
new wire($map->personnes[33],$map->personnes[98],19);
new wire($map->personnes[33],$map->personnes[99],21);
new wire($map->personnes[33],$map->personnes[100],46);
new wire($map->personnes[33],$map->personnes[101],3);
new wire($map->personnes[33],$map->personnes[103],15);
new wire($map->personnes[33],$map->personnes[112],3);
new wire($map->personnes[33],$map->personnes[122],17);
new wire($map->personnes[33],$map->personnes[123],8);
new wire($map->personnes[33],$map->personnes[130],7);
new wire($map->personnes[33],$map->personnes[132],1);
new wire($map->personnes[33],$map->personnes[135],2);
new wire($map->personnes[33],$map->personnes[138],1);
new wire($map->personnes[33],$map->personnes[141],30);
new wire($map->personnes[33],$map->personnes[142],4);
new wire($map->personnes[33],$map->personnes[145],2);
new wire($map->personnes[33],$map->personnes[150],3);
new wire($map->personnes[33],$map->personnes[151],21);
new wire($map->personnes[33],$map->personnes[165],6);
new wire($map->personnes[33],$map->personnes[167],2);
new wire($map->personnes[33],$map->personnes[168],1);
new wire($map->personnes[34],$map->personnes[4],1);
new wire($map->personnes[34],$map->personnes[165],1);
new wire($map->personnes[39],$map->personnes[9],1);
new wire($map->personnes[39],$map->personnes[60],1);
new wire($map->personnes[43],$map->personnes[50],2);
new wire($map->personnes[43],$map->personnes[52],2);
new wire($map->personnes[44],$map->personnes[10],4);
new wire($map->personnes[44],$map->personnes[18],1);
new wire($map->personnes[44],$map->personnes[26],8);
new wire($map->personnes[44],$map->personnes[31],5);
new wire($map->personnes[44],$map->personnes[32],14);
new wire($map->personnes[44],$map->personnes[33],5);
new wire($map->personnes[44],$map->personnes[50],2);
new wire($map->personnes[44],$map->personnes[57],3);
new wire($map->personnes[44],$map->personnes[60],10);
new wire($map->personnes[44],$map->personnes[73],1);
new wire($map->personnes[44],$map->personnes[83],2);
new wire($map->personnes[44],$map->personnes[94],3);
new wire($map->personnes[44],$map->personnes[98],1);
new wire($map->personnes[44],$map->personnes[112],2);
new wire($map->personnes[44],$map->personnes[122],3);
new wire($map->personnes[44],$map->personnes[130],6);
new wire($map->personnes[44],$map->personnes[132],6);
new wire($map->personnes[44],$map->personnes[142],7);
new wire($map->personnes[44],$map->personnes[150],1);
new wire($map->personnes[44],$map->personnes[162],1);
new wire($map->personnes[44],$map->personnes[165],5);
new wire($map->personnes[49],$map->personnes[129],1);
new wire($map->personnes[50],$map->personnes[33],1);
new wire($map->personnes[50],$map->personnes[43],2);
new wire($map->personnes[50],$map->personnes[44],2);
new wire($map->personnes[50],$map->personnes[57],3);
new wire($map->personnes[50],$map->personnes[60],3);
new wire($map->personnes[50],$map->personnes[83],2);
new wire($map->personnes[50],$map->personnes[130],1);
new wire($map->personnes[50],$map->personnes[140],1);
new wire($map->personnes[50],$map->personnes[162],1);
new wire($map->personnes[50],$map->personnes[165],1);
new wire($map->personnes[52],$map->personnes[18],1);
new wire($map->personnes[52],$map->personnes[43],2);
new wire($map->personnes[57],$map->personnes[10],5);
new wire($map->personnes[57],$map->personnes[14],35);
new wire($map->personnes[57],$map->personnes[18],7);
new wire($map->personnes[57],$map->personnes[26],25);
new wire($map->personnes[57],$map->personnes[31],3);
new wire($map->personnes[57],$map->personnes[32],33);
new wire($map->personnes[57],$map->personnes[33],39);
new wire($map->personnes[57],$map->personnes[44],3);
new wire($map->personnes[57],$map->personnes[50],3);
new wire($map->personnes[57],$map->personnes[60],20);
new wire($map->personnes[57],$map->personnes[73],41);
new wire($map->personnes[57],$map->personnes[83],7);
new wire($map->personnes[57],$map->personnes[85],8);
new wire($map->personnes[57],$map->personnes[98],12);
new wire($map->personnes[57],$map->personnes[99],15);
new wire($map->personnes[57],$map->personnes[100],31);
new wire($map->personnes[57],$map->personnes[101],4);
new wire($map->personnes[57],$map->personnes[103],3);
new wire($map->personnes[57],$map->personnes[114],2);
new wire($map->personnes[57],$map->personnes[120],1);
new wire($map->personnes[57],$map->personnes[122],6);
new wire($map->personnes[57],$map->personnes[125],1);
new wire($map->personnes[57],$map->personnes[130],7);
new wire($map->personnes[57],$map->personnes[132],1);
new wire($map->personnes[57],$map->personnes[134],2);
new wire($map->personnes[57],$map->personnes[141],9);
new wire($map->personnes[57],$map->personnes[142],2);
new wire($map->personnes[57],$map->personnes[151],3);
new wire($map->personnes[57],$map->personnes[162],4);
new wire($map->personnes[57],$map->personnes[165],4);
new wire($map->personnes[57],$map->personnes[168],1);
new wire($map->personnes[59],$map->personnes[14],1);
new wire($map->personnes[59],$map->personnes[125],1);
new wire($map->personnes[60],$map->personnes[9],1);
new wire($map->personnes[60],$map->personnes[10],3);
new wire($map->personnes[60],$map->personnes[14],12);
new wire($map->personnes[60],$map->personnes[26],6);
new wire($map->personnes[60],$map->personnes[31],3);
new wire($map->personnes[60],$map->personnes[32],13);
new wire($map->personnes[60],$map->personnes[33],10);
new wire($map->personnes[60],$map->personnes[39],1);
new wire($map->personnes[60],$map->personnes[44],10);
new wire($map->personnes[60],$map->personnes[50],3);
new wire($map->personnes[60],$map->personnes[57],20);
new wire($map->personnes[60],$map->personnes[73],7);
new wire($map->personnes[60],$map->personnes[83],4);
new wire($map->personnes[60],$map->personnes[85],3);
new wire($map->personnes[60],$map->personnes[98],2);
new wire($map->personnes[60],$map->personnes[99],1);
new wire($map->personnes[60],$map->personnes[100],2);
new wire($map->personnes[60],$map->personnes[101],3);
new wire($map->personnes[60],$map->personnes[103],3);
new wire($map->personnes[60],$map->personnes[130],5);
new wire($map->personnes[60],$map->personnes[134],1);
new wire($map->personnes[60],$map->personnes[142],4);
new wire($map->personnes[60],$map->personnes[162],2);
new wire($map->personnes[60],$map->personnes[165],5);
new wire($map->personnes[60],$map->personnes[168],1);
new wire($map->personnes[61],$map->personnes[31],2);
new wire($map->personnes[61],$map->personnes[33],1);
new wire($map->personnes[61],$map->personnes[101],2);
new wire($map->personnes[61],$map->personnes[103],2);
new wire($map->personnes[66],$map->personnes[72],1);
new wire($map->personnes[66],$map->personnes[115],1);
new wire($map->personnes[66],$map->personnes[150],1);
new wire($map->personnes[68],$map->personnes[103],1);
new wire($map->personnes[68],$map->personnes[122],3);
new wire($map->personnes[68],$map->personnes[127],4);
new wire($map->personnes[68],$map->personnes[141],1);
new wire($map->personnes[69],$map->personnes[108],1);
new wire($map->personnes[69],$map->personnes[116],1);
new wire($map->personnes[69],$map->personnes[128],1);
new wire($map->personnes[69],$map->personnes[129],1);
new wire($map->personnes[70],$map->personnes[33],1);
new wire($map->personnes[70],$map->personnes[101],1);
new wire($map->personnes[70],$map->personnes[113],1);
new wire($map->personnes[70],$map->personnes[114],1);
new wire($map->personnes[70],$map->personnes[130],1);
new wire($map->personnes[70],$map->personnes[150],2);
new wire($map->personnes[70],$map->personnes[152],1);
new wire($map->personnes[72],$map->personnes[1],1);
new wire($map->personnes[72],$map->personnes[10],1);
new wire($map->personnes[72],$map->personnes[66],1);
new wire($map->personnes[72],$map->personnes[113],4);
new wire($map->personnes[72],$map->personnes[115],1);
new wire($map->personnes[72],$map->personnes[130],1);
new wire($map->personnes[72],$map->personnes[134],1);
new wire($map->personnes[72],$map->personnes[138],1);
new wire($map->personnes[72],$map->personnes[141],3);
new wire($map->personnes[72],$map->personnes[142],1);
new wire($map->personnes[72],$map->personnes[145],3);
new wire($map->personnes[72],$map->personnes[150],3);
new wire($map->personnes[72],$map->personnes[151],1);
new wire($map->personnes[72],$map->personnes[158],2);
new wire($map->personnes[72],$map->personnes[161],1);
new wire($map->personnes[73],$map->personnes[5],3);
new wire($map->personnes[73],$map->personnes[10],3);
new wire($map->personnes[73],$map->personnes[14],17);
new wire($map->personnes[73],$map->personnes[26],13);
new wire($map->personnes[73],$map->personnes[31],11);
new wire($map->personnes[73],$map->personnes[32],29);
new wire($map->personnes[73],$map->personnes[33],17);
new wire($map->personnes[73],$map->personnes[44],1);
new wire($map->personnes[73],$map->personnes[57],41);
new wire($map->personnes[73],$map->personnes[60],7);
new wire($map->personnes[73],$map->personnes[83],3);
new wire($map->personnes[73],$map->personnes[85],6);
new wire($map->personnes[73],$map->personnes[98],9);
new wire($map->personnes[73],$map->personnes[99],11);
new wire($map->personnes[73],$map->personnes[100],17);
new wire($map->personnes[73],$map->personnes[101],5);
new wire($map->personnes[73],$map->personnes[103],2);
new wire($map->personnes[73],$map->personnes[113],1);
new wire($map->personnes[73],$map->personnes[122],1);
new wire($map->personnes[73],$map->personnes[125],5);
new wire($map->personnes[73],$map->personnes[134],2);
new wire($map->personnes[73],$map->personnes[135],1);
new wire($map->personnes[73],$map->personnes[138],1);
new wire($map->personnes[73],$map->personnes[141],4);
new wire($map->personnes[73],$map->personnes[145],1);
new wire($map->personnes[73],$map->personnes[151],2);
new wire($map->personnes[73],$map->personnes[153],6);
new wire($map->personnes[73],$map->personnes[165],3);
new wire($map->personnes[74],$map->personnes[95],1);
new wire($map->personnes[74],$map->personnes[96],1);
new wire($map->personnes[83],$map->personnes[14],1);
new wire($map->personnes[83],$map->personnes[22],1);
new wire($map->personnes[83],$map->personnes[26],4);
new wire($map->personnes[83],$map->personnes[30],1);
new wire($map->personnes[83],$map->personnes[32],6);
new wire($map->personnes[83],$map->personnes[33],2);
new wire($map->personnes[83],$map->personnes[44],2);
new wire($map->personnes[83],$map->personnes[50],2);
new wire($map->personnes[83],$map->personnes[57],7);
new wire($map->personnes[83],$map->personnes[60],4);
new wire($map->personnes[83],$map->personnes[73],3);
new wire($map->personnes[83],$map->personnes[85],7);
new wire($map->personnes[83],$map->personnes[101],1);
new wire($map->personnes[83],$map->personnes[114],1);
new wire($map->personnes[83],$map->personnes[122],1);
new wire($map->personnes[83],$map->personnes[125],1);
new wire($map->personnes[83],$map->personnes[130],2);
new wire($map->personnes[83],$map->personnes[132],1);
new wire($map->personnes[83],$map->personnes[140],1);
new wire($map->personnes[83],$map->personnes[141],2);
new wire($map->personnes[83],$map->personnes[162],1);
new wire($map->personnes[83],$map->personnes[165],3);
new wire($map->personnes[85],$map->personnes[14],5);
new wire($map->personnes[85],$map->personnes[26],2);
new wire($map->personnes[85],$map->personnes[32],7);
new wire($map->personnes[85],$map->personnes[33],9);
new wire($map->personnes[85],$map->personnes[57],8);
new wire($map->personnes[85],$map->personnes[60],3);
new wire($map->personnes[85],$map->personnes[73],6);
new wire($map->personnes[85],$map->personnes[83],7);
new wire($map->personnes[85],$map->personnes[98],1);
new wire($map->personnes[85],$map->personnes[100],2);
new wire($map->personnes[85],$map->personnes[101],1);
new wire($map->personnes[85],$map->personnes[122],3);
new wire($map->personnes[85],$map->personnes[138],1);
new wire($map->personnes[85],$map->personnes[141],5);
new wire($map->personnes[85],$map->personnes[150],1);
new wire($map->personnes[86],$map->personnes[115],1);
new wire($map->personnes[91],$map->personnes[14],1);
new wire($map->personnes[91],$map->personnes[15],2);
new wire($map->personnes[91],$map->personnes[96],3);
new wire($map->personnes[91],$map->personnes[98],2);
new wire($map->personnes[91],$map->personnes[100],3);
new wire($map->personnes[91],$map->personnes[103],1);
new wire($map->personnes[91],$map->personnes[122],1);
new wire($map->personnes[94],$map->personnes[31],1);
new wire($map->personnes[94],$map->personnes[32],2);
new wire($map->personnes[94],$map->personnes[44],3);
new wire($map->personnes[94],$map->personnes[101],1);
new wire($map->personnes[94],$map->personnes[129],1);
new wire($map->personnes[94],$map->personnes[133],7);
new wire($map->personnes[94],$map->personnes[142],1);
new wire($map->personnes[95],$map->personnes[74],1);
new wire($map->personnes[95],$map->personnes[96],1);
new wire($map->personnes[96],$map->personnes[15],3);
new wire($map->personnes[96],$map->personnes[74],1);
new wire($map->personnes[96],$map->personnes[91],3);
new wire($map->personnes[96],$map->personnes[95],1);
new wire($map->personnes[96],$map->personnes[98],1);
new wire($map->personnes[96],$map->personnes[100],2);
new wire($map->personnes[96],$map->personnes[122],2);
new wire($map->personnes[98],$map->personnes[5],2);
new wire($map->personnes[98],$map->personnes[10],4);
new wire($map->personnes[98],$map->personnes[14],39);
new wire($map->personnes[98],$map->personnes[18],8);
new wire($map->personnes[98],$map->personnes[26],7);
new wire($map->personnes[98],$map->personnes[31],4);
new wire($map->personnes[98],$map->personnes[32],11);
new wire($map->personnes[98],$map->personnes[33],19);
new wire($map->personnes[98],$map->personnes[44],1);
new wire($map->personnes[98],$map->personnes[57],12);
new wire($map->personnes[98],$map->personnes[60],2);
new wire($map->personnes[98],$map->personnes[73],9);
new wire($map->personnes[98],$map->personnes[85],1);
new wire($map->personnes[98],$map->personnes[91],2);
new wire($map->personnes[98],$map->personnes[96],1);
new wire($map->personnes[98],$map->personnes[99],26);
new wire($map->personnes[98],$map->personnes[100],34);
new wire($map->personnes[98],$map->personnes[101],2);
new wire($map->personnes[98],$map->personnes[103],7);
new wire($map->personnes[98],$map->personnes[115],2);
new wire($map->personnes[98],$map->personnes[122],2);
new wire($map->personnes[98],$map->personnes[130],1);
new wire($map->personnes[98],$map->personnes[132],1);
new wire($map->personnes[98],$map->personnes[151],1);
new wire($map->personnes[98],$map->personnes[169],1);
new wire($map->personnes[99],$map->personnes[4],2);
new wire($map->personnes[99],$map->personnes[5],5);
new wire($map->personnes[99],$map->personnes[10],3);
new wire($map->personnes[99],$map->personnes[14],27);
new wire($map->personnes[99],$map->personnes[18],8);
new wire($map->personnes[99],$map->personnes[26],4);
new wire($map->personnes[99],$map->personnes[31],3);
new wire($map->personnes[99],$map->personnes[32],10);
new wire($map->personnes[99],$map->personnes[33],21);
new wire($map->personnes[99],$map->personnes[57],15);
new wire($map->personnes[99],$map->personnes[60],1);
new wire($map->personnes[99],$map->personnes[73],11);
new wire($map->personnes[99],$map->personnes[98],26);
new wire($map->personnes[99],$map->personnes[100],27);
new wire($map->personnes[99],$map->personnes[101],1);
new wire($map->personnes[99],$map->personnes[103],4);
new wire($map->personnes[99],$map->personnes[115],2);
new wire($map->personnes[99],$map->personnes[122],2);
new wire($map->personnes[99],$map->personnes[151],1);
new wire($map->personnes[100],$map->personnes[5],1);
new wire($map->personnes[100],$map->personnes[10],2);
new wire($map->personnes[100],$map->personnes[14],76);
new wire($map->personnes[100],$map->personnes[15],1);
new wire($map->personnes[100],$map->personnes[18],15);
new wire($map->personnes[100],$map->personnes[26],12);
new wire($map->personnes[100],$map->personnes[31],4);
new wire($map->personnes[100],$map->personnes[32],19);
new wire($map->personnes[100],$map->personnes[33],46);
new wire($map->personnes[100],$map->personnes[57],31);
new wire($map->personnes[100],$map->personnes[60],2);
new wire($map->personnes[100],$map->personnes[73],17);
new wire($map->personnes[100],$map->personnes[85],2);
new wire($map->personnes[100],$map->personnes[91],3);
new wire($map->personnes[100],$map->personnes[96],2);
new wire($map->personnes[100],$map->personnes[98],34);
new wire($map->personnes[100],$map->personnes[99],27);
new wire($map->personnes[100],$map->personnes[101],3);
new wire($map->personnes[100],$map->personnes[103],14);
new wire($map->personnes[100],$map->personnes[113],1);
new wire($map->personnes[100],$map->personnes[122],4);
new wire($map->personnes[100],$map->personnes[123],1);
new wire($map->personnes[100],$map->personnes[124],4);
new wire($map->personnes[100],$map->personnes[134],2);
new wire($map->personnes[100],$map->personnes[141],1);
new wire($map->personnes[100],$map->personnes[142],2);
new wire($map->personnes[100],$map->personnes[165],2);
new wire($map->personnes[100],$map->personnes[167],1);
new wire($map->personnes[101],$map->personnes[4],1);
new wire($map->personnes[101],$map->personnes[10],2);
new wire($map->personnes[101],$map->personnes[14],4);
new wire($map->personnes[101],$map->personnes[18],3);
new wire($map->personnes[101],$map->personnes[26],4);
new wire($map->personnes[101],$map->personnes[31],6);
new wire($map->personnes[101],$map->personnes[32],1);
new wire($map->personnes[101],$map->personnes[33],3);
new wire($map->personnes[101],$map->personnes[57],4);
new wire($map->personnes[101],$map->personnes[60],3);
new wire($map->personnes[101],$map->personnes[61],2);
new wire($map->personnes[101],$map->personnes[70],1);
new wire($map->personnes[101],$map->personnes[73],5);
new wire($map->personnes[101],$map->personnes[83],1);
new wire($map->personnes[101],$map->personnes[85],1);
new wire($map->personnes[101],$map->personnes[94],1);
new wire($map->personnes[101],$map->personnes[98],2);
new wire($map->personnes[101],$map->personnes[99],1);
new wire($map->personnes[101],$map->personnes[100],3);
new wire($map->personnes[101],$map->personnes[103],9);
new wire($map->personnes[101],$map->personnes[122],3);
new wire($map->personnes[101],$map->personnes[130],1);
new wire($map->personnes[101],$map->personnes[135],1);
new wire($map->personnes[101],$map->personnes[141],1);
new wire($map->personnes[101],$map->personnes[145],1);
new wire($map->personnes[101],$map->personnes[150],1);
new wire($map->personnes[101],$map->personnes[165],1);
new wire($map->personnes[103],$map->personnes[10],4);
new wire($map->personnes[103],$map->personnes[14],44);
new wire($map->personnes[103],$map->personnes[18],2);
new wire($map->personnes[103],$map->personnes[26],12);
new wire($map->personnes[103],$map->personnes[31],6);
new wire($map->personnes[103],$map->personnes[32],5);
new wire($map->personnes[103],$map->personnes[33],15);
new wire($map->personnes[103],$map->personnes[57],3);
new wire($map->personnes[103],$map->personnes[60],3);
new wire($map->personnes[103],$map->personnes[61],2);
new wire($map->personnes[103],$map->personnes[68],1);
new wire($map->personnes[103],$map->personnes[73],2);
new wire($map->personnes[103],$map->personnes[91],1);
new wire($map->personnes[103],$map->personnes[98],7);
new wire($map->personnes[103],$map->personnes[99],4);
new wire($map->personnes[103],$map->personnes[100],14);
new wire($map->personnes[103],$map->personnes[101],9);
new wire($map->personnes[103],$map->personnes[109],4);
new wire($map->personnes[103],$map->personnes[114],1);
new wire($map->personnes[103],$map->personnes[122],2);
new wire($map->personnes[103],$map->personnes[123],3);
new wire($map->personnes[103],$map->personnes[124],6);
new wire($map->personnes[103],$map->personnes[138],1);
new wire($map->personnes[103],$map->personnes[141],2);
new wire($map->personnes[103],$map->personnes[142],1);
new wire($map->personnes[103],$map->personnes[151],3);
new wire($map->personnes[103],$map->personnes[167],1);
new wire($map->personnes[103],$map->personnes[169],2);
new wire($map->personnes[108],$map->personnes[69],1);
new wire($map->personnes[109],$map->personnes[32],1);
new wire($map->personnes[109],$map->personnes[103],4);
new wire($map->personnes[112],$map->personnes[26],2);
new wire($map->personnes[112],$map->personnes[31],2);
new wire($map->personnes[112],$map->personnes[32],1);
new wire($map->personnes[112],$map->personnes[33],3);
new wire($map->personnes[112],$map->personnes[44],2);
new wire($map->personnes[112],$map->personnes[130],1);
new wire($map->personnes[112],$map->personnes[142],1);
new wire($map->personnes[112],$map->personnes[165],1);
new wire($map->personnes[113],$map->personnes[1],2);
new wire($map->personnes[113],$map->personnes[14],1);
new wire($map->personnes[113],$map->personnes[70],1);
new wire($map->personnes[113],$map->personnes[72],4);
new wire($map->personnes[113],$map->personnes[73],1);
new wire($map->personnes[113],$map->personnes[100],1);
new wire($map->personnes[113],$map->personnes[130],8);
new wire($map->personnes[113],$map->personnes[134],9);
new wire($map->personnes[113],$map->personnes[135],6);
new wire($map->personnes[113],$map->personnes[138],11);
new wire($map->personnes[113],$map->personnes[141],6);
new wire($map->personnes[113],$map->personnes[145],10);
new wire($map->personnes[113],$map->personnes[147],2);
new wire($map->personnes[113],$map->personnes[150],7);
new wire($map->personnes[113],$map->personnes[151],4);
new wire($map->personnes[113],$map->personnes[155],1);
new wire($map->personnes[113],$map->personnes[158],5);
new wire($map->personnes[113],$map->personnes[165],2);
new wire($map->personnes[113],$map->personnes[169],2);
new wire($map->personnes[114],$map->personnes[4],1);
new wire($map->personnes[114],$map->personnes[14],2);
new wire($map->personnes[114],$map->personnes[22],1);
new wire($map->personnes[114],$map->personnes[26],1);
new wire($map->personnes[114],$map->personnes[57],2);
new wire($map->personnes[114],$map->personnes[70],1);
new wire($map->personnes[114],$map->personnes[83],1);
new wire($map->personnes[114],$map->personnes[103],1);
new wire($map->personnes[114],$map->personnes[115],3);
new wire($map->personnes[114],$map->personnes[120],1);
new wire($map->personnes[114],$map->personnes[121],1);
new wire($map->personnes[114],$map->personnes[122],1);
new wire($map->personnes[114],$map->personnes[123],1);
new wire($map->personnes[114],$map->personnes[130],4);
new wire($map->personnes[114],$map->personnes[141],3);
new wire($map->personnes[114],$map->personnes[150],1);
new wire($map->personnes[114],$map->personnes[151],6);
new wire($map->personnes[114],$map->personnes[161],1);
new wire($map->personnes[114],$map->personnes[169],1);
new wire($map->personnes[115],$map->personnes[22],1);
new wire($map->personnes[115],$map->personnes[26],1);
new wire($map->personnes[115],$map->personnes[66],1);
new wire($map->personnes[115],$map->personnes[72],1);
new wire($map->personnes[115],$map->personnes[86],1);
new wire($map->personnes[115],$map->personnes[98],2);
new wire($map->personnes[115],$map->personnes[99],2);
new wire($map->personnes[115],$map->personnes[114],3);
new wire($map->personnes[115],$map->personnes[124],1);
new wire($map->personnes[115],$map->personnes[130],1);
new wire($map->personnes[115],$map->personnes[150],9);
new wire($map->personnes[115],$map->personnes[158],5);
new wire($map->personnes[115],$map->personnes[161],1);
new wire($map->personnes[116],$map->personnes[1],2);
new wire($map->personnes[116],$map->personnes[5],1);
new wire($map->personnes[116],$map->personnes[22],1);
new wire($map->personnes[116],$map->personnes[69],1);
new wire($map->personnes[116],$map->personnes[128],2);
new wire($map->personnes[116],$map->personnes[129],1);
new wire($map->personnes[116],$map->personnes[134],1);
new wire($map->personnes[116],$map->personnes[135],1);
new wire($map->personnes[116],$map->personnes[138],2);
new wire($map->personnes[116],$map->personnes[141],1);
new wire($map->personnes[116],$map->personnes[150],4);
new wire($map->personnes[120],$map->personnes[14],1);
new wire($map->personnes[120],$map->personnes[57],1);
new wire($map->personnes[120],$map->personnes[114],1);
new wire($map->personnes[120],$map->personnes[121],5);
new wire($map->personnes[121],$map->personnes[5],1);
new wire($map->personnes[121],$map->personnes[114],1);
new wire($map->personnes[121],$map->personnes[120],5);
new wire($map->personnes[121],$map->personnes[123],2);
new wire($map->personnes[121],$map->personnes[124],1);
new wire($map->personnes[121],$map->personnes[127],1);
new wire($map->personnes[121],$map->personnes[138],1);
new wire($map->personnes[122],$map->personnes[4],4);
new wire($map->personnes[122],$map->personnes[5],20);
new wire($map->personnes[122],$map->personnes[14],3);
new wire($map->personnes[122],$map->personnes[15],4);
new wire($map->personnes[122],$map->personnes[18],1);
new wire($map->personnes[122],$map->personnes[22],2);
new wire($map->personnes[122],$map->personnes[26],1);
new wire($map->personnes[122],$map->personnes[31],2);
new wire($map->personnes[122],$map->personnes[32],4);
new wire($map->personnes[122],$map->personnes[33],17);
new wire($map->personnes[122],$map->personnes[44],3);
new wire($map->personnes[122],$map->personnes[57],6);
new wire($map->personnes[122],$map->personnes[68],3);
new wire($map->personnes[122],$map->personnes[73],1);
new wire($map->personnes[122],$map->personnes[83],1);
new wire($map->personnes[122],$map->personnes[85],3);
new wire($map->personnes[122],$map->personnes[91],1);
new wire($map->personnes[122],$map->personnes[96],2);
new wire($map->personnes[122],$map->personnes[98],2);
new wire($map->personnes[122],$map->personnes[99],2);
new wire($map->personnes[122],$map->personnes[100],4);
new wire($map->personnes[122],$map->personnes[101],3);
new wire($map->personnes[122],$map->personnes[103],2);
new wire($map->personnes[122],$map->personnes[114],1);
new wire($map->personnes[122],$map->personnes[125],2);
new wire($map->personnes[122],$map->personnes[127],3);
new wire($map->personnes[122],$map->personnes[134],1);
new wire($map->personnes[122],$map->personnes[138],2);
new wire($map->personnes[122],$map->personnes[142],3);
new wire($map->personnes[122],$map->personnes[147],1);
new wire($map->personnes[122],$map->personnes[150],1);
new wire($map->personnes[122],$map->personnes[151],16);
new wire($map->personnes[122],$map->personnes[165],1);
new wire($map->personnes[122],$map->personnes[167],1);
new wire($map->personnes[123],$map->personnes[14],6);
new wire($map->personnes[123],$map->personnes[18],4);
new wire($map->personnes[123],$map->personnes[33],8);
new wire($map->personnes[123],$map->personnes[100],1);
new wire($map->personnes[123],$map->personnes[103],3);
new wire($map->personnes[123],$map->personnes[114],1);
new wire($map->personnes[123],$map->personnes[121],2);
new wire($map->personnes[123],$map->personnes[124],19);
new wire($map->personnes[123],$map->personnes[138],1);
new wire($map->personnes[123],$map->personnes[141],2);
new wire($map->personnes[123],$map->personnes[147],1);
new wire($map->personnes[123],$map->personnes[151],1);
new wire($map->personnes[123],$map->personnes[162],2);
new wire($map->personnes[123],$map->personnes[165],2);
new wire($map->personnes[123],$map->personnes[169],2);
new wire($map->personnes[124],$map->personnes[4],10);
new wire($map->personnes[124],$map->personnes[14],2);
new wire($map->personnes[124],$map->personnes[100],4);
new wire($map->personnes[124],$map->personnes[103],6);
new wire($map->personnes[124],$map->personnes[115],1);
new wire($map->personnes[124],$map->personnes[121],1);
new wire($map->personnes[124],$map->personnes[123],19);
new wire($map->personnes[124],$map->personnes[130],1);
new wire($map->personnes[124],$map->personnes[142],4);
new wire($map->personnes[124],$map->personnes[158],2);
new wire($map->personnes[124],$map->personnes[165],6);
new wire($map->personnes[125],$map->personnes[14],2);
new wire($map->personnes[125],$map->personnes[31],3);
new wire($map->personnes[125],$map->personnes[32],4);
new wire($map->personnes[125],$map->personnes[57],1);
new wire($map->personnes[125],$map->personnes[59],1);
new wire($map->personnes[125],$map->personnes[73],5);
new wire($map->personnes[125],$map->personnes[83],1);
new wire($map->personnes[125],$map->personnes[122],2);
new wire($map->personnes[125],$map->personnes[130],1);
new wire($map->personnes[125],$map->personnes[153],4);
new wire($map->personnes[125],$map->personnes[158],1);
new wire($map->personnes[125],$map->personnes[168],1);
new wire($map->personnes[127],$map->personnes[68],4);
new wire($map->personnes[127],$map->personnes[121],1);
new wire($map->personnes[127],$map->personnes[122],3);
new wire($map->personnes[128],$map->personnes[69],1);
new wire($map->personnes[128],$map->personnes[116],2);
new wire($map->personnes[128],$map->personnes[129],1);
new wire($map->personnes[129],$map->personnes[49],1);
new wire($map->personnes[129],$map->personnes[69],1);
new wire($map->personnes[129],$map->personnes[94],1);
new wire($map->personnes[129],$map->personnes[116],1);
new wire($map->personnes[129],$map->personnes[128],1);
new wire($map->personnes[129],$map->personnes[135],1);
new wire($map->personnes[130],$map->personnes[10],7);
new wire($map->personnes[130],$map->personnes[13],1);
new wire($map->personnes[130],$map->personnes[26],20);
new wire($map->personnes[130],$map->personnes[31],4);
new wire($map->personnes[130],$map->personnes[32],11);
new wire($map->personnes[130],$map->personnes[33],7);
new wire($map->personnes[130],$map->personnes[44],6);
new wire($map->personnes[130],$map->personnes[50],1);
new wire($map->personnes[130],$map->personnes[57],7);
new wire($map->personnes[130],$map->personnes[60],5);
new wire($map->personnes[130],$map->personnes[70],1);
new wire($map->personnes[130],$map->personnes[72],1);
new wire($map->personnes[130],$map->personnes[83],2);
new wire($map->personnes[130],$map->personnes[98],1);
new wire($map->personnes[130],$map->personnes[101],1);
new wire($map->personnes[130],$map->personnes[112],1);
new wire($map->personnes[130],$map->personnes[113],8);
new wire($map->personnes[130],$map->personnes[114],4);
new wire($map->personnes[130],$map->personnes[115],1);
new wire($map->personnes[130],$map->personnes[124],1);
new wire($map->personnes[130],$map->personnes[125],1);
new wire($map->personnes[130],$map->personnes[132],7);
new wire($map->personnes[130],$map->personnes[134],1);
new wire($map->personnes[130],$map->personnes[135],1);
new wire($map->personnes[130],$map->personnes[138],8);
new wire($map->personnes[130],$map->personnes[141],2);
new wire($map->personnes[130],$map->personnes[142],7);
new wire($map->personnes[130],$map->personnes[145],11);
new wire($map->personnes[130],$map->personnes[150],2);
new wire($map->personnes[130],$map->personnes[151],4);
new wire($map->personnes[130],$map->personnes[152],2);
new wire($map->personnes[130],$map->personnes[158],7);
new wire($map->personnes[130],$map->personnes[162],1);
new wire($map->personnes[130],$map->personnes[165],9);
new wire($map->personnes[130],$map->personnes[168],1);
new wire($map->personnes[130],$map->personnes[169],6);
new wire($map->personnes[132],$map->personnes[10],1);
new wire($map->personnes[132],$map->personnes[26],5);
new wire($map->personnes[132],$map->personnes[31],1);
new wire($map->personnes[132],$map->personnes[32],6);
new wire($map->personnes[132],$map->personnes[33],1);
new wire($map->personnes[132],$map->personnes[44],6);
new wire($map->personnes[132],$map->personnes[57],1);
new wire($map->personnes[132],$map->personnes[83],1);
new wire($map->personnes[132],$map->personnes[98],1);
new wire($map->personnes[132],$map->personnes[130],7);
new wire($map->personnes[132],$map->personnes[142],3);
new wire($map->personnes[132],$map->personnes[169],4);
new wire($map->personnes[133],$map->personnes[94],7);
new wire($map->personnes[133],$map->personnes[142],2);
new wire($map->personnes[133],$map->personnes[169],3);
new wire($map->personnes[134],$map->personnes[1],2);
new wire($map->personnes[134],$map->personnes[4],8);
new wire($map->personnes[134],$map->personnes[5],6);
new wire($map->personnes[134],$map->personnes[10],1);
new wire($map->personnes[134],$map->personnes[57],2);
new wire($map->personnes[134],$map->personnes[60],1);
new wire($map->personnes[134],$map->personnes[72],1);
new wire($map->personnes[134],$map->personnes[73],2);
new wire($map->personnes[134],$map->personnes[100],2);
new wire($map->personnes[134],$map->personnes[113],9);
new wire($map->personnes[134],$map->personnes[116],1);
new wire($map->personnes[134],$map->personnes[122],1);
new wire($map->personnes[134],$map->personnes[130],1);
new wire($map->personnes[134],$map->personnes[135],29);
new wire($map->personnes[134],$map->personnes[138],1);
new wire($map->personnes[134],$map->personnes[141],18);
new wire($map->personnes[134],$map->personnes[142],6);
new wire($map->personnes[134],$map->personnes[145],4);
new wire($map->personnes[134],$map->personnes[147],3);
new wire($map->personnes[134],$map->personnes[150],4);
new wire($map->personnes[134],$map->personnes[151],21);
new wire($map->personnes[134],$map->personnes[158],3);
new wire($map->personnes[134],$map->personnes[165],2);
new wire($map->personnes[135],$map->personnes[1],2);
new wire($map->personnes[135],$map->personnes[4],6);
new wire($map->personnes[135],$map->personnes[5],4);
new wire($map->personnes[135],$map->personnes[14],1);
new wire($map->personnes[135],$map->personnes[26],1);
new wire($map->personnes[135],$map->personnes[31],1);
new wire($map->personnes[135],$map->personnes[33],2);
new wire($map->personnes[135],$map->personnes[73],1);
new wire($map->personnes[135],$map->personnes[101],1);
new wire($map->personnes[135],$map->personnes[113],6);
new wire($map->personnes[135],$map->personnes[116],1);
new wire($map->personnes[135],$map->personnes[129],1);
new wire($map->personnes[135],$map->personnes[130],1);
new wire($map->personnes[135],$map->personnes[134],29);
new wire($map->personnes[135],$map->personnes[138],2);
new wire($map->personnes[135],$map->personnes[142],2);
new wire($map->personnes[135],$map->personnes[145],5);
new wire($map->personnes[135],$map->personnes[147],10);
new wire($map->personnes[135],$map->personnes[150],6);
new wire($map->personnes[135],$map->personnes[151],3);
new wire($map->personnes[135],$map->personnes[158],1);
new wire($map->personnes[135],$map->personnes[165],1);
new wire($map->personnes[138],$map->personnes[1],3);
new wire($map->personnes[138],$map->personnes[4],3);
new wire($map->personnes[138],$map->personnes[5],1);
new wire($map->personnes[138],$map->personnes[18],1);
new wire($map->personnes[138],$map->personnes[22],1);
new wire($map->personnes[138],$map->personnes[33],1);
new wire($map->personnes[138],$map->personnes[72],1);
new wire($map->personnes[138],$map->personnes[73],1);
new wire($map->personnes[138],$map->personnes[85],1);
new wire($map->personnes[138],$map->personnes[103],1);
new wire($map->personnes[138],$map->personnes[113],11);
new wire($map->personnes[138],$map->personnes[116],2);
new wire($map->personnes[138],$map->personnes[121],1);
new wire($map->personnes[138],$map->personnes[122],2);
new wire($map->personnes[138],$map->personnes[123],1);
new wire($map->personnes[138],$map->personnes[130],8);
new wire($map->personnes[138],$map->personnes[134],1);
new wire($map->personnes[138],$map->personnes[135],2);
new wire($map->personnes[138],$map->personnes[141],29);
new wire($map->personnes[138],$map->personnes[142],3);
new wire($map->personnes[138],$map->personnes[144],1);
new wire($map->personnes[138],$map->personnes[145],15);
new wire($map->personnes[138],$map->personnes[147],2);
new wire($map->personnes[138],$map->personnes[150],3);
new wire($map->personnes[138],$map->personnes[151],24);
new wire($map->personnes[138],$map->personnes[158],2);
new wire($map->personnes[138],$map->personnes[165],4);
new wire($map->personnes[138],$map->personnes[169],14);
new wire($map->personnes[139],$map->personnes[145],1);
new wire($map->personnes[139],$map->personnes[165],1);
new wire($map->personnes[140],$map->personnes[50],1);
new wire($map->personnes[140],$map->personnes[83],1);
new wire($map->personnes[141],$map->personnes[1],1);
new wire($map->personnes[141],$map->personnes[4],4);
new wire($map->personnes[141],$map->personnes[5],3);
new wire($map->personnes[141],$map->personnes[14],21);
new wire($map->personnes[141],$map->personnes[18],6);
new wire($map->personnes[141],$map->personnes[26],21);
new wire($map->personnes[141],$map->personnes[32],5);
new wire($map->personnes[141],$map->personnes[33],30);
new wire($map->personnes[141],$map->personnes[57],9);
new wire($map->personnes[141],$map->personnes[68],1);
new wire($map->personnes[141],$map->personnes[72],3);
new wire($map->personnes[141],$map->personnes[73],4);
new wire($map->personnes[141],$map->personnes[83],2);
new wire($map->personnes[141],$map->personnes[85],5);
new wire($map->personnes[141],$map->personnes[100],1);
new wire($map->personnes[141],$map->personnes[101],1);
new wire($map->personnes[141],$map->personnes[103],2);
new wire($map->personnes[141],$map->personnes[113],6);
new wire($map->personnes[141],$map->personnes[114],3);
new wire($map->personnes[141],$map->personnes[116],1);
new wire($map->personnes[141],$map->personnes[123],2);
new wire($map->personnes[141],$map->personnes[130],2);
new wire($map->personnes[141],$map->personnes[134],18);
new wire($map->personnes[141],$map->personnes[138],29);
new wire($map->personnes[141],$map->personnes[142],6);
new wire($map->personnes[141],$map->personnes[145],4);
new wire($map->personnes[141],$map->personnes[147],2);
new wire($map->personnes[141],$map->personnes[150],4);
new wire($map->personnes[141],$map->personnes[151],58);
new wire($map->personnes[141],$map->personnes[158],2);
new wire($map->personnes[141],$map->personnes[165],3);
new wire($map->personnes[141],$map->personnes[169],15);
new wire($map->personnes[142],$map->personnes[4],7);
new wire($map->personnes[142],$map->personnes[5],7);
new wire($map->personnes[142],$map->personnes[10],4);
new wire($map->personnes[142],$map->personnes[18],1);
new wire($map->personnes[142],$map->personnes[26],8);
new wire($map->personnes[142],$map->personnes[31],3);
new wire($map->personnes[142],$map->personnes[32],5);
new wire($map->personnes[142],$map->personnes[33],4);
new wire($map->personnes[142],$map->personnes[44],7);
new wire($map->personnes[142],$map->personnes[57],2);
new wire($map->personnes[142],$map->personnes[60],4);
new wire($map->personnes[142],$map->personnes[72],1);
new wire($map->personnes[142],$map->personnes[94],1);
new wire($map->personnes[142],$map->personnes[100],2);
new wire($map->personnes[142],$map->personnes[103],1);
new wire($map->personnes[142],$map->personnes[112],1);
new wire($map->personnes[142],$map->personnes[122],3);
new wire($map->personnes[142],$map->personnes[124],4);
new wire($map->personnes[142],$map->personnes[130],7);
new wire($map->personnes[142],$map->personnes[132],3);
new wire($map->personnes[142],$map->personnes[133],2);
new wire($map->personnes[142],$map->personnes[134],6);
new wire($map->personnes[142],$map->personnes[135],2);
new wire($map->personnes[142],$map->personnes[138],3);
new wire($map->personnes[142],$map->personnes[141],6);
new wire($map->personnes[142],$map->personnes[145],1);
new wire($map->personnes[142],$map->personnes[151],7);
new wire($map->personnes[142],$map->personnes[158],2);
new wire($map->personnes[142],$map->personnes[165],7);
new wire($map->personnes[142],$map->personnes[169],2);
new wire($map->personnes[144],$map->personnes[138],1);
new wire($map->personnes[144],$map->personnes[145],1);
new wire($map->personnes[145],$map->personnes[1],1);
new wire($map->personnes[145],$map->personnes[14],2);
new wire($map->personnes[145],$map->personnes[26],1);
new wire($map->personnes[145],$map->personnes[31],1);
new wire($map->personnes[145],$map->personnes[33],2);
new wire($map->personnes[145],$map->personnes[72],3);
new wire($map->personnes[145],$map->personnes[73],1);
new wire($map->personnes[145],$map->personnes[101],1);
new wire($map->personnes[145],$map->personnes[113],10);
new wire($map->personnes[145],$map->personnes[130],11);
new wire($map->personnes[145],$map->personnes[134],4);
new wire($map->personnes[145],$map->personnes[135],5);
new wire($map->personnes[145],$map->personnes[138],15);
new wire($map->personnes[145],$map->personnes[139],1);
new wire($map->personnes[145],$map->personnes[141],4);
new wire($map->personnes[145],$map->personnes[142],1);
new wire($map->personnes[145],$map->personnes[144],1);
new wire($map->personnes[145],$map->personnes[147],1);
new wire($map->personnes[145],$map->personnes[150],4);
new wire($map->personnes[145],$map->personnes[151],8);
new wire($map->personnes[145],$map->personnes[158],4);
new wire($map->personnes[145],$map->personnes[162],1);
new wire($map->personnes[145],$map->personnes[165],13);
new wire($map->personnes[145],$map->personnes[169],5);
new wire($map->personnes[147],$map->personnes[4],1);
new wire($map->personnes[147],$map->personnes[113],2);
new wire($map->personnes[147],$map->personnes[122],1);
new wire($map->personnes[147],$map->personnes[123],1);
new wire($map->personnes[147],$map->personnes[134],3);
new wire($map->personnes[147],$map->personnes[135],10);
new wire($map->personnes[147],$map->personnes[138],2);
new wire($map->personnes[147],$map->personnes[141],2);
new wire($map->personnes[147],$map->personnes[145],1);
new wire($map->personnes[147],$map->personnes[150],8);
new wire($map->personnes[147],$map->personnes[151],12);
new wire($map->personnes[147],$map->personnes[158],1);
new wire($map->personnes[147],$map->personnes[165],1);
new wire($map->personnes[150],$map->personnes[1],3);
new wire($map->personnes[150],$map->personnes[10],1);
new wire($map->personnes[150],$map->personnes[22],1);
new wire($map->personnes[150],$map->personnes[33],3);
new wire($map->personnes[150],$map->personnes[44],1);
new wire($map->personnes[150],$map->personnes[66],1);
new wire($map->personnes[150],$map->personnes[70],2);
new wire($map->personnes[150],$map->personnes[72],3);
new wire($map->personnes[150],$map->personnes[85],1);
new wire($map->personnes[150],$map->personnes[101],1);
new wire($map->personnes[150],$map->personnes[113],7);
new wire($map->personnes[150],$map->personnes[114],1);
new wire($map->personnes[150],$map->personnes[115],9);
new wire($map->personnes[150],$map->personnes[116],4);
new wire($map->personnes[150],$map->personnes[122],1);
new wire($map->personnes[150],$map->personnes[130],2);
new wire($map->personnes[150],$map->personnes[134],4);
new wire($map->personnes[150],$map->personnes[135],6);
new wire($map->personnes[150],$map->personnes[138],3);
new wire($map->personnes[150],$map->personnes[141],4);
new wire($map->personnes[150],$map->personnes[145],4);
new wire($map->personnes[150],$map->personnes[147],8);
new wire($map->personnes[150],$map->personnes[151],5);
new wire($map->personnes[150],$map->personnes[158],38);
new wire($map->personnes[150],$map->personnes[161],1);
new wire($map->personnes[150],$map->personnes[165],3);
new wire($map->personnes[150],$map->personnes[169],1);
new wire($map->personnes[151],$map->personnes[1],5);
new wire($map->personnes[151],$map->personnes[4],2);
new wire($map->personnes[151],$map->personnes[5],12);
new wire($map->personnes[151],$map->personnes[10],1);
new wire($map->personnes[151],$map->personnes[14],7);
new wire($map->personnes[151],$map->personnes[18],5);
new wire($map->personnes[151],$map->personnes[26],15);
new wire($map->personnes[151],$map->personnes[32],5);
new wire($map->personnes[151],$map->personnes[33],21);
new wire($map->personnes[151],$map->personnes[57],3);
new wire($map->personnes[151],$map->personnes[72],1);
new wire($map->personnes[151],$map->personnes[73],2);
new wire($map->personnes[151],$map->personnes[98],1);
new wire($map->personnes[151],$map->personnes[99],1);
new wire($map->personnes[151],$map->personnes[103],3);
new wire($map->personnes[151],$map->personnes[113],4);
new wire($map->personnes[151],$map->personnes[114],6);
new wire($map->personnes[151],$map->personnes[122],16);
new wire($map->personnes[151],$map->personnes[123],1);
new wire($map->personnes[151],$map->personnes[130],4);
new wire($map->personnes[151],$map->personnes[134],21);
new wire($map->personnes[151],$map->personnes[135],3);
new wire($map->personnes[151],$map->personnes[138],24);
new wire($map->personnes[151],$map->personnes[141],58);
new wire($map->personnes[151],$map->personnes[142],7);
new wire($map->personnes[151],$map->personnes[145],8);
new wire($map->personnes[151],$map->personnes[147],12);
new wire($map->personnes[151],$map->personnes[150],5);
new wire($map->personnes[151],$map->personnes[152],2);
new wire($map->personnes[151],$map->personnes[158],3);
new wire($map->personnes[151],$map->personnes[165],6);
new wire($map->personnes[151],$map->personnes[167],5);
new wire($map->personnes[151],$map->personnes[169],6);
new wire($map->personnes[152],$map->personnes[70],1);
new wire($map->personnes[152],$map->personnes[130],2);
new wire($map->personnes[152],$map->personnes[151],2);
new wire($map->personnes[153],$map->personnes[31],9);
new wire($map->personnes[153],$map->personnes[32],5);
new wire($map->personnes[153],$map->personnes[73],6);
new wire($map->personnes[153],$map->personnes[125],4);
new wire($map->personnes[155],$map->personnes[113],1);
new wire($map->personnes[158],$map->personnes[72],2);
new wire($map->personnes[158],$map->personnes[113],5);
new wire($map->personnes[158],$map->personnes[115],5);
new wire($map->personnes[158],$map->personnes[124],2);
new wire($map->personnes[158],$map->personnes[125],1);
new wire($map->personnes[158],$map->personnes[130],7);
new wire($map->personnes[158],$map->personnes[134],3);
new wire($map->personnes[158],$map->personnes[135],1);
new wire($map->personnes[158],$map->personnes[138],2);
new wire($map->personnes[158],$map->personnes[141],2);
new wire($map->personnes[158],$map->personnes[142],2);
new wire($map->personnes[158],$map->personnes[145],4);
new wire($map->personnes[158],$map->personnes[147],1);
new wire($map->personnes[158],$map->personnes[150],38);
new wire($map->personnes[158],$map->personnes[151],3);
new wire($map->personnes[158],$map->personnes[165],1);
new wire($map->personnes[158],$map->personnes[168],1);
new wire($map->personnes[158],$map->personnes[169],2);
new wire($map->personnes[161],$map->personnes[22],1);
new wire($map->personnes[161],$map->personnes[72],1);
new wire($map->personnes[161],$map->personnes[114],1);
new wire($map->personnes[161],$map->personnes[115],1);
new wire($map->personnes[161],$map->personnes[150],1);
new wire($map->personnes[162],$map->personnes[5],1);
new wire($map->personnes[162],$map->personnes[13],1);
new wire($map->personnes[162],$map->personnes[32],2);
new wire($map->personnes[162],$map->personnes[44],1);
new wire($map->personnes[162],$map->personnes[50],1);
new wire($map->personnes[162],$map->personnes[57],4);
new wire($map->personnes[162],$map->personnes[60],2);
new wire($map->personnes[162],$map->personnes[83],1);
new wire($map->personnes[162],$map->personnes[123],2);
new wire($map->personnes[162],$map->personnes[130],1);
new wire($map->personnes[162],$map->personnes[145],1);
new wire($map->personnes[162],$map->personnes[164],1);
new wire($map->personnes[162],$map->personnes[165],2);
new wire($map->personnes[163],$map->personnes[168],1);
new wire($map->personnes[164],$map->personnes[5],1);
new wire($map->personnes[164],$map->personnes[162],1);
new wire($map->personnes[165],$map->personnes[4],2);
new wire($map->personnes[165],$map->personnes[5],1);
new wire($map->personnes[165],$map->personnes[10],4);
new wire($map->personnes[165],$map->personnes[14],2);
new wire($map->personnes[165],$map->personnes[18],1);
new wire($map->personnes[165],$map->personnes[26],8);
new wire($map->personnes[165],$map->personnes[31],3);
new wire($map->personnes[165],$map->personnes[32],5);
new wire($map->personnes[165],$map->personnes[33],6);
new wire($map->personnes[165],$map->personnes[34],1);
new wire($map->personnes[165],$map->personnes[44],5);
new wire($map->personnes[165],$map->personnes[50],1);
new wire($map->personnes[165],$map->personnes[57],4);
new wire($map->personnes[165],$map->personnes[60],5);
new wire($map->personnes[165],$map->personnes[73],3);
new wire($map->personnes[165],$map->personnes[83],3);
new wire($map->personnes[165],$map->personnes[100],2);
new wire($map->personnes[165],$map->personnes[101],1);
new wire($map->personnes[165],$map->personnes[112],1);
new wire($map->personnes[165],$map->personnes[113],2);
new wire($map->personnes[165],$map->personnes[122],1);
new wire($map->personnes[165],$map->personnes[123],2);
new wire($map->personnes[165],$map->personnes[124],6);
new wire($map->personnes[165],$map->personnes[130],9);
new wire($map->personnes[165],$map->personnes[134],2);
new wire($map->personnes[165],$map->personnes[135],1);
new wire($map->personnes[165],$map->personnes[138],4);
new wire($map->personnes[165],$map->personnes[139],1);
new wire($map->personnes[165],$map->personnes[141],3);
new wire($map->personnes[165],$map->personnes[142],7);
new wire($map->personnes[165],$map->personnes[145],13);
new wire($map->personnes[165],$map->personnes[147],1);
new wire($map->personnes[165],$map->personnes[150],3);
new wire($map->personnes[165],$map->personnes[151],6);
new wire($map->personnes[165],$map->personnes[158],1);
new wire($map->personnes[165],$map->personnes[162],2);
new wire($map->personnes[165],$map->personnes[169],2);
new wire($map->personnes[167],$map->personnes[1],1);
new wire($map->personnes[167],$map->personnes[4],7);
new wire($map->personnes[167],$map->personnes[5],10);
new wire($map->personnes[167],$map->personnes[14],1);
new wire($map->personnes[167],$map->personnes[33],2);
new wire($map->personnes[167],$map->personnes[100],1);
new wire($map->personnes[167],$map->personnes[103],1);
new wire($map->personnes[167],$map->personnes[122],1);
new wire($map->personnes[167],$map->personnes[151],5);
new wire($map->personnes[168],$map->personnes[33],1);
new wire($map->personnes[168],$map->personnes[57],1);
new wire($map->personnes[168],$map->personnes[60],1);
new wire($map->personnes[168],$map->personnes[125],1);
new wire($map->personnes[168],$map->personnes[130],1);
new wire($map->personnes[168],$map->personnes[158],1);
new wire($map->personnes[168],$map->personnes[163],1);
new wire($map->personnes[169],$map->personnes[1],2);
new wire($map->personnes[169],$map->personnes[4],1);
new wire($map->personnes[169],$map->personnes[5],1);
new wire($map->personnes[169],$map->personnes[26],2);
new wire($map->personnes[169],$map->personnes[98],1);
new wire($map->personnes[169],$map->personnes[103],2);
new wire($map->personnes[169],$map->personnes[113],2);
new wire($map->personnes[169],$map->personnes[114],1);
new wire($map->personnes[169],$map->personnes[123],2);
new wire($map->personnes[169],$map->personnes[130],6);
new wire($map->personnes[169],$map->personnes[132],4);
new wire($map->personnes[169],$map->personnes[133],3);
new wire($map->personnes[169],$map->personnes[138],14);
new wire($map->personnes[169],$map->personnes[141],15);
new wire($map->personnes[169],$map->personnes[142],2);
new wire($map->personnes[169],$map->personnes[145],5);
new wire($map->personnes[169],$map->personnes[150],1);
new wire($map->personnes[169],$map->personnes[151],6);
new wire($map->personnes[169],$map->personnes[158],2);
new wire($map->personnes[169],$map->personnes[165],2);

$tension_max=119;
*/
$step = 50;
if ( isset($_GET["step"]) )
   $step = intval($_GET["step"]);

for($i=0;$i<$step;$i++)
{
  echo "Step $i : ";
  $st = microtime(true);
  $map->poll();
  echo "done in ".(microtime(true)-$st)."seconds<br/>\n";
  //$map->echo_infos();
}


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
