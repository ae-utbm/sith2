<?php
/** @file
 *
 * @brief Page d'informations diverses sur les UVs.
 *
 */

/* Copyright 2007
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

include($topdir. "include/site.inc.php");
require_once($topdir . "include/entities/uv.inc.php");


$site = new site();

$site->start_page("services", "Informations UV");

$depts = array('Humas', 'TC', 'GESC', 'GI', 'IMAP', 'GMC', 'EDIM');


/* modification d'uv */

if (($site->user->is_in_group('gestion_ae')) 
    && (isset($_REQUEST['edituvsubmit'])))
{
  $uv = new uv($site->db, $site->dbrw);
  $uv->load_by_id($_REQUEST['iduv']);

  $departements = array();

  if ($_REQUEST['Humas'] == 1)
    $departements[] = 'Humas';
  if ($_REQUEST['TC'] == 1)
    $departements[] = 'TC';
  if ($_REQUEST['GESC'] == 1)
    $departements[] = 'GESC';
  if ($_REQUEST['GI'] == 1)
    $departements[] = 'GI';
  if ($_REQUEST['IMAP'] == 1)
    $departements[] = 'IMAP';
  if ($_REQUEST['GMC'] == 1)
    $departements[] = 'GMC';
  if ($_REQUEST['EDIM'] == 1)
    $departements[] = 'EDIM';


  $uv->modify($_REQUEST['name'],
	      $_REQUEST['intitule'],
	      $_REQUEST['cours'],
	      $_REQUEST['td'],
	      $_REQUEST['tp'],
	      $_REQUEST['ects'],
	      $departements);
  
  $site->add_contents($cts);  
}

if (isset($_REQUEST['id_uv']) || (isset($_REQUEST['code_uv'])))
{
  $uv = new uv($site->db);

  if (isset($_REQUEST['id_uv']))
    {
      $uv->load_by_id($_REQUEST['id_uv']);
    }
  else
    {
      $uv->load_by_code($_REQUEST['code_uv']);
    }

  $cts = new contents('');

  /* Code + intitulé + crédits ECTS */
  $cts->add_title(1, $uv->code);
  $cts->add_paragraph("<center><i>\"".$uv->intitule."\"</i></center>");
  $cts->add_paragraph("Cette UV équivaut à <b>".$uv->ects."</b> crédits ECTS");

  /* format horaire */
  $cts->add_title(2, "Formats horaires");
  
  $parag = "<ul>";

  if ($uv->cours == 1)
    {
      $parag .= "<li>Cours</li>\n";
    }
  if ($uv->td == 1)
    {
      $parag .= "<li>TD</li>\n";
    }
  if ($uv->tp == 1)
    {
      $parag .= "<li>TP</li>\n";
    }
  $tpuv = false;

  $parag .= "</ul>\n";

  if (($uv->cours == 0) 
      && ($uv->td == 0) 
      && ($uv->tp == 0)) 
    $parag = "<b>UV Hors Emploi du Temps (HET)</b>";

  
  $cts->add_paragraph($parag);
  
  /* départements concernés */
  $cts->add_title(2, "Départements dans lequel l'UV est enseignée");

  for ($i = 0 ; $i < count($uv->depts); $i++)
    {

      $myuvdpts[] = "<a href=\"./uvs.php?iddept=".
	$uv->depts[$i]."\">".$uv->depts[$i]."</a>\n";
      $uvdept[] = $uv->depts[$i];
    }

  $lst = new itemlist("Départements",
		      false,
		      $myuvdpts);
  $cts->add($lst);

  /* listing des personnes ayant suivi l'UV */
  
  /* a migrer dans uv.inc.php ? */
  $suivrq = new requete ($site->db,
			 "SELECT 
                                  `id_utilisateur` 
                                  , `prenom_utl`
                                  , `nom_utl`
                                  , `surnom_utbm`
                                  , `semestre_grp`
                          FROM 
                                  `edu_uv_groupe_etudiant`
                          INNER JOIN
                                  `edu_uv_groupe`
                          USING (`id_uv_groupe`)
                          INNER JOIN
                                   `edu_uv`
                          USING(`id_uv`)
                          INNER JOIN
                                    `utilisateurs`
                          USING(`id_utilisateur`)
                          INNER JOIN
                                    `utl_etu_utbm`
                          USING (`id_utilisateur`)
                          WHERE 
                                `id_uv` = ".$uv->id."
                          GROUP BY `code_uv`, `id_utilisateur`");

  if ($suivrq->lines > 0)
    {
      require_once($topdir . "include/cts/sqltable.inc.php");
      $sqlt = new sqltable('userslst', 
			   "Liste des utilisateurs suivant ou ayant suivi l'UV",
			   $suivrq,
			   '../user.php',
			   'id_utilisateur', 
			   array('prenom_utl' => 'prenom', 'nom_utl' => 'nom', 'surnom_utbm' => 'surnom', 'semestre_grp' => 'semestre'),
			   array('view' => 'Voir la fiche'), 
			   array(), 
			   array());
      $cts->add_title(2, "Ils suivent ou ont suivi cette UV");
      $cts->add($sqlt);
    }


  /* commentaires sur les uvs ? */
  /* TODO ! */

  /* édition */
  if ($site->user->is_in_group("gestion_ae"))
    {
      $cts->add_title(2, "Modification d'UV");

      $edituv = new form("edituv", 
			 "uvs.php?id_uv=".$uv->id,
			 true,
			 "post",
			 "Modification del'UV");

      $edituv->add_hidden('iduv', $uv->id);
      $edituv->add_text_field('name',
			      "Code de l'UV <b>sans espace, ex: 'MT42'</b>",
			      $uv->code, true, 4);
  
      $edituv->add_text_area('intitule',
			     "Intitulé de l'UV",
			     $uv->intitule);
  
      $edituv->add_checkbox('cours',
			    "Cours",
			    $uv->cours == 1);
  
      $edituv->add_checkbox('td',
			    "TD",
			    $uv->td == 1);
  
      $edituv->add_checkbox('tp',
			    "TP",
			    $uv->tp == 1);
  
      $edituv->add_text_field('ects',
			      "Credits ECTS",
			      $uv->ects, false, 1);
  
      $edituv->add_checkbox('Humas',
			    "Humanités",
			    in_array('Humas', $uvdept));
  
      $edituv->add_checkbox('TC',
			    "TC",
			    in_array('TC', $uvdept));

      $edituv->add_checkbox('GESC',
			    "GESC",
			    in_array('GESC', $uvdept));

      $edituv->add_checkbox('GI',
			    "GI",
			    in_array('GI', $uvdept));

      $edituv->add_checkbox('IMAP',
			    "IMAP",
			    in_array('IMAP', $uvdept));

      $edituv->add_checkbox('GMC',
			    "GMC",
			    in_array('GMC', $uvdept));

      $edituv->add_checkbox('EDIM',
			    "EDIM",
			    in_array('EDIM', $uvdept));

      $edituv->add_submit('edituvsubmit',
			  "Modifier");
  
      $cts->add($edituv);
    }

  /* Ressources externes */
  $cts->add_title(2, "Ailleurs sur le net ...");

  foreach ($uvdept as $departement)
    {
      if ($departement == 'Humas')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=207\"><b>Site de l'UTBM</b>, information sur le département des Humanités</a>";
      if ($departement == 'TC')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=205\"><b>Site de l'UTBM</b>, information sur le département de Tronc Commun</a>";
      if ($departement == 'GESC')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=70\"><b>Site de l'UTBM</b>, information sur le département du Génie Electrique et ".
	  "Systèmes de Commande (GESC)</a>";
      if ($departement == 'GI')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=67\"><b>Site de l'UTBM</b>, information sur le département du Génie Informatique (GI)</a>";
      if ($departement == 'IMAP')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=69\"><b>Site de l'UTBM</b>, information sur le département de l'Ingénierie et".
	  " management de process (IMAP)</a>";
      if ($departement == 'GMC')
	$exts[] = "<a href=\"http://www.utbm.fr/index.php?pge=68\"><b>Site de l'UTBM</b>, information sur le département du Génie Mécanique ".
	  "et conception (GMC)</a>";
    }
  $exts[] = "<a href=\"http://bankexam.fr/etablissement/1-Universite-de-Technologie-de-Belfort-Montbeliard\">".
    "<b>Bankexam.fr</b>, base de données d'examens</a>";
  $exts[] = "<a href=\"https://webct6.utbm.fr/\"><b>WebCT</b>, la plateforme pédagogique de l'UTBM</a>";
  

  $itmlst = new itemlist("Ressources externes",
			 false,
			 $exts);

  $cts->add($itmlst);

  $site->add_contents($cts);
  $site->end_page();

  exit();
}



if (isset($_REQUEST['iddept']))
{
  if (in_array($_REQUEST['iddept'], $depts))
    {
      $cts = new contents ("UVs - Département " . $_REQUEST['iddept']);
      
      $dept = mysql_real_escape_string($_REQUEST['iddept']);

      $req = new requete($site->db,
			 "SELECT 
                             `edu_uv`.`id_uv`
                             , `edu_uv`.`code_uv`
                             , `edu_uv`.`intitule_uv`
                          FROM
                             `edu_uv`
                          LEFT JOIN
                             `edu_uv_dept`
                          USING (`id_uv`)
                          WHERE
                             `id_dept` = '".$dept."'
                          ORDER BY
                             `edu_uv`.`code_uv`");

      $uvs = array();
      while ($rs = $req->get_row())
	{
	  $uvs[] = "<a href=\"./uvs.php?id_uv=".$rs['id_uv']."\">". 
	    $rs['code_uv'] . " - " . $rs['intitule_uv'] . "</a>";
	}

      $lst = new itemlist($dept,
			  false,
			  $uvs);
      $cts->add($lst);

      $site->add_contents($cts);
      
      $site->end_page();
      exit();
    }
}

$cts = new contents("Guide - Informations sur les UVs");


foreach ($depts as $dept)
{
  $req = new requete($site->db,
		     "SELECT 
                             `edu_uv`.`id_uv`
                             , `edu_uv`.`code_uv`
                             , `edu_uv`.`intitule_uv`
                      FROM
                             `edu_uv`
                      LEFT JOIN
                             `edu_uv_dept`
                      USING (`id_uv`)
                      WHERE
                             `id_dept` = '".$dept."'
                      ORDER BY
                             `edu_uv`.`code_uv`");

  $uvs = array();
  while ($rs = $req->get_row())
    {
      $uvs[] = "<a href=\"./uvs.php?id_uv=".$rs['id_uv']."\">". 
	$rs['code_uv'] . " - " . $rs['intitule_uv'] . "</a>";
    }

  $lst = new itemlist($dept,
		      false,
		      $uvs);
  $cts->add_title(2,"<a href=\"./uvs.php?iddept=$dept\">$dept</a>");
  $cts->add($lst);
}

$site->add_contents($cts);


$site->end_page();


?>