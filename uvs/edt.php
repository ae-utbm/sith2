<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");


$site = new site();


if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}



$cts = new contents("Emploi du temps",
		    "Sur cette page, vous allez pouvoir ".
		    "créer votre emploi du temps.");

$adduv = new form("adduv", "edt.php?adduv=1", true, "post", "Ajout d'une UV");

$adduv->add_info("Ce formulaire vous permet d'ajouter une UV, au cas où ".
		 "celle-ci ne serait pas déjà enregistrée en base.");

$adduv->add_text_field('adduv_name',
		       "Code de l'UV",
		       "", true);

$adduv->add_text_area('adduv_intitule',
		      "Intitulé de l'UV",
		      "");

$adduv->add_submit('adduv_sbmt',
		   "Ajouter");

$cts->add($adduv);

$addseance = new form("addseance", 
		      "edt.php?addseance=1",
		      true,
		      "post", 
		      "Ajout d'une séance");

$q = new requete($site->db, "SELECT 
                                     id_uv
                                     , code_uv
                             FROM
                                     edu_uv");
if ($q->lines <= 0)
     $addseance->add_info("Pas d'UV enregistrée en base, ".
			  "veuillez en rajouter dans le formulaire ci-dessus");
else
{
  while ($rs = $q->get_row())
    $uv[$rs['id_uv']] = $rs['code_uv'];
  
  $addseance->add_select_field('adds_uv',
			       'UV',
			       $uv);
  $addseance->add_select_field('adds_types',
			       'Type de séance',
			       array("C" => "Cours",
				     "TD" => "TD",
				     "TP" => "TP"));
  $addseance->add_text_field('adds_numgp',
			     'Numéro de groupe',
			     '', false, 1);
  
  /* défini dans entities/edt.inc.php */
  global $jour;
  $addseance->add_select_field('adds_j',
				'jour',
				$jour);

  $addseance->add_time_field('adds_hdeb',
			      'Heure de début',
			      -1, true);

  $addseance->add_time_field('adds_hfin',
			      'Heure de fin',
			      -1, true);

  $addseance->add_select_field('adds_freq',
				'Fréquence',
				array("1" => "Hebdomadaire",
				      "2" => "Bimensuelle"));

  $addseance->add_hidden('adds_semestre',
			  (date('M') > 06 ? "A" : "P"). date("y"));

  $addseance->add_submit("adds_sbmt",
			  "Ajouter la séance");
  
}


$cts->add($addseance);


$site->add_contents($cts);


$site->end_page();

?>
