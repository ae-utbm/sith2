<?php


class galaxy
{
  var $db;
  var $dbrw;
  
  var $width;
  var $height;
  
  
  function galaxy ( &$db, &$dbrw )
  {
    $this->db = $db;
    $this->dbrw = $dbrw;
  }  
  
  
  function init ( )
  {
    new requete($this->dbrw,"TRUNCATE `galaxy_link`");    new requete($this->dbrw,"TRUNCATE `galaxy_star`");
    
    $liens = array();
    
    // 1- Cacul du score
    
    // a- Les photos : 1pt / photo ensemble
    $req = new requete($this->db, "SELECT COUNT( * ) as c, p1.id_utilisateur as u1, p2.id_utilisateur as u2 ".
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
    $req = new requete($this->db, "SELECT id_utilisateur as u1, id_utilisateur_fillot as u2 ".
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
    $req = new requete($this->db,"SELECT a.id_utilisateur as u1,b.id_utilisateur as u2,
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
    
    // 2- On vire les liens pas significatifs
    foreach ( $liens as $a => $data )
    {
      foreach ( $data as $b => $score )
        if ( $score < 10 )
          unset($liens[$a][$b]);
    }
    
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
      new insert($this->dbrw,"galaxy_star",array( "id_star"=>$id, "x_star" => $gx, "y_star" => $gy ));
      $gx++;
      if ( $gx > $width )
      {
        $gx=0;
        $gy++;
      }
    } 
  
    // 4- On crée les liens
    foreach ( $liens as $a => $data )
      foreach ( $data as $b => $score )
        new insert($this->dbrw,"galaxy_link",array( "id_star_a"=>$a, "id_star_b"=>$b, "tense_link" => $score ));
      
    //fixe_star
    new requete($this->dbrw, "UPDATE galaxy_star SET max_tense_star = ( SELECT MAX(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
    new requete($this->dbrw, "UPDATE galaxy_star SET sum_tense_star = ( SELECT SUM(tense_link) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
    new requete($this->dbrw, "UPDATE galaxy_star SET nblinks_star = ( SELECT COUNT(*) FROM galaxy_link WHERE id_star_a=id_star OR id_star_b=id_star )");
    new requete($this->dbrw, "UPDATE galaxy_link SET max_tense_stars_link=( SELECT MAX(max_tense_star) FROM galaxy_star WHERE id_star=id_star_a OR id_star=id_star_b )");
    
    new requete($this->dbrw, "UPDATE galaxy_link SET ideal_length_link=0.1+((1-(tense_link/max_tense_stars_link))*20)");
    
    
    new requete($this->dbrw, "DELETE FROM galaxy_star WHERE nblinks_star = 0");
  
  
  }
  
  function cycle ( $detectcollision=false )
  {
    $req = new requete($this->db,"SELECT MAX(length_link/ideal_length_link) FROM galaxy_link");
    
    $reducer=200;
    
    if ( $req->lines > 0 )
    {
      list($max) = $req->get_row();
      if ( $max > 1000 )
      {
        echo "failed due to expension";
        exit();
      }
      if ( !is_null($max) && $max > 0 )
        $reducer = max(10,round($max)*2);  
      echo $max." (".$reducer.") - ";
    }     
    
    new requete($this->dbrw,"UPDATE galaxy_link, galaxy_star AS a, galaxy_star AS b SET ".
    "vx_link = b.x_star-a.x_star, ".
    "vy_link = b.y_star-a.y_star  ".
    "WHERE a.id_star = galaxy_link.id_star_a AND b.id_star = galaxy_link.id_star_b");
    new requete($this->dbrw,"UPDATE galaxy_link SET length_link = SQRT(POW(vx_link,2)+POW(vy_link,2))");
    new requete($this->dbrw,"UPDATE galaxy_link SET dx_link=vx_link/length_link, dy_link=vy_link/length_link WHERE length_link != 0");
    new requete($this->dbrw,"UPDATE galaxy_link SET dx_link=0, dy_link=0 WHERE length_link = ideal_length_link");
    new requete($this->dbrw,"UPDATE galaxy_link SET dx_link=RAND(), dy_link=RAND() WHERE length_link != ideal_length_link AND dx_link=0 AND dy_link=0");
    
    new requete($this->dbrw,"UPDATE galaxy_link, galaxy_star AS a, galaxy_star AS b SET  ".
    "delta_link_a=(length_link-ideal_length_link)/ideal_length_link/$reducer, ".
    "delta_link_b=(length_link-ideal_length_link)/ideal_length_link/$reducer*-1 ".
    "WHERE a.id_star = galaxy_link.id_star_a AND b.id_star = galaxy_link.id_star_b");
    
    new requete($this->dbrw,"UPDATE galaxy_star SET ".
    "dx_star = COALESCE(( SELECT SUM( delta_link_a * dx_link ) FROM galaxy_link WHERE id_star_a = id_star ),0) + ".
      "COALESCE((SELECT SUM( delta_link_b * dx_link ) FROM galaxy_link WHERE id_star_b = id_star ),0), ".
    "dy_star = COALESCE(( SELECT SUM( delta_link_a * dy_link ) FROM galaxy_link WHERE id_star_a = id_star ),0) + ".
      "COALESCE((SELECT SUM( delta_link_b * dy_link ) FROM galaxy_link WHERE id_star_b = id_star ),0) WHERE fixe_star != 1");
    if ( $detectcollision )
    {
      new requete($this->dbrw,"UPDATE galaxy_star AS a, galaxy_star AS b SET a.dx_star=0, a.dy_star=0, b.dx_star=0, b.dy_star=0 WHERE a.id_star != b.id_star AND POW(a.x_star+a.dx_star-b.x_star-b.dx_star,2)+POW(a.y_star+a.dy_star-b.y_star-b.dy_star,2) < 0.05");
      new requete($this->dbrw,"UPDATE galaxy_star AS a, galaxy_star AS b SET a.dx_star=0, a.dy_star=0, b.dx_star=0, b.dy_star=0 WHERE a.id_star != b.id_star AND POW(a.x_star+a.dx_star-b.x_star-b.dx_star,2)+POW(a.y_star+a.dy_star-b.y_star-b.dy_star,2) < 0.05");
    }
    new requete($this->dbrw,"UPDATE galaxy_star SET x_star = x_star + dx_star, y_star = y_star + dy_star WHERE dx_star != 0 OR dy_star != 0 AND fixe_star != 1");
    
    
  }
  
  function rand()
  {
    new requete($this->dbrw, "UPDATE `galaxy_star` SET x_star = x_star+5-( RAND( ) *10 ), y_star = y_star+5-( RAND( ) *10)");
  }
  
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
  
  function pre_render ($tx=100)
  {
    $req = new requete($this->db, "SELECT MIN(x_star), MIN(y_star), MAX(x_star), MAX(y_star) FROM  galaxy_star");
    list($top_x,$top_y,$bottom_x,$bottom_y) = $req->get_row();
    
    $top_x = floor($top_x);
    $top_y = floor($top_y);
    $bottom_x = ceil($bottom_x);
    $bottom_y = ceil($bottom_y);
      
    $this->width = ($bottom_x-$top_x)*$tx;
    $this->height = ($bottom_y-$top_y)*$tx;
    
    new requete($this->dbrw,"UPDATE galaxy_star SET rx_star = (x_star-".sprintf("%f",$top_x).") * $tx, ry_star = (y_star-".sprintf("%f",$top_y).") * $tx");    
  }
  
  function render ($target="galaxy_temp.png") 
  {
    if ( empty($this->width) || empty($this->height) )
      $this->pre_render();
    
    $img = imagecreatetruecolor($this->width,$this->height);
  
    if ( $img === false )
    {
      echo "failed imagecreatetruecolor($width,$height);";
      exit();
    }
    
    $bg = imagecolorallocate($img, 0, 0, 0);
    $textcolor = imagecolorallocate($img, 255, 255, 255);
    $wirecolor = imagecolorallocate($img, 32, 32, 32);
    
    imagefill($img, 0, 0, $bg);
    
    imagestring($img, 1, 0, 0, "AE R&D - GALAXY", $textcolor);
    
    for($i=0;$i<820;$i++)
    {
      imageline($img,$i,10,$i,20,$this->star_color($img,$i));
      
      if ( $i %100 == 0)
        imagestring($img, 1, $i, 22, $i, $textcolor);
    }
    
    $req = new requete($this->db, "SELECT ABS(length_link-ideal_length_link) as ex, ".
    "a.rx_star as x1, a.ry_star as y1, b.rx_star as x2, b.ry_star as y2 ".
    "FROM  galaxy_link ".
    "INNER JOIN galaxy_star AS a ON (a.id_star=galaxy_link.id_star_a) ".
    "INNER JOIN galaxy_star AS b ON (b.id_star=galaxy_link.id_star_b)");
    
    while ( $row = $req->get_row() )
    {
      imageline ($img, $row['x1'], $row['y1'], $row['x2'], $row['y2'], $wirecolor );    
    } 
  
    $req = new requete($this->db, "SELECT ".
    "rx_star, ry_star, sum_tense_star  ".
    "FROM  galaxy_star");
    
    while ( $row = $req->get_row() )
    {
      imagefilledellipse ($img, $row['rx_star'], $row['ry_star'], 5, 5, $this->star_color($img,$row['sum_tense_star']) ); 
    }
    
    $req = new requete($this->db, "SELECT ".
    "rx_star, ry_star, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom ".
    "FROM  galaxy_star ".
    "INNER JOIN utilisateurs ON (utilisateurs.id_utilisateur=galaxy_star.id_star)");  
    
    while ( $row = $req->get_row() )
    {
      imagestring($img, 1, $row['rx_star']+5, $row['ry_star']-3,  utf8_decode($row['nom']), $textcolor);
    }
    
    if ( is_null($target) )
      imagepng($img);
    else
      imagepng($img,$target);
    imagedestroy($img);
    
  }
  
  function render_area ( $tx, $ty, $w, $h, $target=null )
  {
    $st = microtime(true);
  
    $x1 = $tx-3;
    $y1 = $ty-3;
    $x2 = $tx+$w+3;
    $y2 = $ty+$h+3;
  
    $img = imagecreatetruecolor($w,$h);
     
    $bg = imagecolorallocate($img, 0, 0, 0);
    $textcolor = imagecolorallocate($img, 255, 255, 255);
    $wirecolor = imagecolorallocate($img, 32, 32, 32);
    
    imagefill($img, 0, 0, $bg);  
    
    $req = new requete($this->db, "SELECT ABS(length_link-ideal_length_link) as ex, ".
    "a.rx_star as x1, a.ry_star as y1, b.rx_star as x2, b.ry_star as y2 ".
    "FROM  galaxy_link ".
    "INNER JOIN galaxy_star AS a ON (a.id_star=galaxy_link.id_star_a) ".
    "INNER JOIN galaxy_star AS b ON (b.id_star=galaxy_link.id_star_b)");
    
    while ( $row = $req->get_row() )
    {
      imageline ($img, $row['x1']-$tx, $row['y1']-$ty, $row['x2']-$tx, $row['y2']-$ty, $wirecolor );    
    } 
  
    $req = new requete($this->db, "SELECT ".
    "rx_star, ry_star, sum_tense_star  ".
    "FROM  galaxy_star ".
    "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2");
    
    while ( $row = $req->get_row() )
    {
      imagefilledellipse ($img, $row['rx_star']-$tx, $row['ry_star']-$ty, 5, 5, $this->star_color($img,$row['sum_tense_star']) ); 
    }
    
    $req = new requete($this->db, "SELECT ".
    "rx_star, ry_star, COALESCE(alias_utl,CONCAT(prenom_utl,' ',nom_utl)) AS nom ".
    "FROM  galaxy_star ".
    "INNER JOIN utilisateurs ON (utilisateurs.id_utilisateur=galaxy_star.id_star) ".
    "WHERE rx_star >= $x1 AND rx_star <= $x2 AND ry_star >= $y1 AND ry_star <= $y2" );  
    
    while ( $row = $req->get_row() )
    {
      imagestring($img, 1, $row['rx_star']+5-$tx, $row['ry_star']-3-$ty,  utf8_decode($row['nom']), $textcolor);
    }
    if ( is_null($target) )
      imagepng($img);
    else
      imagepng($img,$target);
    imagedestroy($img);  
        
  }

  
  
  
  
}




?>