<?php
$topdir="../";
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");

$dbrw = new mysqlae("rw");

$req = new requete($dbrw, "SELECT * 
FROM `mc_jeton` 
LEFT JOIN mc_jeton_utilisateur ON ( mc_jeton.id_jeton = mc_jeton_utilisateur.id_jeton
AND retour_jeton IS NULL ) 
ORDER BY id_salle, type_jeton, nom_jeton, id_utilisateur DESC");



while ( $row = $req->get_row() )
{
  if ( isset($done[$row['id_salle']][$row['type_jeton']][$row['nom_jeton']]))
  {
    echo "Doublons ! : ".$row[0]."<br/>\n";

    if ( is_null($row['id_utilisateur']) )
      new delete($dbrw,"mc_jeton",array("id_jeton"=>$row[0]));


  }
  else
    $done[$row['id_salle']][$row['type_jeton']][$row['nom_jeton']]=1;




}




?>
