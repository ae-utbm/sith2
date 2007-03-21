<?php
/**
 * @brief Creation d'un trajet - covoiturage
 *
 */

/* Copyright 2006
 * Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir="../";

require_once($topdir . "include/site.inc.php");

$site = new site();

$site->start_page ("Covoiturage", "Creation d'un trajet");
$accueil = new contents("Covoiturage",
			"<p>Bienvenue sur la page du covoiturage, "
			."<br/><strong>AE.COM - Recherche & Dev.".
			"</strong></p>");

$site->add_contents ($accueil);

if (isset($_REQUEST['reset']))
{
  unset($_SESSION['trajet']);
}


/* formulaire envoye */
if (isset($_REQUEST['submit']))
{
  $start = "'" . mysql_real_escape_string ($_REQUEST['start']) . "'";
  $stop =  "'" . mysql_real_escape_string ($_REQUEST['stop']) . "'";
  if ($_REQUEST['etape'] != "")
    $etape = "'" . mysql_real_escape_string ($_REQUEST['etape']) . "'";
  if (is_array($_SESSION['trajet']['etapes']))
    {
      $etapes = implode(", ", $_SESSION['trajet']['etapes']);
      $etapes = "(" . $etapes . ")";
    }
  if ($etape)
    $in = "($start, $stop, $etape)";
  else
    $in = "($start, $stop)";

  if ($etapes)
    $in .= " OR `id_ville` IN $etapes";
  $req = new requete ($site->db,"SELECT `id_ville`, `nom_ville`
                                  FROM `villes`
                                 WHERE `nom_ville` IN $in");

  while ($res = $req->get_row())
    {
      $villes_db[$res['id_ville']] = $res['nom_ville'];
      if ($res['nom_ville'] == $_REQUEST['start'])
	$start = $res['id_ville'];
      if ($res['nom_ville'] == $_REQUEST['stop'])
	$stop = $res['id_ville'];
      if (($res['nom_ville'] == $_REQUEST['etape']) &&
	  ($_REQUEST['etape'] != ""))
	$etape = $res['id_ville'];
    }
  if ((!isset($_SESSION['trajet']['start']))
      && (intval($start) != 0))
    $_SESSION['trajet']['start']    = $start;

  if (($_REQUEST['etape'] != "")
      && (intval($etape) != 0))
    $_SESSION['trajet']['etapes'][] = $etape;

  if ((!isset($_SESSION['trajet']['stop']))
      && (intval($stop) != 0))
    $_SESSION['trajet']['stop']     = $stop;
}

/* formulaire */
$frm = new form ("trip",
		 "trajet.php",
		 true);
/* depart */
if (!isset($_SESSION['trajet']['start']))
  $frm->add_text_field ("start",
			"Ville de depart");

$infos = "<p><h3>Etapes actuelles</h3><br/>
          <ul>\n";
foreach ($_SESSION['trajet']['etapes'] as $id_etape)
  $infos .= ("<li>". $villes_db[$id_etape] . "</li>\n");
$infos .= "</ul>\n</p>\n";

$frm->add_info ($infos);


$frm->add_text_field ("etape",
		      "Etape");
/* arrivee */
if (!isset($_SESSION['trajet']['stop']))
$frm->add_text_field ("stop",
		      "Arrivee");

$frm->add_submit ("submit",
		  "Envoyer");
$frm->add_submit ("reset",
		  "Effacer le trajet");


/* carte */
$carte = new contents ("Trajet",
		       "<img src=\"./generate.php\" alt=\"carte\" />");



$site->add_contents ($frm);
$site->add_contents ($carte);

/* fin page */
$site->end_page ();
?>