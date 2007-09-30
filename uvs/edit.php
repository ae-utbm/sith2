<?php
/** @file
 *
 * @brief Page d'édition des emplois du temps.
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

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$site = new site();

$site->start_page("services", "Emploi du temps");

/* protection d'usage */
if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}


$cts = new contents("Edition d'emploi du temps");

$edt = new edt($site->db, $site->dbrw);

if ($semestre == '')
{
  $semestre = (date("m") > 6 ? "A" : "P") . date("y");
}  
else
{
  $semestre = mysql_real_escape_string($_REQUEST['semestre']);
}

/* passage en revue des actions */
/* desinscription */
if ($_REQUEST['action'] == 'unsubscribe')
{
  $ret = $edt->unsubscr_etu_from_grp($site->user->id,
				     $_REQUEST['idseance']);

  $newcts = new contents("Désinscription");

  if ($ret)
    $newcts->add_paragraph("Desinscription de la séance effectuée avec succès.");
  else
    $newcts->add_paragraph("Une erreur est survenue lors de la ".
			"désinscription de la séance.");

  $site->add_contents($newcts);
}

/* confirmation ajout */
else if ($_REQUEST['action'] == 'commitadd')
{
  $newcts = new contents("DEBUG", "<pre>" . print_r($_POST, true) . "</pre>");
  $site->add_contents($newcts);
}

/* ajout */
else if ($_REQUEST['action'] == 'addseance')
{
  $uv = intval($_REQUEST['iduv']);

  $req = new requete($site->db, "SELECT `code_uv`, `cours_uv`, `td_uv`, `tp_uv`, `id_uv` 
  FROM   `edu_uv` 
  WHERE `id_uv` = $uv");
  
  $rs = $req->get_row();
  $nomuv = $rs['code_uv'];
  $c     = $rs['cours_uv'];
  $td    = $rs['td_uv'];
  $tp    = $rs['tp_uv'];
  $iduv  = $rs['id_uv'];

  $newcts = new contents($nomuv ." - Ajout d'une séance horaire");
  
  $frm = new form('frm', 'edit.php?action=commitadd');
  

  if (($c==0) && ($td == 0) && ($tp == 0))
    $frm->puts("<b>UV hors emploi du temps. En conséquence, elle n'apparaitra pas sur l'Emploi du temps.</b>");

  
  $req = new requete($site->db, 
		     "SELECT  `id_uv_groupe`
                            , `numero_grp`
                            , `jour_grp`
                            , `type_grp`
                            , `heure_debut_grp`
                            , `heure_fin_grp`
                      FROM 
                            `edu_uv_groupe`
                      WHERE 
                            `id_uv` = $uv 
                      AND 
                            `semestre_grp` = '".$semestre."'");
  
  if ($req->lines <= 0)
    $frm->puts("<p>Aucune séance connue pour cette UV. Vous êtes donc amené à ".
	       "en renseigner les caractéristiques via le formulaire ci-dessous :<br/></p>");
  else
    {
      $seances = array(-1 => "--");
      while ($rs = $req->get_row())
	$seances[$rs['id_uv_groupe']] = 'Seance de '.
	  ($rs['type_grp'] == 'C' ? 'cours' : $rs['type_grp']) .
	  ' N°'.$rs['numero_grp']. " du ". $jour[$rs['jour_grp']] . 
	  " de ".$rs['heure_debut_grp']." à ".$rs['heure_fin_grp'];
      
      $frm->puts("<h3>Séances connues :</h3>");
      
      $frm->add_select_field("addfrm_scid", 
			     'Séances connues', 
			     $seances,
			     false,
			     "", false, true);
    }
  
  $frm->puts("<h3>Création d'une séance horaire inexistante</h3>");
  
  /* type de séance */
  $frm->add_select_field("addfrm_typeseance",
			 'Type de séance',
			 array("Cours", "TD", "TP"));
  /* numéro groupe */
  $frm->add_text_field("addfrm_numgrp",
		       'Numéro de groupe',
		       '1', false, 1);
  /* jour */
  global $jour;
  $frm->add_select_field("addfrm_jour",
			     'jour',
			     $jour);
  
  /* horaires debut / fin */
  /* horaires */
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

  $frm->add_select_field("addfrm_hdeb",
			 'Heure de début', $hours);
  
  $frm->add_select_field("addfrm_mdeb",
			 'Minutes de début', $minut);
  

  $frm->add_select_field("addfrm_hfin",
			 'Heure de fin', $hours);
  
  $frm->add_select_field("addfrm_mfin",
			 'Minutes de fin', $minut);
  
  $frm->add_select_field("addfrm_freq",
			 'Fréquence',
			 array("0" => "--",
			       "1" => "Hebdomadaire",
			       "2" => "Bimensuelle"),
			 false,
			 "",
			 false,
			 true);
  
  $frm->add_select_field("addfrm_grpfreq",
			 'Semaine',
			 array("AB" => "Toutes les semaines",
			       "A" => "Semaine A",
			       "B" => "Semaine B"));
  
  $frm->add_text_field("addfrm_salle",
		       'salle <b>sans espace, ex : "P103")</b>',
		       "", false, 4);


  $frm->add_submit("addfrm_submit", "Ajouter");

  $newcts->add($frm);
  $site->add_contents($newcts);
}

/* modification */
else if ($_REQUEST['action'] == 'modify')
{
  $newcts = new contents("Modification d'une séance horaire");
  $site->add_contents($newcts);

}


$edt->load($site->user->id, $semestre);



/* on ré-agence par code d'UV */
for ($i = 0; $i < count($edt->edt_arr); $i++)
{
  $curr = &$edt->edt_arr[$i];
  $uvs[$curr['nom_uv']][] = $curr;
}

if (count($uvs) < 1)
{
  $cts->add_paragraph("Vous n'avez pas renseigné d'emploi du ".
		      "temps ce semestre.");
}

else 
{
  foreach($uvs as $code => $values)
    {
      $cts->add_title(2, $code);
      $cts->add_paragraph("Vous êtes inscrit aux séances ".
			  "suivantes pour cette UV :");

      $lst = array();
      
      foreach ($values as $seance)
	{
	  $descr = "Séance de <b>".$seance['type_seance'] . "</b>".
	    " n°".$seance['grp_seance']." le <b>".
	    $seance['jour_seance'] . "</b> de <b>" .
	    $seance['hr_deb_seance'] . "</b> à <b>" . $seance['hr_fin_seance'].
	    "</b> en salle <b>" . $seance['salle_seance']."</b>";
	  if ($seance['semaine_seance'] == 'AB')
	    $descr .= " - Fréquence 1 (hebdomadaire)";
	  else
	    $descr .= " - Fréquence 2, Semaine ".$seance['semaine_seance'] .
	      " (bimensuelle)";


	  /* ajout d'une séance horaire */
	  $links = "<a href=\"".$topdir.
	    "uvs/edit.php?action=addseance&iduv=".
	    $seance['id_uv']."&semestre=".$semestre.
	    "\">Ajout d'une séance horaire</a><br/>";
	  /* desinscription */
	  $links .= "<a href=\"".$topdir.
	    "uvs/edit.php?action=unsubscribe&idseance=".
	    $seance['id_seance']."&semestre=".$semestre.
	    "\">Désinscription de la séance</a><br/>";
	  /* modification de la séance */
	  $links .= "<a href=\"".$topdir.
	    "uvs/edit.php?action=modify&idseance=".
	    $seance['id_seance']."&semestre=".$semestre.
	    "\">Modification de la séance</a><br/>";
	


	  $lst[] = $descr . "<br/>" . $links;
	} // fin passage en revue des séances
      
      $cts->add(new itemlist(false, false, $lst));
    } // fin boucle uvs

}


//$cts->add_paragraph("<pre>" . print_r($uvs, true) . "</pre>");


$site->add_contents($cts);

$site->end_page();

?>

