<?php
/**
 * @brief L'accueil du magasin en ligne de l'AE (e-boutic).
 *
 */

/* Copyright 2006
 * 
 * Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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
require_once($topdir . "comptoir/include/produit.inc.php");
require_once($topdir . "comptoir/include/venteproduit.inc.php");
require_once("include/e-boutic.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/gallery.inc.php");
require_once($topdir . "include/cts/vignette.inc.php");


$site = new eboutic();

if ( $site->user->id < 1 )
	error_403();

/* vidage du panier */
if ($_REQUEST['act'] == "empty_cart")
{
  $site->empty_cart ();
}

/*mise a jour du panier */
if ($_REQUEST['act'] == "add")
{
  $item = intval($_REQUEST['item']);
  $ret = $site->add_item ($item);

  /* produit non trouve ou stock insuffisant */
  if ($ret == false)
    $add_rs = new error("Ajout",
			"<b>Impossible d'ajouter le produit dans le panier</b>. Soit le produit ne peut être cheté, soit il est incompatible avec un produit se trouvant déjà dans le panier.");
  /*ajout possible */
  else
    {
      	$add_rs = new contents ("Ajout");
		$add_rs->add_paragraph ( "Ajout de l'article effectue avec succes.");     
		$add_rs->add_paragraph ("<a href=\"./cart.php\">Passer la commande</a>");	      
    }
}

$site->start_page ("Accueil e-boutic", "Bienvenue");

/* ajout panier ? */
if (isset($add_rs))
	$site->add_contents ($add_rs);
	  
if (!isset($_REQUEST['cat']))
{
	$accueil = new contents("E-boutic",
				"Bienvenue sur E-boutic, la boutique en ligne ".
				"de l'AE. Sur cette page, vous allez pouvoir ".
				"selectionner des categories dans lesquelles ".
				"sont ranges les differents articles proposes ".
				"a la vente.<br/>".
				"Une fois votre panier rempli, vous pourrez ".
				"passer a l'achat, en basculant sur les serveurs".
				" securises de notre partenaire<br/><br/>");
	
	$site->add_contents ($accueil);
}

/* si pas de categorie (rayon selectionne) */
if (!isset($_REQUEST['cat']))
{
  /* recuperation des categories */
  $cat = $site->get_cat ();
  
	/* on traite les categories en vue d'un affichage dans un
	* contenu itemlist
	*/
	$items_lst = new gallery ("");
  foreach ($cat as $c)
	{
	  /*if( !empty($c['url_logo_typeprod']) && file_exists($topdir."images/comptoir/eboutic/".$c["url_logo_typeprod"]) ) 
      $img = $topdir."images/comptoir/eboutic/".$c["url_logo_typeprod"];
		else
		  $img = $topdir."var/img/matmatronch/na.gif";*/
		$img = $c['url_logo_typeprod'];
		$title = $c['nom_typeprod'];
		$id = $c['id_typeprod'];
		$desc = $c['description_typeprod'];
    $items_lst->add_item (new vignette2($id,$title,$img,$desc));
	}

  $cat_cts = new contents ("E-boutic : rayons disponibles",
			   "Veuillez selectionner le rayon desire ".
			   "dans la liste ci-dessous :</p>");

  $site->add_contents ($cat_cts);
	$site->add_contents ($items_lst);
}

/* sinon : $_REQUEST['cat'] renseigne */
else
{
  $cat = intval($_REQUEST['cat']);
  $items = $site->get_items_by_cat ($cat);

  if ((count($items) <= 0)
      || ($items == false))
    $site->add_contents(new error("Choix de la categorie",
				  "Categorie inconnue ou vide !"));
  else
    {
      $cat_name = $items[0]['nom_typeprod'];

      /* creation du cts contenant les infos sur les articles */
      $items_lst = new gallery ("<a href=\"index.php\">e-boutic</a> / ".$cat_name);

      /* traitement des donnees avant affichage
       * dans un contents gallery               */
      foreach ($items as $item)
	{
	  /* image */
	  $img = $item['url_logo_prod'];
	  /* titre */
	  $title = $item['nom_prod'];
	  /* description */
	  $desc = $item['description_prod'];
	  /* prix (public ou barman, peu importe sur eboutic */
	  $price = $item['prix_vente_prod'];
	  /* prix cotisants */
	  $price_cotisants = $item['prix_vente_prod_cotisant'];
	  /* stock (stock global ou local) */
	  $stock = $item['stock_local_prod'] == -1 ?
	    $item['stock_global_prod'] : $item['stock_local_prod'];
	  /* id article */
	  $id = $item['id_produit'];

		if ( $item['a_retirer_prod'])
		{
				
			
			$req = new requete($site->db,
		 		"SELECT `cpt_comptoir`.`nom_cpt` 
		  		FROM `cpt_mise_en_vente` 
		  		INNER JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` = `cpt_mise_en_vente`.`id_comptoir`
		  		WHERE `cpt_mise_en_vente`.`id_produit` = '".$item['id_produit']."' AND `cpt_comptoir`.`type_cpt`!=1");
			
			if ( $req->lines != 0 )
			{
				$noms=array();
				while ( list($nom) = $req->get_row() )
					$noms[] = $nom;
				$desc .="<br/><br/>Produit à venir retirer : ".implode(" ou ",$noms);	
			}
			else
				$desc .="<br/><br/>Produit à venir retirer";
		}

                if ($site->user->ae && $price_cotisants > 0 && $_SESSION['eboutic_cart'][$id]== 0 )
		{
		  $req = new requete($site->db,
		         "SELECT *
		         FROM `cpt_vendu_cotisant`
		         WHERE `id_utilisateur` = ".$site->user->id."
		         AND `id_produit` =".$id."");
		  if ( $req->lines == 0 )
		    $price=$price_cotisants;
		}
	  $items_lst->add_item (new vignette($id,
					     $title,
					     $img,
					     $desc,
					     $price,
					     $stock,
					     $cat,
					     $price_cotisants));
	}
      /* ajout liste des articles au site */
      $site->add_contents ($items_lst);
    }
  /* fin categorie non vide */
}

/* fin page */
$site->end_page ();
?>
