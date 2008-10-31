<?php
$topdir="../";
header("Content-Type: text/html; charset=utf-8");

require_once($topdir."include/site.inc.php");

$site = new site();

$id_cptbc=7;


$sql1 = new requete($site->db,
"SELECT cpta_classeur.*,asso.nom_asso FROM cpta_classeur " .
"INNER JOIN cpta_cpasso ON cpta_classeur.id_cptasso = cpta_cpasso.id_cptasso  " .
"INNER JOIN asso ON asso.id_asso = cpta_cpasso.id_asso ".
"WHERE cpta_cpasso.id_cptbc=$id_cptbc");

echo "<table>\n";
echo "<tr><td>Classeur</td><td>Solde</td><td>Ouverture+Fermeture</td><td>En interne sur le compte bancaire</td><td>Operations</td></tr>\n";
while ( $cla = $sql1->get_row() )
{
	echo "<tr>\n<td>".$cla["nom_asso"]." ".$cla["nom_classeur"]."</td>\n";

	$sql2 = new requete($site->db,"SELECT " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla["id_classeur"]."' ");

	list($sum) = $sql2->get_row();
	echo "<td>$sum</td>\n";

	$sql2 = new requete($site->db,"SELECT COUNT(*), " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) " .
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla["id_classeur"]."' AND " .
		"((cpta_operation.id_cptasso IS NULL) OR (cpta_operation.id_cptasso=".$cla["id_cptasso"].")) AND ".
		"(cpta_operation.id_utilisateur IS NULL) AND ".
		"(cpta_operation.id_asso IS NULL) AND ".
		"(cpta_operation.id_ent IS NULL)");

	list($nb,$intassocptsum) = $sql2->get_row();
	echo "<td>$nb/$intassocptsum</td>\n";

	$sql2 = new requete($site->db,"SELECT COUNT(*), " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) " .
		"FROM `cpta_operation` " .
		"INNER JOIN cpta_cpasso ON cpta_operation.id_cptasso = cpta_cpasso.id_cptasso  " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla["id_classeur"]."' AND " .
		"(cpta_cpasso.id_cptbc=$id_cptbc) AND " .
		"(cpta_operation.id_cptasso!=".$cla["id_cptasso"].") AND ".
		"(cpta_operation.id_utilisateur IS NULL) AND ".
		"(cpta_operation.id_asso IS NULL) AND ".
		"(cpta_operation.id_ent IS NULL)");

	list($nb,$intbccptsum) = $sql2->get_row();
	echo "<td>$nb/$intbccptsum</td>\n";



	$sql2 = new requete($site->db,"SELECT COUNT(*), " .
		"SUM(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) " .
		"FROM `cpta_operation` " .
		"LEFT JOIN cpta_cpasso ON cpta_operation.id_cptasso = cpta_cpasso.id_cptasso  " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"WHERE `cpta_operation`.id_classeur='".$cla["id_classeur"]."' AND " .
		"!((cpta_cpasso.id_cptbc=$id_cptbc) AND " .
		"(cpta_operation.id_utilisateur IS NULL) AND ".
		"(cpta_operation.id_asso IS NULL) AND ".
		"(cpta_operation.id_ent IS NULL))");

	list($nb,$extsum) = $sql2->get_row();

	$cext+=$extsum;
	$cintbc+=$intbccptsum;
	$cintassocpt+=$intassocptsum;
	$c+=$sum;

	echo "<td>$nb/$extsum</td>\n";

	echo "</tr>\n";

}
echo "<tr><td>TOTAL</td><td>$c</td><td>$cintassocpt</td><td>$cintbc</td><td>$cext</td></tr>\n";
echo "</table>\n";

?>
