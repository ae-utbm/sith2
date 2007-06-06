<?php

/**
 * Fonctions de calcul geographique
 */

/**
 * Toute latitude positive => Nord, negative => Sud
 * Toute longiture positive => Est, negative => Ouest
 */
 
function geo_radians_to_degrees ( $rad )
{
  $degrees = $rad*360/2/M_PI;
  $deg = floor($degrees);
  $minutes = ($degrees-$deg)*60;
  $min = floor($minutes);
  $sec = round(($minutes-$min)*60,2);
  return $deg."°".$min."'".$sec."\"";
}

function geo_degrees_to_radians ( $deg )
{
  if ( ereg("^([0-9]+)°([0-9]+)'([0-9,\.]+)\"(E|N|S|O|W)$",$deg,$regs) )
  {
    $res = ((((str_replace(",",".",$regs[3])/60)+$regs[2])/60)+$regs[1])*2*M_PI/360;
    
    if ( $regs[4] == "O" || $regs[4] == "S" || $regs[4] == "W" )
      return -1*$res;
    
    return $res;
  }  
  else if ( ereg("^([0-9]+)°([0-9]+)'([0-9,\.]+)\"$",$deg,$regs) )
    return ((((str_replace(",",".",$regs[3])/60)+$regs[2])/60)+$regs[1])*2*M_PI/360;  
  
  return NULL;
}


?>