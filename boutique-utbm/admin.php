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

if(!is_dir("/var/www/ae/www/ae2/var/files"))
  $site->fatal_partial("fichiers");

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

if ( $_REQUEST["action"] == "addfile" )
{
  if ( !$_REQUEST["nom"] )
  {
    $_REQUEST["page"] = "newfile";
    $ErreurAjout="Veuillez préciser un nom pour le fichier.";
  }
  elseif( !is_uploaded_file($_FILES['file']['tmp_name']) || ($_FILES['file']['error'] != UPLOAD_ERR_OK ) )
  {
    $_REQUEST["page"] = "newfile";
    $ErreurAjout="Erreur lors du transfert.";
  }
  else
  {
    $file->herit($folder);
    $file->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin']);
    $file->add_file ( $_FILES["file"], $_REQUEST["nom"], $folder->id, $_REQUEST["description"],null );
    $file->set_tags($_REQUEST["tags"]);
  }
}
elseif ( $_REQUEST["action"] == "addtype" )
{
  $file->load_by_id($_REQUEST["id_file"]);

  $typeprod->ajout( $_REQUEST["nom"], $file->id,$_REQUEST["description"] );

  $site->log("Ajout d'un type de produit","Ajout du type de produit \"".$_REQUEST["nom"]."\" (".$_REQUEST["description"].")","Comptoirs",$site->user->id);
}
elseif ( $_REQUEST["action"] == "addproduit" )
{
  $stock_global = -1;
  $file->load_by_id($_REQUEST["id_file"]);
  $produit_parent->load_by_id($_REQUEST["id_produit_parent"]);
  if ( $_REQUEST["stock"] == "lim" )
    $stock_global = $_REQUEST["stock_value"];
  if ( $produit->ajout ($typeprod->id,
    $_REQUEST['nom'],
    $_REQUEST['prix_vente_prod_service'],
    $_REQUEST['prix_vente'],
    $_REQUEST['prix_achat'],
    $stock_global,
    $file->id,
    $_REQUEST["description"],
    $_REQUEST["description_longue"],

    $_REQUEST["id_groupe"],
    $_REQUEST["date_fin"],

    $produit_parent->id
    ))
  {
  }
}
else if ( $_REQUEST["action"] == "upproduit" && ($produit->id > 0) && ($typeprod->id > 0) )
{
  $stock_global = -1;
  $file->load_by_id($_REQUEST["id_file"]);
  $produit_parent->load_by_id($_REQUEST["id_produit_parent"]);
  if ( $_REQUEST["stock"] == "lim" )
    $stock_global = $_REQUEST["stock_value"];
  $produit->modifier ($typeprod->id,
    $_REQUEST['nom'],
    $_REQUEST['prix_vente_prod_service'],
    $_REQUEST['prix_vente'],
    $_REQUEST['prix_achat'],
    $stock_global,
    $file->id,
    $_REQUEST["description"],
    $_REQUEST["description_longue"],
    $_REQUEST["date_fin"],
    $produit_parent->id
    );
}
else if ( $_REQUEST["action"] == "uptype" && ($typeprod->id > 0) )
{
  $file->load_by_id($_REQUEST["id_file"]);

  $typeprod->modifier( $_REQUEST["nom"], $file->id, $_REQUEST["description"] );
}


if ( $_REQUEST["page"] == "newfile" )
{
  $folder->droits_acces |= 0x200;
  $site->start_page($section,"Fichiers");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / Ajouter un fichier");

  $frm = new form("addfile","admin.php?id_folder=".$folder->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addfile");
  $frm->add_hidden("rights_id_group_admin",7);
  $frm->add_hidden("rights_id_group",7);
  $frm->add_hidden("__rights_lect","273");
  $frm->add_hidden("__rights_ecrt","544");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_file_field("file","Fichier",true);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_text_area("description","Description","");
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif( $_REQUEST["page"] == "newcmd" )
{
   $req = new requete($site->db,
    "SELECT `boutiqueut_produits`.`nom_prod`, `boutiqueut_produits`.`id_produit`," .
    "`boutiqueut_produits`.stock_global_prod, " .
    "`boutiqueut_produits`.prix_vente_prod/100 AS prix_vente_prod " .
    "FROM `boutiqueut_produits` " .
    "INNER JOIN `boutiqueut_type_produit` ON `boutiqueut_type_produit`.`id_typeprod`=`boutiqueut_produits`.`id_typeprod` " .
    "WHERE prod_archive != 1 " .
    "ORDER BY `boutiqueut_type_produit`.`nom_typeprod`,`boutiqueut_produits`.`nom_prod`");
  if(isset($_REQUEST['action']))
  {
    if($_REQUEST['action']=="newcmd" && trim($_REQUEST['nom'])!='' && trim($_REQUEST['prenom'])!='')
    {
      $site->start_page("services","Administration");
      $cts = new contents("<a href=\"admin.php\">Administration</a> / Enregistrer une commande");
      $frm = new form ("addtype","admin.php",false,"POST","Enregistrer une commande (PAS POUR LES SERVICES!)");
      $frm->allow_only_one_usage();
      $frm->add_hidden("page","newcmd");
      $frm->add_hidden("action","validercmd");
      $frm->add_text_field("nom","Nom :",$_REQUEST['nom'],true);
      $frm->add_text_field("prenom","Prenom",$_REQUEST['prenom'],true);
      $frm->add_text_area("adresse","Adresse",$_REQUEST['adresse']);
      $sum=0;
      while(list($nom_prod,$id_produit,$stock_global_prod,$prix)=$req->get_row())
      {
        $_prix=sprintf("%.2f Euros",$prix);
        $frm->add_hidden("max_idprod".$id_produit,$stock_global_prod);
        if(isset($_REQUEST['prod'][$id_produit]) && intval($_REQUEST['prod'][$id_produit])!=0)
        {
          $frm->add_text_field("prod[$id_produit]","<b>$nom_prod</b>",intval($_REQUEST['prod'][$id_produit]),false,false,true,true,$_prix);
          $sum=$sum+($prix*intval($_REQUEST['prod'][$id_produit]));
        }
      }
      $frm->add_info('<b>Total : '.sprintf("%.2f Euros",$sum).'</b>');
      if($sum>0)
        $frm->add_submit("save","Valider");
      $frm->add_submit("save","Annuler");
      $cts->add($frm,true);
      $site->add_contents($cts);
      $site->end_page();
      exit();

    }
    elseif($_REQUEST['action']=="validercmd" && $_REQUEST['save']=='Valider')
    {
      $debfact = new debitfacture ($site->db, $site->dbrw);
      foreach ($_REQUEST['prod'] as $id=>$nb)
      {
        $vp = new venteproduit ($site->db, $site->dbrw);
        if($vp->load_by_id ($id))
          $cpt_cart[] = array($nb, $vp);
      }
      $client=new utilisateur($site->db);
      $client->id=-1;
      $debfact->debit ($client,$cpt_cart,0,convertir_nom($_REQUEST['nom']),convertir_prenom($_REQUEST['prenom']),$_REQUEST['adresse']);
      if($debfact->is_valid())
        $info='<script language="javascript" type="text/javascript">newwindow=window.open(\'suivi.php?id_facture='.$debfact->id.'&gen_pdf=1\',\'facture\',\'height=500,width=300\');</script>';
    }
  }
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / Enregistrer une commande");
  if(isset($info))
  $cts->add_paragraph($info);
  $frm = new form ("addtype","admin.php",false,"POST","Enregistrer une commande (PAS POUR LES SERVICES!)");
  $frm->allow_only_one_usage();
  $frm->add_hidden("page","newcmd");
  $frm->add_hidden("action","newcmd");
  $frm->add_text_field("nom","Nom :","",true);
  $frm->add_text_field("prenom","Prenom","",true);
  $frm->add_text_area("adresse","Adresse");
  while(list($nom_prod,$id_produit,$stock_global_prod,$prix)=$req->get_row())
  {
    $prix=sprintf("%.2f Euros",$prix);
    $frm->add_hidden("max_idprod".$id_produit,$stock_global_prod);
    $frm->add_text_field("prod[$id_produit]","<b>$nom_prod</b>","",false,false,true,true,$prix);
  }
//liste des articles et leur nombre
//affichage du total en temps réel ?

  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $_REQUEST["page"] == "addtype" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin.php?page=produits\">Produits</a> / Ajouter un type de produit");
  $frm = new form ("addtype","admin.php",true,"POST","Ajout d'un type de produit");
  $frm->add_hidden("action","addtype");
  $frm->add_text_field("nom","Nom du type","",true);
  $frm->add_entity_smartselect("id_file","Image",$file,true,false,array('id_folder'=>FOLDERID));
  $frm->add_text_area("description","Description");
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
elseif ( $_REQUEST["page"] == "addproduit" )
{
  $site->start_page("services","Administration");
  $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin.php?page=produits\">Produits</a> / Ajouter un produit");
  $frm = new form ("addproduit","admin.php",true,"POST","Ajout d'un produit");
  $frm->add_hidden("action","addproduit");
  $frm->add_entity_select("id_typeprod", "Type", $site->db, "typeproduit");
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_entity_smartselect("id_file","Image",$file,true);
  $frm->add_entity_smartselect("id_produit_parent","Produit parent",$produit_parent,true);
  $frm->add_text_area("description","Résumé");
  $frm->add_text_area("description_longue","Description");
  $frm->add_price_field("prix_vente_prod_service","Prix services",0,true);
  $frm->add_price_field("prix_vente","Prix autre",0,true);
  $frm->add_price_field("prix_achat","Prix achat",0,true);
  $frm->add_datetime_field("date_fin","Date de fin de mise en vente");
  $frm->add(generate_subform_stock("Stock global","global","stock","stock_value",-1),false, false, false,false, true);
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
if ( ereg("^settypeprod=([0-9]*)$",$_REQUEST["action"],$regs) )
{
  $typeprod->load_by_id( $regs[1]);
  foreach($_REQUEST["id_produits"] as $id)
  {
    $produit->load_by_id($id);
    if ( $produit->is_valid() )
        $produit->modifier_typeprod ($typeprod->id);
  }
  $_REQUEST["page"] = "produits";
}
if ( $_REQUEST["page"] == "produits" )
{


  $batch = array();

 $req = new requete($site->db,
  "SELECT `id_typeprod`,`nom_typeprod` " .
  "FROM `boutiqueut_type_produit`  " .
  "ORDER BY `nom_typeprod`");

 while ( $row = $req->get_row() )
   $batch["settypeprod=".$row['id_typeprod']] = "Modifier le type pour ".$row['nom_typeprod'];


 $site->start_page("services","Administration");
 $cts = new contents("<a href=\"admin.php\">Administration</a> / Produits");

 $req = new requete($site->db,
  "SELECT `boutiqueut_produits`.`nom_prod`, `boutiqueut_produits`.`id_produit`," .
  "`boutiqueut_produits`.stock_global_prod, `boutiqueut_produits`.prix_vente_prod_service/100 AS prix_vente_prod_service," .
  "`boutiqueut_produits`.prix_vente_prod/100 AS prix_vente_prod, `boutiqueut_produits`.prix_achat_prod/100 AS  prix_achat_prod, " .
  "`boutiqueut_type_produit`.`id_typeprod`,`boutiqueut_type_produit`.`nom_typeprod` " .
  "FROM `boutiqueut_produits` " .
  "INNER JOIN `boutiqueut_type_produit` ON `boutiqueut_type_produit`.`id_typeprod`=`boutiqueut_produits`.`id_typeprod` " .
  "WHERE prod_archive != 1 " .
  "ORDER BY `boutiqueut_type_produit`.`nom_typeprod`,`boutiqueut_produits`.`nom_prod`");

 $tbl = new sqltable(
   "lstproduits",
   "Produits (hors archivés)", $req, "admin.php",
   "id_produit",
   array(
   "nom_typeprod"=>"Type",
   "nom_prod"=>"Nom du produit",
   "prix_vente_prod_service"=>"Prix service",
   "prix_vente_prod"=>"Prix de vente",
   "prix_achat_prod"=>"Prix d'achat",
   "stock_global_prod"=>"Stock global"
   ),
   array("edit"=>"Editer"), $batch, array()
   );

 $cts->add($tbl,true);

 $req = new requete($site->db,
  "SELECT `id_typeprod`,`nom_typeprod` " .
  "FROM `boutiqueut_type_produit`  " .
  "ORDER BY `nom_typeprod`");

 $tbl = new sqltable(
   "lsttypeproduits",
   "Types de produits", $req, "admin.php",
   "id_typeprod",
   array(
   "nom_typeprod"=>"Type"
   ),
   array("edit"=>"Editer"), array(), array()
   );
 $cts->add($tbl,true);
 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $produit->id > 0 )
{
 $typeprod->load_by_id($produit->id_type);

 $site->start_page("services","Administration");
 $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin.php?page=produits\">Produits</a> / ".$typeprod->get_html_link()." / ".$produit->get_html_link());

 $cts->add_paragraph("<a href=\"compta.php?id_produit=".$produit->id."\">Comptabilité</a>");

 $frm = new form ("upproduit","admin.php",false,"POST","Editer");
 $frm->add_hidden("action","upproduit");
 $frm->add_hidden("id_produit",$produit->id);
 $frm->add_entity_select("id_typeprod", "Type", $site->db, "typeproduit",$produit->id_type);
 $frm->add_text_field("nom","Nom",$produit->nom,true);

 $file->load_by_id($produit->id_file);
 $frm->add_entity_smartselect("id_file","Image",$file,true);

 $produit_parent->load_by_id($produit->id_produit_parent);
 $frm->add_entity_smartselect("id_produit_parent","Produit parent",$produit_parent,true);

 $frm->add_text_area("description","Résumé",$produit->description);
 $frm->add_text_area("description_longue","Description",$produit->description_longue);

 $frm->add_price_field("prix_vente_prod_service","Prix service",$produit->prix_vente_service,true);
 $frm->add_price_field("prix_vente","Prix vente",$produit->prix_vente,true);
 $frm->add_price_field("prix_achat","Prix achat",$produit->prix_achat,true);

 $frm->add_datetime_field("date_fin","Date de fin de mise en vente",$produit->date_fin);

 $frm->add(generate_subform_stock("Stock global","global","stock","stock_value",$produit->stock_global),false, false, false,false, true);
 $frm->add_submit("valid","Enregistrer");
 $cts->add($frm,true);

 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $typeprod->id > 0 )
{
  if ( $_REQUEST["action"] == "arch" )
  {
    foreach($_REQUEST["id_produits"] as $id)
    {
      $produit->load_by_id($id);
      if ( $produit->is_valid() )
         $produit->archiver();
    }
  }
  elseif ( $_REQUEST["action"] == "unarch" )
  {
    foreach($_REQUEST["id_produits"] as $id)
    {
      $produit->load_by_id($id);
      if ( $produit->is_valid() )
         $produit->dearchiver();
    }
  }


 $site->start_page("services","Administration");
 $cts = new contents("<a href=\"admin.php\">Administration</a> / <a href=\"admin.php?page=produits\">Produits</a> / ".$typeprod->nom);

 $cts->add_paragraph("<a href=\"compta.php?id_typeprod=".$typeprod->id."\">Comptabilité</a>");


 $frm = new form ("uptype","admin.php",true,"POST","Editer");
 $frm->add_hidden("action","uptype");
 $frm->add_hidden("id_typeprod", $typeprod->id);
 $frm->add_text_field("nom","Nom du type",$typeprod->nom,true);

 $file->load_by_id($typeprod->id_file);
 $frm->add_entity_smartselect("id_file","Image",$file,true);

 $frm->add_text_area("description","Description",$typeprod->description);
 $frm->add_submit("valid","Enregistrer");
 $cts->add($frm,true);

 $req = new requete($site->db,
  "SELECT `boutiqueut_produits`.`nom_prod`, `boutiqueut_produits`.`id_produit`,`boutiqueut_produits`.`prod_archive`, " .
  "`boutiqueut_produits`.stock_global_prod, `boutiqueut_produits`.prix_vente_prod_service/100 AS prix_vente_prod_service," .
  "`boutiqueut_produits`.prix_vente_prod/100 AS prix_vente_prod, `boutiqueut_produits`.prix_achat_prod/100 AS  prix_achat_prod, " .
  "`boutiqueut_type_produit`.`id_typeprod`,`boutiqueut_type_produit`.`nom_typeprod` " .
  "FROM `boutiqueut_produits` " .
  "INNER JOIN `boutiqueut_type_produit` USING(`id_typeprod`) " .
  "WHERE `boutiqueut_produits`.`id_typeprod`='".$typeprod->id."' " .
  "ORDER BY `boutiqueut_type_produit`.`nom_typeprod`,`boutiqueut_produits`.`nom_prod`");
 $tbl = new sqltable(
   "lstproduits",
   "Produits", $req, "admin.php?id_typeprod=".$typeprod->id,
   "id_produit",
   array(
   "nom_typeprod"=>"Type",
   "nom_prod"=>"Nom du produit",
   "prix_vente_prod_service"=>"Prix service",
   "prix_vente_prod"=>"Prix de vente",
   "prix_achat_prod"=>"Prix d'achat",
   "stock_global_prod"=>"Stock global",
   "prod_archive"=>"Archivé"
   ),
   array("edit"=>"Editer"), array("arch"=>"Archiver","unarch"=>"Desarchiver"), array("prod_archive"=>array(0=>"non",1=>"oui"))
   );

 $cts->add($tbl,true);

 $site->add_contents($cts);
 $site->end_page();
 exit();
}


$site->add_css("css/d.css");
$site->start_page('adminbooutique',"Administration");
$cts = new contents("Administration");
$lst = new itemlist("Gestion des produits");
$lst->add("<a href=\"admin.php?page=addproduit\">Ajouter un produit</a>");
$lst->add("<a href=\"admin.php?page=addtype\">Ajouter un type de produit</a>");
$lst->add("<a href=\"admin.php?page=produits\">Liste des produits et des types de produits</a>");
$lst->add("<a href=\"admin.php?page=newfile\">Ajouter un fichier</a>");
$lst->add("<a href=\"admin.php?page=newcmd\">Enregistrer une commande</a>");
$cts->add($lst,true);
$site->add_contents($cts);
$site->end_page();

?>
