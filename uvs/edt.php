<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


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


if ($_REQUEST['render'] == 1)
{
  $edt->load($site->user->id);

  $edtimg = new edt_img($site->user->alias,  $edt->edt_arr);
  $edtimg->generate ();
  exit();

}

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
  $ret = $edt->create_grp ($_REQUEST['adds_uv'],
			   $_REQUEST['adds_types'],
			   $_REQUEST['adds_numgp'],
			   $_REQUEST['adds_hdeb'] . ":" . $_REQUEST['adds_mdeb'] . ":00",
			   $_REQUEST['adds_hfin'] . ":" . $_REQUEST['adds_mfin'] . ":00",
			   $_REQUEST['adds_j'],
			   $_REQUEST['adds_freq'],
			   $_REQUEST['adds_semestre'],
			   $_REQUEST['adds_numsalle']);


  if ($ret > 0)
    {
      $aret = $edt->assign_etu_to_grp($site->user->id,
				      $ret,
				      $_REQUEST['adds_semgrp']);

      if ($aret)
	$site->add_contents(new contents("Ajout d'une séance", "Vous avez été ajouté à ".
					 "la séance donnée avec succès."));
      else
	$site->add_contents(new contents("Ajout d'une séance", "Erreur à l'ajout / la tentative d'inscription " .
					 "à la séance donnée. Peut-être y êtes-vous déjà inscrit ?"));

    } 
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
    {
      $tmp = sprintf("%02d", $i);
      $hours[$tmp] = $tmp; 
    }

  for ($i = 0; $i < 60; $i++)
    {
      $tmp = sprintf("%02d", $i);
      $minut[$tmp] = $tmp;
    }
    
  $addseance->add_select_field('adds_hdeb',
			       'Heure de début', $hours);
  
  $addseance->add_select_field('adds_mdeb',
			       '', $minut);


  $addseance->add_select_field('adds_hfin',
			       'Heure de fin', $hours);
  
  $addseance->add_select_field('adds_mfin',
			       '', $minut);


  

  $addseance->add_select_field('adds_freq',
				'Fréquence',
				array("1" => "Hebdomadaire",
				      "2" => "Bimensuelle"));

  $addseance->add_select_field('adds_semgrp',
				'Semaine',
				array("AB" => "Toutes les semaines",
				      "A" => "Semaine A",
				      "B" => "Semaine B"));

  $addseance->add_hidden('adds_semestre',
			  (intval(date('m')) > 6 ? "A" : "P"). date("y"));

  $addseance->add_text_field('adds_numsalle',
			     'Numéro de salle',
			     '', false);

  $addseance->add_submit("adds_sbmt",
			  "Ajouter la séance");
  
}


$cts->add($addseance);
/*
$cts->add_title(2, "Inscriptions aux séances pour le semestre en cours");

$semestre = (date("m") > 6 ? "A" : "P") . date("y");

$sqltr = new requete($this->db,
		     "SELECT 
                              `edu_uv_groupe`.`id_uv_groupe`
                            , `edu_uv_groupe`.`type_grp`
                            , `edu_uv_groupe`.`heure_debut_grp`
                            , `edu_uv_groupe`.`heure_fin_grp`
                            , `edu_uv_groupe`.`jour_grp`
                            , `edu_uv_groupe`.`numero_grp`
                            , `edu_uv_groupe`.`frequence_grp`
                            , `edu_uv_groupe`.`salle_grp`
                            , `edu_uv_groupe_etudiant`.`semaine_etu_grp`
                            , `edu_uv`.`code_uv`
                       FROM
                              `edu_uv_groupe_etudiant`
                       INNER JOIN
                              `edu_uv_groupe`
                              USING (`id_uv_groupe`)
                       INNER JOIN
                              `edu_uv`
                              USING (`id_uv`)
                       WHERE
                             `edu_uv_groupe`.`semestre_grp` = '".$semestre."'
                       AND
                             `edu_uv_groupe_etudiant`.`id_utilisateur` = ".$site->user->id);

$sqlt = new sqltable('sqlt_cours',
		     'inscriptions dans :',
		     $sqltr,
		     "edt.php?manage=1&",
		     "id_uv_groupe",
		     array('code_uv' => "Nom de l'UV", 
			   'type_grp' => "Type de groupe", 
			   'heure_debut_grp' => "Heure de début",
			   'heure_fin_grp'   => "Heure de fin",
			   'jour_grp' => "jour", 
			   'numero_grp' => "Numéro du groupe", 
			   'frequence_grp' => "Fréquence", 
			   'salle_grp' => "Salle", 
			   'semaine_etu_grp' => "Indice de semaine"),
*/		     
$cts->add_title(2, "Rendu graphique de l'emploi du temps");

$cts->add_paragraph("<center><img src=\"./edt.php?render=1\" alt=\"Emploi du temps graphique\" /></center>");

$site->add_contents($cts);


$site->end_page();

?>
