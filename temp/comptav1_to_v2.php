<?php

exit();

$db = mysql_connect("localhost", "ae_read_write", "TazPkEr");

mysql_select_db("ae");

$reqper = mysql_query("SELECT
compta_op.date_facture AS date_op,
compta_op.paiement AS mode_op,
compta_op.num_facture AS num_op,
IF(compta_op.num_facture_deb IS NULL,1,-1) AS type_mouvement,
compta_op.montant*100 AS montant_op,
compta_op.num_cheque AS num_cheque_op,
compta_op.effectue AS op_effctue,
compta_op.commentaire AS commentaire_op,
compta_type.id AS type_id,
compta_type.nom AS type_nom,
ae2.temp_cpta_classeurs.id_classeur,
ae2.temp_cpta_personnes.id_utilisateur,
ae2.temp_cpta_personnes.id_ent,
ae2.temp_cpta_personnes.id_cptasso,
ae2.temp_cpta_personnes.id_asso,
ae2.temp_cpta_personnes.comment AS personne_comment,
ae2.cpta_cpasso.id_asso as id_asso_target
FROM compta_op
INNER JOIN compta_type ON compta_op.type=compta_type.id
INNER JOIN ae2.temp_cpta_classeurs ON ae2.temp_cpta_classeurs.id_oldclasseur = compta_op.classeur
INNER JOIN ae2.temp_cpta_personnes ON ae2.temp_cpta_personnes.id_personne = compta_op.personne
INNER JOIN ae2.cpta_classeur ON ae2.temp_cpta_classeurs.id_classeur = ae2.cpta_classeur.id_classeur
INNER JOIN ae2.cpta_cpasso ON ae2.cpta_classeur.id_cptasso = ae2.cpta_cpasso.id_cptasso");

mysql_select_db("ae2");
while ( $row = mysql_fetch_array($reqper) )
{
	$type_id = $row["type_id"];
	$type_mouvement = $row["type_mouvement"];
	$id_asso = $row["id_asso_target"];
	
	if ( !isset($types[$id_asso][$type_mouvement][$type_id]) )
	{
		
		$req = mysql_query("INSERT INTO cpta_op_clb (id_asso,libelle_opclb,type_mouvement) VALUES ($id_asso,'".mysql_real_escape_string($row["type_nom"])."',$type_mouvement)");
		echo mysql_error();
		$types[$id_asso][$type_mouvement][$type_id] = mysql_insert_id();
	}
	
	$ntype_id = $types[$id_asso][$type_mouvement][$type_id];

	if ( $row['personne_comment'] )
		$row['commentaire_op'] .= " (".$row['personne_comment'].")";

	$req = mysql_query("INSERT INTO cpta_operation " .
				"(id_opclb," .
				"id_cptasso," .
				"id_utilisateur," .
				"id_asso," .
				"id_ent," .
				"id_classeur," .
				"num_op," .
				"montant_op," .
				"date_op," .
				"commentaire_op," .
				"op_effctue," .
				"mode_op," .
				"num_cheque_op) VALUES " .
				"('$ntype_id'," .
				"'".intval($row['id_cptasso'])."'," .
				"'".intval($row['id_utilisateur'])."'," .
				"'".intval($row['id_asso'])."'," .
				"'".intval($row['id_ent'])."'," .
				"'".intval($row['id_classeur'])."'," .
				"'".intval($row['num_op'])."'," .
				"'".intval($row['montant_op'])."'," .
				"'".mysql_real_escape_string($row['date_op'])."'," .
				"'".mysql_real_escape_string($row['commentaire_op'])."'," .
				"'".intval($row['op_effctue'])."'," .
				"'".intval($row['mode_op'])."'," .
				"'".mysql_real_escape_string($row['num_cheque_op'])."')");
	echo mysql_error();

}

?>
