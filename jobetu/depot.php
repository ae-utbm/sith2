<?
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

$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once("include/jobetu.inc.php");
require_once("include/jobuser_client.inc.php");
require_once("include/annonce.inc.php");

$site = new site();
$site->start_page("services", "AE Job Etu");

$cts = new contents("Déposer une annonce");

$tabs = array(
							array("", "jobetu/depot.php", "Informations"),
							array("infos", "jobetu/depot.php?action=infos", "Vos informations"),
	      			array("annonce", "jobetu/depot.php?action=annonce", "Votre annonce")
	      			);
$cts->add(new tabshead($tabs, $_REQUEST['action']));


/****************************************************************************************************
 *  Formulaire de dépôt de l'annonce
 */

if(!empty($_REQUEST['action']) && $_REQUEST['action']=="annonce")
{
	/*************************************************************
	 * Récupération et traitement des infos de l'onglet connexion 
	 */
	if(isset($_REQUEST) && $_REQUEST['magicform']['name'] == "user_info")
	{
		$jobuser = new jobuser_client($site->db, $site->dbrw);
		
		if($_REQUEST['type_indent'] == "connexion")
		{
			$jobuser->connexion($_REQUEST['ident_email'], $_REQUEST['ident_passwd']);
		}
		else if($_REQUEST['type_indent'] == "inscr_particulier")
		{
//			print_r($_REQUEST);

			if(!empty($_REQUEST['ip_client_nom'])
			&& !empty($_REQUEST['ip_client_prenom'])
			&& !empty($_REQUEST['ip_email'])
			&& !empty($_REQUEST['ip_passwd'])
			&& $_REQUEST['ip_passwd'] == $_REQUEST['ip_passwd_conf'])
			{
				$jobuser->create_user($_REQUEST['ip_client_nom'], $_REQUEST['ip_client_prenom'], false, $_REQUEST['ip_email'], $_REQUEST['ip_passwd'], false, false, $_REQUEST['sexe']);
			}
		}
		else if($_REQUEST['type_indent'] == "inscr_societe")
		{
		}
	}
	
	$jobetu = new jobetu($site->db, $site->dbrw);
	$jobetu->get_job_types();
	
	$frm = new form("jobs", "depot.php?action=add", false, "POST", "Taiste");
	
	$cts->add_title(2, "Details concernant la sauce aux poireaux");
	$frm->add_text_field("titre_ann", "Titre de l'annonce", false, true, 60);
	$jobetu->add_jobtypes_select_field($frm, "job_type", "Catégorie");
	$frm->add_info("<i>Si vous ne trouvez pas de categorie adequate, n'hesitez pas a <a href=''>le signaler</a></i>");
	$frm->add_text_area("desc_ann", "Description de l'annonce", false, 60, 8, true);
	$frm->add_text_area("profil", "Profil recherche", false, 60, 3, true);
	$frm->add_date_field("date_debut", "Date de debut (facultatif)");
	$frm->add_text_field("duree", "Duree (facultatif)");
	$frm->add_text_field("remuneration", "Rémuneration (facultatif)");
	$frm->add_text_area("divers", "Autres informations", false, 60, 3);
	$frm->add_submit("next", "Etape suivante");
	
	$cts->add($frm);
}


/*******************************************************************************************************
 * Formulaire inscription/connexion 
 */
else if(!empty($_REQUEST['action']) && $_REQUEST['action']=="infos")
{
	$frm_main = new form("user_info", "depot.php?action=annonce", false, "POST", "Informations sur toi public ;)");
	
	/*****************************
	 * Connexion compte existant
	 */	
		$frm_identification = new form("type_indent",null,null,null,"Déjà inscrit ? Identifiez vous");
		$frm_identification->add_text_field("ident_email", "Adresse email");
		$frm_identification->add_password_field("ident_passwd", "Mot de passe");
		$frm_identification->add_submit("go", "Envoyer");
	$frm_main->add($frm_identification,false,true,1,"connexion",false,true,true);

	/**********************************
	 * Nouvelle inscription particulier
	 */
		$frm_inscr_particulier = new form("type_indent",null,null,null,"Particulier ? Créez votre compte");
		$frm_inscr_particulier->add_select_field("ip_sexe", false, array(
																																"m" => "M.",
																																"mme" => "Mme",
																																"mlle" => "Mlle"));
		$frm_inscr_particulier->add_text_field("ip_client_nom", "Nom");
		$frm_inscr_particulier->add_text_field("ip_client_prenom","Prenom");
		$frm_inscr_particulier->add_text_area("ip_adresse", "Adresse", false, 40, 1);
		$frm_inscr_particulier->add_text_field("ip_cpostal", "Code postal");
		$frm_inscr_particulier->add_text_field("ip_ville", "Ville");
		$frm_inscr_particulier->add_text_field("ip_pays", "Pays", "France");
		$frm_inscr_particulier->add_text_field("ip_telephone", "Numero de telephone");
		$frm_inscr_particulier->add("");
		$frm_inscr_particulier->puts("<h3>Votre compte sur AE Job Etu</h3>");
		$frm_inscr_particulier->add_text_field("ip_email", "Adresse email de contact", null, null, null, null, true, "<i>Cette adresse sera votre identifiant sur le site</i>");
		$frm_inscr_particulier->add_password_field("ip_passwd", "Choisissez un mot de passe");
		$frm_inscr_particulier->add_password_field("ip_passwd_conf", "Confirmez votre mot de passe");
		$frm_inscr_particulier->add_submit("go", "Envoyer");
	$frm_main->add($frm_inscr_particulier,false,true,0,"inscr_particulier",false,true);

	/*******************************
	 * Nouvelle inscription société
	 */
		$frm_inscr_societe = new form("type_indent",null,null,null,"Société ? Créez votre compte");
		$frm_inscr_societe->add_text_field("is_client_nom", "Nom de votre société");
		$frm_inscr_societe->add_text_field("is_client_nom", "Activité");
		$frm_inscr_societe->add_text_area("is_adresse", "Adresse", false, 40, 1);
		$frm_inscr_societe->add_text_field("is_cpostal", "Code postal");
		$frm_inscr_societe->add_text_field("is_ville", "Ville");
		$frm_inscr_societe->add_text_field("is_pays", "Pays", "France");
		$frm_inscr_societe->add_text_field("is_telephone", "Numéro de telephone");
		$frm_inscr_societe->add_text_field("is_fax", "Numéro de fax");
		
		$frm_inscr_societe->puts("<h3>Contact au sein de votre entreprise</h3>");
		$frm_inscr_societe->add_select_field("is_identite", false, array(
																																		"m" => "M.",
																																		"mme" => "Mme",
																																		"mlle" => "Mlle"));
		$frm_inscr_societe->add_text_field("is_client_nom", "Nom");
		$frm_inscr_societe->add_text_field("is_client_prenom","Prenom");
		$frm_inscr_societe->add_text_field("is_telephone", "Numero de telephone");

		$frm_inscr_societe->puts("<h3>Votre compte sur AE Job Etu</h3>");
		$frm_inscr_societe->add_text_field("is_email", "Adresse email de contact", null, null, null, null, true, "<i>Cette adresse sera votre identifiant sur le site</i>");
		$frm_inscr_societe->add_password_field("is_passwd", "Choisissez un mot de passe");
		$frm_inscr_societe->add_password_field("is_passwd_conf", "Confirmez votre mot de passe");
		$frm_inscr_societe->add_submit("go", "Envoyer");
	$frm_main->add($frm_inscr_societe,false,true,0,"inscr_societe",false,true);
	$cts->add($frm_main, true);

}

/*******************************************************************************************************
 * Traitement de l'annonce à enregistrer
 */
else if(!empty($_REQUEST['action']) && $_REQUEST['action']=="add" && $_REQUEST['magicform']['name'] == "jobs")
{	
	$jobuser = new jobuser_client($site->db);
	$annonce = new annonce($site->db, $site->dbrw);
	$jobuser->load_by_id($site->user->id);
	
	$annonce->add($jobuser, $_REQUEST['titre_ann'], $_REQUEST['job_type'], $_REQUEST['desc_ann'], $_REQUEST['profil'], $_REQUEST['date_debut'], $_REQUEST['duree'], $_REQUEST['divers']);
	//print_r($annonce);
}
else
{
	/******************************************************************************************************
	 * Onglets informations générales et conditions
	 */
	
	$cts->add_title(2, "Quelques details concernant votre depot");
	$cts->add_paragraph("Vous vous appretez à déposer une annonce sur AE Job Etu et nous vous en remercions");
	$cts->add_paragraph("Patati patata bla bla bla <br /> Vous devrez tout d'abord vous inscrire si c'est la premiere fois que vous venez et puis allez y");
	$cts->add_paragraph("A lire : <a href=\"http://ae.utbm.fr/article.php?name=legals-jobetu-cgu\">C.G.U.</a>");
	
	$frm = new form("go", "depot.php?action=infos", false, "POST", false);
	$frm->add_submit("next", "Etape suivante");
	
	$cts->add($frm);

}


$site->add_contents($cts);

$site->end_page();

?>
