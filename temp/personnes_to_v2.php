<?php

exit();

header("Content-Type: text/html; charset=utf-8");
$db = mysql_connect("localhost", "ae_read_write", "TazPkEr");

mysql_select_db("ae");

$reqper = mysql_query("SELECT compta_personne.id,compta_personne.nom, COUNT(compta_op.id) " .
		"FROM compta_op " .
		"INNER JOIN compta_personne ON compta_op.personne=compta_personne.id " .
		"GROUP BY compta_personne.id " .
		"HAVING COUNT(compta_op.id)>0 ".
		"ORDER BY compta_personne.nom ");


mysql_select_db("ae2");

function try_user ( $nom, $prenom )
{
	// la table utilisateurs n'est pas encore UTF-8
	$nom = utf8_decode($nom);
	$prenom = utf8_decode($prenom);
	
	$req=mysql_query("SELECT id_utilisateur,nom_utl,prenom_utl FROM utilisateurs WHERE " .
			"LCASE(nom_utl)='".mysql_real_escape_string($nom)."' AND LCASE(prenom_utl)='".mysql_real_escape_string($prenom)."'");
	if ( mysql_num_rows($req) )
		return mysql_fetch_array($req);
	
	$req=mysql_query("SELECT id_utilisateur,nom_utl,prenom_utl FROM utilisateurs WHERE " .
			"LCASE(nom_utl)='".mysql_real_escape_string($nom)."' AND LCASE(prenom_utl) LIKE '".mysql_real_escape_string($prenom)."%'");
	if ( mysql_num_rows($req) )
		return mysql_fetch_array($req);
	
	$req=mysql_query("SELECT id_utilisateur,nom_utl,prenom_utl FROM utilisateurs WHERE " .
			"LCASE(nom_utl) LIKE '%".mysql_real_escape_string($nom)."%' AND LCASE(prenom_utl) LIKE '%".mysql_real_escape_string($prenom)."%'");
	if ( mysql_num_rows($req) )
		return mysql_fetch_array($req);
		
	return array(false,false,false);
}

echo "<table border=1>\n";

while ( list($id,$nom) = mysql_fetch_array($reqper) )
{
	
	unset($id_ent);
	unset($nom_entreprise);
	unset($id_asso);
	unset($nom_asso);
	unset($id_utl);
	unset($nom_utl);
	unset($prenom_utl);
	unset($id_cptbc);
	unset($nom_cptbc);
	unset($comment);
	unset($id_cptasso);
	$nom = strtolower(utf8_encode(trim($nom)));


	if ( $nom )
	{
	
	// Est-ce une entreprise ???
	$req=mysql_query("SELECT id_ent,nom_entreprise FROM entreprise WHERE LCASE(nom_entreprise)='".mysql_real_escape_string($nom)."'");
	if ( mysql_num_rows($req) )
		list($id_ent,$nom_entreprise) = mysql_fetch_array($req);
		
	if ( !$id_ent )
	{
		$req=mysql_query("SELECT id_ent,nom_entreprise FROM entreprise WHERE LCASE(nom_entreprise) LIKE '%".mysql_real_escape_string($nom)."%'");
		if ( mysql_num_rows($req) )
			list($id_ent,$nom_entreprise) = mysql_fetch_array($req);
	}
	
	if ( !$id_ent )
	{
		$req=mysql_query("SELECT id_ent,nom_entreprise FROM entreprise WHERE '".mysql_real_escape_string($nom)."' LIKE CONCAT('%',LCASE(nom_entreprise),'%')");
		if ( mysql_num_rows($req) )
			list($id_ent,$nom_entreprise) = mysql_fetch_array($req);
	}
	
	// Est-ce une association ?
	$req=mysql_query("SELECT id_asso,nom_asso FROM asso WHERE LCASE(nom_asso)='".mysql_real_escape_string($nom)."'");
	if ( mysql_num_rows($req) )
		list($id_asso,$nom_asso) = mysql_fetch_array($req);
		
	if ( !$id_asso )
	{
		$req=mysql_query("SELECT id_asso,nom_asso FROM asso WHERE LCASE(nom_asso) LIKE '%".mysql_real_escape_string($nom)."%'");
		if ( mysql_num_rows($req) )
			list($id_asso,$nom_asso) = mysql_fetch_array($req);
	}
	
	if ( !$id_asso )
	{
		$req=mysql_query("SELECT id_asso,nom_asso FROM asso WHERE  '".mysql_real_escape_string($nom)."' LIKE CONCAT('%',LCASE(nom_asso),'%')");
		if ( mysql_num_rows($req) )
			list($id_asso,$nom_asso) = mysql_fetch_array($req);
	}
	
	
	// Est-ce un compte bancaire ?
	$req=mysql_query("SELECT id_cptbc,nom_cptbc FROM cpta_cpbancaire WHERE LCASE(nom_cptbc)='".mysql_real_escape_string($nom)."'");
	if ( mysql_num_rows($req) )
		list($id_cptbc,$nom_cptbc) = mysql_fetch_array($req);
		
	if ( !$id_cptbc )
	{
		$req=mysql_query("SELECT id_cptbc,nom_cptbc FROM cpta_cpbancaire WHERE LCASE(nom_cptbc) LIKE '%".mysql_real_escape_string($nom)."%'");
		if ( mysql_num_rows($req) )
			list($id_cptbc,$nom_cptbc) = mysql_fetch_array($req);
	}
	
	if ( !$id_cptbc )
	{
		$req=mysql_query("SELECT id_cptbc,nom_cptbc FROM cpta_cpbancaire WHERE  '".mysql_real_escape_string($nom)."' LIKE CONCAT('%',LCASE(nom_cptbc),'%')");
		if ( mysql_num_rows($req) )
			list($id_cptbc,$nom_cptbc) = mysql_fetch_array($req);
	}
		
		
		
	// Est-ce un étudiant ?
	if ( !$id_asso && !$id_ent )
	{
	$tokens = explode(" ",$nom);
		
	if ( count($tokens) == 1 )
		list($id_utl,$nom_utl,$prenom_utl) = try_user($nom,"");	
	else if ( count($tokens) == 2 )
	{
		list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[0],$tokens[1]);
		if ( !$id_utl )
			list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[1],$tokens[0]);	
	}
	else if ( count($tokens) == 3 )
	{
		list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[0]." ".$tokens[1],$tokens[2]);
		if ( !$id_utl )
			list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[0],$tokens[1]." ".$tokens[2]);	
		if ( !$id_utl )
			list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[2],$tokens[0]." ".$tokens[1]);
		if ( !$id_utl )
			list($id_utl,$nom_utl,$prenom_utl) = try_user($tokens[1]." ".$tokens[2],$tokens[0]);	
	}
	}
	}
	
	// tentons de deviner le compte asso (s'il y a)
	if ( $id_asso && $id_cptbc )
	{
		
		$req=mysql_query("SELECT id_cptasso FROM cpta_cpasso WHERE id_asso=$id_asso AND id_cptbc=$id_cptbc");
		if ( mysql_num_rows($req) )
			list($id_cptasso) = mysql_fetch_array($req);
		
		
		
		
	}
	else if ( $id_cptbc )
	{
		$req=mysql_query("SELECT asso.id_asso,asso.nom_asso,cpta_cpasso.id_cptasso " .
				"FROM cpta_cpasso " .
				"INNER JOIN asso ON asso.id_asso=cpta_cpasso.id_asso " .
				"WHERE cpta_cpasso.id_cptbc=$id_cptbc");
		if ( mysql_num_rows($req) )
			list($id_asso,$nom_asso,$id_cptasso) = mysql_fetch_array($req);

	}
	else if ( $id_asso )
	{
		$req=mysql_query("SELECT cpta_cpbancaire.id_cptbc,cpta_cpbancaire.nom_cptbc,cpta_cpasso.id_cptasso " .
				"FROM cpta_cpasso " .
				"INNER JOIN cpta_cpbancaire ON cpta_cpbancaire.id_cptbc=cpta_cpasso.id_cptbc " .
				"WHERE cpta_cpasso.id_asso=$id_asso");
		if ( mysql_num_rows($req) )
			list($id_cptbc,$nom_cptbc,$id_cptasso) = mysql_fetch_array($req);
	}
	
	if ( $id_asso || $id_ent || $id_utl || $id_cptbc )
	echo "<tr>";
	else
	{
	echo "<tr bgcolor=red>";
	
	$id_ent = 3;
	$nom_entreprise = "Etudiants/Tiers";
	$comment = $nom;
	
	}
	echo "<td>$id</td><td>$nom</td><td>$id_ent</td><td>$nom_entreprise</td><td>$id_asso</td><td>$nom_asso</td><td>$id_cptbc</td><td>$nom_cptbc</td><td>$id_cptasso</td><td>$id_utl</td><td>$prenom_utl $nom_utl</td><td>$comment</td>";

	echo "</tr>\n";
}
echo "</table>";




?>
