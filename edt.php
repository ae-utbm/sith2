<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "./";

include($topdir. "include/site.inc.php");

//require_once ($topdir . "include/edt.inc.php");
//require_once ($topdir . "include/seance.inc.php");

$site = new site();
error_403("C'est pour bientot");

if (!$site->user->ae)
	error_403("reserved");

if (!$site->user->utbm)
	error_403("reservedutbm");

if ($site->user->id < 1)
	error_403();

function add_seance($autorefill_id = 0)
{
	global $frm,$site;
	if ($autorefill_id)
	{
		$seance = new seance($site->db);
		$seance->load_by_id($autorefill_id);

		$frm->puts("<tr align=\"center\" style=\"background: #BABBBC;\">");
		$frm->puts(utf8_encode("<td>Séance</td>"));

		$frm->puts("<td>");
	
		$frm->puts("<select name=\"id_uv\" DISABLED>");
		$frm->puts("<option value=\"".$seance->id_uv."\" SELECTED>".$seance->nom_uv."</option>");
		$frm->puts("</select>");

		$frm->puts("</td>");

		$frm->puts("<td>");

		$frm->add_select_field("type",null,array("C"=>"Cours","TD"=>"TD","TP"=>"TP"),$seance->type,null,true,false);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_select_field("groupe",null,array(""=>"","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5","6"=>"6","7"=>"7","8"=>"8","9"=>"9","10"=>"10","11"=>"11","12"=>"12","13"=>"13","14"=>"14","15"=>"15"),$seance->groupe,null,true,false);
		$frm->puts("</td>");
		
		$frm->puts("<td>");
	$frm->add_select_field("jour",null,array("Lundi"=>"Lundi","Mardi"=>"Mardi","Mercredi"=>"Mercredi","Jeudi"=>"Jeudi","Vendredi"=>"Vendredi","Samedi"=>"Samedi"),$seance->jour,null,true,false);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_text_field("hr_deb",null,$seance->hr_deb,true,5,true,false);
		$frm->puts("</td>");
		$frm->puts("<td>");
		$frm->add_text_field("hr_fin",null,"10:00",$seance->hr_deb,5,true,false);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_select_field("semaine",null,array("T"=>"Toutes","A"=>"A","B"=>"B"),$seance->semaine,null,true,false);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_text_field("salle",null,$seance->salle,true,4,true,false);
		$frm->puts("</td>");
		$frm->puts("<td><img src=\"".$topdir."images/actions/edit.png\">&nbsp;&nbsp;<input class=\"img_submit\" type=\"image\" src=\"".$topdir."images/actions/delete.png\" name=\"delete\" value=\"".$seance->id."\"></td>");
		$frm->puts("</tr>");

	}
	else
	{
		$frm->puts("<tr align=\"center\" style=\"background: gray;\">");

		$frm->puts("<tr align=\"center\">");
		$frm->puts(utf8_encode("<td>Séance</td>"));

		$frm->puts("<td>");
	
		$req = new requete($site->db,"SELECT * FROM `edt_uv` ORDER BY `nom` ASC");
		$frm->puts("<select name=\"id_uv\">");

		while ( $res = $req->get_row() )
			$frm->puts("<option value=\"".$res['id_uv']."\">".$res['nom']."</option>");

		$frm->puts("</select>");
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_select_field("type",null,array("C"=>"Cours","TD"=>"TD","TP"=>"TP"),false,null,true);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_select_field("groupe",null,array(""=>"","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5","6"=>"6","7"=>"7","8"=>"8","9"=>"9","10"=>"10"),false,null,true);
		$frm->puts("</td>");
		
		$frm->puts("<td>");
$frm->add_select_field("jour",null,array("Lundi"=>"Lundi","Mardi"=>"Mardi","Mercredi"=>"Mercredi","Jeudi"=>"Jeudi","Vendredi"=>"Vendredi","Samedi"=>"Samedi"),false,null,true);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_text_field("hr_deb",null,"08:00",true,5,true);
		$frm->puts("</td>");
		$frm->puts("<td>");
		$frm->add_text_field("hr_fin",null,"10:00",true,5,true);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_select_field("semaine",null,array("T"=>"Toutes","A"=>"A","B"=>"B"),false,null,true);
		$frm->puts("</td>");

		$frm->puts("<td>");
		$frm->add_text_field("salle",null,"X000",true,4,true);
		$frm->puts("</td>");
		$frm->puts("<td><img src=\"".$topdir."images/actions/done.png\">&nbsp;&nbsp;<img src=\"".$topdir."images/actions/delete.png\"></td>");
		$frm->puts("</tr>");
	}
}

function edt_error( $s, $level = 1 )
{
	global $topdir,$site;

	$cts = new contents ("Erreur");
	$cts->set_toolbox(new toolbox(array("javascript:history.go(-1);"=>"Retour")));
	if ( $level == 1 )
		$cts->add_paragraph("<img src=\"".$topdir."images/actions/info.png\"><font colo=\"blue\"><b>&nbsp;&nbsp;".utf8_encode($s)."</b></font>");
	elseif ( $level == 2 )
		$cts->add_paragraph("<img src=\"".$topdir."images/actions/delete.png\"><font colo=\"red\"><b>&nbsp;&nbsp;".utf8_encode($s)."</b></font>");

	$site->add_contents($cts,true);
	$site->end_page();
	exit();

}

if ( isset($_REQUEST['id_utilisateur']) )
{
	$user = new utilisateur($site->db,$site->dbrw);
	$user->load_by_id($_REQUEST["id_utilisateur"]);	
	if ( $user->id < 0 )
	{
		header("Location: ".$topdir."404.php");	
		exit();	
	}
	$user->load_all_extra();
	if (strlen($user->semestre) == 1)
		$semestre = "0".$user->semestre;
	else
		$semestre = $user->semestre;
	$cts = new contents("Emploi du temps de " . $user->nom . " " . $user->prenom . " actuellement en " . $user->branche . $semestre);

	if ($site->user->id == $user->id)
	{
		$edt = new edt($site->db,$site->dbrw);

		if (isset($_POST['delete']))
		{
			$edt->load_lastest($user->id);
			if ($edt->unassign_seance_edt($_POST['delete']))
			{
				$seance = new seance($site->db,$site->db_rw);
				$seance->load_by_id($_POST['delete']);
				if (!$seance->erase())
					edt_error("Erreur lors de la suppression de la séance : " . $_POST['delete'],2);
			}
			else
				edt_error("Erreur lors de la suppression de la correspondance edt <-> séance : " . $_POST['delete'],2);
		}
		if (isset($_POST['add_edt']))
		{
			$edt->add($_POST['branche'],$_POST['semestre'],$user->id);

			if ($edt->id == -1)
				edt_error("Erreur SQL lors de la création de votre emploi du temps");
			if ($edt->id == -2)
				edt_error("Emploi du temps déjà existant pour ce semestre de ".$_POST['branche'].$_POST['semestre'],2);

			if ($edt->id >0)
			{
				header("Location: ".$topdir."edt.php?id_utilisateur=".$user->id);	
				exit();	
			}
		}

		if (isset($_POST['validseance']))
		{
			$edt->load_by_id($_POST['id_edt']);
			$seance = new seance($site->db,$site->dbrw);

			if (!$seance->exists($_POST['id_uv'],$_POST['type'],$_POST['groupe']))
			{
				$seance->add($_POST['hr_deb'],$_POST['hr_fin'],$_POST['jour'],$_POST['semaine'],$_POST['type'],$_POST['groupe'],$_POST['id_uv'],$_POST['salle']);

				if ($seance->id == -1)
					/* erreur de heure de début */
					edt_error("Veuillez vérifier la syntaxe de votre heure de début de seance ! Celle-ci doit être de la forme 08:00 ou 08-00 ou 08/00");
				elseif ($seance->id == -2)
					/* erreur de heure de fin */
					edt_error("Veuillez vérifier la syntaxe de votre heure de fin de seance ! Celle-ci doit être de la forme 08:00 ou 08-00 ou 08/00");
				elseif ($seance->id == -3)
					/* erreur de heure de fin */
					edt_error("Veuillez vérifier la syntaxe de votre salle ! Celle-ci doit être de la forme X000 typiquement UTBM");
				elseif ($seance->id == -4)
					/* erreur SQL */
					edt_error("Erreur dans la requète SQL d'ajout d'une séance !",2);
			}
			if ($seance->id > 0)
			{
				if (!$edt->assign_seance_edt($seance->id))
					edt_error("Erreur dans la requète SQL de mise en correspondance d'une séance avec un utilisateur !");
				else
				{
					$cts = new contents (utf8_encode("Séance ajoutée avec succès"));
					$cts->add_paragraph("<img src=\"".$topdir."images/actions/done.png\"><font colo=\"red\"><b>&nbsp;&nbsp;".utf8_encode("Votre séance de ". $seance->get_nom_uv_by_seance() ." le " . $_POST['jour'] . " de ". $_POST['hr_deb'] . " à " . $_POST['hr_fin'] . " à bien été prise en compte ! ")."</b></font>");
				
					$frm = new form("edtvalid","edt.php?id_utilisateur=".$user->id,true,"POST");
					$frm->add_hidden("id_seance_prec",$seance->id);
					$frm->add_submit("add_seance",utf8_encode("Ajouter une séance"));
					$frm->add_submit("validedt",utf8_encode("Génèrer mon emploi du temps"));
					$frm->set_focus("add_seance");
					$cts->add($frm);
					$site->add_contents($cts,true);
					$site->end_page();
					exit();
				}
			}
		}

		if (isset($_GET['action']) && $_GET['action'] == "new")
		{
			$frm = new form("newedt","edt.php?id_utilisateur=".$user->id,true,"POST",utf8_encode("Création d'un nouvel emploi du temps"));
			$frm->puts("Pour le semestre de :<br/><br/>");
			$frm->add_select_field("branche","Branche",array("TC"=>"TC","GI"=>"GI","GSP"=>"IMAP","GSC"=>"GESC","GMC"=>"GMC"),$user->utbm?$user->branche:null);
			$frm->add_select_field("semestre","Niveau",array("01"=>"01","02"=>"02","03"=>"03","04"=>"04","05"=>"05","06"=>"06","07"=>"07","08"=>"08"));
			$frm->puts("<br/><br/>");
			$frm->add_submit("add_edt",utf8_encode("Créer !"));
			$cts->add($frm,true,false,"newedt",false,false);
			$site->add_contents($cts);
			$site->end_page();
			exit();
		}

		$edt->load_lastest($user->id);
		if ($edt->id <0)
		{
				header("Location: ".$topdir."edt.php?id_utilisateur=".$user->id."&action=new");	
				exit();	
		}
		if ($edt->id >0)
		{
			
		$frm = new form("edtcts","edt.php?id_utilisateur=".$user->id,true,"POST","Remplir mon emploi du temps pour le semestre de ".$edt->branche.$edt->semestre);
		$frm->puts("<table width=\"95%\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">");
		/* header du tableau */
		$frm->puts("<thead align=\"center\">");
		$frm->puts("<td></td>");
		$frm->puts(utf8_encode("<td>UV</td>"));
		$frm->puts(utf8_encode("<td>Type</td>"));
		$frm->puts(utf8_encode("<td>Groupe</td>"));
		$frm->puts(utf8_encode("<td>Jour</td>"));
		$frm->puts(utf8_encode("<td>Heure début</td>"));
		$frm->puts(utf8_encode("<td>Heure fin</td>"));
		$frm->puts(utf8_encode("<td>Semaine</td>"));
		$frm->puts(utf8_encode("<td>Salle</td>"));
		$frm->puts("</thead>");

		/* corps du tableau */
		$frm->puts("<tbody>");
		
		/*if (isset($_POST['add_seance']))
		{*/
			/* rechercher toutes les séances de l'utilisateur */
			$req = new requete($site->db,"SELECT `id_seance` FROM `edt_edts` WHERE `id_edt`='".mysql_real_escape_string($edt->id)."' ORDER BY `id_seance` ASC");
			if (!$req)
				edt_error("Erreur lors de l'auto remplissage des seances");
			/* et les afficher grisées et non modifiables */
			while ( $res = $req->get_row() )
				add_seance($res['id_seance']);
		//}
		add_seance();

		$frm->puts("</tbody>");
		$frm->puts("</table");
		$frm->puts("<br/>");
		$frm->add_hidden("id_edt",$edt->id);
		$frm->add_submit("validseance",utf8_encode("Valider la séance"));
		$frm->set_focus("id_uv");
		$cts->add($frm,true,false,"myedt",false,true,true);
		$cts->set_toolbox(new toolbox(array("edt.php?id_utilisateur=".$user->id."&action=new"=>"Nouvel Emploi du temps")));
		}
	}

	if (file_exists($topdir."images/edts/".$user->id.".edt.jpg"))
	{
		if ($site->user->id == $user->id)
		{
			$cts_2 = new contents("Mon emploi du temps");
			$cts_2->add_paragraph("<img src=\"".$topdir."images/edts/".$user->id.".edt.jpg\">",center);
			$cts->add($cts_2,true,false,"hisedt",false,true,false);
		}
		else
		{
			$cts_2 = new contents("Son emploi du temps");
			$cts_2->add_paragraph("<img src=\"".$topdir."images/edts/".$user->id.".edt.jpg\">",center);
			$cts->add($cts_2,true,false,"hisedt",false,true,true);
		}
	}
	$site->add_contents($cts);
	$site->end_page();
}
else
{
	header("Location: ".$topdir."404.php");	
	exit();
}


/*$lines = array(array ("hr_deb_seance" => "14h00",
		      "hr_fin_seance" => "16h00",
		      "jour_seance" => "Vendredi",
		      "semaine_seance" => "T",
		      "type_seance" => "Cours",
		      "nom_uv" => "AG41",
		      "salle_seance" => "E107"),
	       array ("hr_deb_seance" => "10h15",
		      "hr_fin_seance" => "12h15",
		      "jour_seance" => "Jeudi",
		      "semaine_seance" => "T",
		      "type_seance" => "TD",
		      "grp_seance" => "3",
		      "nom_uv" => "AG41",
		      "salle_seance" => "A205"),
	       array ("hr_deb_seance" => "16h15",
		      "hr_fin_seance" => "18h15",
		      "jour_seance" => "Lundi",
		      "type_seance" => "TP",
		      "semaine_seance" => "A",
		      "grp_seance" => "2",
		      "nom_uv" => "AG41",
		      "salle_seance" => "B403"),
	       array ("hr_deb_seance" => "10h15",
		      "hr_fin_seance" => "12h15",
		      "semaine_seance" => "T",
		      "jour_seance" => "Lundi",
		      "type_seance" => "Cours",
		      "nom_uv" => "IN41",
		      "salle_seance" => "A203"),
	       array ("hr_deb_seance" => "16h15",
		      "hr_fin_seance" => "18h15",
		      "jour_seance" => "Mercredi",
		      "semaine_seance" => "T",
		      "type_seance" => "TD",
		      "grp_seance" => "1",
		      "nom_uv" => "IN41",
		      "salle_seance" => "A310"),
	       array ("hr_deb_seance" => "16h15",
		      "hr_fin_seance" => "18h15",
		      "jour_seance" => "Lundi",
		      "type_seance" => "TP",
		      "semaine_seance" => "B",
		      "grp_seance" => "1",
		      "nom_uv" => "IN41",
		      "salle_seance" => "B404"),
	       array ("hr_deb_seance" => "08h00",
		      "hr_fin_seance" => "10h00",
		      "jour_seance" => "Mardi",
		      "semaine_seance" => "T",
		      "type_seance" => "Cours",
		      "nom_uv" => "RE41",
		      "salle_seance" => "A200"),
	       array ("hr_deb_seance" => "14h00",
		      "hr_fin_seance" => "16h00",
		      "jour_seance" => "Mercredi",
		      "type_seance" => "TD",
		      "semaine_seance" => "T",
		      "grp_seance" => "3",
		      "nom_uv" => "RE41",
		      "salle_seance" => "A314"),
	       array ("hr_deb_seance" => "16h15",
		      "hr_fin_seance" => "19h15",
		      "jour_seance" => "Vendredi",
		      "type_seance" => "TP",
		      "semaine_seance" => "A",
		      "grp_seance" => "2",
		      "nom_uv" => "RE41",
		      "salle_seance" => "B405"),
	       array ("hr_deb_seance" => "14h00",
		      "hr_fin_seance" => "16h00",
		      "jour_seance" => "Lundi",
		      "type_seance" => "Cours",
		      "nom_uv" => "MT41",
		      "semaine_seance" => "T",
		      "salle_seance" => "A203"),
	       array ("hr_deb_seance" => "10h15",
		      "hr_fin_seance" => "12h15",
		      "jour_seance" => "Vendredi",
		      "type_seance" => "TD",
		      "semaine_seance" => "T",
		      "grp_seance" => "2",
		      "nom_uv" => "MT41",
		      "salle_seance" => "A210"),
	       array ("hr_deb_seance" => "08h00",
		      "hr_fin_seance" => "10h00",
		      "jour_seance" => "Lundi",
		      "type_seance" => "TD",
		      "semaine_seance" => "T",
		      "grp_seance" => "2",
		      "nom_uv" => "LS02",
		      "salle_seance" => "A312"),
	       array ("hr_deb_seance" => "13h00",
		      "hr_fin_seance" => "14h00",
		      "jour_seance" => "Mercredi",
		      "type_seance" => "TP",
		      "semaine_seance" => "T",
		      "grp_seance" => "3",
		      "nom_uv" => "LS02",
		      "salle_seance" => "A305"),
	       array ("hr_deb_seance" => "13h00",
		      "semaine_seance" => "T",
		      "hr_fin_seance" => "14h00",
		      "jour_seance" => "Mardi",
		      "type_seance" => "Cours",
		      "nom_uv" => "MG02",
		      "salle_seance" => "A306"),
	       array ("hr_deb_seance" => "14h00",
		      "hr_fin_seance" => "16h00",
		      "semaine_seance" => "T",
		      "jour_seance" => "Mardi",
		      "type_seance" => "TD",
		      "grp_seance" => "1",
		      "nom_uv" => "MG02",
		      "salle_seance" => "A306"));


$edt_pedrov = new edt ("Pierre Mauduit",
		       $lines,
		       "/var/www/ae/www/taiste/images/logo_utbm_edt.png");

$edt_pedrov->generate ();*/

?>
