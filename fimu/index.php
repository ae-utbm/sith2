<?php

/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir="../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");


$site = new site;

if ( $site->user->id == -1 )
	error_403("reserved");

$site->start_page ("none", "FIMU 2007 - Inscriptions des bénévoles");

$cts = new contents("Festival International de Musique Universitaire");

if(isset($_REQUEST['magicform']) && $_REQUEST['magicform']['name'] == "fimu_inscr")
{
	$sql = new insert($site->dbrw, "fimu_inscr",
		array(
			"id_inscr" => '',
			"id_utilisateur" => $site->user->id,
			"disp_24" => $_REQUEST['disp_24'],
			"disp_25" => $_REQUEST['disp_25'],
			"disp_26" => $_REQUEST['disp_26'],
			"disp_27" => $_REQUEST['disp_27'],
			"disp_28" => $_REQUEST['disp_28'],
			"disp_29" => $_REQUEST['disp_29'],
			"choix1_choix" => $_REQUEST['choix1_choix'],
			"choix1_com" => $_REQUEST['choix1_com'],
			"choix2_choix" => $_REQUEST['choix2_choix'],
			"choix2_com" => $_REQUEST['choix2_com'],
			"lang1_lang" => $_REQUEST['lang1_lang'],
			"lang1_lvl" => $_REQUEST['lang1_lvl'],
			"lang1_com" => $_REQUEST['lang1_com'],
			"lang2_lang" => $_REQUEST['lang2_lang'],
			"lang2_lvl" => $_REQUEST['lang2_lvl'],
			"lang2_com" => $_REQUEST['lang2_com'],
			"lang3_lang" => $_REQUEST['lang3_lang'],
			"lang3_lvl" => $_REQUEST['lang3_lvl'],
			"lang3_com" => $_REQUEST['lang3_com'],
			"permis" => $_REQUEST['permis'],
			"voiture" => $_REQUEST['voiture'],
			"afps" => $_REQUEST['afps'],
			"afps_com" => $_REQUEST['afps_com'],
			"poste_preced" => $_REQUEST['poste_preced'],
			"remarques" => $_REQUEST['remarques']
		)
		);
	if($sql->result)
		$cts->add_paragraph("Votre inscription s'est correctement déroulée, ".$site->user->prenom." ". $site->user->nom." <br />
					Nous vous remercions de votre implication. <br />
					A présent si vous ne savez pas quoi faire nous vous conseillons cet excellent <a href='http://fr.wikipedia.org/wiki/M%C3%A9sopotamie'>article sur la Mésopotamie</a>");
	else
		$cts->add_paragraph("Une erreur est survenue <br />
				erreur n°$sql->errno <br />
				détail : $sql->errmsg <br /><br />
				Merci de contacter les authorités compétentes ");
		
}
else if (isset($_REQUEST['listing']))
{

//	$tbl = new itemlist("Liste des personnes s'étant inscrites pour le FIMU via le site de l'AE", false);
	
	$sql = new requete($site->db, "SELECT fimu_inscr.id_utilisateur, 
						utilisateurs.nom_utl, 
						utilisateurs.prenom_utl, 
						utilisateurs.id_utilisateur, 
						fimu_inscr.choix1_choix, 
						fimu_inscr.choix2_choix, 
						fimu_inscr.lang1_lang, 
						fimu_inscr.lang2_lang,
						fimu_inscr.lang3_lang,
					CONCAT(utilisateurs.prenom_utl,' ',utilisateurs.nom_utl) AS `nom_utilisateur`
					FROM fimu_inscr 
					LEFT JOIN utilisateurs 
					ON fimu_inscr.id_utilisateur = utilisateurs.id_utilisateur");
	$tbl = new sqltable("fimu_benevoles", 
				"Liste des personnes s'étant inscrites pour le FIMU via le site de l'AE",
				$sql,
				"index.php",
				"utilisateurs.id_utilisateur",
				array("=num" => "N°",
					"nom_utilisateur" => "Utilisateur",
					"choix1_choix" => "Choix 1",
					"choix2_choix" => "Choix 2",
					"lang1_lang" => "Langue 1",
					"lang2_lang" => "Langue 2",
					"lang3_lang" => "Langue 3"
					),
				array(),
				array(),
				array()
				);
	$cts->add($tbl,true);

}
else
{

/*******************************************************************
 * Start fimu_inscr form
 */

	$intro = new wikicontents(false, "
	**22ème FIMU : les 10, 11 et 12 Mai 2008**\\
	
L'AE vous permet de vous inscrire en ligne pour être bénévole au FIMU 2008. Le formulaire suivant est la copie conforme de la feuille que 	vous pourrez trouver dans les points de distribution.\\
	
Les informations personnelles (telles que votre nom, prénom, adresse...) seront remplies à partir de vos informations Matmatronch', vous n'avez plus qu'à indiquer vos disponibilités et vos souhaits d'affectation.\\

Pour plus d'informations sur les différents postes disponible pendant le FIMU, [[rendez vous ici|http://ae.utbm.fr/article.php?name=fimu_info|rendez-vous ici]].\\

L'AE, Com'Et, les Belfortains, la Région et certainement une bonne moitié de la planète vous remercient de votre implication dans cet évenement, qui n'existerait pas sans le bénévolat étudiant.

	");
	
	$cts->add_paragraph($intro->buffer);
	$cts->add_title(1, " ");
	
	$usrinfo = new userinfo($site->user, true, false, false, false, true, true);
	$cts->add($usrinfo, false, true, "Informations personnelles");
	$cts->add_title(1, " ");

	/* Prévention des doublons */
	$sql = new requete($site->db, "SELECT id_utilisateur 
					FROM fimu_inscr 
					WHERE id_utilisateur = ".$site->user->id);
	if($sql->lines)
	{
		$cts->add_paragraph("Nous vous remercions de votre impressionante volonté d'implication dans le FIMU, cependant vous vous êtes déjà inscrit.");
		$cts->add_paragraph("Si vous souhaitez effectuer une modification dans votre inscription, contactez les administrateurs du site");
	}
	else
	{

	/* Start form */
	
	$frm = new form("fimu_inscr", "index.php", true, "POST", "Inscription");
	$frm->allow_only_one_usage();
	
	$subfrm = new form("fimu_inscr", "index.php", true, "POST", "Disponibilités");
		$subfrm->add_info("Il est fortement souhaitable que vous soyez disponible 3 jours consécutifs minimum");
		$subfrm->add_checkbox("disp_24", "Jeudi 24 Mai");
		$subfrm->add_checkbox("disp_25", "Vendredi 25 Mai");
	$subfrm->add_checkbox("disp_26", "Samedi 26 Mai");
		$subfrm->add_checkbox("disp_27", "Dimanche 27 Mai");
		$subfrm->add_checkbox("disp_28", "Lundi 28 Mai");
		$subfrm->add_checkbox("disp_29", "Mardi 29 Mai");
	$frm->add($subfrm);
	
	$subfrm = new form("fimu_inscr", "index.php", true, "POST", "<a href='http://ae.utbm.fr/article.php?name=fimu_info'>Souhaits de poste <img src='$topdir/images/tipp.png' /></a>");

		$prefs = array("pilote" => "Pilote de groupe", "regisseur" => "Régisseur de scène", "accueil" => "Accueil du public", "signaletic" => "Equipe signalétique");
	
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_select_field("choix1_choix", "Choix 1", $prefs);
			$subfrm2->add_text_field("choix1_com", "Commentaire", "", false, 63);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_select_field("choix2_choix", "Choix 2", $prefs);
			$subfrm2->add_text_field("choix2_com", "Commentaire", "", false, 63);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
	$frm->add($subfrm);

	$subfrm = new form("fimu_inscr", "index.php", true, "POST", "Langues parlées");
	
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_text_field("lang1_lang", "Langue 1");
			$subfrm2->add_text_field("lang1_lvl", "Niveau", "", false, 10);
			$subfrm2->add_text_field("lang1_com", "Commentaire", "", false, 40);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_text_field("lang2_lang", "Langue 2");
			$subfrm2->add_text_field("lang2_lvl", "Niveau", "", false, 10);
			$subfrm2->add_text_field("lang2_com", "Commentaire", "", false, 40);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_text_field("lang3_lang", "Langue 3");
			$subfrm2->add_text_field("lang3_lvl", "Niveau", "", false, 10);
			$subfrm2->add_text_field("lang3_com", "Commentaire", "", false, 40);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
	$frm->add($subfrm);
	
	$ouinon = array('O' => "Oui", 'N' => "Non");
	$subfrm = new form("fimu_inscr", "index.php", true, "POST", "Autres renseignements");
		$subfrm->add_radiobox_field("permis", "Possession du permis de conduire", $ouinon, "N");
		$subfrm->add_radiobox_field("voiture", "Possession d'une voiture personnelle", $ouinon, "N");
		
		$subfrm2 = new form("fimu_inscr", "index.php");
			$subfrm2->add_radiobox_field("afps", "Titulaire d'un diplôme de premiers secours (AFPS...)", $ouinon, "N");
			$subfrm2->add_text_field("type_afps", "Lequel", "", false, 35);
		$subfrm->add($subfrm2, false, false, false, false, true);
		
		$subfrm->add_text_field("poste_preced", "Poste(s) aux précédents FIMU", "", false, 43);
		$subfrm->add_text_area("remarques", "Remarques/suggestions");
		
		
		
	$frm->add($subfrm);
	
	$frm->add_submit("valid","Valider");
	
	
	
$cts->add($frm,true);

} //fin condition prevention doublons


$cts->add_paragraph("<br /><br />Le FIMU est un évenement co-organisé par la Ville de Belfort, la Fédération Com'Et et l'UTBM");
$cts->add_paragraph("Pour plus d'information : <a href='http://www.fimu.com'>www.fimu.com</a> <br />
			Cellule des Festivals : 03 84 22 94 43 <br />
			Com'Et : 03 84 26 48 01 / catherine DOT mougin AT comet DOT asso DOT fr <br />
			Renseignement auprès de l'AE ");
			
}

$site->add_contents($cts);

$site->end_page ();

?>
