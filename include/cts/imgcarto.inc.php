<?
/*
 * @brief Classe de traçage d'objets géographiques. 
 *
 */
/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Ãtudiants de
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

define ("IMG_MAX_WIDTH", 800);

class imgcarto
{
  /* objets graphiques à ajouter à l'image */

  var $lines = array();
  var $polygons = array();

  var $points = array();

  /* ressource image GD */
  var $imgres = null;
  /* Couleurs */
  var $colors = array();

  /* un facteur de division d'échelle */
  var $factor = 1.0;
  
  /* une valeur en pixels de décalage */
  var $offset = 10;

  /* dimensions */
  var $dimx, $dimy;

  var $errmsg;

  function imgcarto()
  {
    $this->addcolor("black", 0,0,0);
    $this->addcolor("red", 255, 0,0);
    $this->addcolor("blue", 0,0,255);
    $this->addcolor("white", 255,255,255);
    return;
  }
  
  function addcolor($def, $r,$g,$b)
  {
    /* on ne génère pas encore la couleur via imagecolorallocate()
     * étant donné que les dimensions de l'image ne sont pas encore
     * connues, et que l'image n'est pas encore créée.
     */

    $this->colors[$def] = array($r,$g,$b);
  }
  

  function addpoint($x, $y, $r, $color)
  {
    $this->points[] = array($x,$y,$r, $color);
  }

  function addline($x, $y, $fx, $fy, $color)
  {
    $this->lines[] = array($x,$y,$fx,$fy,$color);
  }
  function addpolygon($plg, $color, $filled = false)
  {
    $this->polygons[] = array($plg, $color, $filled);
  }

  function setfactor($factor)
  {
    $this->factor = $factor;
  }

  function setoffset($offset)
  {
    $this->offset = $offset;
  }
  /*
   * Fonction permettant de calculer les dimensions de l'image. Elle
   * permet en outre de recalculer les points si nécessaire
   * (coordonnées négatives à passer en "relativement positif" par
   * exemple).
   *
   */
  function calculatedimensions()
  {
    $max_x = 0;
    $min_x = 0;
    $max_y = 0;
    $min_y = 0;

    if (count($this->lines))
      {
	foreach ($this->lines as $line)
	  {
	    if ($max_x < $line[0])
	      $max_x = $line[0];
	    if ($min_x > $line[0])
	      $min_x = $line[0];

	    if ($max_y < $line[1])
	      $max_y = $line[1];
	    if ($min_y > $line[1])
	      $min_y = $line[1];

	    if ($max_x < $line[2])
	      $max_x = $line[2];
	    if ($min_x > $line[2])
	      $min_x = $line[2];

	    if ($max_y < $line[3])
	      $max_y = $line[3];
	    if ($min_y > $line[3])
	      $min_y = $line[3];
	  }
      } // end parsing lines

    if (count($this->points))
      {
	foreach ($this->points as $point)
	  {
	    if ($max_x < $point[0])
	      $max_x = $point[0];
	    if ($min_x > $point[0])
	      $min_x = $point[0];

	    if ($max_y < $point[1])
	      $max_y = $point[1];
	    if ($min_y > $point[1])
	      $min_y = $point[1];
	  }
      } // end parsing points

    if (count($this->polygons))
      {
	foreach ($this->polygons as $polygon)
	  {
	    for ($i = 0; $i < count($polygon[0]); $i+= 2)
	      {
		if ($max_x < $polygon[0][$i])
		  $max_x = $polygon[0][$i];
		if ($min_x > $polygon[0][$i])
		  $min_x = $polygon[0][$i];

		if ($max_y < $polygon[0][$i+1])
		  $max_y = $polygon[0][$i+1];
		if ($min_y > $polygon[0][$i+1])
		  $min_y = $polygon[0][$i+1];
	      }
	  }
      } // end parsing polygons

    
    $this->dimx = (($max_x - $min_x) / $this->factor) + $this->offset * 2;
    $this->dimy = (($max_y - $min_y) / $this->factor) + $this->offset * 2;

    $min_x = ($min_x / $this->factor);
    $min_y = ($min_y / $this->factor);

    /* on peut maintenant recalculer les coordonnées */
   
    // points
    if (count($this->points))
      {
	for ($i = 0; $i < count($this->points); $i++)
	  {
	    $this->points[$i][0] = ($this->points[$i][0] / $this->factor) - $min_x + $this->offset;
	    /* ATTENTION : inversion des ordonnées */
	    $this->points[$i][1] = $this->dimy - ($this->points[$i][1] / $this->factor) - $min_y + $this->offset;
	  }
      }
    // lines
    if (count($this->lines))
      {
	for ($i = 0; $i < count($this->lines); $i++)
	  {
	    $this->lines[$i][0] = ($this->lines[$i][0] / $this->factor) - $min_x + $this->offset;
	    /* attention : inversion ordonnées */
	    $this->lines[$i][1] = $this->dimy - ($this->lines[$i][1] / $this->factor) - $min_y + $this->offset;
	    
	    $this->lines[$i][2] = ($this->lines[$i][2] / $this->factor) - $min_x + $this->offset;
	    /* meme remarque ... */
	    $this->lines[$i][3] = $this->dimy - ($this->lines[$i][3] / $this->factor) - $min_y + $this->offset;
	  }

      }
    // polygons
    if (count($this->polygons))
      {
	for ($i = 0; $i < count($this->polygons); $i++)
	  {
	    for ($j = 0; $j < count($this->polygons[$i][0]); $j += 2)
	      {
		$this->polygons[$i][0][$j] =   ($this->polygons[$i][0][$j] / $this->factor) - $min_x + $this->offset;
		/* ATTENTION : invesion des ordonnées ! */
		$this->polygons[$i][0][$j+1] = $this->dimy - ($this->polygons[$i][0][$j+1] / $this->factor) - $min_y + $this->offset;
	      }
	  }
      }

    /* HACK ? on reparse (suite aux calculs) pour avoir la
     * bonne composante en y pour la dimension "hauteur" de l'image */
    $max_y = 0;
    $min_y = 0;

    if (count($this->lines))
      {
	foreach ($this->lines as $line)
	  {
	    if ($max_y < $line[1])
	      $max_y = $line[1];
	    if ($min_y > $line[1])
	      $min_y = $line[1];
	    if ($max_y < $line[3])
	      $max_y = $line[3];
	    if ($min_y > $line[3])
	      $min_y = $line[3];
	  }
      } // end parsing lines

    if (count($this->points))
      {
	foreach ($this->points as $point)
	  {
	    if ($max_y < $point[1])
	      $max_y = $point[1];
	    if ($min_y > $point[1])
	      $min_y = $point[1];
	  }
      } // end parsing points

    if (count($this->polygons))
      {
	foreach ($this->polygons as $polygon)
	  {
	    for ($i = 0; $i < count($polygon[0]); $i+= 2)
	      {
		if ($max_y < $polygon[0][$i+1])
		  $max_y = $polygon[0][$i+1];
		if ($min_y > $polygon[0][$i+1])
		  $min_y = $polygon[0][$i+1];
	      }
	  }
      } // end parsing polygons

    $this->dimy = ($max_y - $min_y) + $this->offset * 2;
  }

  function draw()
  {
    if ($max_x == 0)
      $this->calculatedimensions();

    if ($this->dimx > IMG_MAX_WIDTH)
      {
	$this->errmsg = "Image too large : $this->dimx:$this->dimy\n";
	return false;
      }

    $this->imgres = imagecreatetruecolor($this->dimx, $this->dimy);
    
    /* allocate colors */
    if (count($this->colors))
      {
	foreach ($this->colors as $key => $color)
	  {
	    $this->colors[$key]['gd'] = imagecolorallocate($this->imgres,
							   $color[0],
							   $color[1],
							   $color[2]);
	  }
      }

    imagefill($this->imgres, 0,0, $this->colors['white']['gd']);

    /* draw polygons */
    if (count($this->polygons))
      {
	foreach ($this->polygons as $polygon)
	  {
	    if ($polygon[2] == false)
	      imagepolygon($this->imgres, $polygon[0], count($polygon[0]) / 2, $this->colors[$polygon[1]]['gd']);
	    else
	      imagefilledpolygon($this->imgres, $polygon[0], count($polygon[0]) / 2, $this->colors[$polygon[1]]['gd']);
	  }
      }

    /* draw lines */
    if (count($this->lines))
      {
	foreach ($this->lines as $line)
	  {
	    imageline ($this->imgres,
		       $line[0],
		       $line[1],
		       $line[2],
		       $line[3],
		       $this->colors[$line[4]]['gd']);
	  }
      }
    /* draw points */
    if (count($this->points))
      {
	foreach ($this->points as $point)
	  {
	    imagefilledellipse ($this->imgres,
				$point[0],
				$point[1],
				$point[2],
				$point[2],
				$this->colors[$point[3]]['gd']);
	    
	  }

      }

  }
  function saveas($path)
  {
    if ($this->imgres)
      imagepng($this->imgres, $path);
  }

  function output()
  {
    if ($this->imgres)
      {
	header("Content-Type: image/png");
	imagepng($this->imgres);
      }
  }

  function destroy()
  {
    if ($imgres)
      imagedestroy($imgres);
  }

}

?>
