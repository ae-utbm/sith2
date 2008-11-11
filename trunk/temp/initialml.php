<?php

$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$db = new mysqlae ("rw");
/*
$req = new requete($db,
    "SELECT ".
    "`asso`.`nom_unix_asso`, ".
    "`asso`.`id_asso_parent`, " .
    "`asso`.`email_asso` " .
    "FROM `asso` " .
    "WHERE id_asso=1 OR (id_asso_parent IS NOT NULL AND id_asso_parent!=3) ".
    "ORDER BY `asso`.`nom_unix_asso`");

while ( list($name,$parent,$email) = $req->get_row() )
{
  if ( !is_null($parent) )
	  asso::_ml_create($db,$name.".membres",$email);

	asso::_ml_create($db,$name.".bureau",$email);
}
*/
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
    "AND (asso.id_asso=1 OR (id_asso_parent IS NOT NULL AND id_asso_parent!=3))".
    "ORDER BY `asso`.`nom_unix_asso`");

while ( list($email,$name,$role,$parent) = $req->get_row() )
{

  if ( $role > 1 )
    asso::_ml_subscribe($db,$name.".bureau",$email);

  if( !is_null($parent) )
    asso::_ml_subscribe($db,$name.".membres",$email);
  /*
  if ( $role > 1 )
    echo $name.".bureau > ".$email."<br/>";

  if( !is_null($parent) )
    echo $name.".membres > ".$email."<br/>";
    */
}

echo "done";

?>
