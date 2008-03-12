<?php
/* Copyright 2006
 *
 * - BURNEY Rémy < remy dot burney at utbm dot fr >
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

$topdir = "../"; 
require_once("include/site.inc.php");
require_once($topdir . "include/entities/page.inc.php");
require_once($topdir."include/entities/news.inc.php");
require_once($topdir."include/entities/lieu.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$news = new nouvelle($site->db,$site->dbrw);
$lieu = new lieu($site->db);



if (!$site->user->is_in_group ("moderateur_site"))


/* la page n'est pas autorise pour l'utilisateur s'il n'a pas les droits */
if ( !$site->user->is_in_group ("moderateur_site"))
{
  $site->start_page ( $section, "Erreur" );
  
  $err = new error("Accès restreint","Vous n'avez pas le droit d'accéder à cette page.");
  $site->add_contents($err);
  
  $site->end_page();
  exit();
}


$site->start_page (CMS_PREFIX."config", "Liste des nouvelles");

/* suppression de la nouvelle via la sqltable */
if ((isset($_REQUEST['id_nouvelle']))
    && ($_REQUEST['action'] == "delete"))
  {

    $news = new nouvelle ($site->db, $site->dbrw);
    $id = intval($_REQUEST['id_nouvelle']);
    $news->load_by_id ($id);
    $news->delete (); 
    $site->add_contents (new contents("Suppression",
				      "<p>Suppression de la nouvelle eff&eacute;ctu&eacute;e avec succ&egrave;s</p>"));
  }

/* modification de la nouvelle via la sqltable */
if ((isset($_REQUEST['id_nouvelle']))
    && ($_REQUEST['action'] == "edit"))
  {
    $news = new nouvelle ($site->db);
    $id = intval($_REQUEST['id_nouvelle']);
    $news->load_by_id ($id);

    $site->add_contents(new contents ("Aper&ccedil;u de la nouvelle :",
			   "<p>Dans le cadre ci-dessous, vous allez avoir un ".
			   "aper&ccedil;u de la nouvelle</p>"));
    // affichage de la nouvelle
    $site->add_contents ($news->get_contents ());

    //** TODO : on affiche un formulaire d'edition **//
  }


/* affichage de la liste des nouvelles */
else{

  $req = new requete($site->db,
		     "SELECT `nvl_nouvelles`.*,
                             CONCAT(`utilisateurs`.`prenom_utl`,
                                    ' ',
                                    `utilisateurs`.`nom_utl`) AS `nom_prenom`
                      FROM `nvl_nouvelles`, `utilisateurs` 
                      WHERE `nvl_nouvelles`.`modere_nvl`='1' 
                      AND `nvl_nouvelles`.`id_utilisateur` = `utilisateurs`.`id_utilisateur`
                      ORDER BY `nvl_nouvelles`.`date_nvl` 
                      DESC");

  $texte = new contents("Liste des nouvelles",
			  "<p>Sur cette page, vous pouvez mod&eacute;rer ".
			  "les nouvelles</p>");
  
  // génération de la liste de nouvelles
  $tabl = new sqltable ("news_list",
			"Liste des nouvelles",
			$req,
			"newsliste.php",
			"id_nouvelle",
			array ("titre_nvl" => "Titre",
			       "nom_prenom" => "auteur",
			       "date_nvl" => "Date"),
			array ("edit" => "modifier",
			       "delete" => "supprimer"),
			array (),
			array ());



  $texte->add ($tabl);
  $texte->add_title(2,"Outils");
  $texte->add(new itemlist("Outils",false,array(
    "<a href=\"newsliste.php\">Lister les nouvelles</a>",
    "<a href=\"news.php\">Ajouter une nouvelle</a>"

)));

  $site->add_contents ($texte);
  
 }

$site->end_page ();

?>