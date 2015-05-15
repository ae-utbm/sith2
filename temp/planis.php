<?php

define('RAYON_TERRE', 6400);



$dbconn = pg_connect("host=localhost dbname=geography user=geography password=geography");


$req = pg_query("SELECT name AS nom, AsText(Transform(simplify(the_geom, 0.1), 3395)) AS points FROM worldadmwgs WHERE region != 'Antarctica'");


$rs = pg_fetch_all($req);

foreach($rs as $result)
{
    $astext .= $result['points'];
}

preg_match_all("/\(([^)]*)\)/", $astext, $matched);

$i = 0;

foreach ($matched[1] as $polygon)
{
  $polygon = str_replace("(", "", $polygon);
  $points = explode(",", $polygon);

  foreach ($points as $point)
    {
      $point = explode (" ", $point);
      //      $totalpoints[$i][] = array(rad2deg($point[0]) * RAYON_TERRE,
      //				 rad2deg($point[1]) * RAYON_TERRE);
      $totalpoints[$i][] = $point;
    }

  $i++;
}


/** Now we have every points of the polygons, and ready to (try to) plot this ! **/




/** STEP 1 : try to determine a minima / maxima for dimension of the image */
$tot_min_latitude = 0;
$tot_max_latitude = 0;

$tot_min_longitude = 0;
$tot_max_longitude = 0;

foreach ($totalpoints as $polygon)
{
  foreach($polygon as $point)
    {
      /* max latitude ? */
      if ($point[0] > $tot_max_latitude)
	$tot_max_latitude = $point[0];
      /* min latitude ? */
      if ($point[0] < $tot_min_latitude)
	$tot_min_latitude = $point[0];

      /* max longitude */
      if ($point[1] > $tot_max_longitude)
	$tot_max_longitude = $point[1];

      /* min longitude */
      if ($point[1] < $tot_min_longitude)
	$tot_min_longitude = $point[1];
    }
}

/* SCALE !!! 1px = ??? meters */

$factor = 50000;

$yfactor = 1;


/* offset (border) in px */

$offset = 10;



/* STEP 2 : Time to do some (useless ?) calculus */

$i = 0;

foreach ($totalpoints as $polygon)
{

  foreach($polygon as $point)
    {
      $sommetsx[$i][] = (int) ($point[0] / $factor);
      $sommetsy[$i][] = (int) ($point[1] / $factor);
    }

  $i++;
}


for ($i = 0; $i < count($rtpoints); $i++)
{
  $rtpoints[$i][0] = (int) ($rtpoints[$i][0] / $factor);
  $rtpoints[$i][1] = (int) ($rtpoints[$i][1] / $factor);
}

/* we've to get minimas / maximas for polygons */
$minlat = 0;
$minlng = 0;

$maxlat = 0;
$minlng = 0;

for ($i = 0; $i < count($sommetsx); $i++)
{
  if (min($sommetsx[$i]) < $minlat)
    $minlat = min($sommetsx[$i]);

  if (max($sommetsx[$i]) > $maxlat)
    $maxlat = max($sommetsx[$i]);

  if (min($sommetsy[$i]) < $minlng)
    $minlng = min($sommetsy[$i]);

  if (max($sommetsy[$i]) > $maxlng)
    $maxlng = max($sommetsy[$i]);
}

/* recalculating lat/long -> px/px */
for ($i = 0; $i < count($sommetsx) ; $i++)
{
  for ($j = 0; $j < count($sommetsx[$i]); $j++)
    {
      $sommetsx[$i][$j] = $sommetsx[$i][$j] - $minlat + $offset;
      $sommetsy[$i][$j] = $sommetsy[$i][$j] - $minlng + $offset;
    }
}


/* recalculating trajet points */
for ($i = 0 ; $i < count($rtpoints); $i++)
{
  $rtpoints[$i][0] = $rtpoints[$i][0] - $minlat + $offset;
  $rtpoints[$i][1] = $rtpoints[$i][1] - $minlng + $offset;
}


$dimx = $maxlat - $minlat + $offset * 2;
$dimy = $maxlng - $minlng + $offset * 2;

$dimy *= $yfactor;

if (($dimx > 40000) || ($dimy > 15000))
     die ("LAST CHANCE : image too large !!!\n");

$tol = $dimx / 1.2;

for ($i = 0; $i < count($sommetsx); $i++)
{
  //  unset($plg);
  for ($j = 0; $j < count($sommetsx[$i]); $j++)
    {
      if (($j > 0) && (checkcoords($plg[$i][count($plg[$i]) - 2],
				   $plg[$i][count($plg[$i]) - 1],
				   $sommetsx[$i][$j],
				   $dimy - $sommetsy[$i][$j] * $yfactor,
				   $tol)))
	{
	  $plg[$i][] = $sommetsx[$i][$j];
	  $plg[$i][] = $dimy - $sommetsy[$i][$j] * $yfactor;
	  $ycoords[] = $plg[$i][count($plg[$i]) - 1];
	}
    }
}


function checkcoords($lx, $ly, $x, $y, $tolerance)
{
  if (sqrt(pow($x - $lx, 2) + pow($y - $ly, 2)) > $tolerance)
    return false;
  return true;
}

$dimy = max($ycoords) + $offset;

/* STEP 3 Actually draw something ! */
$img = imagecreatetruecolor((int) $dimx, (int) $dimy);

$white = imagecolorallocate($img, 255,255,255);
$black = imagecolorallocate($img, 0,0,0);
$red   = imagecolorallocate($img, 255, 0, 0);

imagefill($img, 0,0, $white);

foreach($plg as $polygone)
{
  imagefilledpolygon($img, $polygone, count($polygone) / 2, $black);
}



$topdir = "../";
require_once($topdir . "include/watermark.inc.php");
$wm = new img_watermark($img);
$wm->output();

/*
header("Content-Type: image/png");
imagepng($img);
*/

?>
