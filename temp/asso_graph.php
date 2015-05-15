<?php

//header ("Content-Type: text/plain");

$topdir = "../";
require_once ($topdir . "include/genealogie.inc.php");
require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

$gene = new genealogie ("\"Arbre des associations et clubs\"",
			"5.0");

$req = new requete(new mysqlae (),
		   "SELECT
		   `asso1`.`nom_asso`,
		   `asso2`.`nom_asso` as `nom_asso_parent`,
                   `asso1`.`id_asso`,
                   `asso2`.`id_asso` as `id_asso_parent`
		   FROM `asso` AS `asso1`
		   LEFT JOIN `asso` AS `asso2` ON
                     `asso1`.`id_asso_parent`=`asso2`.`id_asso`
		   ORDER BY `asso2`.`id_asso`,`asso1`.`nom_asso` ");

for ($i = 0;$i < $req->lines; $i++)
{
  $res[] = $req->get_row();
}

$conf = "digraph \"Arbre des associations et clubs\" {\n";
$conf .= "\tranksep = \"3.0 equally\";\n";
$conf .= "\tnode [shape=box,style=filled,color=lightblue];\n";

// contraintes
foreach ($res as $line)
{
  /* racine */
  if (($line['id_asso_parent'] == 0)
      && ($line['nom_asso'] != ""))
    $constraints['root'][] = $line['nom_asso'];
  /* autres */
  else
    {
      $asso_root = $line['nom_asso_parent'];
      $constraints[$asso_root][] = $line['nom_asso'];
    }
}


foreach ($constraints as $asso_root => $assoces)
{
  $conf .= "\t{ rank=same; ";

  foreach ($assoces as $assoce)
    if ($assoce != "")
      $conf .= "\"$assoce\"; ";

  $conf .= " }\n";
}

// couleurs
/* voir http://www.graphviz.org/doc/info/colors.html pour plus
 * d'infos */
$ae = "navyblue";
$poles = "gold";
$clubs = "firebrick1";

foreach ($res as $line)
{
  /* asso meres */
  if (in_array($line['id_asso_parent'],
	       array(1,2,3,51,52,53,54,57,58,59,60,61,70,71)))
    $conf .= "\t\"".$line['nom_asso']."\" [color=$ae];\n";
  /* poles */
  if($line['id_asso_parent'] == 1)
    $conf .= "\t\"".$line['nom_asso']."\" [color=$poles];\n";
  /* clubs */
  if (in_array($line['id_asso_parent'], array(9,10,11,12,13)))
    $conf .= "\t\"".$line['nom_asso']."\" [color=$clubs];\n";

}


// liens
foreach ($res as $line)
{
  if ($line['nom_asso'] != "")
    if (strlen($line['nom_asso_parent']) > 0)
      $conf .= ("\t\"".$line['nom_asso_parent'].
		"\" -> \"".$line['nom_asso']."\"\n");

  else
    if ($line['nom_asso_parent'] != "")
      $conf .= ("\t\"".$line['nom_asso_parent']."\"\n");
}

$conf .= "}\n";
//echo $conf;

$gene->generate_conf_from_string (utf8_decode($conf));
$gene->generate ();
?>
