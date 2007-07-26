<?
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");
$dbrw = new mysqlae("rw");

if ( isset($_REQUEST["init"]) )
{

  // INITIALISATION
  echo "INITIALISATION : ";
  $st = microtime(true);
  
  new requete($dbrw,"TRUNCATE `galaxy_link`");  new requete($dbrw,"TRUNCATE `galaxy_star`");
  
  
  $req1 = new requete($dbrw, "SELECT p1.id_utilisateur, alias_utl
  FROM `sas_personnes_photos` AS `p1`
  INNER JOIN utilisateurs ON ( p1.id_utilisateur= utilisateurs.id_utilisateur )
  JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photo
  AND p1.id_utilisateur != p2.id_utilisateur )
  GROUP BY p1.id_utilisateur");
  
  $gx=0;
  $gy=0;
  
  $width = floor(sqrt($req1->lines));
  
  while ( $row = $req1->get_row() )
  {
    new insert($dbrw,"galaxy_star",array( "id_star"=>$row['id_utilisateur'], "x_star" => $gx, "y_star" => $gy ));
    
    $gx++;
    
    if ( $gx > $width )
    {
      $gx=0;
      $gy++;
    }
  }
  
  
  $req2 = new requete($dbrw, "SELECT COUNT( * ) as c, p1.id_utilisateur as u1, p2.id_utilisateur as u2
  FROM `sas_personnes_photos` AS `p1`
  JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photo
  AND p1.id_utilisateur != p2.id_utilisateur )
  GROUP BY p1.id_utilisateur, p2.id_utilisateur");
  
  $done=array();
  
  while ( $row = $req2->get_row() )
  {
    if ( !isset($done[$row['u2']]) && $row['c'] > 9 )
      new insert($dbrw,"galaxy_link",array( "id_star_a"=>$row['u1'], "id_star_b"=>$row['u2'], "tense_link" => $row['c'] ));
    $done[$row['u1']]=true;
  }
    
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  
  // POST-INITIALISATION
  echo "POST-INITIALISATION : ";
  $st = microtime(true);
  
  //fixe_star
  new requete($dbrw, "UPDATE galaxy_star SET max_tense_star = ( SELECT MAX(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_star SET sum_tense_star = ( SELECT SUM(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_star SET nblinks_star = ( SELECT COUNT(*) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_link SET max_tense_stars_link=( SELECT MAX(max_tense_star) FROM galaxy_star WHERE id_star=id_star_a OR id_star=id_star_b )");
  
  new requete($dbrw, "UPDATE galaxy_link SET ideal_length_link=0.5+((1-(tense_link/max_tense_stars_link))*15)");
  
  
  new requete($dbrw, "DELETE FROM galaxy_star WHERE nblinks_star = 0");
  
  
  
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";

}

if ( isset($_GET["rand"]) )
  new requete($dbrw, "UPDATE `galaxy_star` SET x_star = x_star+5-( RAND( ) *10 ), y_star = y_star+5-( RAND( ) *10)");

$cycles=10;

if ( isset($_GET["cycles"]) )
   $cycles = intval($_GET["cycles"]);
   
for($i=0;$i<$cycles;$i++)
{
  // CYCLE
  echo "CYCLE : ";
  $st = microtime(true);
  new requete($dbrw,"UPDATE galaxy_link, galaxy_star AS a, galaxy_star AS b SET ".
  "vx_link = b.x_star-a.x_star, ".
  "vy_link = b.y_star-a.y_star  ".
  "WHERE a.id_star = galaxy_link.id_star_a AND b.id_star = galaxy_link.id_star_b");
  echo "1: ".round(microtime(true)-$st,2)." - ";
  new requete($dbrw,"UPDATE galaxy_link SET length_link = SQRT(POW(vx_link,2)+POW(vy_link,2))");
  echo "2: ".round(microtime(true)-$st,2)." - ";
  new requete($dbrw,"UPDATE galaxy_link SET dx_link=vx_link/length_link, dy_link=vy_link/length_link WHERE length_link != 0");
  echo "3: ".round(microtime(true)-$st,2)." - ";
  new requete($dbrw,"UPDATE galaxy_link SET dx_link=0, dy_link=0 WHERE length_link = ideal_length_link");
  new requete($dbrw,"UPDATE galaxy_link SET dx_link=RAND(), dy_link=RAND() WHERE length_link != ideal_length_link AND dx_link=0 AND dy_link=0");
  echo "4: ".round(microtime(true)-$st,2)." - ";
  new requete($dbrw,"UPDATE galaxy_link, galaxy_star AS a, galaxy_star AS b SET  ".
  "delta_link_a=(length_link-ideal_length_link)/ideal_length_link/4, ".
  "delta_link_b=(length_link-ideal_length_link)/ideal_length_link/4*-1 ".
  "WHERE a.id_star = galaxy_link.id_star_a AND b.id_star = galaxy_link.id_star_b");
  echo "5: ".round(microtime(true)-$st,2)." - ";
  new requete($dbrw,"UPDATE galaxy_star SET ".
  "dx_star = COALESCE(( SELECT SUM( delta_link_a * dx_link ) FROM galaxy_link WHERE id_star_a = id_star ),0) + ".
    "COALESCE((SELECT SUM( delta_link_b * dx_link ) FROM galaxy_link WHERE id_star_b = id_star ),0), ".
  "dy_star = COALESCE(( SELECT SUM( delta_link_a * dy_link ) FROM galaxy_link WHERE id_star_a = id_star ),0) + ".
    "COALESCE((SELECT SUM( delta_link_b * dy_link ) FROM galaxy_link WHERE id_star_b = id_star ),0) WHERE fixe_star != 1");
    
  if ( !isset($_REQUEST["bypasscollision"]) )
  {
    echo "6: ".round(microtime(true)-$st,2)." - ";
    new requete($dbrw,"UPDATE galaxy_star AS a, galaxy_star AS b SET a.dx_star=0, a.dy_star=0, b.dx_star=0, b.dy_star=0 WHERE a.id_star != b.id_star AND POW(a.x_star+a.dx_star-b.x_star-b.dx_star,2)+POW(a.y_star+a.dy_star-b.y_star-b.dy_star,2) < 0.05");
    new requete($dbrw,"UPDATE galaxy_star AS a, galaxy_star AS b SET a.dx_star=0, a.dy_star=0, b.dx_star=0, b.dy_star=0 WHERE a.id_star != b.id_star AND POW(a.x_star+a.dx_star-b.x_star-b.dx_star,2)+POW(a.y_star+a.dy_star-b.y_star-b.dy_star,2) < 0.05");
  }
  echo "7: ".round(microtime(true)-$st,2)." - ";
  
  new requete($dbrw,"UPDATE galaxy_star SET x_star = x_star + dx_star, y_star = y_star + dy_star WHERE dx_star != 0 OR dy_star != 0 AND fixe_star != 1");
  
  echo "done in ".round(microtime(true)-$st,2)." sec<br/>\n";
}
//


if ( isset($_REQUEST["render"]) )
{
  echo "RENDER : ";
  $st = microtime(true);
  
  $req = new requete($dbrw, "SELECT MIN(x_star), MIN(y_star), MAX(x_star), MAX(y_star) FROM  galaxy_star");
  list($top_x,$top_y,$bottom_x,$bottom_y) = $req->get_row();
  
  $tx=100;
  
  $top_x = floor($top_x);
  $top_y = floor($top_y);
  $bottom_x = ceil($bottom_x);
  $bottom_y = ceil($bottom_y);
    
  $width = ($bottom_x-$top_x)*$tx;
  $height = ($bottom_y-$top_y)*$tx;
  
  new requete($dbrw,"UPDATE galaxy_star SET rx_star = (x_star-".sprintf("%f",$top_x).") * $tx, ry_star = (y_star-".sprintf("%f",$top_y).") * $tx");
  
  $img = imagecreate($width,$height);
  
  if ( $img === false )
  {
    echo "failed imagecreate($width,$height);";
    exit();
  }
  
  $bg = imagecolorallocate($img, 0, 0, 0);
  $textcolor = imagecolorallocate($img, 255, 255, 255);
  $wirecolor = imagecolorallocate($img, 64, 0, 0);
  $idealwirecolor = imagecolorallocate($img, 0, 64, 0);
  $bullcolor = imagecolorallocate($img, 128, 128, 128);
  
  $req = new requete($dbrw, "SELECT ABS(length_link-ideal_length_link) as ex, ".
  "a.rx_star as x1, a.ry_star as y1, b.rx_star as x2, b.ry_star as y2 ".
  "FROM  galaxy_link ".
  "INNER JOIN galaxy_star AS a ON (a.id_star=galaxy_link.id_star_a) ".
  "INNER JOIN galaxy_star AS b ON (b.id_star=galaxy_link.id_star_b)");
  
  while ( $row = $req->get_row() )
  {
    if ( $row['ex'] < 0.2 )
      imageline ($img, $row['x1'], $row['y1'], $row['x2'], $row['y2'], $idealwirecolor );
    else
      imageline ($img, $row['x1'], $row['y1'], $row['x2'], $row['y2'], $wirecolor );    
  }
  
  $req = new requete($dbrw, "SELECT ".
  "rx_star, ry_star ".
  "FROM  galaxy_star");
  
  while ( $row = $req->get_row() )
  {
    imagefilledellipse ($img, $row['rx_star'], $row['ry_star'], 5, 5, $textcolor );
  }
  
  $req = new requete($dbrw, "SELECT ".
  "rx_star, ry_star, alias_utl ".
  "FROM  galaxy_star ".
  "INNER JOIN utilisateurs ON (utilisateurs.id_utilisateur=galaxy_star.id_star)");  
  
  while ( $row = $req->get_row() )
  {
    imagestring($img, 1, $row['rx_star']+5, $row['ry_star']-3,  utf8_decode($row['alias_utl']), $textcolor);
  }
  
  echo "<br/><br/><img src=\"galaxy_temp.png\" />";

  imagepng($img,"galaxy_temp.png");
  imagedestroy($img);
    
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";

}

?>