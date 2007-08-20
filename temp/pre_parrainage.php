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

echo "PARRAINS\n";

echo "<table border=\"1\">\n<tr><td>NOM</td><td>PRENOM</td><td>VILLE</td><td>PAYS</td>";
foreach($question AS $q)
  echo "<td>".$q."</td>";
echo "</tr>\n";


while ( list($id_utl, $nom, $prenom, $ville, $cpostal, $pays)=$req->get_row() )
{
  $_req = new requete($sql,"SELECT `id_question`, `valeur_reponse` FROM `cpg_reponse` ".
                           "INNER JOIN `cpg_question` USING(`id_campagne`,`id_question`) ".
                           "WHERE `id_campagne`='".$cpg."' AND `id_utilisateur`='".$id_utl."' ".
                           "ORDER BY `id_question`");
  if( $_req->lines>0 )
  {
    echo "<tr><td>".$nom."</td><td>".$prenom."<td>".$ville." (".$cpostal.")</td><td>$pays</td>";
    $rep=array();
    while( list($id,$_rep)=$_req->get_row())
      $rep[$id]=$_rep;
    foreach($question AS $id=>$q)
    {
      if(isset($rep[$id]) && !empty($rep[$id]))
        echo "<td>".$rep[$id]."</td>";
      else
        echo "<td>&nbsp;</td>";
    }
    echo "</tr>\n";
  }
}
echo "</table>";



echo "\n<br />\n<hr />\n<br />\nBIJOUX\n";

$req = new requete($sql,"SELECT `nom_utl`, `prenom_utl`, `email_utl`, `addresse_utl`, `nom_ville`, `cpostal_ville`, `nom_pays`, `tc`, `branche` ".
                        "FROM `pre_parrainage` ".
                        "INNER JOIN `utilisateurs` USING(`id_utilisateur`) ".
                        "LEFT JOIN `loc_pays` USING(`id_pays`) ".
                        "LEFT JOIN `loc_ville` USING(`id_ville`) ".
                        "WHERE SEMESTRE='".$sem."'");
echo "\n<br />\n";
echo "<table border=\"1\">\n";
echo "<tr><td>NOM</td><td>PRENOM</td><td>EMAIL</td><td>ADRESSE</td><td>VILLE</td><td>PAYS</td><td>TC</td><td>BRANCHE</td></tr>\n";

while(list($nom,$prenom,$email,$adresse,$ville,$cpostal,$pays,$tc,$branche)=$req->get_row())
{
  echo "<tr><td>".$nom."</td><td>".$prenom."</td><td>".$email."</td><td>".$adresse."</td><td>".$ville." (".$cpostal.")</td><td>".$pays."</td><td>".$tc."</td><td>".$branche."</td></tr>\n";
}
echo "</table>";

?>
