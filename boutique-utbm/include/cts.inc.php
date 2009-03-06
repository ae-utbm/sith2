<?php
/* Copyright 2007
 *
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

/**
 * @file
 */

/**
 * @defgroup display_cts_eboutic Contents e-boutic
 * Contents pour le rendu des pages d'e-boutic
 * @ingroup display_cts
 */

/**
 * Contents pour afficher la fiche d'un produit dans e-boutic
 * dans le but de vendre (pas d'éditer la fiche)
 *
 * Supporte :
 * - Affichage des sous-produits (variantes)
 * - Affichage des informations complémentaires (générée spécifiquement pour le client)
 * @ingroup display_cts_eboutic
 */
class ficheproduit extends contents
{

  function ficheproduit( &$typeproduit, &$produit, &$venteprod, &$user )
  {
    global $topdir;

    $this->title =
        "<a href=\"index.php\">Boutique</a>".
        " / ".$typeproduit->get_html_link().
        " / ".$produit->get_html_link();

    $this->buffer .= "<div class=\"ebficheprod\">";

    // Nom et prix de ref
    $this->buffer .= "<h2>".$produit->nom."</h2>";
    $this->buffer .= "<p class=\"prix\">".($produit->obtenir_prix($user)/100)." &euro;</p>";

    // Image
    if ( is_null($produit->id_file) )
    {
      $this->buffer .=
      "<p class=\"image\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</p>\n";
    }
    else
    {
      $this->buffer .=
      "<p class=\"image\">".
      "<img src=\"".$topdir."d.php?id_file=".$produit->id_file."&amp;action=download&amp;download=preview\" alt=\"\" />".
      "</p>\n";
    }

    $this->buffer .= "<div class=\"description\">";

    // Description
    if ( !$produit->description_longue )
      $this->buffer .= doku2xhtml($produit->description);
    else
      $this->buffer .= doku2xhtml($produit->description_longue);

    $extra = $produit->get_extra_info($user);

    if ( !empty($extra) )
      $this->buffer .= "<br/>".$extra;

    $this->buffer .= "</div>";

    // Informations sur le retrait
    $this->buffer .= "<div class=\"retrait\">";
    $this->buffer .= "<h3>Produit à venir retirer à l'accueil sevenans</h3>";
    $this->buffer .= "</div>";

    $req = new requete($produit->db,"SELECT `boutiqueut_produits`.* ".
            "FROM     `boutiqueut_produits` ".
            "WHERE ".
            "`boutiqueut_produits`.`id_produit_parent`='".$produit->id."'");

    $this->buffer .= "<div class=\"achat\">";
    $this->buffer .= "<h3>Acheter le produit</h3>";
    if ( $req->lines > 0 )
    {
      $frm = new form('cmd', '/?act=adds');
      $subprod=new produit($produit->db);

      while ( $row = $req->get_row() )
      {
        $subprod->_load($row);

        $extra = $subprod->get_extra_info($user);

        if ( !empty($extra) )
          $extra = " <i>($extra)</i>";

        $stock = $row["stock_global_prod"];

        if ( $stock == 0 )
          continue;
        else
        {
          if($stock!=-1)
            $extra.=' (stock : '.$stock.')';
          $row["nom_prod"]=trim(str_replace($produit->nom,'',$row["nom_prod"]));
          $frm->add_text_field('nb['.$row["id_produit"].']', $row["nom_prod"].$extra);
        }
      }
      $frm->add_submit ( 'ajout', 'Ajouter');
      $this->add($frm);
      $this->buffer .= "</ul>";
    }
    else
    {
      $stock = $produit->stock_global;

      if ( $stock == 0 )
        $this->buffer .=
          "<ul><li>Produit épuisé</li></ul>";
      else
      {
        if ( $stock != -1 )
          $info_stock=" ($stock en stock)";
        else
          $info_stock="";
        $frm = new form('cmd', '/?act=add');
        $frm->add_hidden('id_produit',$produit->id);
        $frm->add_text_field('nb', 'Quantité '.$info_stock);
        $frm->add_submit ( 'ajout', 'Ajouter');
        $this->add($frm);
      }
    }
    $this->buffer .= "</div>";
    $this->buffer .= "</div>";
  }
}

/**
 * Vignette pour un produit
 * @ingroup display_cts_eboutic
 */
class vigproduit extends stdcontents
{
  function vigproduit ( $row, &$user )
  {
    global $topdir;

    $subprod=new produit($user->db);
    $subprod->_load($row);

    $this->buffer .= "<div class=\"ebv\">\n";
    $this->buffer .= "<h2><a href=\"?id_produit=".$row["id_produit"]."\">".$row["nom_prod"]."</a></h2>\n";

    if ( !is_null($row["id_file"]) )
      $this->buffer .=
      "<p class=\"image\"><a href=\"?id_produit=".$row["id_produit"]."\">".
      "<img src=\"".$topdir."d.php?id_file=".$row["id_file"]."&amp;action=download&amp;download=thumb\" alt=\"\" />".
      "</a></p>\n";
    else
      $this->buffer .=
      "<p class=\"image\"><a href=\"?id_produit=".$row["id_produit"]."\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</a></p>\n";

    $this->buffer .= "<p class=\"description\">".doku2xhtml($row["description_prod"])."</p>\n";
    $this->buffer .= "<p class=\"prixunit\">".($subprod->obtenir_prix($user)/100)." &euro;</p>\n";


    $stock = $row["stock_global_prod"];
    $this->buffer .= "<p class=\"details\"><a href=\"./?id_produit=".$row["id_produit"]."\">Détails / Acheter</a></p>\n";
    $this->buffer .= "</div>\n";
  }
}

/**
 * Vignette pour une catégorie de produit
 * @ingroup display_cts_eboutic
 */
class vigtypeproduit extends stdcontents
{
  function vigtypeproduit ( $row )
  {
    global $topdir;

    $this->buffer .= "<div class=\"ebv\">\n";
    $this->buffer .= "<h2><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".$row["nom_typeprod"]."</a></h2>\n";

    if ( !is_null($row["id_file"]) )
      $this->buffer .=
      "<p class=\"image\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".
      "<img src=\"".$topdir."d.php?id_file=".$row["id_file"]."&amp;action=download&amp;download=thumb\" alt=\"\" />".
      "</a></p>\n";
    else
      $this->buffer .=
      "<p class=\"image\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</a></p>\n";

    $this->buffer .= "<p class=\"description\">".doku2xhtml($row["description_typeprod"])."</p>\n";
    $this->buffer .= "<p class=\"details\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">Produits</a></p>\n";
    $this->buffer .= "</div>\n";

  }
}


?>
