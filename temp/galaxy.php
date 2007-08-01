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
  
  $liens = array();
  
  // 1- Cacul du score
  
  // a- Les photos : 1pt / photo ensemble
  $req = new requete($dbrw, "SELECT COUNT( * ) as c, p1.id_utilisateur as u1, p2.id_utilisateur as u2 ".
  "FROM `sas_personnes_photos` AS `p1` ".
  "JOIN `sas_personnes_photos` AS `p2` ON ( p1.id_photo = p2.id_photo ".
  "AND p1.id_utilisateur != p2.id_utilisateur ) ".
  "GROUP BY p1.id_utilisateur, p2.id_utilisateur");
  
  while ( $row = $req->get_row() )
  {
    $a = min($row['u1'],$row['u2']);
    $b = max($row['u1'],$row['u2']);
    
    $liens[$a][$b] = $row['c'];
  }  
  
  // b- Parrainage : 15pt / relation parrain-fillot
  $req = new requete($dbrw, "SELECT id_utilisateur as u1, id_utilisateur_fillot as u2 ".
  "FROM `parrains` ".
  "GROUP BY id_utilisateur, id_utilisateur_fillot");
  while ( $row = $req->get_row() )
  {
    $a = min($row['u1'],$row['u2']);
    $b = max($row['u1'],$row['u2']);    
    
    if ( isset($liens[$a][$b]) )
      $liens[$a][$b] += 15;
    else
      $liens[$a][$b] = 15;
  }  
  
  // c- associations et clubs : 1pt / 75 jours ensemble / assos
  $req = new requete($dbrw,"SELECT a.id_utilisateur as u1,b.id_utilisateur as u2,
SUM(DATEDIFF(LEAST(COALESCE(a.date_fin,NOW()),COALESCE(b.date_fin,NOW())),GREATEST(a.date_debut,b.date_debut))) AS together
FROM asso_membre AS a
JOIN asso_membre AS b ON
( 
a.id_utilisateur < b.id_utilisateur  
AND a.id_asso = b.id_asso
AND DATEDIFF(LEAST(COALESCE(a.date_fin,NOW()),COALESCE(b.date_fin,NOW())),GREATEST(a.date_debut,b.date_debut)) > 74
)
GROUP BY a.id_utilisateur,b.id_utilisateur
ORDER BY a.id_utilisateur,b.id_utilisateur");

  while ( $row = $req->get_row() )
  {
    $a = min($row['u1'],$row['u2']);
    $b = max($row['u1'],$row['u2']);    
    
    if ( isset($liens[$a][$b]) )
      $liens[$a][$b] += round($row['together']/75);
    else
      $liens[$a][$b] += round($row['together']/75);
  }    
  
  echo "step 1 (finished at ".(microtime(true)-$st)." sec)<br/>\n";

  // 2- On vire les liens pas significatifs
  foreach ( $liens as $a => $data )
  {
    foreach ( $data as $b => $score )
      if ( $score < 10 )
        unset($liens[$a][$b]);
  }
  
  echo "step 2 (finished at ".(microtime(true)-$st)." sec)<br/>\n";

  // 3- On crée les peronnes requises
  $stars = array();
  foreach ( $liens as $a => $data )
  {
    if ( !isset($stars[$a]) )
      $stars[$a] = $a;
    
    foreach ( $data as $b => $score )
      if ( !isset($stars[$b]) )
        $stars[$b] = $b;
  }
  
  $gx=0;
  $gy=0;
  
  $width = floor(sqrt(count($stars)));
  
  foreach ( $stars as $id )
  {
    new insert($dbrw,"galaxy_star",array( "id_star"=>$id, "x_star" => $gx, "y_star" => $gy ));
    $gx++;
    if ( $gx > $width )
    {
      $gx=0;
      $gy++;
    }
  } 
  echo "step 3 (finished at ".(microtime(true)-$st)." sec)<br/>\n";

  // 4- On crée les liens
  foreach ( $liens as $a => $data )
    foreach ( $data as $b => $score )
      new insert($dbrw,"galaxy_link",array( "id_star_a"=>$a, "id_star_b"=>$b, "tense_link" => $score ));
  

/*  $req1 = new requete($dbrw, "SELECT p1.id_utilisateur, alias_utl
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
  }*/
    
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  
  // POST-INITIALISATION
  echo "POST-INITIALISATION : ";
  $st = microtime(true);
  
  //fixe_star
  new requete($dbrw, "UPDATE galaxy_star SET max_tense_star = ( SELECT MAX(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_star SET sum_tense_star = ( SELECT SUM(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_star SET nblinks_star = ( SELECT COUNT(*) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
  new requete($dbrw, "UPDATE galaxy_link SET max_tense_stars_link=( SELECT MAX(max_tense_star) FROM galaxy_star WHERE id_star=id_star_a OR id_star=id_star_b )");
  
  new requete($dbrw, "UPDATE galaxy_link SET ideal_length_link=0.1+((1-(tense_link/max_tense_stars_link))*20)");
  
  
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
  "delta_link_a=(length_link-ideal_length_link)/ideal_length_link/100, ".
  "delta_link_b=(length_link-ideal_length_link)/ideal_length_link/100*-1 ".
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
  
  echo "8: ".round(microtime(true)-$st,2)." - ";
  $req = new requete($dbrw,"SELECT MAX(length_link/ideal_length_link) FROM galaxy_link");
  if ( $req->lines > 0 )
  {
    list($max) = $req->get_row();
    echo "divergence : $max - ";
    if ( $max > 1000 )
    {
      echo "dégénérescence<br/>";
      exit();
    }
  }
  
  
  
  echo "done in ".round(microtime(true)-$st,2)." sec<br/>\n";
}
//

    // 0 ---------- 35 ----------------- 400 ---------------- 700 --- 800
    // Noir         | Rouge              | Jaune              | Bleu  | Blanc
function star_color ( $img, $i )
{
  if ( $i > 800 )
    return imagecolorallocate($img, 255, 255, 255);
    
  if ( $i > 700 )
    return imagecolorallocate($img, (($i-700)*255/100), (($i-700)*255/100), 255);      
    
  if ( $i > 400 )
    return imagecolorallocate($img, 255 -(($i-400)*255/300), 255 -(($i-400)*255/300), ($i-400)*255/300);
    
  if ( $i > 35 )
    return imagecolorallocate($img, 255, ($i-35)*255/365, 0); 
  
  return imagecolorallocate($img, $i*255/36, 0, 0);   
}

function render_area ( $db, $tx, $ty, $w, $h )
{
  $st = microtime(true);

  echo "RENDER AREA : ";

  $x1 = $tx-3;
  $y1 = $ty-3;
  $x2 = $tx+$w+3;
  $y2 = $ty-$y+3;

    $img = imagecreatetruecolor($w,$h);
   
  $bg = imagecolorallocate($img, 0, 0, 0);
  $textcolor = imagecolorallocate($img, 255, 255, 255);
  $wirecolor = imagecolorallocate($img, 32, 32, 32);
  
  imagefill($img, 0, 0, $bg);  
  
  echo "2: ".round(microtime(true)-$st,2)." - ";

  
  if ( !isset($_REQUEST["nowires"]) )
  {
    $req = new requete($db, "SELECT ABS(length_link-ideal_length_link) as ex, ".
    "a.rx_star as x1, a.ry_star as y1, b.rx_star as x2, b.ry_star as y2 ".
    "FROM  galaxy_link ".
    "INNER JOIN galaxy_star AS a ON (a.id_star=galaxy_link.id_star_a) ".
    "INNER JOIN galaxy_star AS b ON (b.id_star=galaxy_link.id_star_b)");
    
    while ( $row = $req->get_row() )
    {
      imageline ($img, $row['x1']-$tx, $row['y1']-$ty, $row['x2']-$tx, $row['y2']-$ty, $wirecolor );    
    } 
  }

  echo "3: ".round(microtime(true)-$st,2)." - ";

  $req = new requete($db, "SELECT ".
  "rx_star, ry_star, sum_tense_star  ".
  "FROM  galaxy_star ".
  "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2");
  
  while ( $row = $req->get_row() )
  {
    imagefilledellipse ($img, $row['rx_star']-$tx, $row['ry_star']-$ty, 5, 5, star_color($img,$row['sum_tense_star']) ); 
  }
  
  echo "4: ".round(microtime(true)-$st,2)." - ";
  
  $req = new requete($db, "SELECT ".
  "rx_star, ry_star, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom ".
  "FROM  galaxy_star ".
  "INNER JOIN utilisateurs ON (utilisateurs.id_utilisateur=galaxy_star.id_star) ".
  "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2" );  
  
  while ( $row = $req->get_row() )
  {
    imagestring($img, 1, $row['rx_star']+5-$tx, $row['ry_star']-3-$ty,  utf8_decode($row['nom']), $textcolor);
  }
  
  echo "rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2 - ";
  echo $req->lines. " - ";

  imagepng($img,"galaxy_area_temp.png");
  imagedestroy($img);  
  
  
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
    echo "<br/><br/><img src=\"galaxy_area_temp.png\" />";

}


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
  
  echo "1: ".round(microtime(true)-$st,2)." - ";

  
  $img = imagecreatetruecolor($width,$height);
  
  if ( $img === false )
  {
    echo "failed imagecreatetruecolor($width,$height);";
    exit();
  }
  imagealphablending($img,true);
  //imageantialias($img,true);
  
  
  
  $bg = imagecolorallocate($img, 0, 0, 0);
  $textcolor = imagecolorallocate($img, 255, 255, 255);
  $wirecolor = imagecolorallocate($img, 32, 32, 32);
  $bullcolor = imagecolorallocate($img, 128, 128, 128);
  
  imagefill($img, 0, 0, $bg);
  
  imagestring($img, 1, 0, 0, "AE R&D - GALAXY", $textcolor);
  
  for($i=0;$i<820;$i++)
  {
    imageline($img,$i,10,$i,20,star_color($img,$i));
    
    if ( $i %100 == 0)
      imagestring($img, 1, $i, 22, $i, $textcolor);
  }
  
  echo "2: ".round(microtime(true)-$st,2)." - ";

  
  if ( !isset($_REQUEST["nowires"]) )
  {
    $req = new requete($dbrw, "SELECT ABS(length_link-ideal_length_link) as ex, ".
    "a.rx_star as x1, a.ry_star as y1, b.rx_star as x2, b.ry_star as y2 ".
    "FROM  galaxy_link ".
    "INNER JOIN galaxy_star AS a ON (a.id_star=galaxy_link.id_star_a) ".
    "INNER JOIN galaxy_star AS b ON (b.id_star=galaxy_link.id_star_b)");
    
    while ( $row = $req->get_row() )
    {
      imageline ($img, $row['x1'], $row['y1'], $row['x2'], $row['y2'], $wirecolor );    
    } 
  }

  echo "3: ".round(microtime(true)-$st,2)." - ";

  $req = new requete($dbrw, "SELECT ".
  "rx_star, ry_star, sum_tense_star  ".
  "FROM  galaxy_star");
  
  while ( $row = $req->get_row() )
  {
    imagefilledellipse ($img, $row['rx_star'], $row['ry_star'], 5, 5, star_color($img,$row['sum_tense_star']) ); 
  }
  
  echo "4: ".round(microtime(true)-$st,2)." - ";
  
  $req = new requete($dbrw, "SELECT ".
  "rx_star, ry_star, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom ".
  "FROM  galaxy_star ".
  "INNER JOIN utilisateurs ON (utilisateurs.id_utilisateur=galaxy_star.id_star)");  
  
  while ( $row = $req->get_row() )
  {
    imagestring($img, 1, $row['rx_star']+5, $row['ry_star']-3,  utf8_decode($row['nom']), $textcolor);
  }
  

  imagepng($img,"galaxy_temp.png");
  imagedestroy($img);
    
  echo "done in ".(microtime(true)-$st)." sec<br/>\n";
  echo "<br/><br/><img src=\"galaxy_temp.png\" />";
  
  render_area ( $dbrw, floor($width/2)-150, floor($height/2)-150, 300, 300 );
}

?>