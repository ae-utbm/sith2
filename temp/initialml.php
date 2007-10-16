<?php

$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$db = new mysqlae ("rw");

$req = new requete($db,
    "SELECT ".
    "`asso`.`nom_unix_asso`, ".
    "`asso`.`id_asso_parent` " .
    "FROM `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
    "ORDER BY `asso`.`nom_unix_asso`");

while ( list($name,$parent) = $req->get_row() )
{
  if ( !is_null($parent) )
	  asso::_ml_create($name."-membres");	
	  
	asso::_ml_create($name."-bureau");	
}

$req = new requete($db,
    "SELECT ".
    "`utilisateurs`.email_utl, ".
    "`asso`.`nom_unix_asso`, ".
    "`asso_membre`.`role`, ".
    "`asso`.`id_asso_parent` " .
    "FROM `asso_membre` " .
    "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
    "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`asso_membre`.`id_utilisateur` " .
    "WHERE `asso_membre`.`date_fin` is NULL " .
    "AND (`asso`.`id_asso_parent` IS NOT NULL OR `asso_membre`.`role` > 1 ) " .
    "ORDER BY `asso`.`nom_unix_asso`");

while ( list($email,$name,$role,$parent) = $req->get_row() )
{
  if ( $role > 1 )
    asso::_ml_subscribe($name."-bureau",$email);
  
  if( !is_null($parent) )
    asso::_ml_subscribe($name."-membres",$email);
}



?>