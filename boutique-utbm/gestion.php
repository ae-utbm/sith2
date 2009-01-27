<?php
/**
 * @brief Admin de la boutique utbm
 *
 */

/* Copyright 2008
 *
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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
require_once($topdir . "include/entities/folder.inc.php");
require_once($topdir . "include/entities/files.inc.php");
require_once($topdir . "include/cts/taglist.inc.php");

$GLOBALS["entitiescatalog"]["typeproduit"]   = array ( "id_typeprod", "nom_typeprod", "typeprod.png", "boutique-utbm/admin.php", "boutiqueut_type_produit");
$GLOBALS["entitiescatalog"]["produit"]       = array ( "id_produit", "nom_prod", "produit.png", "boutique-utbm/admin.php", "boutiqueut_produits" );

function generate_subform_stock ( $nom,$form_n, $stock_n, $stock_value_n, $stock = -1 )
{

 $subfrm=new form ($form_n,false,false,false,$nom);

 $subfrm1=new form ($stock_n,false,false,false,"Non limité");
 $subfrm->add($subfrm1,false,true,($stock==-1),"nlim",true);

 $subfrm2=new form ($stock_n,false,false,false,"Limité à");
 $subfrm2->add_text_field($stock_value_n,"",($stock==-1)?"":$stock);
 $subfrm->add($subfrm2,false,true,($stock!=-1),"lim",true);

 return $subfrm;
}


$site = new boutique();
if(!$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$file = new dfile($site->db, $site->dbrw);
$folder = new dfolder($site->db, $site->dbrw);
$folder->load_by_id(FOLDERID);
$file = new dfile($site->db, $site->dbrw);
$typeprod = new typeproduit($site->db,$site->dbrw);
$produit = new produit($site->db,$site->dbrw);
$produit_parent = new produit($site->db);

if ( isset($_REQUEST["id_typeprod"]) )
 $typeprod->load_by_id($_REQUEST["id_typeprod"]);
if ( isset($_REQUEST["id_produit"]) )
 $produit->load_by_id($_REQUEST["id_produit"]);

if ( $_REQUEST["page"] == "statistiques" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Statistiques");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "factures" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Factures");
  $req = new requete($site->db,
         "SELECT ".
         "IF(f.id_utilisateur=0, CONCAT(u.prenom_utl,' ',u.nom_utl), CONCAT(f.prenom,' ',f.nom)) AS nom_utilisateur ".
         ", IF(f.id_utilisateur=0, -1, f.id_utilisateur ".
         ", f.date_facture ".
         ", IF(f.mode_paiement='UT', 'Boutique', IF(f.mode_paiement='CH','Chèque','Liquide')) AS mode ".
         ", f.id_facture ".
         ", IF(f.ready=1,IF(f.etat=1,'à retirer','retiré'),'en préparation') AS avancement ".
         "FROM boutiqueut_debitfacture f ".
         "LEFT JOIN utilisateurs u USING(id_utilisateur) ".
         "ORDER BY f.id_facture DESC");
  $cts->add(new sqltable(
         "factures",
         "Factures",
         $req,
         "admin_gen_fact.php",
         "id_facture",
         array('date_facture'=>'Date',"nom_utilisateur" => "Utilisateur","mode"=>"Mode de paiement","avancement"=>"État"),
         array('info'=>'Détail'),
         array(),
         array()),
         true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "ventes" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"gestion.php\">Gestion</a> / Ventes");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}

$site->add_css("css/d.css");
$site->start_page('adminbooutique',"Administration");
$cts = new contents("Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Gestion");
$lst = new itemlist("");
$lst->add("<a href=\"gestion.php?page=statistiques\">Statistiques</a>");
$lst->add("<a href=\"gestion.php?page=factures\">Factures</a>");
$lst->add("<a href=\"gestion.php?page=ventes\">Ventes</a>");
$cts->add($lst,true);
$site->add_contents($cts);
$site->end_page();

?>
