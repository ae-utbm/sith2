<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 */


// array ( nom du champ id, nom du champ nom (peut être calculé), icone, fichier [, table ] )

/**
 * @file
 */


/**
 * Donnés sur les différents objets traités :
 * nom de la classe => array (
 *     nom du champ SQL d'identification
 *     nom du champ SQl du nom
 *     url de l'icone associée
 *     url de la page d'information
 *     nom de table SQL (facultatif)
 *     )
 */
$GLOBALS["entitiescatalog"] = array (
	"utilisateur"	=> array ( "id_utilisateur", "nom_utilisateur"/*alias*/, "user.png", "user.php", null, "utilisateur.inc.php"),
	"page" => array ( "id_page", "titre_page", "page.png", "article.php"),
	"wiki" => array ( "id_wiki", "fullpath_wiki", "page.png", "wiki2/"),
	"asso" 			=> array ( "id_asso", "nom_asso", "asso.png", "asso.php", "asso", "asso.inc.php" ),
	"group" 			=> array ( "id_groupe", "nom_groupe", "group.png", "group.php", "groupe" ),
	"sitebat" 		=> array ( "id_site", "nom_site", "site.png", "sitebat.php" ),
	"salle" 			=> array ( "id_salle", "nom_salle", "salle.png", "salle.php" ),
	"batiment" 		=> array ( "id_batiment", "nom_bat", "batiment.png", "batiment.php" ),
	"entreprise" 	=> array ( "id_ent", "nom_entreprise", "entreprise.png", "entreprise.php", "entreprise","entreprise.inc.php" ),
	"classeur_compta"=> array ( "id_classeur", "nom_classeur", "classeur.png", "compta/classeur.php"),
	"compte_asso"    => array ( "id_cptasso", "nom_cptasso","compte.png","compta/cptasso.php"),
	"budget"			=> array ( "id_budget","nom_budget","budget.png","compta/budget.php"),
	"objtype"		=> array ( "id_objtype","nom_objtype","objtype.png","objtype.php", "inv_type_objets" ),
	"objet"			=> array ( "id_objet","nom_objet","objet.png","objet.php"),
	"reservation"	=> array ( "id_salres","id_salres","reservation.png","reservation.php"),
	"assocpt"		=> array ( "id_assocpt", "nom_asso", "asso.png", "asso.php"),
	"typeproduit" 	=> array ( "id_typeprod", "nom_typeprod", "typeprod.png", "comptoir/admin.php", "cpt_type_produit" ),
	"catphoto"		=> array ( "id_catph", "nom_catph", "catph.png", "sas2/", "sas_cat_photos" ),
	"photo"			=> array ( "id_photo", "id_photo", "photo.png", "sas2/", "sas_photos" ),
	"emprunt"		=> array ( "id_emprunt", "id_emprunt", "emprunt.png", "emprunt.php", "inv_emprunt" ),
	"produit"		=> array ( "id_produit", "nom_prod", "produit.png", "comptoir/admin.php", "cpt_produits","produit.inc.php" ),
	"facture"		=> array ( "id_facture", "id_facture", "emprunt.png", "comptoir/gen_fact.php", "cpt_debitfacture" ),
	"editeur"		=> array ( "id_editeur", "nom_editeur", "editeur.png", "biblio/", "bk_editeur"),
	"serie"			=> array ( "id_serie", "nom_serie", "serie.png", "biblio/", "bk_serie"),
	"auteur"			=> array ( "id_auteur", "nom_auteur", "auteur.png", "biblio/", "bk_auteur"),
	"livre"			=> array ( "id_livre", "nom_livre", "livre.png", "biblio/"),
	"sondage"		=> array ( "id_sondage","question","sondage.png","sondage.php"),
	"comptoir"		=> array ( "id_comptoir", "nom_cpt", "misc.png", false, "cpt_comptoir" ),
	"compte_bancaire"=> array ( "id_cptbc","nom_cptbc","cptbc.png","compta/cptbc.php"),
	"dfile"			=> array ( "id_file", "titre_file", "file.png", "d.php",false,"files.inc.php"),
	"dfolder"		=> array ( "id_folder", "titre_folder", "folder.png", "d.php",false,"folder.inc.php"),
	"operation" => array( "id_op", "id_op", "file.png", "compta/classeur.php"),
	
	"forum" => array( "id_forum", "titre_forum", "forum.png", "forum2/"),
	"sujet" => array( "id_sujet", "titre_sujet", "sujet.png", "forum2/"),
	"message" => array( "id_message", "id_message", "message.png", "forum2/"),
	
	"pays" => array( "id_pays", "nom_pays", "pays.png", "loc.php", "loc_pays","pays.inc.php"),
	"ville" => array( "id_ville", "nom_ville", "ville.png", "loc.php", "loc_ville","ville.inc.php"),
	"lieu" => array( "id_lieu", "nom_lieu", "lieu.png", "loc.php", "loc_lieu","lieu.inc.php"),
	
	"secteur" => array( "id_secteur", "nom_secteur", "lieu.png", "entreprise.php", "secteur"),
	
	"efact"=>array("id_efact","titre_facture","file.png","compta/efact.php","cpta_facture","efact.inc.php")
	
	);

function entitylink ( $class, $id, $nom )
{
	global $topdir;
	if ( !isset($GLOBALS["entitiescatalog"][$class]) )
		return $nom;
	return "<a href=\"".$topdir.$GLOBALS["entitiescatalog"][$class][3]."?".$GLOBALS["entitiescatalog"][$class][0]."=$id\"><img src=\"".$topdir."images/icons/16/".$GLOBALS["entitiescatalog"][$class][2]."\" class=\"icon\" alt=\"Fiche\" /> ".htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a>";
}

/**
 * Crée un lien vers la page traitant de l'instance de l'objet passé.
 * Utilise les donnés du tableau $GLOBALS["entitiescatalog"].
 * @param $obj Instance à traiter.
 * @param $obj1 Autre objet requis pour la génération du lien.
 * @param $obj2 Autre objet requis pour la génération du lien.
 * @return Le code html du lien avec une iconne.
 */
function classlink ( $obj, $obj1=null,$obj2=null )
{

  return $obj->get_html_link();

/*
	global $topdir;
	$class = get_class($obj);	
	if ( !isset($GLOBALS["entitiescatalog"][$class]) )
		return "?!?";
	if ( $obj->id < 1 )
		return "(aucun)";
	$id = &$obj->id;
	if ( $class == "utilisateur" )
		$nom = $obj->prenom." ".$obj->nom;
	elseif ( $class == "objet" )
	{
		if ( $obj->nom )
			$nom = $obj->nom." (".$obj->num.")";
		else
			$nom = $obj->num;
	}
	elseif ( isset ($obj->nom) )
		$nom = &$obj->nom;
	elseif ( isset($obj->titre))	
		$nom = &$obj->titre;
	elseif ( isset($obj->num))	
		$nom = "n°".$obj->num;
	else
		$nom = "n°".$obj->id;
	return "<a href=\"".$topdir.$GLOBALS["entitiescatalog"][$class][3]."?".$GLOBALS["entitiescatalog"][$class][0]."=$id\"><img src=\"".$topdir.$GLOBALS["entitiescatalog"][$class][2]."\" class=\"icon\" alt=\"Fiche\"  /> ".htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a>";
*/
}

/**
 * Génère un lien vers une page du wiki.
 * @param $name Nom de la page wiki.
 * @param $title Titre de lien.
 * @return le code html du lien avec son iconne.
 */
function wikilink ( $name, $title )
{
	global $topdir;
	return "<a href=\"".$topdir."article.php?name=$name\"><img src=\"".$topdir."images/icons/16/page.png\" class=\"icon\" alt=\"Article\" /> ".htmlentities($title,ENT_NOQUOTES,"UTF-8")."</a>";
}


?>
