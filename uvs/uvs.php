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


$site = new site();

$site->start_page("services", "Informations UV");

$depts = array('Humas', 'TC', 'GESC', 'GI', 'IMAP', 'GMC');

if (isset($_REQUEST['id_uv']) || (isset($_REQUEST['code_uv'])))
{
  if (isset($_REQUEST['id_uv']))
    {
      $iduv = intval($_REQUEST['id_uv']);
  
      $req = new requete($site->db,
			 "SELECT 
                             `edu_uv`.`code_uv`
                             , `edu_uv`.`intitule_uv`
                             , `edu_uv`.`cours_uv`
                             , `edu_uv`.`td_uv`
                             , `edu_uv`.`tp_uv`
                             , `edu_uv`.`ects_uv`

                      FROM
                             `edu_uv`
                      WHERE
                             `edu_uv`.`id_uv` = $iduv
                      ORDER BY
                             `edu_uv`.`code_uv`");
    }
  else
    {
      $codeuv = mysql_real_escape_string($_REQUEST['code_uv']);
  
      $req = new requete($site->db,
			 "SELECT 
                             `edu_uv`.`code_uv`
                             , `edu_uv`.`id_uv`
                             , `edu_uv`.`intitule_uv`
                             , `edu_uv`.`cours_uv`
                             , `edu_uv`.`td_uv`
                             , `edu_uv`.`tp_uv`
                             , `edu_uv`.`ects_uv`

                      FROM
                             `edu_uv`
                      WHERE
                             `edu_uv`.`code_uv` = '".$codeuv."'
                      ORDER BY
                             `edu_uv`.`code_uv`");
    }

  $cts = new contents('');

  $rs = $req->get_row();

  if (isset($_REQUEST['code_uv']))
    $iduv = $rs['id_uv'];

  /* Code + intitulé + crédits ECTS */
  $cts->add_title(1, $rs['code_uv']);
  $cts->add_paragraph("<center><i>\"".$rs['intitule_uv']."\"</i></center>");
  $cts->add_paragraph("Cette UV équivaut à <b>".$rs['ects_uv']."</b> crédits ECTS");

  /* format horaire */
  $cts->add_title(2, "Formats horaires");
  
  $parag = "<ul>";

  if ($rs['cours_uv'] == 1)
    $parag .= "<li>Cours</li>\n";
  if ($rs['td_uv'] == 1)
    $parag .= "<li>TD</li>\n";
  if ($rs['tp_uv'] == 1)
    $parag .= "<li>TP</li>\n";
  $parag .= "</ul>\n";

  if (($rs['cours_uv']== 0) && ($rs['cours_uv']== 0) && ($rs['cours_uv']== 0)) 
    $parag = "<b>UV Hors Emploi du Temps (HET)</b>";

  
  $cts->add_paragraph($parag);
  
  /* départements concernés */
  $cts->add_title(2, "Départements dans lequel l'UV est enseignée");

  $req = new requete($site->db,
		     "SELECT 
                             `id_dept`
                      FROM
                             `edu_uv_dept`
                      WHERE
                              `id_uv` = $iduv");


  while ($rs = $req->get_row())
    {
      $myuvdpts[] = "<a href=\"./uvs.php?iddept=".$rs['id_dept']."\">".$rs['id_dept']."</a>\n";
      $uvdept[] = $rs['id_dept'];
    }
  $lst = new itemlist("Départements",
		      false,
		      $myuvdpts);
  $cts->add($lst);

  /* commentaires sur les uvs ? */

  /* TODO : prévoir une table, l'ajout de commentaires, la modération
   * éventuellement un lien vers le forum */


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