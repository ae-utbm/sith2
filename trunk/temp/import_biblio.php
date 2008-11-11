<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
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
$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/books.inc.php");
require_once($topdir. "include/entities/objet.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/sitebat.inc.php");
require_once($topdir. "include/entities/batiment.inc.php");
require_once($topdir. "include/entities/salle.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("books_admin")  )
	$site->error_forbidden();

$oediteur = new editeur($site->db,$site->dbrw);
$oserie = new serie($site->db,$site->dbrw);
$oauteur = new auteur($site->db,$site->dbrw);
$olivre = new livre($site->db,$site->dbrw);

if ( $_REQUEST["action"] == "process")
{
	$lines = explode("\n",$_REQUEST["data"]);

	foreach ( $lines as $line)
	{
		if ( $line)
		{

		list($serie,$nom,$num_livre,$auteur,$editeur) = explode(";",$line);

		if ( $num_livre != intval($num_livre) ) $num_livre=null;


		if ( $serie && $serie!= "****" && $serie != "*****" )
		{
			$id_serie = $cache_series[$serie];
			if ( !$id_serie )
			{
				$oserie->add_serie($serie);
				$id_serie = $oserie->id;
				$cache_series[$serie]	 = $id_serie;
			}
		}
		else
			$id_serie = NULL;

		$id_auteur = $cache_auteurs[$auteur];
		if ( !$id_auteur )
		{
			$oauteur->add_auteur($auteur);
			$id_auteur = $oauteur->id;
			$cache_auteurs[$auteur]	 = $id_auteur;
		}

		$id_editeur = $cache_editeurs[$editeur];
		if ( !$id_editeur )
		{
			$oediteur->add_editeur($editeur);
			$id_editeur = $oediteur->id;
			$cache_editeurs[$editeur]	 = $id_editeur;
		}

		$olivre->add_book ( 33, 1, 5, 12, null, $nom,
				"BDN", "", 700, 1500, 0, 1,
				1, 0, "",
				$id_serie, $id_auteur, $id_editeur,$num_livre );

		}
	}


	exit();
}


$site->start_page("services","Bibliothéque");

$cts = new contents("Import CSV(;)");


$frm = new form("process","import_biblio.php");
$frm->add_hidden("action","process");
$frm->add_text_area("data","Données");
$frm->add_submit("valide","Ajouter");
$cts->add($frm);


$site->add_contents($cts);
$site->end_page();



?>
