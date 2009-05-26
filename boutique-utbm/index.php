<?php
/**
 * @brief L'accueil du magasin en ligne de l'AE (e-boutic).
 *
 */

/* Copyright 2006,2007
 *
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
 * - Julien Etelain <julien CHEZ pmad POINT net>
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

require_once("include/boutique.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/gallery.inc.php");


$site = new boutique();
if($site->is_closed())
{
  $site->start_page ("Accueil boutique utbm", "boutiqueutbm");
  $cts = new contents("Boutique utbm",
        "Bienvenue dans la boutique UTBM, la boutique en ligne ".
        "de l'UTBM.<br />".
        "Cette boutique est réalisée en partenariat avec l'association des étudiants de l'utbm."
        );
  $cts2 = new contents("Boutique fermée jusqu'au ".$site->get_boutique_param('open'),
                       $site->get_boutique_param('close_message','La boutique est actuellement fermée'));
  $cts->add($cts2,true);
  $site->add_contents ($cts);
  $site->end_page ();
  exit();
}
$produit = new produit($site->db);
$typeproduit = new typeproduit($site->db);
if ( isset($_REQUEST["id_produit"]) )
{
  $produit->load_by_id($_REQUEST["id_produit"]);

  if ($produit->is_valid())
  {
    $venteprod = new venteproduit ($site->db);
    if ( !$venteprod->_charge($produit) )
      $produit->id = null;
    else
      $typeproduit->load_by_id($produit->id_type);
  }
}
elseif ( isset($_REQUEST["item"]) ) // legacy support
{
  $produit->load_by_id($_REQUEST["item"]);

  if ( !$produit->is_valid() )
  {
    $venteprod = new venteproduit ($site->db);
    if ( !$venteprod->_charge($produit,$site->comptoir) )
      $produit->id = null;
    else
      $typeproduit->load_by_id($produit->id_type);
  }
}

elseif ( isset($_REQUEST["id_typeprod"]) )
  $typeproduit->load_by_id($_REQUEST["id_typeprod"]);
elseif ( isset($_REQUEST["cat"]) ) // legacy support
  $typeproduit->load_by_id($_REQUEST["cat"]);


/* vidage du panier */
if ($_REQUEST['act'] == "empty_cart")
  $site->empty_cart ();

/*mise a jour du panier */
if ($_REQUEST['act'] == "add")
{
  $nb=1;
  if(isset($_REQUEST['nb']) && !empty($_REQUEST['nb']) && $_REQUEST['nb']>1)
  $nb=intval($_REQUEST['nb']);
  $ret = $site->add_item ($produit->id,$nb);

  /* produit non trouve ou stock insuffisant */
  if ($ret == false)
    $add_rs = new error("Ajout",
      "<b>Impossible d'ajouter le produit dans le panier</b>. Soit le produit ne peut être acheté, soit vous avez indiqué une quantité incorrecte.");
  /*ajout possible */
  else
  {
    $add_rs = new contents ("Ajout");
    $add_rs->add_paragraph ( "Ajout de $nb l'article effectue avec succes.");
    $add_rs->add_paragraph ("<a href=\"./cart.php\">Passer la commande</a>");
    $produit->id=null;
  }
}
elseif($_REQUEST['act'] == "adds")
{
  if(isset($_REQUEST['nb']) && is_array($_REQUEST['nb']))
  {
    $buffer='';
    $produit = new produit($site->db);
    foreach($_REQUEST['nb'] as $id => $nb)
    {
      $nb=intval($nb);
      $id=intval($id);
      if(empty($nb) || $nb < 1)
        continue;
      $produit->load_by_id($id);
      if ($produit->is_valid())
      {
        if($site->add_item ($produit->id,$nb))
          $buffer.='<li>Ajout de '.$nb.' '.$produit->nom.' au panier</li>';
        else
          $buffer.='<li>Impossible d\'ajouter '.$nb.' '.$produit->nom.' au panier</li>';
      }
    }
    if(!empty($buffer))
    {
      $add_rs = new contents ("Ajout");
      $add_rs->puts('<ul>'.$buffer.'</ul>');
      $add_rs->add_paragraph ("<a href=\"./cart.php\">Passer la commande</a>");
    }
  }
}
if ( $produit->is_valid() && !is_null($produit->id_produit_parent) )
{
  while ( $produit->is_valid() && !is_null($produit->id_produit_parent) )
    $produit->load_by_id($produit->id_produit_parent);

  if ( $produit->is_valid() )
  {
    $venteprod = new venteproduit ($site->db);
    if ( !$venteprod->_charge($produit,$site->comptoir) )
      $produit->id = null;
    else
      $typeproduit->load_by_id($produit->id_type);
  }
}

$site->start_page ("Accueil boutique utbm", "boutiqueutbm");

$site->add_contents(new tabshead(array(array("boutique","boutique-utbm/index.php","Boutique"),array("panier","boutique-utbm/cart.php","Panier"),array("suivi","boutique-utbm/suivi.php","Commandes")),"boutique"));
/* ajout panier ? */
if (isset($add_rs))
  $site->add_contents ($add_rs);

if(
   $typeproduit->is_valid()
   && !empty($typeproduit->css)
   && file_exists($wwwtopdir.'css/eboutic/'.$typeproduit->css)
  )
  $site->add_css('css/eboutic/'.$typeproduit->css);

if ( $produit->is_valid() )
{
  $site->add_contents (new ficheproduit( $typeproduit, $produit, $venteprod, $site->user ));
}
elseif ( !$typeproduit->is_valid() )
{
  $accueil = new contents("Boutique utbm",
        "Bienvenue dans la boutique UTBM, la boutique en ligne ".
        "de l'UTBM.<br />".
        "Cette boutique est réalisée en partenariat avec l'association des étudiants de l'utbm."
        );

  if($site->user->type!='srv')
  {
    $accueil->add_paragraph("Toutes les commandes sont à retirer à l'accueil de l'UTBM à sévenans le jeudi après-midi. ".
                            "<br />Le paiement s'effectue exclusivlement par chèque lors du retrait de la ".
                            "commande.<br />Pour plus d'informations contactez boutique@utbm.fr.");
  }

  $site->add_contents ($accueil);

  $items = new requete($site->db,"SELECT `boutiqueut_produits`.* , `boutiqueut_type_produit`.`nom_typeprod` ".
            "FROM `boutiqueut_produits` ".
            "INNER JOIN `boutiqueut_type_produit` USING (`id_typeprod`) ".
            "WHERE `boutiqueut_produits`.`prod_archive` = 0 ".
            "AND `boutiqueut_produits`.`stock_global_prod`!=0 ".
            "AND (`boutiqueut_produits`.date_fin_produit > NOW() OR `boutiqueut_produits`.date_fin_produit IS NULL) ".
            "AND id_produit_parent IS NULL ".
            "ORDER BY RAND() ".
            "LIMIT 3");

  $items_lst = new gallery ("Quelques produits :");

  while ( $row = $items->get_row() )
    $items_lst->add_item (new vigproduit($row,$site->user));

  /* ajout liste des articles au site */
  $site->add_contents ($items_lst);

  /* recuperation des categories */
  $cat = $site->get_cat ();

  /* on traite les categories en vue d'un affichage dans un
  * contenu itemlist
  */
  $items_lst = new gallery ("Rayons disponibles");
  if(!empty($cat))
    foreach ($cat as $c)
      $items_lst->add_item (new vigtypeproduit($c));

  $site->add_contents ($items_lst);
}
/* sinon : $_REQUEST['cat'] renseigne */
else
{
  $items = $site->get_items_by_cat ($typeproduit->id);

  if ((count($items) <= 0) || ($items == false))
    $site->add_contents(new contents("<a href=\"index.php\">Boutique</a> / ".$typeproduit->get_html_link(),"<p>Aucun produit en vente</p>"));
  else
  {
    /* creation du cts contenant les infos sur les articles */
    $items_lst = new gallery ("<a href=\"index.php\">Boutique</a> / ".$typeproduit->get_html_link());

    /* traitement des donnees avant affichage
     * dans un contents gallery               */
    foreach ($items as $row)
      $items_lst->add_item (new vigproduit($row,$site->user));
    /* ajout liste des articles au site */
    $site->add_contents ($items_lst);
  }
  /* fin categorie non vide */
}

/* fin page */
$site->end_page ();
?>
