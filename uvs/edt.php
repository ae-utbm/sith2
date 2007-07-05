<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");


$site = new site();

$site->start_page("services", "Emploi du temps");

if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}

$edt = new edt($site->db, $site->dbrw);

if ($_REQUEST['adduv'] == 1)
{
 
  $ret = $edt->create_uv($_REQUEST['adduv_name'], $_REQUEST['adduv_intitule']);
  
  if ($ret)
    $site->add_contents(new contents("Ajout d'UV", "l'UV ".
				     $_REQUEST['adduv_name'].
				     " a été ajoutée avec succès"));
}


else if ($_REQUEST['addseance'] == 1)
{
  $site->add_contents(new contents("Ajout d'une séance", print_r($_POST, true)));
}

$cts = new contents("Emploi du temps",
		    "Sur cette page, vous allez pouvoir ".
		    "créer votre emploi du temps.");

$adduv = new form("adduv", "edt.php?adduv=1", true, "post", "Ajout d'une UV");

$cts->add_title(2, "Ajout d'une UV");

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

$cts->add_title(2, "Ajout d'une séance");

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

  for ($i = 0; $i < 24; $i++)
    $hours[$i] = sprintf("%02d", $i); 
  
  for ($i = 0; $i < 60; $i++)
    $minut[$i] = sprintf("%02d", $i);

  $addseance->add_infos("Heure de début");
  
  
  $addseance->add_select_field('adds_hdeb',
			       '', $hours);
  
  $addseance->add_select_field('adds_mdeb',
			       '', $minutes);

  $addseance->add_infos("Heure de début");

  $addseance->add_select_field('adds_hfin',
			       '', $hours);
  
  $addseance->add_select_field('adds_mfin',
			       '', $minutes);


  

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
