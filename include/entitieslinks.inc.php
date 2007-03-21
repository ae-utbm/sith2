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
$GLOBALS["entitieslinks"] = array (
	"utilisateur"	=> array ( "id_utilisateur", "nom_utilisateur"/*alias*/, "images/icons16/user.png", "user.php" ),
	"asso" 			=> array ( "id_asso", "nom_asso", "images/icons16/asso.png", "asso.php", "asso" ),
	"group" 			=> array ( "id_groupe", "nom_groupe", "images/icons16/group.png", "group.php", "groupe" ),
	"sitebat" 		=> array ( "id_site", "nom_site", "images/icons16/site.png", "sitebat.php" ),
	"salle" 			=> array ( "id_salle", "nom_salle", "images/icons16/salle.png", "salle.php" ),
	"batiment" 		=> array ( "id_batiment", "nom_bat", "images/icons16/batiment.png", "batiment.php" ),
	"entreprise" 	=> array ( "id_ent", "nom_entreprise", "images/icons16/entreprise.png", "entreprise.php", "entreprise" ),
	"classeur_compta"=> array ( "id_classeur", "nom_classeur", "images/icons16/classeur.png", "compta/classeur.php"),
	"compte_asso"    => array ( "id_cptasso", "nom_cptasso","images/icons16/compte.png","compta/cptasso.php"),
	"budget"			=> array ( "id_budget","nom_budget","images/icons16/budget.png","compta/budget.php"),
	"objtype"		=> array ( "id_objtype","nom_objtype","images/icons16/objtype.png","objtype.php", "inv_type_objets" ),
	"objet"			=> array ( "id_objet","nom_objet","images/icons16/objet.png","objet.php"),
	"reservation"	=> array ( "id_salres","id_salres","images/icons16/reservation.png","reservation.php"),
	"assocpt"		=> array ( "id_assocpt", "nom_asso", "images/icons16/asso.png", "asso.php"),
	"typeproduit" 	=> array ( "id_typeprod", "nom_typeprod", "images/icons16/typeprod.png", "comptoir/admin.php", "cpt_type_produit" ),
	"catphoto"		=> array ( "id_catph", "nom_catph", "images/icons16/catph.png", "sas2/", "sas_cat_photos" ),
	"photo"			=> array ( "id_photo", "id_photo", "images/icons16/photo.png", "sas2/", "sas_photos" ),
	"emprunt"		=> array ( "id_emprunt", "id_emprunt", "images/icons16/emprunt.png", "emprunt.php", "inv_emprunt" ),
	"produit"		=> array ( "id_produit", "nom_prod", "images/icons16/produit.png", "comptoir/admin.php", "cpt_produits" ),
	"facture"		=> array ( "id_facture", "id_facture", "images/icons16/emprunt.png", "comptoir/gen_fact.php", "cpt_debitfacture" ),
	"editeur"		=> array ( "id_editeur", "nom_editeur", "images/icons16/editeur.png", "biblio/", "bk_editeur"),
	"serie"			=> array ( "id_serie", "nom_serie", "images/icons16/serie.png", "biblio/", "bk_serie"),
	"auteur"			=> array ( "id_auteur", "nom_auteur", "images/icons16/auteur.png", "biblio/", "bk_auteur"),
	"livre"			=> array ( "id_livre", "nom_livre", "images/icons16/livre.png", "biblio/"),
	"sondage"		=> array ( "id_sondage","question","images/icons16/sondage.png","sondage.php"),
	"comptoir"		=> array ( "id_comptoir", "nom_cpt", "images/icons16/misc.png", false, "cpt_comptoir" ),
	"compte_bancaire"=> array ( "id_cptbc","nom_cptbc","images/icons16/cptbc.png","compta/cptbc.php"),
	"dfile"			=> array ( "id_file", "titre_file", "images/icons16/file.png", "d.php"),
	"dfolder"		=> array ( "id_folder", "titre_folder", "images/icons16/folder.png", "d.php"),
	"operation" => array( "id_op", "id_op", "images/icons16/file.png", "compta/classeur.php")
	
	);

function entitylink ( $class, $id, $nom )
{
	global $topdir;
	if ( !isset($GLOBALS["entitieslinks"][$class]) )
		return $nom;
	return "<a href=\"".$topdir.$GLOBALS["entitieslinks"][$class][3]."?".$GLOBALS["entitieslinks"][$class][0]."=$id\"><img src=\"".$topdir.$GLOBALS["entitieslinks"][$class][2]."\" class=\"icon\" alt=\"Fiche\" /> ".htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a>";
}

/**
 * Crée un lien vers la page traitant de l'instance de l'objet passé.
 * Utilise les donnés du tableau $GLOBALS["entitieslinks"].
 * @param $obj Instance à traiter.
 * @param $obj1 Autre objet requis pour la génération du lien.
 * @param $obj2 Autre objet requis pour la génération du lien.
 * @return Le code html du lien avec une iconne.
 */
function classlink ( $obj, $obj1=null,$obj2=null )
{
	global $topdir;
	$class = get_class($obj);	
	if ( !isset($GLOBALS["entitieslinks"][$class]) )
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
	return "<a href=\"".$topdir.$GLOBALS["entitieslinks"][$class][3]."?".$GLOBALS["entitieslinks"][$class][0]."=$id\"><img src=\"".$topdir.$GLOBALS["entitieslinks"][$class][2]."\" class=\"icon\" alt=\"Fiche\"  /> ".htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a>";
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
	return "<a href=\"".$topdir."article.php?name=$name\"><img src=\"".$topdir."images/icons16/page.png\" class=\"icon\" alt=\"Article\" /> ".htmlentities($title,ENT_NOQUOTES,"UTF-8")."</a>";
}


?>
