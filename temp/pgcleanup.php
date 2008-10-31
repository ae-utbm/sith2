<?php

$topdir = "../";
require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");
$dbpg = new mysqlpg ();

/* Nettoyage de la base de données du PG v1
 * Objectif : calculer les champs suivants :
 * id_liste_parent : Fiche "parent" pour les fichez dupliqués dans plusieurs * 	catégories * import_liste : Import ou non de la fiche
 */

/*NOTE: ne tente de travailler que si import_liste=1, sinon ignorer*/

/*SELECT * FROM `pg_liste` WHERE import_liste=1 AND tel IN (SELECT tel FROM pg_liste WHERE import_liste=1 AND tel!='' GROUP BY tel HAVING COUNT(*) > 1) ORDER BY tel*/

$req = new requete($dbpg,"SELECT p1.id, p1.nom, p1.cat, p1.date_maj, p2.id, p2.nom, p2.cat, p2.date_majFROM `pg_liste` AS p1, pg_liste AS p2WHERE p1.import_liste =1AND p2.import_liste =1AND p1.id < p2.idAND p1.tel = p2.tel
AND p1.tel != ''AND p1.voie = p2.voie
ORDER BY p1.id, p2.id");

$solved=array();

while ( list($id1,$nom1,$cat1,$date1,$id2,$nom2,$cat2,$date2) = $req->get_row() )
{

  if ( !isset($solved[$id1]) && !isset($solved[$id2]) )
  {
    echo "<br/>$id1, $nom1, $cat1 - $id2, $nom2, $cat2<br/>";

    if ( $nom1 == $nom2 && $cat1 == $cat2 ) // de pures doublons
    {
      echo "Resolution : Cas 1<br/>";
      // On conserve le plus récent
      if ( $cat1 != $cat2 )
        new update($dbpg,"pg_liste",array("id_liste_parent"=>$id1),array("id"=>$id2));
      elseif ( $date1 > $date2 )
        new update($dbpg,"pg_liste",array("import_liste"=>0),array("id"=>$id2));
      else
        new update($dbpg,"pg_liste",array("import_liste"=>0),array("id"=>$id1));

      $solved[$id2]=true;
    }
    elseif ( strpos($nom2, $nom1) !== false ) // nom1 inclus dans nom2
    {
      echo "Resolution : Cas 2<br/>";
      if ( $cat1 != $cat2 )
        new update($dbpg,"pg_liste",array("id_liste_parent"=>$id1),array("id"=>$id2));
      else
        new update($dbpg,"pg_liste",array("import_liste"=>0),array("id"=>$id2));
      $solved[$id2]=true;
    }
    elseif ( strpos($nom1, $nom2) !== false ) // nom2 inclus dans nom1
    {
      echo "Resolution : Cas 3<br/>";
      if ( $cat1 != $cat2 )
        new update($dbpg,"pg_liste",array("id_liste_parent"=>$id2),array("id"=>$id1));
      else
        new update($dbpg,"pg_liste",array("import_liste"=>0),array("id"=>$id1));
      $solved[$id2]=true;
    }
    else
      echo "<b>Non solvable automatiquement</b><br/>";
  }
}



?>
