<?php

$topdir="../";
require_once($topdir."include/site.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir."include/entities/files.inc.php");
$site = new site();

$root = new dfolder($site->db);
$root->load_by_id(1);

$cats=array();

$sql = new requete($site->db,"SELECT `id_catfch`,`nom_catfch` FROM `fichiers_cat`");

while ( list($id,$nom) = $sql->get_row() )
{
	$cats[$id] = new dfolder($site->db,$site->dbrw);
	$cats[$id]->herit($root);
	$cats[$id]->add_folder ( $nom, $root->id, "", null );
}

print_r($cats);
$sql = new requete($site->db,"SELECT * FROM `fichiers`");

while ( $row = $sql->get_row() )
{
	$f = new dfile($site->db,$site->dbrw);
	$f->herit($cats[$row['id_catfch']]);
	$f->id_utilisateur = $row['id_utilisateur'];
	
	if ( !$row['public_fichier'] )
	{
		$f->droits_acces = $f->droits_acces & 0xFFE;		
	}

	$f->import_file ( 
		$topdir.$row['nom_fichier'], basename($topdir.$row['nom_fichier']), filesize($topdir.$row['nom_fichier']), 
		$row['mime_fichier'], strtotime($row['date_fichier']), true, 
		$row['nb_telecharge_fichier'], $row['titre_fichier'], $cats[$row['id_catfch']]->id, 
		$row['commentaire_fichier'], null );
	
}


?>
