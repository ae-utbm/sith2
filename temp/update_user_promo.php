<?php

$topdir = "../";
include($topdir. "include/site.inc.php");


$site = new site ();

$req = new requete($site->db,
  "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.*, `utl_extra`.* ".
  "FROM utilisateurs ".
  "INNER JOIN `utl_etu_utbm` ON (`utilisateurs`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur`) ".
  "LEFT JOIN `utl_etu` ON (`utilisateurs`.`id_utilisateur`=`utl_etu`.`id_utilisateur`) ".
  "LEFT JOIN `utl_extra` ON (`utilisateurs`.`id_utilisateur`=`utl_extra`.`id_utilisateur`) ".
  "WHERE ".
  "(`utl_etu_utbm`.`promo_utbm` = '0' OR `utl_etu_utbm`.`promo_utbm` IS NULL) ".
  "AND `utl_etu_utbm`.`role_utbm` = 'etu' ".
  "AND `date_diplome_utbm` IS NULL ".
  "LIMIT 1");

$user = new utilisateur($site->db,$site->dbrw);

while ( $row = $req->get_row() )
{
  $user->_load_all($row);  
  
  $promo=0;
  
  if ( $user->departement == 'tc' )
  {
    if ( $user->semestre == 1 ) //TC1
      $promo=9;
    elseif ( $user->semestre < 4 ) // TC2-TC3
      $promo=8;
    elseif ( $user->semestre < 6 ) // TC4-TC5
      $promo=7;
    else // TC6
      $promo=6;
  }
  elseif ( $user->departement != 'na' && !empty($user->departement) )
  {
    if ( $user->semestre == 1 ) //GX1
      $promo=7;
    elseif ( $user->semestre < 4 ) // GX2-GX3
      $promo=6;
    elseif ( $user->semestre < 6 ) // GX4-GX5
      $promo=5;      
    elseif ( $user->semestre < 8 ) // GX6-GX7
      $promo=4;    
    else // GX8
      $promo=3;
  }
  
  if ( $promo )
  {
    echo $user->departement.$user->semestre." => $promo <br/>\n";
    
  }
  /*  new update($this->dbrw,"utl_etu_utbm",
      array('promo_utbm' => $promo),array( 'id_utilisateur' => $user->id)); */   
  
}



?>