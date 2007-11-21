<?php
/**
 * @brief Covoiturage : détail d'un trajet
 *
 */

/* Copyright 2007
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
require_once($topdir . "include/pgsqlae.inc.php");
require_once($topdir . "include/entities/ville.inc.php");
require_once($topdir . "include/entities/trajet.inc.php");


$site = new site();

$site->start_page ("services", "Covoiturage - Détails");



$accueil = new contents("Covoiturage - Détails d'un trajet",
			"");

$datetrj = $_REQUEST['date'];

$trajet = new trajet($site->db, $site->dbrw, null);

$trajet->load_by_id($_REQUEST['id_trajet']);



/* demande de suppresion d'etapes */
if ($_REQUEST['action'] == 'delete')
{
  $id = $_REQUEST['id'];
  $id = explode(',', $id);
  $id_etape     = intval($id[0]);
  $id_trajet    = intval($id[1]);
  $date_etape   = mysql_real_escape_string($id[2]); 

  $req = new requete($site->db, "SELECT
                                         `id_utilisateur` 
                                 FROM 
                                         `cv_trajet_etape` 
                                 WHERE
                                         `id_trajet`   = $id_trajet
                                 AND
                                         `id_etape`    = $id_etape
                                 AND
                                         `trajet_date` = '".$date_etape."'");

  if ($req->lines == 1)
    {
      $idusr = $req->get_row();
      $idusr = $idusr['id_utilisateur'];
      
      if ($site->user->id == $idusr)
	{
	  $req = new delete($site->dbrw, 
			    'cv_trajet_etape',
			    array('id_trajet' => $id_trajet,
				  'id_etape'  =>$id_etape,
				  'trajet_date' => $date_etape));

	  if ($req->lines == 1)
	    {
	      $accueil->add_title(2, "Suppression d'une étape");
	      $accueil->add_paragraph("<b>Etape supprimée avec succès.</b>");

	      /* options */

	      $accueil->add_title(2, "Autres options");
	      $opts[] = "<a href=\"./\">Retour à la page d'accueil du covoiturage</a>";
	      $opts[] = "<a href=\"./propose.php\">Proposer un trajet</a>";
	      $opts[] = "<a href=\"./search.php\">Rechercher un trajet</a>";
	      
	      $options = new itemlist(false, false, $opts);
	      $accueil->add($options);


	      $site->add_contents($accueil);
	      $site->end_page();
	      exit();
	    }
	}
      
    }
}


if (($trajet->id <= 0) || (! in_array($datetrj, $trajet->dates)))
{
  header("Location: ../404.php");
  exit();
}

if (isset($_REQUEST['add_step_sbmt']))
{
  $accueil->add_title(2, "Proposition d'une étape");
  
  $ret = $trajet->add_step($site->user->id,
			   $_REQUEST['comments'],
			   $_REQUEST['date'],
			   $_REQUEST['mydest']);
  if ($ret)
    {
      $steps = $trajet->get_steps_by_date($_REQUEST['date']);
      
      $accueil->add_paragraph("Etape proposée avec succès.<br/>".
			      "<center><img src=\"./imgtrajet.php?id_trajet=".
			      $trajet->id."&amp;date=".$_REQUEST['date']."&amp;id_etape=".
			      $steps[count($steps) - 1]['id']."\" alt=\"Trajet hypothétique\" /></center>");

      $accueil->add_paragraph("Ci-dessus un rendu \"vol d'oiseau\" du trajet prévu, sous réserve d'acceptation.");
    }
  else
    {
      $accueil->add_paragraph("<b>Une erreur est survenue lors de l'ajout de l'étape.</b>");
    }


}
else
{
  $respusr = new utilisateur($site->db);
  $respusr->load_by_id($trajet->id_utilisateur);

  $accueil->add_title(2, "Informations");
  $accueil->add_paragraph("Ce trajet <b>" . $trajet->ville_depart->nom . " / " .
			  $trajet->ville_arrivee->nom 
			  ."</b> est proposé par <a href=\"../user.php?id_utilisateur=".
			  $respusr->id."\">" . $respusr->get_html_link() . 
			  "</a>, qui prévoit de le réaliser le ".HumanReadableDate($datetrj, "", false, true) . ".");

  $accueil->add_paragraph("<div class=\"comment\">".doku2xhtml($trajet->commentaires)."</div>");

  $accueil->add_paragraph("<center><img src=\"./imgtrajet.php?id_trajet=".
			$trajet->id."&amp;date=".$datetrj."\" alt=\"Rendu géographique\" /></center>");

  $accueil->add_paragraph("Ci-dessus une vue du trajet, avec les étapes acceptées éventuelles");



  /* proposer de rejoindre le trajet */
  if ((! isset($_REQUEST['add_step_sbmt'])) && ($trajet->id_utilisateur != $site->user->id))
    {
      if (! $trajet->already_proposed_step($site->user->id, $datetrj))
	{
	  $accueil->add_title(2, "Vous souhaitez rejoindre ce trajet");
	  $accueil->add_paragraph("Veuillez remplir le formulaire ci-dessous. Après validation, ".
				  "l'étape ainsi créée sera mise à appréciation du responsable du trajet.");
      
	  $frm = new form('add_step', 'details.php', true);
	  $frm->add_hidden('id_trajet', $trajet->id);
	  $frm->add_hidden('date', $datetrj);
      
	  $ville = new ville($site->db);
	  $frm->add_entity_smartselect("mydest","Ma destination", $ville);
	  
	  $frm->add_dokuwiki_toolbar('comments');
	  $frm->add_text_area('comments', 'Commentaires (facultatif - Syntaxe DokuWiki)', null, 80, 20);
	  
	  $frm->add_submit('add_step_sbmt', 'Proposer');
	  $accueil->add($frm);
	  $accueil->add_paragraph("Une fois l'étape proposée, cette dernière pourra être acceptée ou refusée par ".
				  "l'initiateur du trajet. Vos coordonnées (renseignées dans le matmatronch) lui ".
			      "seront fournies, au cas où il ait besoin de vous joindre.");
	}
      else 
	$accueil->add_paragraph("Vous avez déjà proposé une étape pour ".
				"ce trajet à la date choisie. Vous ne ".
				"pouvez plus, en toute logique, proposer".
				" d'étapes.");
    }
}
/* options */
$accueil->add_title(2, "Autres options");
$opts[] = "<a href=\"./\">Retour à la page d'accueil du covoiturage</a>";
$opts[] = "<a href=\"./propose.php\">Proposer un trajet</a>";
$opts[] = "<a href=\"./search.php\">Rechercher un trajet</a>";

$options = new itemlist(false, false, $opts);
$accueil->add($options);


$site->add_contents ($accueil);


/* fin page */
$site->end_page ();
?>