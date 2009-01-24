<?
/**
 * @brief La classe boutique permettant le design de la boutique utbm.
 *
 */

/* Copyright 2008
 *
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

define('NOTAE',true);
define('FOLDERID',1314);

require_once($topdir . "include/site.inc.php");
require_once("include/defines.inc.php");
require_once("include/produit.inc.php");
require_once("include/typeproduit.inc.php");
require_once("include/venteproduit.inc.php");
require_once("include/debitfacture.inc.php");
require_once("include/cts.inc.php");

// Adaptation du catalog pour boutique
$GLOBALS["entitiescatalog"]["typeproduit"]   = array ( "id_typeprod", "nom_typeprod", "typeprod.png", "boutique-utbm/", "boutiqueut_type_produit");
$GLOBALS["entitiescatalog"]["produit"]       = array ( "id_produit", "nom_prod", "produit.png", "boutique-utbm/", "boutiqueut_produits" );
$GLOBALS['entitiescatalog']['facture']       = array ('id_facture', 'id_facture', 'emprunt.png', 'boutique-utbm/suivi.php', 'boutiqueut_debitfacture', "boutiqueut_debitfacture");
/**
 * Version spécialisée de site pour boutique
 */
class boutique extends site
{
  /* une variable panier (tableau contenant les articles) */
  var $cart;
  /* une variable total */
  var $total;
  /* une variable pour la css */
  var $css = 'themes/boutiqueutbm/css/boutique.css';

  function boutique ()
  {
   global $topdir;
    $this->site();
    if(!isset($_REQUEST["domain"]) || $_SERVER["SCRIPT_NAME"]!='/boutique-utbm/connect.php')
    {
print_r('beh');
      $this->allow_only_logged_users();
      if($this->user->type != "srv" && !$this->user->is_in_group("gestion_ae") && !$this->user->is_in_group("adminboutiqueutbm"))
        $this->error_forbidden();
    }

    $this->set_side_boxes("left",array());
    $this->set_side_boxes("right",array());

    if ( $this->get_param("closed.boutiqueutbm",false) && !$this->user->is_in_group("root")  )
      $this->fatal_partial("services");

    $this->tab_array = array(
       array("accueil", "index.php", "Accueil")/*,
       array ("pannier", "cart.php", "Pannier"),
       array("commandes", "suivi.php", "Commandes")*/
    );

  }

  function start_page ($section, $title)
  {
    global $topdir;

    if ( $this->cart == null && isset($_SESSION['boutique_cart']))
      $this->load_cart();


    $categories = $this->get_cat ();

    /* demarrage normal de la page */
    parent::start_page("boutique",'Boutique UTBM');
  }

  /* chargement du panier de l'utilisateur */
  function load_cart ()
  {
    $this->total = 0;

    if ( !isset($_SESSION['boutique_cart']) || empty($_SESSION['boutique_cart']) )
      return;

    foreach ( $_SESSION['boutique_cart'] as $id => $count)
    {
      $prod = new produit ($this->db);
      $prod->load_by_id ($id);
      if ( $prod->is_valid() )
      {
        $this->cart[] = $prod;
        $this->total += ($prod->obtenir_prix(false,$this->user) * $count);
      }
    }

    if ( isset($_SESSION['boutique_locked']) && $_SESSION['boutique_locked'] != $site->user->id )
    {
      // Les verrous ont été posés pour "$_SESSION['boutique_locked']", faut les convertirs pour "$site->user->id"
      // Cela arrive quand un utilisateur se loggue, ou lors d'un changement d'utilisateur
      // cela permet d'authoriser le remplissage du panier si non connecté

      $vp = new venteproduit ($this->db, $this->dbrw);
      $prev_user = new utilisateur($this->db);
      $prev_user->load_by_id($_SESSION['boutique_locked']);

      foreach ( $this->cart as $prod )
      {
        $vp->charge($prod,$this->comptoir);
        $vp->debloquer ($prev_user,$_SESSION['boutique_cart'][$item->id]);
        $vp->bloquer ($this->user,$_SESSION['boutique_cart'][$item->id]);
      }

      $_SESSION['boutique_locked'] = $site->user->id;
    }

  }

  /* vidange du panier de l'utilisateur */
  function empty_cart ()
  {
    if ($this->cart == null)
      $this->load_cart();

    /* si toujours vide, on sort */
    if ($this->cart == null)
      return;

    /* else */
    foreach ($this->cart as $item)
    {
      $vp = new venteproduit ($this->db, $this->dbrw);
      $vp->load_by_id ($item->id, CPT_E_BOUTIC);
      $vp->debloquer ($this->user,$_SESSION['boutique_cart'][$item->id]);
    }
    unset($_SESSION['boutique_cart']);
    unset($_SESSION['boutique_locked']);
    $this->cart=null;
  }

  /* ajout d'un article dans le panier de l'utilisateur */
  function add_item ($item)
  {
    $vp = new venteproduit ($this->db, $this->dbrw);
    $ret = $vp->load_by_id ($item, CPT_E_BOUTIC);

    if ($ret == false)
      return false;

    if ( !$vp->produit->can_be_sold($this->user) )
      return;

    $_SESSION['boutique_locked'] = $site->user->id;

    $ret = $vp->bloquer($this->user);

    if ($ret == true)
      $_SESSION['boutique_cart'][$item] += 1;

    return $ret;
  }


  /*
   * Recuperation des articles d'une categorie donnee en argument
   *
   * @param (optionnel) cat la categorie concernee
   * @return false si echec, un tableau si succes
   *
   */
  function get_items_by_cat($cat = null)
  {
    if ($cat)
      $cat = intval($cat);

    /* note sur la requete : 3 designe le comptoir boutique */
    /*
    $sql = "SELECT   `boutiqueut_comptoir`.*
                   , `boutiqueut_produits`.*
                   , `boutiqueut_type_produit`.*

    */
    $sql = "SELECT  `boutiqueut_produits`.*
                   , `boutiqueut_type_produit`.`nom_typeprod`

            FROM  `boutiqueut_produits`
            INNER JOIN `boutiqueut_type_produit` USING (`id_typeprod`)

            WHERE `boutiqueut_produits`.`prod_archive` = 0
            AND (`boutiqueut_produits`.date_fin_produit > NOW() OR `boutiqueut_produits`.date_fin_produit IS NULL)
            AND id_produit_parent IS NULL";

    if ($cat)
      $sql.=" AND `boutiqueut_produits`.`id_typeprod` = $cat";
    $sql .= " ORDER BY `boutiqueut_produits`.`id_typeprod` ASC";

    $req = new requete ($this->db, $sql);
    if (!$req->lines)
      return false;


    for ($i = 0; $i < $req->lines; $i++)
    {
      /* si une categorie precise est fournie */
      if ($cat != null)
        $tab[] = $req->get_row ();
      /* sinon on ordonne le tableau resultant
       * par categorie                         */
      else
      {
        $rs = $req->get_row ();
        $tab[$rs['nom_typeprod']][] = $rs;
      }
    }
    return $tab;
  }

  /* fonction de recuperation des categories */
  function get_cat ()
  {
    $except="";
    if(!$this->user->ae)
      $except =" AND id_typeprod!=11 ";
    $sql = "SELECT   `boutiqueut_type_produit`.`id_typeprod`
                   , `boutiqueut_type_produit`.`nom_typeprod`
                   , `boutiqueut_type_produit`.`id_file`
                   , `boutiqueut_type_produit`.`description_typeprod`

            FROM  `boutiqueut_produits`
            INNER JOIN `boutiqueut_type_produit` USING (`id_typeprod`)
            WHERE (`boutiqueut_produits`.date_fin_produit > NOW() OR `boutiqueut_produits`.date_fin_produit IS NULL)
                 AND `boutiqueut_produits`.`stock_global_prod`!=0
            $except
            GROUP BY `id_typeprod`
            ORDER BY `id_typeprod`";

    $req = new requete ($this->db, $sql);
    if (!$req->lines)
      return false;


    for ($i = 0; $i < $req->lines; $i++)
      $cat[] = $req->get_row();

    return $cat;
  }
}

?>
