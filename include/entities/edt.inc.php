<?php
/** @file edt.inc.php : Gestion du système des emplois du temps.
 *
 *
 */
/* Copyright 2005,2006
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
 * 02111-1307, USA.
 */

require_once ($topdir . "include/entities/basedb.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$jour = array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", 
	      "Dimanche");


class edt extends stdentity
{

  var $edt_arr;

  function load ($id, $semestre = null)
  {

    $this->id_utilisateur = intval($id);
    
    $this->edt_arr = null;

    /** semestre courant par défaut */
    if ($semestre == null)
      $semestre = (date("m") >= 9 ? "A" : "P") . date("Y");
    else
      $semestre = mysql_real_escape_string($semestre);


    $req= new requete($this->db,
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
                             `edu_uv_groupe_etudiant`.`id_utilisateur` = $id");
    if ($req->lines <= 0)
      return;

    global $jour;

    while ($row = $req->get_row())
      {

	if ($row['semaine_etu_grp'] == 'T')
	  $semaine_seance = 'AB';
	else
	  $semaine_seance = $row['semaine_etu_grp'];

	$hrdeb = substr($row['heure_debut_grp'], 0, 2) . "h" . substr($row['heure_debut_grp'], 2,2);
	$hrfin = substr($row['heure_fin_grp'], 0, 2) . "h" . substr($row['heure_fin_grp'], 2,2);
	
	$jsem = $jour[$row['jour_grp']];

	$type = ($row['type_grp'] == 'C' ? 'Cours' : $type['grp']);
	$grp  = $row['numero_grp'];
	$nomuv = $row['code_uv'];
	$salle = $row['salle_grp'];

	$this->edt_arr[] = array("semaine_seance" => $semaine_seance,
				 "hr_deb_seance"  => $hrdeb,
				 "hr_fin_seance"  => $hrfin,
				 "jour_seance"    => $jsem,
				 "type_seance"    => $type,
				 "grp_seance"     => $grp,
				 "nom_uv"         => $nomuv,
				 "salle_seance"   => $salle);
      }
    return;
  }


  function assign_etu_to_grp($id_etu, $id_group, $freq = 'AB')
  {
    if (!$this->dbrw)
      return false;


    $sql = new insert($this->dbrw,
		      "edu_uv_groupe_etudiant",
		      array("id_uv_groupe"    => $id_group,
			    "id_utilisateur"  => $id_etu,
			    "semaine_etu_grp" => $freq));

    if ($sql->lines <= 0)
      return false;
    
    return true;
  }


  function create_uv ($code_uv,
		      $intitule_uv)
  {
    
    if (!$this->dbrw)
      return false;


    $sql = new insert($this->dbrw,
		      "edu_uv",
		      array("code_uv"     => $code_uv,
			    "intitule_uv" => $intitule_uv));

    if ($sql->lines <= 0)
      return false;
    
    return true;
  }

  function create_grp ($iduv,
		       $numgrp,
		       $hdebgrp,
		       $hfingrp,
		       $jourgrp,
		       $freqgrp,
		       $semestre,
		       $sallegrp)
  {
    if (!$this->dbrw)
      return false;

    $sql = new insert($this->dbrw,
		      "edu_uv_groupe",
		      array("id_uv"           => $iduv,
			    "type_grp"        => $id_etu,
			    "numero_grp"      => $numgrp,
			    "heure_debut_grp" => $hdebgrp,
			    "heure_fin_grp"   => $hfingrp,
			    "jour_grp"        => $jourgrp,
			    "frequence_grp"   => $freqgrp,
			    "semestre_grp"    => $semestre,
			    "salle_grp"       => $sallegrp));

    if ($sql->lines <= 0)
      return false;
    
    return true;



  }
		      
		      
}


?>
