<?
/**
 * @brief La classe e-boutic permettant le design du site de la
 * boutique en ligne.
 *
 */

/* Copyright 2006
 *
 * Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
 *
 * Ce fichier fait partie du site de l'Association des �tudiants de
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


/* definit l'état de e-boutic :
 *
 * false si en test (rien ne passe alors par le circuit bancaire)
 * true  si en production (tout est sérieux);
 */
define ("STO_PRODUCTION", true );
/*
 * identifiant du comptoir e-boutic dans les tables MySQL
 */
define ("CPT_E_BOUTIC", 3);
/*
 * Depense minimale avant autorisation depense par carte bancaire par
 * sogenactif **en centimes d'euro**.
 */
define ("EB_TOT_MINI_CB", 500);


class eboutic extends site
{
  /* une variable panier (tableau contenant les articles) */
  var $cart;
  /* une variable total */
  var $total;

  function eboutic ()
  {
    $this->site();
    $this->set_side_boxes("left",array("e-boutic"));
    $this->set_side_boxes("right",array("Panier"));
    $this->add_css ("css/mmt.css");
  }

  function start_page ($section, $title)
  {
    global $topdir;
  	if ( $this->cart == null && isset($_SESSION['eboutic_cart']))
      $this->load_cart ($_SESSION['eboutic_cart']);

    /*** boite e-boutic */
    $eb_box = new contents("e-boutic");

    /* A etudier : n'autorisons l'acces e-boutic qu'aux cotisants ? */
    if ($this->user->id > 0)
      {
	/* rayons */
	$categories = $this->get_cat ();
	$eb_box->puts ("<h3>Rayons disponibles</h3>
                        <ul>\n");


	foreach ($categories as $c)
	    $eb_box->puts ("<li><a href=\"./?cat=".
			   $c['id_typeprod'].
			   "\">".
			   $c['nom_typeprod'].
			   "</a></li>\n");

	$eb_box->puts ("</ul>\n\n");

	/* options diverses */
	$eb_box->puts ("<h3>Autres options</h3>
                        <ul>\n");
	$eb_box->puts ("<li><a href=\"./prev_ord.php\">".
		       "Mes commandes passees</a></li>\n");
	if ($this->user->is_in_group("gestion_ae"))
	  $eb_box->puts ("<li><a href=\"".$topdir."comptoir/admin.php?id_comptoir=3\">".
			 "Administration E-boutic</a></li>\n");
	$eb_box->puts ("<ul>\n");
      }

    $this->add_box ("e-boutic", $eb_box);


    /*** boite laterale du panier */
    $caddie = new contents ("Panier");

    if ($this->cart != null)
      {
	$item_lst = new itemlist("Articles");

	foreach ($this->cart as $item)
	  {
	    $qte = $_SESSION['eboutic_cart'][$item->id];
	    if ($this->user->ae && $item->prix_vente_cotisant > 0  && $this->user->ae )
	    {
	      $req = new requete($this->db,
	             "SELECT *
		      FROM `cpt_vendu_cotisant` 
		      WHERE `id_utilisateur` = ".$this->user->id."
		      AND `id_produit` =".$item->id."");
	      if ( $req->lines == 0 )
	      {
	        $item_lst->add($item->nom ."(AE) x 1 <b>".
		               sprintf("%.2f Euros", $item->prix_vente_cotisant / 100)
			       . "</b>");
		if ($qte > 1)
		{
		  $qte = $qte-1;
		  $item_lst->add($item->nom ." x $qte <b>".
		                 sprintf("%.2f Euros", $item->prix_vente / 100)
		        	 . "</b>");
	        }
	      }
	      else
	        $item_lst->add($item->nom ." x $qte <b>".
		               sprintf("%.2f Euros-", $item->prix_vente / 100)
			       . "</b>");
	    }
	    else
	      $item_lst->add($item->nom ." x $qte <b>".
			     sprintf("%.2f Euros", $item->prix_vente / 100)
			     . "</b>");
	  }

	$caddie->add ($item_lst);
	$caddie->add_paragraph ("<b>TOTAL : " .
				sprintf("%.2f Euros</b>",$this->total / 100));

	$caddie->add_paragraph ("<a href=\"./?act=empty_cart\">".
				"Vider le panier</a>");

	$caddie->add_paragraph ("<a href=\"./cart.php\">".
				"Passer la commande</a>");

      }
    /* panier vide */
    else
      $caddie->add_paragraph("<b>Votre panier est actuellement vide.</b><br/><br/><br/><br/>");

    $this->add_box ("Panier", $caddie);




    /* demarrage normal de la page */
        parent::start_page("services",$title);
  }


  /* chargement du panier de l'utilisateur */
  function load_cart ($array)
  {
    $this->total = 0;
    
	  if(!empty($array))
		{
      foreach ($array as $id => $count)
      {
        $prod = new produit ($this->db);
	      $id = intval($id);
	      $prod->load_by_id ($id);
	      if ($id > 0)
	      {
	        $this->cart[] = $prod;
	        if ($this->user->ae && $prod->prix_vente_cotisant > 0 )
	        {
	          $req = new requete($this->db,
	             "SELECT *
		            FROM `cpt_vendu_cotisant`
		            WHERE `id_utilisateur` = ".$this->user->id."
		            AND `id_produit` =".$prod->id."");
	          if ( $req->lines == 0 )
	          {
	            $this->total += $prod->prix_vente_cotisant;
		          if ($count > 1)
		          {
		            $count=$count-1;
		            $this->total += ($prod->prix_vente * $count);
		          }
	          }
	          else
	          $this->total += ($prod->prix_vente * $count);
	        }
	        else
	          $this->total += ($prod->prix_vente * $count);
	      }
      }
		}
  }
  /* vidange du panier de l'utilisateur */
  function empty_cart ()
  {
    if ($this->cart == null)
      $this->load_cart($_SESSION['eboutic_cart']);
    /* si toujours vide, on sort */
    if ($this->cart == null)
      return;
    /* else */
    foreach ($this->cart as $item)
      {
	$vp = new venteproduit ($this->db, $this->dbrw);
	$vp->load_by_id ($item->id, CPT_E_BOUTIC);
	$vp->debloquer ($this->user,
			$_SESSION['eboutic_cart'][$item->id]);
      }
    unset($_SESSION['eboutic_cart']);
    $this->cart=null;
  }

  /* ajout d'un article dans le panier de l'utilisateur */
  function add_item ($item)
  {
    $vp = new venteproduit ($this->db, $this->dbrw);
    $ret = $vp->load_by_id ($item, CPT_E_BOUTIC);
    if ($ret == false)
      return false;
    
	if ( $cl = $vp->produit->get_prodclass() )
	{
		if ( !$cl->can_be_sold($this->user) )	
			return false;
			
		if ($this->cart == null)
			$this->load_cart($_SESSION['eboutic_cart']);	
		
		if(!empty($this->cart))
		{
		  foreach ($this->cart as $prod)
		  {
			  $cl2 = $prod->get_prodclass();
			  if ( $cl2 && !$cl->is_compatible($cl2) )
			  	return false;
		  }
		}
	}
        if ($this->user->ae && $vp->produit->prix_vente_cotisant > 0 )
	{
	  $req = new requete($this->db,
	                     "SELECT *
		              FROM `cpt_vendu_cotisant`
			      WHERE `id_utilisateur` = ".$this->user->id."
			      AND `id_produit` =".$vp->produit->id."");
	  $req2 = new requete($this->db,
	                     "SELECT *
			     FROM `cpt_verrou`
			     WHERE `id_utilisateur` = ".$this->user->id."
			     AND `id_produit` =".$vp->produit->id."");
	  if ( $req->lines == 0 && $req2->lines == 0)
	  {
	    $ret = $vp->bloquer ($this->user, 1, TRUE);
	  }
	  else
	    $ret = $vp->bloquer ($this->user);
	}
	else
	  $ret = $vp->bloquer ($this->user);

    if ($ret == true)
      $_SESSION['eboutic_cart'][$item] += 1;

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


    /* note sur la requete : 3 designe le comptoir e-boutic */
    /*
    $sql = "SELECT   `cpt_comptoir`.*
                   , `cpt_produits`.*
                   , `cpt_type_produit`.*

    */
    $sql = "SELECT   `cpt_mise_en_vente`.*
                   , `cpt_produits`.*
                   , `cpt_type_produit`.*

            FROM     `cpt_mise_en_vente`
            INNER JOIN `cpt_produits`
                 USING (`id_produit`)
            INNER JOIN `cpt_type_produit`
                 USING (`id_typeprod`)

            WHERE `cpt_mise_en_vente`.`id_comptoir` = 3
            AND `cpt_produits`.`prod_archive` = 0";

    if ($cat)
      $sql.=" AND `cpt_produits`.`id_typeprod` = $cat";
    $sql .= " ORDER BY `cpt_produits`.`id_typeprod` ASC";

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
    $sql = "SELECT   `cpt_type_produit`.`id_typeprod`
                   , `cpt_type_produit`.`nom_typeprod`
									 , `cpt_type_produit`.`url_logo_typeprod`
									 , `cpt_type_produit`.`description_typeprod`

            FROM     `cpt_mise_en_vente`
            INNER JOIN `cpt_produits`
                 USING (`id_produit`)
            INNER JOIN `cpt_type_produit`
                 USING (`id_typeprod`)

            WHERE `cpt_mise_en_vente`.`id_comptoir` = 3

            GROUP BY `id_typeprod`
            ORDER BY `id_typeprod`";

    $req = new requete ($this->db, $sql);
    if (!$req->lines)
      return false;


    for ($i = 0; $i < $req->lines; $i++)
      $cat[] = $req->get_row();

    return $cat;
  }
  /* fonction de determination de l'achat (si rechargement compte AE)
   * utilisation : si on recharge son compte AE lors d'un achat,
   * le paiement par carte AE devient debile ...
   */
  function is_reloading_AE ()
  {
    foreach ($this->cart as $item)
      {
	/* 11 = categorie rechargement compte AE */
	if ($item->id_type == 11)
	  return true;
      }
    return false;
  }
}


?>
