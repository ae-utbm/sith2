<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site();

$allauthors = array();

$req = new requete($site->db,"SELECT id_auteur,nom_auteur FROM `bk_auteur`");

function books_add_auteur ( $old_id_auteur, $new_id_auteur )
{
	global $site;	
	
	$req = new requete($site->db,"SELECT id_objet FROM `bk_book` WHERE id_auteur='".$old_id_auteur."'");
	
	while ( list($id_objet) = $req->get_row() )
	{
		$sql = new insert ($site->dbrw,
				"bk_livre_auteur",
				array(
					"id_objet" => $id_objet,
					"id_auteur" => $new_id_auteur,
					)
				);
	}
	
}

function add_auteur ( $nom_auteur )
{
	global $site;
	
	$sql = new insert ($site->dbrw,
		"bk_auteur",
		array(
			"nom_auteur" => $nom_auteur
			)
		);
				
	return $sql->get_id();
}

while ( list($id_auteur,$nom_auteur) = $req->get_row() )
{
	if ( strpos($nom_auteur, '/') )
	{
		$auteurs = explode('/',$nom_auteur);
		
		foreach($auteurs  as $auteur )
		{
			if ( !isset($allauthors[$auteur]) )	
				$allauthors[$auteur] = add_auteur($auteur);
			
			books_add_auteur ( $id_auteur, $allauthors[$auteur] );
		}

	}
	else
	{
		if ( !isset($allauthors[$nom_auteur]) )	
			$allauthors[$nom_auteur] = add_auteur($nom_auteur);
			
		books_add_auteur ( $id_auteur, $allauthors[$nom_auteur] );
	}

}

?>
