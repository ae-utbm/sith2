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

$depts = array('Humas', 'GESC', 'GI', 'IMAP', 'GMC');

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

                      FROM
                             `edu_uv`
                      WHERE
                             `edu_uv`.`id_uv` = $iduv");

  $cts = new contents('');

  $rs = $req->get_row();
  
  /* Code + intitulé */
  $cts->add_title(2, $rs['code_uv']);
  $cts->add_paragraph("<b>".$rs['intitule_uv']."</b>");

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
    }
  $lst = new itemlist("Départements",
		      false,
		      $myuvdpts);
  $cts->add($lst);


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
                             `id_dept` = '".$dept."'");

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
                             `id_dept` = '".$dept."'");

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