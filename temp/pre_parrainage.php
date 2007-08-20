<?php

$cpg="1";
$sem="A07";


$topdir="../";

require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

$sql = new mysqlae ();

$req = new requete($sql,"SELECT `id_utilisateur`, `nom_utl`, `prenom_utl`, `nom_ville`, `cpostal_ville`, `nom_pays` ".
                        "FROM `cpg_participe` ".
                        "INNER JOIN `utilisateurs` USING(`id_utilisateur`) ".
                        "INNER JOIN `utl_etu` USING(`id_utilisateur`) ".
                        "LEFT JOIN `loc_pays` ON `loc_pays`.`id_pays`=`utl_etu`.`id_pays` ".
                        "LEFT JOIN `loc_ville` ON `loc_ville`.`id_ville`=`utl_etu`.`id_ville` ".
                        "WHERE `id_campagne`='".$cpg."'");

$question=array();

if( $req->lines==0 )
  exit();

$req2 = new requete($sql,"SELECT `id_question`, `nom_question` FROM `cpg_question` WHERE `id_campagne`='".$cpg."'");
if( $req2->lines==0 )
  exit();
else
  while( list($id,$nom)=$req2->get_row() )
    $question[$id]=$nom;

echo "<pre>\n";
echo "PARRAINS :\n";

while ( list($id_utl, $nom, $prenom, $ville, $cpostal, $pays)=$req->get_row() )
{
  $_req = new requete($sql,"SELECT `id_question`, `valeur_reponse` FROM `cpg_reponse` ".
                           "INNER JOIN `cpg_question` USING(`id_campagne`,`id_question`) ".
                           "WHERE `id_campagne`='".$cpg."' AND `id_utilisateur`='".$id_utl."' ".
                           "ORDER BY `id_question`");
  if( $_req->lines>0 )
  {
    echo "\n==================================\n";
    echo "QUI ? : ".$nom." ".$prenom."\n";
    if(is_null($ville) && is_null($pays))
      echo "cet abrutis n'a spécifié ni sa ville ni son pays ...\n";
    else
    {
      if(!is_null($pays))
        echo "Pays : ".$pays."\n";
      if(!is_null($ville))
        echo "Ville : ".$ville." (".$cpostal.")\n";
    }
    echo "\nRéponses :\n";
    while( list($id,$rep)=$_req->get_row())
      echo "  ".$question[$id]." : ".$rep."\n";
  }
}




echo "\n\n\n\nBIJOUX\n\n";

$req = new requete($sql,"SELECT `nom_utl`, `prenom_utl`, `email_utl`, `addresse_utl`, `nom_ville`, `cpostal_ville`, `nom_pays`, `tc`, `branche` ".
                        "FROM `pre_parrainage` ".
                        "INNER JOIN `utilisateurs` USING(`id_utilisateur`) ".
                        "LEFT JOIN `loc_pays` USING(`id_pays`) ".
                        "LEFT JOIN `loc_ville` USING(`id_ville`) ".
                        "WHERE SEMESTRE='".$sem."'");

while(list($nom,$prenom,$email,$adresse,$ville,$cpostal,$pays,$tc,$branche)=$req->get_row())
{
  echo "\n==================================\n";
  echo "QUI ? : ".$nom." ".$prenom."\n";
  if(is_null($ville) && is_null($pays))
    echo "cet abrutis n'a spécifié ni sa ville ni son pays ...\n";
  else
  {
    if(!is_null($pays))
      echo "Pays : ".$pays."\n";
    if(!is_null($ville))
      echo "Ville : ".$ville." (".$cpostal.")\n";
  }
  echo "Adresse : ".$adresse."\n";
  if($tc==1)
    echo "Rentre en TC et souhaite faire ".$branche."\n";
  else
    echo "Rentre en ".$branche."\n";
}

echo "</pre>";

?>
