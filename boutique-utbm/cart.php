<?php
/**
 * @brief Etat du panier pour le magasin en ligne de l'AE (e-boutic).
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
require_once("include/produit.inc.php");
require_once("include/venteproduit.inc.php");
require_once("include/boutique.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/gallery.inc.php");
require_once($topdir . "include/cts/vignette.inc.php");

$site = new boutique ();
$site->allow_only_logged_users();
if($site->user->type != "srv" )
  $site->error_forbidden();

/* modifications du panier */
if (isset($_POST['cart_modify']))
{
  foreach ($_POST as $item_id => $qte)
  {
    if (!is_int($item_id))
      continue;
    if ($qte == 0)
      unset ($_SESSION['boutique_cart'][$item_id]);
    if ($qte > 0)
      $_SESSION['boutique_cart'][$item_id] = $qte;
  }
}

$site->start_page ("Panier", "Etat du panier");
$site->add_contents(new tabshead(array(array("boutique","boutique-utbm/index.php","Boutique"),array("pannier","boutique-utbm/cart.php","Pannier"),array("suivi","boutique-utbm/suivi.php","Commandes")),"pannier"));
$accueil = new contents ("Etat du panier",
                         "<p>Sur cette page, vous allez pouvoir ".
                         "recenser les articles que vous vous ".
                         "appretez a commander<br/><br/>".
                         "Si tout vous parait normal, vous pouvez ".
                         "passer a l'achat.<br/><br/></p>");


/* panier vide */
if ($site->cart == false)
  $accueil->add_paragraph("Votre panier est actuellement vide");
/* panier non vide */
else
{
  /* faute de mieux, nous utiliserons ici un stdcontents */
  $cart_t = new stdcontents ("Etat du panier");
  $cart_t->buffer .= "<h2>Contenu</h2>\n";
  $cart_t->buffer .= "<form method=\"post\">\n";
  $cart_t->buffer .= "<table class=\"cart\">\n";
  $cart_t->buffer .= "<tr style=\"font-weight: bold;\">".
                     "<td>Article</td>".
                     "<td style=\"text-align: center;\">Quantite</td>".
                     "<td style=\"text-align: right;\">Prix unitaire</td>".
                     "</tr>\n";

  foreach ($site->cart as $item)
  {
    for ($i=0 ; $i < $_SESSION['boutique_cart'][$item->id] + 1 ; $i++)
      $tmp[$i] = $i;

    $cart_t->buffer .= ("<tr>\n".
                    "<td>" . $item->nom . "</td>".
                    "<td style=\"text-align: center;\">");

    if (isset($_POST['cart_submit']))
      $cart_t->buffer .= $_SESSION['boutique_cart'][$item->id];
    else
      $cart_t->buffer .=
                GenerateSelectList($tmp, $_SESSION['boutique_cart'][$item->id] , $item->id);

    $cart_t->buffer .= (" </td>\n".
                        " <td style=\"text-align: right;\">".
                        sprintf("%.2f", $item->obtenir_prix(false,$site->user) / 100) .
                        "</td></tr>\n");
  }
  $cart_t->buffer .= ("<tr style=\"font-weight: bold;\">".
                      "<td colspan=\"2\" style=\"text-align: right;\">Total :</td>".
                      "<td style=\"text-align: right;\">" .
                      sprintf("%.2f", $site->total / 100) .
                      " Euros</td></tr>");
  $cart_t->buffer .= ("</table>");

  if (!isset($_POST['cart_submit']))
  {
    $cart_t->buffer .= ("<h2>Actions</h2>\n");
    $cart_t->buffer .= ("<table><tr><td><input type=\"submit\"".
                        " name=\"cart_modify\" " .
                        "value=\"Accepter les modifications\" />\n");
    $cart_t->buffer .= ("</form></td>\n");

    $cart_t->buffer .= ("<td><form action=\"cart.php\" method=\"post\">\n");
    $cart_t->buffer .= ("<input type=\"submit\" name=\"cart_submit\"
                    value=\"Passer la commande\" />\n");
    $cart_t->buffer .= ("</form></td></tr></table>");
  }
  else
    $cart_t->buffer .= ("</form>");
  $accueil->add ($cart_t);


  /* formulaire "proceder au paiement" poste */
  if (isset($_REQUEST['cart_submit']))
  {

    /* a ce stade le panier ne peut pas etre vide */

    $accueil->add_title(1,"Paiement sur facture");

    $accueil->add_paragraph ("Cliquez sur le lien pour valider la commande : <a href=\"./paiement.php\">Paiement sur facture</a>");

  } // fin si panier poste et demande paiement effective
} // fin panier non vide

$site->add_contents ($accueil);
$site->end_page ();

?>
