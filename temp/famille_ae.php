<?php

/*
 * test
 *
 *
 */

$topdir = "../";
require_once ($topdir . "include/globals.inc.php");
require_once ($topdir . "include/watermark.inc.php");
require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");


$req = new requete (new mysqlae(),
		    "SELECT   `utilisateurs`.`id_utilisateur`,
                              CONCAT(`utilisateurs`.`prenom_utl`,
                             ' ', `utilisateurs`.`nom_utl`) AS `nom`,
                              `utilisateurs`.`alias_utl` AS `surnom`,
                              `utl_etu_utbm`.`promo_utbm`
                     FROM `utilisateurs`
                     LEFT JOIN `utl_etu_utbm` ON
                           `utl_etu_utbm`.`id_utilisateur` =
                             `utilisateurs`.`id_utilisateur`");

for ($i = 0;$i < $req->lines; $i++)
{
  $res = $req->get_row();
  $users[$res['id_utilisateur']] = array(utf8_decode($res['nom'] . "\\n" .
						     $res['surnom']),
					 $res['promo_utbm']);
}

/* on dispose maintenant d'un tableau des utilisateurs de l'AE indexe
 * par id_utilisateur => [0] => nom ... [1] => promo   */

$req = new requete (new mysqlae(),
		    "SELECT `id_utilisateur` AS `parrain`,
                            `id_utilisateur_fillot` AS `fillot`
                     FROM `parrains`");

for ($i = 0;$i < $req->lines; $i++)
{
  $res = $req->get_row();
  $filiation[] = array($users[$res['parrain']][0],$users[$res['fillot']][0]);

  $promos[$users[$res['parrain']][1]][] = $users[$res['parrain']][0];
  /* promo / fillot */
  $promos[$users[$res['fillot']][1]][] = $users[$res['fillot']][0];
  /*
   * pour le tableau $filiation :
   * $filiation['parrain' => 'fillot, ... ]
   *
   */
}


header ("Content-Type: text/plain");


echo "digraph bleh {\n";
echo "\tranksep = \"1.7 equally\";\n";

/*generation des infos de promo */
echo "\t{\n";

echo "\t\tnode[shape=plaintext];\n";
//foreach ($promos as $numero => $membres)
echo "\t\t";
for ($i = 0; $i < 8; $i++)
  echo "\"promo $i\" -> ";
echo "\"futur ...\";\n";
echo "\t}\n\n";

echo "\tnode [shape=box,style=filled,coloc=lightblue];\n";


//foreach ($promos as $numero => $membres)
for ($i = 0; $i < 8; $i++)
{
  echo "\t{ rank=same; \"promo $i\"; ";
  foreach ($promos[$i] as $membre)
    echo "\"$membre\"; ";
  echo " }\n";
}

/* generation des relations */
foreach ($filiation as $row)
{
list($parrain,$fillot) = $row;
  echo "\t\"$parrain\" -> \"$fillot\"\n";
}
echo "}\n";





?>
