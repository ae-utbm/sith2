<?php


$db = mysql_connect("localhost", "ae_read_write", "TazPkEr");

mysql_select_db("ae2");

mysql_query("TRUNCATE `cpt_association`");
mysql_query("TRUNCATE `cpt_comptoir`");
mysql_query("TRUNCATE `cpt_debitfacture`");
mysql_query("TRUNCATE `cpt_mise_en_vente`");
mysql_query("TRUNCATE `cpt_produits`");
mysql_query("TRUNCATE `cpt_rechargements`");
mysql_query("TRUNCATE `cpt_type_produit`");
mysql_query("TRUNCATE `cpt_vendu`");
mysql_query("TRUNCATE `cpt_verrou`");


// Les associations (les id correspondent entre les deux versions, ouf!)
mysql_select_db("ae");
$req = mysql_query("SELECT id,compte,compte_prepaye FROM cpt_association");

mysql_select_db("ae2");
while ( list($id,$compte,$compte_prepaye) = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_association VALUES ('$id','$compte','$compte_prepaye')");

}

// Les comptoirs
mysql_select_db("ae");
$req = mysql_query("SELECT id,nom,id_association,groupe_vendeurs,groupe_admins FROM cpt_comptoir");

mysql_select_db("ae2");
while ( list($id,$nom,$idasso,$grpvend,$grpadm) = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_comptoir VALUES ('$id','$grpadm','$idasso','$grpvend','".mysql_escape_string(utf8_encode($nom))."')");

}

// Les types de produits
mysql_select_db("ae");
$req = mysql_query("SELECT id,nom,action,id_association,url_logo,description FROM cpt_type");

mysql_select_db("ae2");
while ( list($id,$nom,$action,$idasso,$url,$desc) = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_type_produit VALUES ('$id','$idasso'," .
			"'".mysql_escape_string(utf8_encode($nom))."'," .
			"'".mysql_escape_string(utf8_encode($action))."'," .
			"'".mysql_escape_string(utf8_encode($url))."'," .
			"'".mysql_escape_string(utf8_encode($desc))."')");

}

// Les produits
mysql_select_db("ae");
$req = mysql_query("SELECT * FROM cpt_produits");

mysql_select_db("ae2");
while ( $row = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_produits VALUES (" .
			"'".mysql_escape_string(utf8_encode($row['id']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_type']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_association']))."'," .
			"'".mysql_escape_string(utf8_encode($row['nom']))."'," .
			"'".mysql_escape_string(utf8_encode($row['prix_vente_barman']))."'," .
			"'".mysql_escape_string(utf8_encode($row['prix_vente']))."'," .
			"'".mysql_escape_string(utf8_encode($row['prix_achat']))."'," .
			"'".mysql_escape_string(utf8_encode($row['meta']))."'," .
			"'".mysql_escape_string(utf8_encode($row['action']))."'," .
			"'".mysql_escape_string(utf8_encode($row['code_barre']))."'," .
			"'".mysql_escape_string(utf8_encode($row['stock_global']))."'," .
			"'".mysql_escape_string(utf8_encode($row['archive']))."'," .
			"'".mysql_escape_string(utf8_encode($row['url_logo']))."'," .		
			"'".mysql_escape_string(utf8_encode($row['description']))."')");

}

// Les mises en vente (<=> vente produit)
mysql_select_db("ae");
$req = mysql_query("SELECT id_produit,id_comptoir,stock_local FROM cpt_vente_produit");

mysql_select_db("ae2");
while ( list($idprod,$idcpt,$stock) = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_mise_en_vente VALUES ('$idprod','$idcpt','$stock')");

}

// Les rechargements
mysql_select_db("ae");
$req = mysql_query("SELECT * FROM cpt_rechargements");

mysql_select_db("ae2");
while ( $row = mysql_fetch_array($req) )
{
	mysql_query("INSERT INTO cpt_rechargements VALUES (" .
			"'".mysql_escape_string(utf8_encode($row['id']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_client']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_comptoir']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_operateur']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_association']))."'," .
			"'".mysql_escape_string(utf8_encode($row['montant']))."'," .
			"'".mysql_escape_string(utf8_encode($row['type_paiement']))."'," .
			"'".mysql_escape_string(utf8_encode($row['banque']))."'," .
			"'".mysql_escape_string(utf8_encode($row['date']))."')");


}

// Les ventes (alors là c'est le bordel)
mysql_select_db("ae");
$req = mysql_query("SELECT COUNT( * ) AS nb, " .
		"cpt_vente . * ," .
		"SUM(prix_vente) AS ctl, " .
		"CONCAT( heure_vente, ':', id_client, ':', id_comptoir, ':', id_produit, ':', prix_vente ) AS truc " .
		"FROM cpt_vente " .
		"GROUP BY truc " .
		"ORDER BY heure_vente, id_client, id_comptoir");


$prev_client = null;
$prev_heure_vente = null;
$prev_comptoir = null;
$id_facture=null;
mysql_select_db("ae2");
while ( $row = mysql_fetch_array($req) )
{
	if ( $prev_client != $row["id_client"] || 
		$prev_heure_vente != $row["heure_vente"] ||
		$prev_comptoir != $row["id_comptoir"] )	
	{
		if ( $id_facture )
		{
			$sreq = 	mysql_query("SELECT SUM(quantite*prix_unit) FROM cpt_vendu WHERE id_facture=$id_facture");
			list($sum) = mysql_fetch_array($sreq);
			
			mysql_query("UPDATE cpt_debitfacture SET montant_facture=$sum WHERE id_facture=$id_facture");

		}

		
		mysql_query("INSERT INTO cpt_debitfacture VALUES (" .
			"''," .
			"'".mysql_escape_string(utf8_encode($row['id_vendeur']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_comptoir']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_client']))."'," .
			"'".mysql_escape_string(utf8_encode($row['heure_vente']))."'," .
			"'AE'," .
			"'0'," . // A calculer plus tard
			"'')");
		$id_facture=mysql_insert_id();
		$prev_client = $row["id_client"];
		$prev_heure_vente = $row["heure_vente"];
		$prev_comptoir = $row["id_comptoir"];
	}

	mysql_query("INSERT INTO cpt_vendu VALUES (" .
			"'".$id_facture."'," .
			"'".mysql_escape_string(utf8_encode($row['id_produit']))."'," .
			"'".mysql_escape_string(utf8_encode($row['id_association']))."'," .
			"'".mysql_escape_string(utf8_encode($row['nb']))."'," .
			"'".mysql_escape_string(utf8_encode($row['prix_vente']))."')");
}

if ( $id_facture )
{
	$sreq = 	mysql_query("SELECT SUM(quantite*prix_unit) FROM cpt_vendu WHERE id_facture=$id_facture");
	list($sum) = mysql_fetch_array($sreq);
	
	mysql_query("UPDATE cpt_debitfacture SET montant_facture=$sum WHERE id_facture=$id_facture");

}
?>
