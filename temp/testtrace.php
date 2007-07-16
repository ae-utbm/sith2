<?

/* UTM et tout le toutim d'ellipsoïde qui va avec mes données : 
 *
 *  -  zone 1N : 32601 ->  zone 60N : 32660;
 *  -  zone 1S : 32701 ->  zone 60S : 32760;
 *
 */

$topdir = "../";

require_once($topdir . 'include/cts/imgcarto.inc.php');
require_once($topdir . 'include/pgsqlae.inc.php');



$dbconn = new pgsqlae();

$req = new pgrequete($dbconn,
		     "SELECT 
                            name AS nom
                            , ST_AsText(TRANSFORM(the_geom, 32631)) AS points 
                      FROM 
                            worldadmwgs
                      WHERE
                            name IN ('France')");

$rs = $req->get_all_rows();


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
      $coord = explode(" ", $point);
      $totalpoints[$i][] = $coord[0];
      $totalpoints[$i][] = $coord[1];
    }

  $i++;
}

$img = new imgcarto();

foreach($totalpoints as $plg)
{
  $img->addpolygon($plg, 'black', false);    
}
$rtpoints = array(array(460513, 5385217), // 1
		  array(453135, 5385765), // 2
		  array(438623, 5388193), // 3
		  array(420932, 5388193), // 4
		  array(402792, 5372371), // 5
		  array(383085, 5356467), // 6 // ???!?
		  array(365835, 5348786), // 7
		  array(329096, 5336143), // 8
		  array(286619, 5324588), // 9
		  array(243956, 5327818), // 10
		  array(200505, 5333266), // 11
		  array(158642, 5339048), // 12
		  array(121028, 5316705), // 13
		  array(104042, 5309117), // 14
		  array(92709,  5309395), // 15
		  array(86477,  5304332), // 16
		  array(76228,  5296226), // 17
		  array(62057,  5294850), // 18
		  array(51114,  5295455), // 19
		  array(49684,  5291693), // 20
		  array(53755,  5284015));


for ($i = 1; $i < count($rtpoints); $i++)
{
  $img->addline($rtpoints[$i-1][0],
		$rtpoints[$i-1][1],
		$rtpoints[$i][0],
		$rtpoints[$i][1],
		"black");
}

foreach ($rtpoints as $rtpoint)
{
  $img->addpoint($rtpoint[0], $rtpoint[1], 4, "red");
}


$req = pg_query("SELECT 
                        ST_AsText(TRANSFORM(the_geom, 32631)) AS points 
                 FROM 
                        deptfr
                 WHERE
                        nom_region NOT IN ('REUNION', 'MARTINIQUE', 'GUYANE', 'GUADELOUPE')");

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
      $coord = explode(" ", $point);
      $totalpoints[$i][] = $coord[0];
      $totalpoints[$i][] = $coord[1];
    }

  $i++;
}

$img->addcolor('grey', 90,90,90);

foreach($totalpoints as $plg)
{
  $img->addpolygon($plg, 'grey', false);    
}


$img->setfactor(1600);

$img->draw();

$img->output();

?>