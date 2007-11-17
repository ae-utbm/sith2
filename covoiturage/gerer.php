<?php
/**
 * @brief Covoiturage : Gestion d'un trajet
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

$site->start_page ("services", "Covoiturage - Gestion d'un trajet");
$accueil = new contents("Covoiturage - Gestion d'un trajet",
			"");

$trajet = new trajet($site->db, $site->dbrw, null);

$trajet->load_by_id($_REQUEST['id_trajet']);

/* Acceptation / refus */
if ($_REQUEST['action'] == "accept")
{
  $accueil->add_title(2, "Accpetation de l'étape");
  if ($trajet->accept_step($_REQUEST['id_etape'], $_REQUEST['date']))
    $accueil->add_paragraph("Etape acceptée avec succès.");
  else
    $accueil->add_paragraph("Erreur lors de l'acceptation de l'étape.");

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

else if ($_REQUEST['action'] == "refuse")
{
  $accueil->add_title(2, "Accpetation de l'étape");
  if ( $trajet->refuse_step($_REQUEST['id_etape'], $_REQUEST['date']))
    $accueil->add_paragraph("Etape refusée avec succès !");
  else
    $accueil->add_paragraph("Erreur lors du refus de l'étape.");
    
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

/* modération des étapes */
if ($_REQUEST['action'] == "moderer")
{
  $accueil->add_title(2, "Modération des étapes");
  
  $trajet->load_steps();

  $step = $trajet->get_step_by_id($_REQUEST['id_etape']);
  
  $propusr = new utilisateur ($site->db);
  $propusr->load_by_id($step['id_utilisateur']);

  if ($step['ville'] > 0)
    {
      $villeetp = new ville($site->db);
      $villeetp->load_by_id($step['ville']);
    }
  else
    $villeetp = NULL;
  
  if ($villeetp != NULL)
    {
      $accueil->add_paragraph("<b><center>".$propusr->get_html_link() . " souhaiterait faire partie du trajet pour le ".
			      HumanReadableDate($step['date_etape'], "", false) .", et demande un passage via ".
			  $villeetp->nom . ".</center></b><br/><br/>");
    }
  else
    $accueil->add_paragraph("<b><center>".$propusr->get_html_link() . " souhaiterait faire partie du trajet pour le ".
			    HumanReadableDate($step['date_etape'], "", false) .".</center></b><br/><br/>");

  if (strlen($step['comments']) > 0)
    {
      /* TODO : améliorer la présentation des commentaires */
      $accueil->add_paragraph("L'utilisateur a laissé le commentaire suivant :<br/>" . 
			      "<blockquote>".
			      doku2xhtml($step['comments']).
			      "</blockquote>");
    }
  
  if ($villeetp != NULL)
    {
      $accueil->add_paragraph("Ci-dessous un rendu du trajet en prenant en compte cette étape (la ville concernée apparaît en rouge) :");
      $trjimg = "./imgtrajet.php?id_trajet=".$trajet->id."&amp;date=".$step['date_etape']."&amp;hlstp=".$step['id'];

      $accueil->add_paragraph("<center><img src=\"$trjimg\" alt=\"rendu géographique\" /></center>");
    }

  $accueil->add_paragraph("Cliquez sur les liens ci-dessous pour accepter ou refuser l'étape. Vous pouvez en outre prendre contact avec ".
			  "l'utilisateur afin de vous arranger à l'amiable");
  
  $lnkaccept = "gerer.php?action=accept&id_trajet=".$trajet->id."&amp;date=".$step['date_etape']."&amp;id_etape=".$step['id'];
  $lnkrefuse = "gerer.php?action=refuse&id_trajet=".$trajet->id."&amp;date=".$step['date_etape']."&amp;id_etape=".$step['id'];
  $accueil->add_paragraph("<center><a href=\"".$lnkaccept."\">ACCEPTER</a> | <a href=\"".$lnkrefuse."\">REFUSER</a></center>");

  $site->add_contents($accueil);
  
  
  $site->end_page();
  
  exit();
}


/* évidemment, seul le responsable du trajet peut ajouter une date */
$accueil->add_title(2, "Dates du trajet");

if ($trajet->id_utilisateur == $site->user->id)
{
  if (isset($_REQUEST['add_date']))
    {
      $ret = $trajet->add_date($_REQUEST['date']);
      if ($ret)
	{
	  $accueil->add_paragraph("<b>Date ajoutée avec succès.</b>");
	  $trajet->load_dates();
	}
      else
	$accueil->add_paragraph("<b>Erreur lors de l'ajout de la date.</b>");
    }

  $accueil->add_paragraph("Vous pouvez ajouter une date à l'aide du formulaire ci-dessous");

  $frm = new form('trip_adddate', "gerer.php", true);
  $frm->add_hidden('id_trajet', $trajet->id);
  $frm->add_date_field('date', 'Date de voyage proposée');
  $frm->add_submit('add_date', 'Ajouter des dates de trajet');
  $accueil->add($frm);
}

if (count($trajet->dates))
{
  $accueil->add_paragraph("Ci-dessous la liste des dates de trajet actuellement renseignées :");
  foreach($trajet->dates as $date)
    {
      $datetrj[] = "Le " . HumanReadableDate($date, "", false);
    }
  $lst = new itemlist(false, false, $datetrj);
  $accueil->add($lst);
}


$accueil->add_title(2, "Etapes acceptées");

$trajet->load_steps();

if (count($trajet->etapes))
{
  foreach ($trajet->etapes as $etape)
    {
      
      if ($etape['ville'] > 0)
	{
	  $obville = new ville($site->db);
	  $obville->load_by_id($etape['ville']);
	}
      else
	$obville = NULL;

      $propuser = new utilisateur($site->db);
      $propuser->load_by_id($etape['id_utilisateur']);
      

      $trajetdate[$etape['date_etape']][] = &$etape;

      if ($etape['etat'] == STEP_ACCEPTED)
	{
	  if ($obville != NULL)
	    {
	      $str = "Passage par <b>" . $obville->nom . "</b> suggéré par " . 
		$propuser->get_html_link() . " le " . HumanReadableDate($etape['date_proposition'], "", true) .
		" pour le trajet du <b>" . HumanReadableDate($etape['date_etape'], "", false)."</b>";
	    }
	  else
	    $str = $propuser->get_html_link() . 
	      " accepté pour le trajet du <b>" . HumanReadableDate($etape['date_etape'], "", false)."</b>";

	  $accepted[] = $str;
	}
      else if ($etape['etat'] == STEP_WAITING)
	{
	  /* s'il n'est pas trop tard ... */
	  if (strtotime($etape['date_etape']) > time())
	    { 
	      if ($obville != NULL)
		{
		  $str = "Passage par <b>" . 
		    $obville->nom . "</b> suggéré par " . 
		    $propuser->get_html_link() . " le " . HumanReadableDate($etape['date_proposition'], "", true) .
		    " pour le trajet du <b>" . HumanReadableDate($etape['date_etape'], "", false).
		    "</b> | <a href=\"./gerer.php?action=moderer&amp;id_trajet=".$trajet->id ."&amp;date_trajet=".
		    $etape['date_etape']
		    ."&amp;id_etape=".$etape['id']."\">Gérer la demande</a>";
		}
	      else
		{
		  $str = $propuser->get_html_link() . " en attente d'acceptation ".
		    " pour le trajet du <b>" . HumanReadableDate($etape['date_etape'], "", false).
		    "</b> | <a href=\"./gerer.php?action=moderer&amp;id_trajet=".$trajet->id ."&amp;date_trajet=".
		    $etape['date_etape']
		    ."&amp;id_etape=".$etape['id']."\">Gérer la demande</a>";
		}

	      $proposed[] = $str;
	    }
	}
    }
  
}

if (count($accepted))
{
  $accueil->add(new itemlist(false, false, $accepted));
}
else
{
  $accueil->add_paragraph("<b>Aucune étape n'a encore été acceptée.</b>");
}

$accueil->add_title(2, "Etapes en attente d'acceptation");

if (count($proposed))
{
  $accueil->add(new itemlist(false, false, $proposed));
}
else
{
  $accueil->add_paragraph("<b>Aucune étape en attente de validation.</b>");
}

$accueil->add_title(2, "Récapitulatif des trajets par dates");

if (count($trajet->dates))
{
  foreach ($trajet->dates as $date)
    {
      
      $idusers = $trajet->get_users_by_date($date);
      if ($idusers != false)
	{

	  $accueil->add_title(3, "Trajet du ". HumanReadableDate($date, "", false));
	  $accueil->add_paragraph("<center><img src=\"./imgtrajet.php?id_trajet=".$trajet->id.
				  "&amp;date=".$date."\" alt=\"\" /></center>");

	  $accueil->add_paragraph(count($idusers) . " utilisateur(s) intéressé(s) par le trajet");
	  $passager = new utilisateur($site->db);

	  $lstp = array();
	  
	  foreach ($idusers as $idusr)
	    {
	      $passager->load_by_id($idusr);
	      $lstp[] = $passager->get_html_link();
	      
	    }
	  $accueil->add(new itemlist(false, false, $lstp));
	}
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