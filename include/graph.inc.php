<?

/** @file
 *
 * Generation de graphes a la volee
 * utilisation de GnuPlot
 *
 */
/* Copyright 2005,2006
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Ã‰tudiants de
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

require_once ($topdir . "include/globals.inc.php");
require_once ($topdir . "include/watermark.inc.php");


class graphic
{
  /* un identifiant */
  var $graph_id;
  /* un tableau de coordoonnées
   *
   * $coords[k]['x'] => abscisse du k-ieme point
   *
   * $coords[k]['y'] => ordonnee du kieme point
   *
   * OU
   *
   * $coords[k]['y'][0] => ordonnee de la
   * premiere courbe du kieme point
   *
   * $coords[k]['y'][1] => ordonnee de la
   * 2eme courbe du kieme point, etc ...
   *
   **/
  var $coords;
  /* titre du graphique
   * (ou tableau de titres si plusieurs graphes a tracer)
   */
  var $title;
  /* une(des) legende(s) pour le tracé
   * Si plusieurs, envoyer un tableau */
  var $func_legend;

  /*
   * axe des abscisses temporel ou non.
   * false si non temporel
   *
   * array (type de donnees en entree, type de donnees affichees
   * en abscisses).
   *
   * ex : array ("%Y-%m-%d", "%d-%m"), pour avoir des donnees du type
   * "2006-04-04" dans le fichier de donnees (dans le $coord en abscisse),
   * mais affichant "04-04" sur le graphique.
   *
   */
  var $xaxis_time;
  /*
   * un array d'abscisses speciales
   * array (abscisse => "nom")
   */
  var $x_tics;
  /*
   * un array d'ordonnees speciales
   * array (ordonnees => "nom")
   */
  var $y_tics;

  /* fichiers */
  var $conf_file;
  var $data_file;
  var $img_file;

  /* constructeur */
  function graphic ($title,
		    $func_legend,
		    $coords = false,
		    $xaxis_time = false,
		    $x_tics = false,
		    $y_tics = false)
  {
    /* identifiant de graphique */
    $this->graph_id = substr(md5(microtime(true)), 0, 6);
    /* fichiers */
    $this->conf_file = "/tmp/" . $this->graph_id . ".conf";
    $this->data_file = "/tmp/" . $this->graph_id . ".data";
    $this->img_file = "/tmp/" . $this->graph_id . ".png";
    /* titre */
    $this->title = $title;
    /* axe temporel ? */
    $this->xaxis_time = $xaxis_time;
    /* legende(s) des fonctions tracees */
    $this->func_legend = $func_legend;

    /* abscisses speciales */
    $this->x_tics = $x_tics;

    /* ordonnees speciales */
    $this->y_tics = $y_tics;

    if ($coords)
      $this->coords = $coords;
  }

  /* generation du fichier de donnees */
  function generate_database ()
  {
    if ((!$this->coords) ||
	(!is_array($this->coords)))
      return false;

    sort ($this->coords);

    foreach ($this->coords as $plot)
      {
	/* une seule ordonnee */
	if (!is_array($plot['y']))
	  $datas .= $plot['x'] ." ".$plot['y'] . "\n";
	/* plusieurs ordonnees */
	else
	  {
	    $datas .= $plot['x'];
	    foreach ($plot['y'] as $ordonnees)
	      $datas .= (" " .$ordonnees);

	    $datas .= "\n";
	  }
      }
    file_put_contents($this->data_file, $datas);
    return true;
  }

  /* generation du fichier de configuration gnuplot
   *
   * @param xdata_time indique si oui ou non l'axe des x est une
   * interpretation en terme de dates.
   *
   */
  function generate_configuration ()
  {
    $datas = "set terminal png\n";
    $datas .= ("set output '". $this->img_file . "'\n");
    $datas .= ("set grid xtics ytics\n");
    $datas .= "\n";
    $datas .= "set timestamp\n";

    /* ranges en x - si abscisses non temporelles */
    if ($this->xaxis_time == false)
      {
	$min = $this->coords[0]['x'];
	$max = $this->coords[count($this->coords) - 1]['x'];
	$datas .= "set xrange [";
	$datas .= $min;
	$datas .= ":";
	$datas .= $max;
	$datas .= "]\n";
      }

    /* ranges en y */
    $ymin = 0;
    $ymax = 0;
    /* determination min / max de la(des) courbe(s) */
    if ( count($this->coords) > 0 )
    foreach ($this->coords as $coord)
      {
	/* une seule courbe en y */
	if (!is_array($coord['y']))
	  {
	    if ($coord['y'] < $ymin)
	      $ymin = $coord['y'];
	    if ($coord['y'] > $ymax)
	      $ymax = $coord['y'];
	  }
	/* plusieurs :'( */
	else
	  {
	    foreach ($coord['y'] as $value)
	      {
		if ($value < $ymin)
		  $ymin = $value;
		if ($value > $ymax)
		  $ymax = $value;
	      }
	  } // fin plusieurs courbes
      } // fin determination max
    $round = abs($ymin - $ymax) * 5 / 100;

    $ymin -= $round;
    $ymax += $round;

    $ymin = str_replace (",", ".", $ymin);
    $ymax = str_replace (",", ".", $ymax);

    $datas .= "set yrange [". $ymin . ":" . $ymax."]\n";
    // fin range en y

    /* abscisses temporelles */
    if ($this->xaxis_time != false)
      {
	$datas .= "set xdata time\n";
	$datas .= "set timefmt \"".$this->xaxis_time[0]. "\"\n";
	$datas .= "set format x \"".$this->xaxis_time[1]."\"\n";
      }

    /* abscisses speciales */
    if ($this->x_tics != false)
      {
	$datas .= "set xtics (";

	$total = count ($this->x_tics) - 1;
	foreach ($this->x_tics as $xtics => $legend)
	  {
	    /* si format temporel en abscisses */
	    if ($this->xaxis_time != false)
	      $xtics = "\"" . $xtics . "\"";
	    $datas .= ("\"" . $legend . "\" " . $xtics . ", ");
	  }
	/* on enleve la virgule finale */
	$datas = substr ($datas, 0, strlen ($datas) - 2);
	$datas .= ")\n";
      }

    /* ordonnees speciales */
    if ($this->y_tics != false)
      {
	$datas .= "set ytics (";

	$total = count ($this->y_tics) - 1;
	foreach ($this->y_tics as $ytics => $legend)
	  {
	    $datas .= ("\"" . $legend . "\" " . $ytics . ", ");
	  }
	/* on enleve la virgule finale */
	$datas = substr ($datas, 0, strlen ($datas) - 2);
	$datas .= ")\n";
      }

    $datas .= "set datafile missing \"-1\"\n";

    /* une seule courbe a "plotter" */
    if (! is_array ($this->func_legend))
      $datas .= ("plot \"".$this->data_file."\" \
               using 1:2 with lines title \"".$this->func_legend ."\" \
               smooth frequency\n");

    /* plusieurs */
    else
      {
	$nb_courbes = count($this->func_legend);
	$datas .= "plot ";

	for ($i=0; $i < ($nb_courbes - 1); $i++)
	  $datas .= ("\"".$this->data_file."\" \
		      using 1:".sprintf($i + 2)." with lines title \"".
		     $this->func_legend[$i] ."\",   \\" ."\n");

	$datas .= ("\"". $this->data_file ."\" \
		    using 1:".sprintf($nb_courbes + 1).
		   " with lines title \"".
		   $this->func_legend[$nb_courbes - 1] ."\"\n");
      }

    $datas .= "set style fill solid 0.25 border
               replot\n";

    file_put_contents($this->conf_file, $datas);
    return true;
  }

  function generate_graph ()
  {
    if (!file_exists($this->conf_file))
      $this->generate_configuration ();

    if (!file_exists($this->data_file))
      $this->generate_database ();

    //$info=array();
    //$ret=0;

    exec ("/usr/share/php5/exec/gnuplot " .$this->conf_file/*,$info,$ret*/);
    //print_r($info);
    //echo $ret;
  }

  /* fonction de rendu du png
   * Attention, le fichier est craché depuis sur la sortie standard !
   */
  function png_render ()
  {
    if (!file_exists ($this->img_file))
      $this->generate_graph ();

    /* tunage sauce AE */
    $img_wmarked = new img_watermark (imagecreatefrompng($this->img_file));
    $img_wmarked->save_image($this->img_file);
    $img_wmarked->destroy();


    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: image/png");
    header("Content-Disposition: inline; filename=".
	   basename($this->img_file));
    /*on envoie le fichier */
    readfile($this->img_file);

  }
  /* fonction de sauvegarde du graphe */
  function copy_png_to_file ($dest)
  {
    return copy($this->img_file, $dest);
  }
  /* destructeur (a appeller apres generation pour eviter que ca soit
   la foire dans /tmp ... */
  function destroy_graph ()
  {
    unlink ($this->data_file);
    unlink ($this->conf_file);
    unlink ($this->img_file);
    return;
  }
}


class histogram
{
  var $title;
  var $graph_id;

  var $conf_file;
  var $data_file;
  var $img_file;

  function histogram($plots, $title)
  {
    /* identifiant de graphique */
    $this->graph_id = substr(md5(microtime(true)), 0, 6);
    /* fichiers */
    $this->conf_file = "/tmp/" . $this->graph_id . ".conf";
    $this->data_file = "/tmp/" . $this->graph_id . ".data";
    $this->img_file = "/tmp/" . $this->graph_id . ".png";
    /* titre */
    $this->title = $title;


    $out_gplot = "set terminal png nocrop enhanced\n".
      "set output '". $this->img_file."'\n".
      "set style data histogram\n".
      "set style histogram cluster gap 1\n".
      "set style fill solid border -1\n".
      "set boxwidth 0.9\n".
      "set title \"".$title."\"\n".
      "set xtics border nomirror offset character 0,0,0\n".
      "plot '".$this->data_file."' using 2:xtic(1) title col";

    $minvalue = 0;
    $maxvalue = 0;

    

    foreach ($plots as $key => $value)
      {
	$out_data .= $key . "\t". $value."\n";	
	if ($value >= $maxvalue)
	  $maxvalue = $value;
	if ($value <= $minvalue)
	  $minvalue = $value;
      }

    file_put_contents($this->data_file, $out_data);
    file_put_contents($this->conf_file, $out_gplot);
 
  }

  function png_render ()
  {
    exec ("/usr/share/php5/exec/gnuplot-4.2 " .$this->conf_file);

    /* tunage sauce AE */
    $img_wmarked = new img_watermark (imagecreatefrompng($this->img_file));
    $img_wmarked->save_image($this->img_file);
    $img_wmarked->destroy();


    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: image/png");
    header("Content-Disposition: inline; filename=".
	   basename($this->img_file));
    /*on envoie le fichier */
    readfile($this->img_file);

  }


  function destroy()
  {
    @unlink($this->conf_file);
    @unlink($this->data_file);
    @unlink($this->img_file);
  }
}
