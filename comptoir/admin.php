<?php

/* Copyright 2005,2006
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

$topdir="../";
require_once("include/comptoirs.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/salle.inc.php");
require_once($topdir. "include/entities/files.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/group.inc.php");

$site = new sitecomptoirs();
// Session requise
if ( !$site->user->is_valid() )
 $site->error_forbidden();

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

// Recherchons ce que l'utilisteur peut administrer
$site->fetch_admin_comptoirs();

if ( !count($site->admin_comptoirs) && !$site->user->is_in_group("gestion_ae") )
 $site->error_forbidden();

$site->set_admin_mode();

// Charge les objets
$comptoir = new comptoir($site->db,$site->dbrw);
$assocpt = new assocpt($site->db,$site->dbrw);
$typeprod = new typeproduit($site->db,$site->dbrw);
$produit = new produit($site->db,$site->dbrw);
$produit_parent = new produit($site->db);

$venteproduit = new venteproduit($site->db,$site->dbrw);
$salle = new salle($site->db);
$file = new dfile($site->db);
if ( isset($_REQUEST["id_comptoir"]) )
{
 $comptoir->load_by_id($_REQUEST["id_comptoir"]);
 if ( !isset($site->admin_comptoirs[$comptoir->id]) )
  $site->error_forbidden();
}

if ( isset($_REQUEST["id_assocpt"]) )
 $assocpt->load_by_id($_REQUEST["id_assocpt"]);

if ( isset($_REQUEST["id_typeprod"]) )
 $typeprod->load_by_id($_REQUEST["id_typeprod"]);

if ( isset($_REQUEST["id_produit"]) )
 $produit->load_by_id($_REQUEST["id_produit"]);

if ( isset($_REQUEST["id_salle"]) )
 $salle->load_by_id($_REQUEST["id_salle"]);


// Traitement des actions
if ( $_REQUEST["action"] == "addcomptoir" && $site->user->is_in_group("gestion_ae") )
{
 if ( $assocpt->id > 0 )
 {
  $comptoir->ajout( $_REQUEST["nom"], $assocpt->id, $_REQUEST["id_groupe_vendeurs"], $_REQUEST["id_groupe_admins"], $_REQUEST["type"],$salle->id,$_REQUEST["rechargement"] );
  $site->admin_comptoirs[$comptoir->id] = $comptoir->nom;

    $grp_vendeurs = new group ( $site->db);
    $grp_vendeurs->load_by_id($_REQUEST["id_groupe_vendeurs"]);
    $grp_admins = new group ( $site->db);
    $grp_admins->load_by_id($_REQUEST["id_groupe_admins"]);
    _log($site->dbrw,"Ajout d'un comptoir","Ajout du comptoir \"".$comptoir->nom."\" de type ".$comptoir->type.", administré par le groupe ".$grp_admins->nom." (id : ".$grp_admins->id.") et tenu par le groupe ".$grp_vendeurs->nom." (id : ".$grp_vendeurs->id.")","Comptoirs".$site->user);
 }
}
/*
 Ajout d'un type de produit
*/
else if ( $_REQUEST["action"] == "addtype" && ($assocpt->id > 0) )
{
  $file->load_by_id($_REQUEST["id_file"]);

 $typeprod->ajout( $_REQUEST["nom"], $_REQUEST["id_action"], $assocpt->id, $file->id,$_REQUEST["description"] );

  _log($site->dbrw,"Ajout d'un type de produit","Ajout du type de produit \"".$_REQUEST["nom"]."\" (".$_REQUEST["description"].")","Comptoirs",$site->user);
}
/*
 Ajout d'une association
*/
else if ( $_REQUEST["action"] == "addasso" && $site->user->is_in_group("gestion_ae") )
{
 $assocpt->add($_REQUEST["id_asso"]);

  $asso = new asso($site->db);
  $asso->load_by_id($_REQUEST["id_asso"]);
  _log($site->dbrw,"Ajout d'une association","Ajout de l'association ".$asso->nom." (id : ".$asso->id.")","Comptoirs",$site->user);
}
/*
 Ajout d'un produit
*/
else if ( $_REQUEST["action"] == "addproduit" && ($typeprod->id > 0) && ($assocpt->id > 0) )
{
    $stock_global = -1;
    $limite_utilisateur = -1;

  $file->load_by_id($_REQUEST["id_file"]);
  $produit_parent->load_by_id($_REQUEST["id_produit_parent"]);

 if ( $_REQUEST["stock"] == "lim" )
  $stock_global = $_REQUEST["stock_value"];

 if ( $_REQUEST["limite_util"] == "lim" )
  $limite_utilisateur = $_REQUEST["limite_util_value"];

    if ( $produit->ajout ($typeprod->id,
    $assocpt->id,
    $_REQUEST['nom'],
    $_REQUEST['prix_vente_barman'],
    $_REQUEST['prix_vente'],
    $_REQUEST['prix_achat'],
    $_REQUEST['meta'],
    $_REQUEST['id_action'],
    $_REQUEST['code_barre'],
    $stock_global,
    $file->id,
    $_REQUEST["description"],
    $_REQUEST["description_longue"],

    $_REQUEST["a_retirer"],
    $_REQUEST["postable"],
    $_REQUEST["frais_port"],

    $_REQUEST["id_groupe"],
    $_REQUEST["date_fin"],

    $produit_parent->id,
    $_REQUEST["mineur"],
    $limite_utilisateur
    ) )
    {
      $asso = new asso($site->db);
      $asso->load_by_id($assocpt->id);
      _log($site->dbrw,"Ajout d'un produit","Ajout du produit ".$_REQUEST['nom']." (".$_REQUEST["description"].") au profit de ".$asso->nom,"Comptoirs",$site->user);

      foreach( $_REQUEST['cpt'] as $idcomptoir => $on )
      {
      $comptoir->load_by_id($idcomptoir);
      if (($comptoir->id > 0) && ($on == "on") && isset($site->admin_comptoirs[$idcomptoir]) )
      {
       $stock_local = -1;

       if ( $_REQUEST["cpt_stock"][$idcomptoir] == "lim" )
        $stock_global = $_REQUEST["cpt_stock_value"][$idcomptoir];

       $venteproduit->nouveau($produit,$comptoir,$stock_local);
      }
     }
    }
}
/*
 Mise ‡ jour comptoirs d'un produit
*/
else if ( $_REQUEST["action"] == "updcpt" && $produit->id > 0 )
{
 $req = new requete($site->db,
   "SELECT `id_comptoir`
    FROM `cpt_mise_en_vente`
    WHERE `id_produit` = '".intval($produit->id)."'");

 while ( list($id_comptoir) = $req->get_row() )
 {
  if ( $venteproduit->load_by_id($produit->id,$id_comptoir) && isset($site->admin_comptoirs[$id_comptoir]) )
  {
   if ( $_REQUEST['cpt'][$id_comptoir] )
   {
    $stock_local = -1;
    if ( $_REQUEST["cpt_stock"][$id_comptoir] == "lim" )
     $stock_local = $_REQUEST["cpt_stock_value"][$id_comptoir];
    $venteproduit->modifier($stock_local);
   }
   else
    $venteproduit->supprime();
  }
 }
}
/*
 Mise ‡ jour comptoirs d'un produit
*/
else if ( $_REQUEST["action"] == "addnvcpt" && $produit->id > 0 )
{
 foreach( $_REQUEST['nv_cpt'] as $idcomptoir => $on )
 {
  $comptoir->load_by_id($idcomptoir);
  if (($comptoir->id > 0) && $on && isset($site->admin_comptoirs[$idcomptoir]) )
  {
   $stock_local = -1;

   if ( $_REQUEST["nv_stock"][$idcomptoir] == "lim" )
    $stock_local = $_REQUEST["nv_stock_value"][$idcomptoir];

   $venteproduit->nouveau($produit,$comptoir,$stock_local);
  }
 }
}
/*
 Mise ‡ jour d'un produit
*/
else if ( $_REQUEST["action"] == "upproduit" && ($produit->id > 0) && ($typeprod->id > 0) )
{
  $stock_global = -1;
  $limite_utilisateur = -1;

  $file->load_by_id($_REQUEST["id_file"]);

  $produit_parent->load_by_id($_REQUEST["id_produit_parent"]);

  if ( $_REQUEST["stock"] == "lim" )
    $stock_global = $_REQUEST["stock_value"];
  if ( $_REQUEST["limite_util"] == "lim" )
    $limite_utilisateur = $_REQUEST["limite_util_value"];

  $produit->modifier ($typeprod->id,
    $_REQUEST['nom'],
    $_REQUEST['prix_vente_barman'],
    $_REQUEST['prix_vente'],
    $_REQUEST['prix_achat'],
    $_REQUEST['meta'],
    $_REQUEST['id_action'],
    $_REQUEST['code_barre'],
    $stock_global,
    $file->id,
    $_REQUEST["description"],
    $_REQUEST["description_longue"],
    $assocpt->id,
    $_REQUEST["a_retirer"],
    $_REQUEST["postable"],
    $_REQUEST["frais_port"],
    $_REQUEST["id_groupe"],
    $_REQUEST["date_fin"],
    $produit_parent->id,
    $_REQUEST["mineur"],
    $limite_utilisateur
      );
}
else if ( $_REQUEST["action"] == "uptype" && ($typeprod->id > 0) && ($assocpt->id > 0)  )
{
  $file->load_by_id($_REQUEST["id_file"]);

 $typeprod->modifier( $_REQUEST["nom"], $_REQUEST["action"], $assocpt->id, $file->id, $_REQUEST["description"] );
}
else if ( $_REQUEST["action"] == "upcomptoir" &&  ($comptoir->id > 0) && ($assocpt->id > 0) )
{
 $comptoir->modifier( $_REQUEST["nom"], $assocpt->id, $_REQUEST["id_groupe_vendeurs"], $_REQUEST["id_groupe_admins"], $_REQUEST["type"],$salle->id, $_REQUEST["rechargement"] );
}
else if ( $_REQUEST["page"] == "barcodes" && ($comptoir->id > 0) )
{
 header("Content-Type: text/html; charset=utf-8");

  $req = new requete ($site->db,
       "SELECT
       `cpt_produits`.`nom_prod`,
       `cpt_produits`.`cbarre_prod`,
       `cpt_type_produit`.`id_typeprod`,
        `cpt_type_produit`.`nom_typeprod`
        FROM `cpt_produits`
        INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod`
  INNER JOIN `cpt_mise_en_vente` ON `cpt_produits`.`id_produit` = `cpt_mise_en_vente`.`id_produit`
        WHERE `cpt_mise_en_vente`.`id_comptoir` = '".intval($comptoir->id)."'
        ORDER BY `cpt_type_produit`.`nom_typeprod`,`cpt_produits`.`nom_prod`");


 echo "<html><head></head><body>";

echo "<style>

*
{
 font-size: 10pt;
 font-family: sans-serif;
}

.titre
{

 font-size: 14pt;
 font-weight: bold;
}

td
{
 text-align: center;
}

</style>";


 echo "<table>";

 echo "<tr>";
 echo "<td>Valide panier<br/><img src=\"cbar.php?barcode=FIN\" /></td>";
 echo "<td>Annule dernier produit<br/><img src=\"cbar.php?barcode=ANN\" /></td>";
 echo "<td>Annule panier<br/><img src=\"cbar.php?barcode=ANC\" /></td>";
 echo "</tr>";


 $n = 0;

 $prev_type_id = -1;

  while ( list($prod_nom,$code_barre,$type_id,$type_nom) = $req->get_row() )
  {
   if ( $prev_type_id != $type_id )
   {
    if ( $n > 0 ) echo "</tr>";
    echo "<tr><td colspan=\"3\" class=\"titre\">$type_nom</td></tr>";
    $n = 0;
    $prev_type_id = $type_id;
   }
    if ( $n%3 == 0 ) echo "<tr>";

   echo "<td>$prod_nom<br/><img src=\"cbar.php?barcode=$code_barre\" /></td>";


   if ( $n%3 == 2 ) echo "</tr>";
   $n++;
  }

   if ( $n > 0 ) echo "</tr>";

 echo "</table>";

 echo "</body></html>";
 exit();
}

if ( $_REQUEST["page"] == "addcomptoir" && $site->user->is_in_group("gestion_ae") )
{
 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / Creer un comptoir");
 $frm = new form ("addcomptoir","admin.php",true,"POST","Ajout d'un comptoir");
 $frm->add_hidden("action","addcomptoir");
 $frm->add_text_field("nom","Nom du comptoir","",true);
 $frm->add_entity_select("id_groupe_vendeurs", "Groupe vendeur", $site->db, "group");
 $frm->add_entity_select("id_groupe_admins", "Groupe d'administration", $site->db, "group");
 $frm->add_entity_select("id_assocpt", "Association qui tient le comptoir", $site->db, "assocpt");
 $frm->add_select_field("type","Type de comptoir",$TypesComptoir);
 $frm->add_entity_select("id_salle", "Salle", $site->db, "salle",false,true);
  $frm->add_radiobox_field("rechargement", "Rechargement", array(1 => "Activé", 0 => "Désactivé"), 1, -1);
 $frm->add_submit("valid","Ajouter");
 $cts->add($frm,true);
 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $_REQUEST["page"] == "addasso" && $site->user->is_in_group("gestion_ae") )
{
 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / Activer une associaton ou un club");
 $frm = new form ("addasso","admin.php",true,"POST","Ajout d'une association");
 $frm->add_hidden("action","addasso");
 $frm->add_entity_select("id_asso", "Association", $site->db, "asso");
 $frm->add_submit("valid","Ajouter");
 $cts->add($frm,true);
 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $_REQUEST["page"] == "addtype" )
{
 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / Ajouter un type de produit");
 $frm = new form ("addtype","admin.php",true,"POST","Ajout d'un type de produit");
 $frm->add_hidden("action","addtype");
 $frm->add_text_field("nom","Nom du type","",true);
 $frm->add_entity_smartselect("id_file","Image",$file,true);
 $frm->add_text_area("description","Description");
 $frm->add_entity_select("id_assocpt", "Association qui vend généralement ce type", $site->db, "assocpt");
 $frm->add_select_field("id_action","Action par défaut",$ActionsProduits);
 $frm->add_submit("valid","Ajouter");
 $cts->add($frm,true);
 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $_REQUEST["page"] == "addproduit" )
{
 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / Ajouter un produit");
 $frm = new form ("addproduit","admin.php",true,"POST","Ajout d'un produit");
 $frm->add_hidden("action","addproduit");
 $frm->add_entity_select("id_typeprod", "Type", $site->db, "typeproduit");
 $frm->add_text_field("nom","Nom","",true);

 $frm->add_entity_smartselect("id_file","Image",$file,true);

 $frm->add_entity_smartselect("id_produit_parent","Produit parent",$produit_parent,true);

 $frm->add_text_area("description","Résumé");
 $frm->add_text_area("description_longue","Description");

 $frm->add_price_field("prix_vente_barman","Prix barman",0,true);
 $frm->add_price_field("prix_vente","Prix vente",0,true);
 $frm->add_price_field("prix_achat","Prix achat",0,true);
 $frm->add_entity_select("id_assocpt", "Association", $site->db, "assocpt");
 $frm->add_select_field("id_action","Action",$ActionsProduits);
 $frm->add_text_field("meta","Paramètre");
 $frm->add_text_field("code_barre","Code barre");

  $frm->add_select_field("mineur","Age limit",array(0=>'tout public',16=>'16ans ou plus',18=>'18 ans ou plus'));

 $frm->add_checkbox("a_retirer","Produit à venir retirer (pour e-boutic)");
 $frm->add_checkbox("postable","Envoyable par la poste (non disponible)",false,true);
 $frm->add_price_field("frais_port","Frais de port");

 $grp = new group($site->db);
 $grp->load_by_id(10000);
 $frm->add_entity_smartselect("id_groupe", "Limiter l'achat au groupe", $grp,true);

 $frm->add_datetime_field("date_fin","Date de fin de mise en vente");


 $frm->add(generate_subform_stock("Stock global","global","stock","stock_value",-1),false, false, false,false, true);
 $frm->add(generate_subform_stock("Limite par personnes","limite","limite_util","limite_util_value",-1),false, false, false,false, true);

 foreach ( $site->admin_comptoirs as $id => $nom )
 {
  $frm->add(generate_subform_stock("<a href=\"admin.php?id_comptoir=$id\">".$nom."</a>","cpt|$id","cpt_stock[$id]","cpt_stock_value[$id]",-1), true, false, false,false, true);
 }


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
  "FROM `cpt_type_produit`  " .
  "ORDER BY `nom_typeprod`");

 while ( $row = $req->get_row() )
   $batch["settypeprod=".$row['id_typeprod']] = "Modifier le type pour ".$row['nom_typeprod'];


 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / Produits");

 $req = new requete($site->db,
  "SELECT `cpt_produits`.`nom_prod`, `cpt_produits`.`id_produit`," .
  "`cpt_produits`.stock_global_prod, `cpt_produits`.prix_vente_barman_prod/100 AS prix_vente_barman_prod," .
  "`cpt_produits`.prix_vente_prod/100 AS prix_vente_prod, `cpt_produits`.prix_achat_prod/100 AS  prix_achat_prod, " .
  "`asso`.`nom_asso`,`asso`.`id_asso`, " .
  "`cpt_type_produit`.`id_typeprod`,`cpt_type_produit`.`nom_typeprod` " .
  "FROM `cpt_produits` " .
  "INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod` " .
  "INNER JOIN `asso` ON `asso`.`id_asso`=`cpt_produits`.`id_assocpt` " .
  (isset($_REQUEST['showall'])? "" : "AND `cpt_produits`.`prod_archive` != 1 ") .
  "ORDER BY `cpt_type_produit`.`nom_typeprod`,`cpt_produits`.`nom_prod`");


 $section_name = "Produits";
 if (! isset($_REQUEST['showall']))
  $section_name .= " (hors archivés)";

 $tbl = new sqltable(
   "lstproduits",
   $section_name, $req, "admin.php",
   "id_produit",
   array(
   "nom_typeprod"=>"Type",
   "nom_prod"=>"Nom du produit",
   "prix_vente_barman_prod"=>"Prix barman",
   "prix_vente_prod"=>"Prix de vente",
   "prix_achat_prod"=>"Prix d'achat",
   "stock_global_prod"=>"Stock global",
   "nom_asso"=>"Association"
   ),
   array("edit"=>"Editer"), $batch, array()
   );

 $cts->add($tbl,true);

  if (! isset($_REQUEST['showall']))
   $cts->add_paragraph("<a href=\"admin.php?page=produits&showall\">Afficher les produits archivés</a>");

 $req = new requete($site->db,
  "SELECT `id_typeprod`,`nom_typeprod` " .
  "FROM `cpt_type_produit`  " .
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

 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / ".$typeprod->get_html_link()." / ".$produit->get_html_link());

 $cts->add_paragraph("<a href=\"compta.php?id_produit=".$produit->id."\">Comptabilité</a>");

 $nonpresents = $site->admin_comptoirs;

 $req = new requete($site->db,
   "SELECT `cpt_mise_en_vente`.`stock_local_prod`,`cpt_comptoir`.`id_comptoir`,`cpt_comptoir`.`nom_cpt`
    FROM `cpt_mise_en_vente`
    INNER JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` = `cpt_mise_en_vente`.`id_comptoir`
    WHERE `cpt_mise_en_vente`.`id_produit` = '".intval($produit->id)."'");

 $frm = new form ("updcpt","admin.php",true,"POST","Lieux de vente actuels");
 $frm->add_hidden("action","updcpt");
 $frm->add_hidden("id_produit",$produit->id);
 while ( list($stock,$id,$nom) = $req->get_row() )
 {
  $frm->add(generate_subform_stock("<a href=\"admin.php?id_comptoir=$id\">".$nom."</a>","cpt|$id","cpt_stock[$id]","cpt_stock_value[$id]",$stock), true, false, true,false, true);
  unset($nonpresents[$id]);
 }
 $frm->add_submit("valid","Enregistrer");
 $cts->add($frm,true);

 $frm = new form ("addnvcpt","admin.php",true,"POST","Ajouter un lieu de vente");
 $frm->add_hidden("action","addnvcpt");
 $frm->add_hidden("id_produit",$produit->id);
 foreach ( $nonpresents as $id => $nom )
 {
  $frm->add(generate_subform_stock("<a href=\"admin.php?id_comptoir=$id\">".$nom."</a>","nv_cpt|$id","nv_stock[$id]","nv_stock_value[$id]",-1), true, false, false,false, true);
 }
 $frm->add_submit("valid","Ajouter");
 $cts->add($frm,true);

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

 $frm->add_price_field("prix_vente_barman","Prix barman",$produit->prix_vente_barman,true);
 $frm->add_price_field("prix_vente","Prix vente",$produit->prix_vente,true);
 $frm->add_price_field("prix_achat","Prix achat",$produit->prix_achat,true);
 $frm->add_entity_select("id_assocpt", "Association", $site->db, "assocpt",$produit->id_assocpt);
 $frm->add_select_field("id_action","Action",$ActionsProduits,$produit->action);
 $frm->add_text_field("meta","Paramètre",$produit->meta);
 $frm->add_text_field("code_barre","Code barre",$produit->code_barre);

  $frm->add_select_field("mineur","Age limit",array(0=>'tout public',16=>'16ans ou plus',18=>'18 ans ou plus'),$produit->mineur);

 $frm->add_checkbox("a_retirer","Produit à venir retirer (pour e-boutic)",$produit->a_retirer);
 $frm->add_checkbox("postable","Envoyable par la poste (non disponible)",$produit->postable,true);
 $frm->add_price_field("frais_port","Frais de port",$produit->frais_port);

 $grp = new group($site->db);
 $grp->load_by_id($produit->id_groupe);
 $frm->add_entity_smartselect("id_groupe", "Limiter l'achat au groupe", $grp,true);

 $frm->add_datetime_field("date_fin","Date de fin de mise en vente",$produit->date_fin);

 $frm->add(generate_subform_stock("Stock global","global","stock","stock_value",$produit->stock_global),false, false, false,false, true);
 $frm->add(generate_subform_stock("Limite par personnes","limite","limite_util","limite_util_value",$produit->limite_utilisateur),false, false, false,false, true);
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


 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / <a href=\"admin.php?page=produits\">Produits</a> / ".$typeprod->nom);

 $cts->add_paragraph("<a href=\"compta.php?id_typeprod=".$typeprod->id."\">Comptabilité</a>");


 $frm = new form ("uptype","admin.php",true,"POST","Editer");
 $frm->add_hidden("action","uptype");
    $frm->add_hidden("id_typeprod", $typeprod->id);
 $frm->add_text_field("nom","Nom du type",$typeprod->nom,true);

 $file->load_by_id($typeprod->id_file);
 $frm->add_entity_smartselect("id_file","Image",$file,true);

 $frm->add_text_area("description","Description",$typeprod->description);
 $frm->add_entity_select("id_assocpt", "Association qui vend g&eacute;n&eacute;ralement ce type", $site->db, "assocpt",$typeprod->id_assocpt);
 $frm->add_select_field("id_action","Action par d&eacute;faut",$ActionsProduits,$typeprod->id_action);
 $frm->add_submit("valid","Enregistrer");
 $cts->add($frm,true);

 $req = new requete($site->db,
  "SELECT `cpt_produits`.`nom_prod`, `cpt_produits`.`id_produit`,`cpt_produits`.`prod_archive`, " .
  "`cpt_produits`.stock_global_prod, `cpt_produits`.prix_vente_barman_prod/100 AS prix_vente_barman_prod," .
  "`cpt_produits`.prix_vente_prod/100 AS prix_vente_prod, `cpt_produits`.prix_achat_prod/100 AS  prix_achat_prod, " .
  "`asso`.`nom_asso`,`asso`.`id_asso`, " .
  "`cpt_type_produit`.`id_typeprod`,`cpt_type_produit`.`nom_typeprod` " .
  "FROM `cpt_produits` " .
  "INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod` " .
  "INNER JOIN `asso` ON `asso`.`id_asso`=`cpt_produits`.`id_assocpt` " .
  "WHERE `cpt_produits`.`id_typeprod`='".$typeprod->id."' " .
  (isset($_REQUEST['showall'])? "" : "AND `cpt_produits`.`prod_archive` != 1 ") .
  "ORDER BY `cpt_type_produit`.`nom_typeprod`,`cpt_produits`.`nom_prod`");

 $section_name = "Produits";
 if (! isset($_REQUEST['showall']))
  $section_name .= " (hors archivés)";

 $tbl = new sqltable(
   "lstproduits",
   $section_name, $req, "admin.php?id_typeprod=".$typeprod->id,
   "id_produit",
   array(
   "nom_typeprod"=>"Type",
   "nom_prod"=>"Nom du produit",
   "prix_vente_barman_prod"=>"Prix barman",
   "prix_vente_prod"=>"Prix de vente",
   "prix_achat_prod"=>"Prix d'achat",
   "stock_global_prod"=>"Stock global",
   "nom_asso"=>"Association",
   "prod_archive"=>"Archivé"
   ),
   array("edit"=>"Editer"), array("arch"=>"Archiver","unarch"=>"Desarchiver"), array("prod_archive"=>array(0=>"non",1=>"oui"))
   );

 $cts->add($tbl,true);

 if (! isset($_REQUEST['showall']))
   $cts->add_paragraph("<a href=\"admin.php?id_typeprod=".$typeprod->id."&showall\">Afficher les produits archivés</a>");

 $site->add_contents($cts);
 $site->end_page();
 exit();
}
elseif ( $comptoir->id > 0 )
{

  $allow_editteam = ($comptoir->groupe_vendeurs == 5) || ($comptoir->groupe_vendeurs == 16);



 if ( $_REQUEST["action"] == "retirervente" )
 {
  foreach( $_REQUEST["id_produits"] as $id_produit )
  {
   if ( $venteproduit->load_by_id($id_produit,$comptoir->id) )
    $venteproduit->supprime();
  }
 }
 elseif ( $allow_editteam && isset($_REQUEST["action"]) )
 {
   require_once($topdir. "include/entities/group.inc.php");
    $grp = new group ( $site->db,$site->dbrw );
    $grp->load_by_id($comptoir->groupe_vendeurs);

    if ( $_REQUEST["action"] == "delbarman" )
    {
      $grp->remove_user_from_group($_REQUEST["id_utilisateur"]);

    }
    elseif ( $_REQUEST["action"] == "delbarmen" )
    {
      if(is_array($_REQUEST["id_utilisateurs"]) && !empty($_REQUEST["id_utilisateurs"]))
        foreach( $_REQUEST["id_utilisateurs"] as $id_utilisateur)
          $grp->remove_user_from_group($id_utilisateur);
    }
    elseif ( $_REQUEST["action"] == "addbarman" )
    {
      $user = new utilisateur($site->dbrw);
      $user->load_by_id($_REQUEST["id_utilisateur"]);
      if ( $user->id > 0 )
        $grp->add_user_to_group($user->id);
    }
  }

 $site->start_page("services","Administration des comptoirs");
 $cts = new contents("<a href=\"admin.php\">Administration comptoirs</a> / ".$comptoir->nom);

 $cts->add_paragraph("<a href=\"admin.php?page=barcodes&amp;id_comptoir=".$comptoir->id."\">Codes barre</a>");
 $cts->add_paragraph("<a href=\"compta.php?id_comptoir=".$comptoir->id."\">Comptabilité</a>");

 $tabs = array(
    array("","comptoir/admin.php?id_comptoir=".$comptoir->id, "Produits en vente"),
    array("edit","comptoir/admin.php?id_comptoir=".$comptoir->id."&view=edit", "Editer")
    );

 if ( $allow_editteam )
  $tabs[] = array("team","comptoir/admin.php?id_comptoir=".$comptoir->id."&view=team", "Vendeurs/Barmen");

 $cts->add(new tabshead($tabs,$_REQUEST["view"]));

  if ( $_REQUEST["view"] == "team" )
 {

    $req = new requete($site->db,
      "SELECT `utilisateurs`.`id_utilisateur`, " .
      "CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur` " .
      "FROM `utl_groupe` " .
      "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`utl_groupe`.`id_utilisateur` " .
      "WHERE `utl_groupe`.`id_groupe`='".$comptoir->groupe_vendeurs."' " .
      "ORDER BY `utilisateurs`.`nom_utl`,`utilisateurs`.`prenom_utl`");

    $tbl = new sqltable(
        "listbarmen",
        "Personnes autorisées à vendre", $req, "admin.php?view=team&id_comptoir=".$comptoir->id,
        "id_utilisateur",
        array("nom_utilisateur"=>"Utilisateur"),
        array("delbarman"=>"Supprimer"),
        array("delbarmen"=>"Supprimer"),
        array( )
        );
    $cts->add($tbl,true);

    $frm = new form("addbarman","admin.php?view=team&id_comptoir=".$comptoir->id, false,"POST","Ajouter un utilisateur");
    $frm->add_hidden("action","addbarman");
    $frm->add_user_fieldv2("id_utilisateur","");
    $frm->add_submit("valid","Ajouter");
    $cts->add($frm,true);


 }
 elseif ( $_REQUEST["view"] == "edit" )
 {
 $frm = new form ("upcomptoir","admin.php?view=edit&id_comptoir=".$comptoir->id,true,"POST","Editer");
 $frm->add_hidden("action","upcomptoir");
 $frm->add_text_field("nom","Nom du comptoir",$comptoir->nom,true);
 $frm->add_entity_select("id_groupe_vendeurs", "Groupe vendeur", $site->db, "group",$comptoir->groupe_vendeurs);
 $frm->add_entity_select("id_groupe_admins", "Groupe d'administration", $site->db, "group",$comptoir->groupe_admins);
 $frm->add_entity_select("id_assocpt", "Association qui tient le comptoir", $site->db, "assocpt",$comptoir->id_assocpt);
 $frm->add_select_field("type","Type de comptoir",$TypesComptoir,$comptoir->type);
 $frm->add_entity_select("id_salle", "Salle", $site->db, "salle",$comptoir->id_salle,true);
  $frm->add_radiobox_field("rechargement", "Rechargement", array(1 => "Activé", 0 => "Désactivé"), $comptoir->rechargement,-1);
 $frm->add_submit("valid","Enregistrer");
 $cts->add($frm,true);
 }
 else
 {

    $lst = new itemlist();
    $lst->add("<a href=\"admin.php?page=addproduit\">Ajouter un nouveau  produit</a>");
    $lst->add("<a href=\"admin.php?page=produits\">Liste de tous les autres produits</a> (permet de (re)mettre en vente un produit existant)");
    $cts->add($lst);


 $req = new requete($site->db,
  "SELECT `cpt_produits`.`nom_prod`, `cpt_produits`.`id_produit`," .
  "`cpt_produits`.stock_global_prod, `cpt_produits`.prix_vente_barman_prod/100 AS prix_vente_barman_prod," .
  "`cpt_produits`.prix_vente_prod/100 AS prix_vente_prod, `cpt_produits`.prix_achat_prod/100 AS  prix_achat_prod, " .
  "`asso`.`nom_asso`,`asso`.`id_asso`, " .
  "`cpt_type_produit`.`id_typeprod`,`cpt_type_produit`.`nom_typeprod`, " .
  "`cpt_mise_en_vente`.`stock_local_prod` " .
  "FROM `cpt_produits` " .
  "INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod` " .
  "INNER JOIN `asso` ON `asso`.`id_asso`=`cpt_produits`.`id_assocpt` " .
  "INNER JOIN cpt_mise_en_vente ON `cpt_mise_en_vente`.`id_produit`=`cpt_produits`.`id_produit` " .
  "WHERE `cpt_mise_en_vente`.`id_comptoir`='".$comptoir->id."' " .
  "ORDER BY `cpt_type_produit`.`nom_typeprod`,`cpt_produits`.`nom_prod`");

 $tbl = new sqltable(
   "lstproduits",
   "Produits", $req, "admin.php?id_comptoir=".$comptoir->id,
   "id_produit",
   array(
   "nom_typeprod"=>"Type",
   "nom_prod"=>"Nom du produit",
   "prix_vente_barman_prod"=>"Prix barman",
   "prix_vente_prod"=>"Prix de vente",
   "prix_achat_prod"=>"Prix d'achat",
   "stock_global_prod"=>"Stock global",
   "stock_local_prod"=>"Stock local",
   "nom_asso"=>"Association"
   ),
   array("edit"=>"Editer"), array("retirervente"=>"Ne plus vendre ce(s) produit(s)"), array()
   );

 $cts->add($tbl,true);
 }

 $site->add_contents($cts);
 $site->end_page();
 exit();
}

// Page principale
$site->start_page("services","Bienvenue");

$cts = new contents("Administration : comptoirs AE");

if ( $site->user->is_in_group("gestion_ae") )
{
 $lst = new itemlist("Administration");
 $lst->add("<a href=\"admin.php?page=addcomptoir\">Ajouter un comptoir</a>");
 $lst->add("<a href=\"admin.php?page=addasso\">Ajouter une association</a>");
 $lst->add("<a href=\"facture.php\">Génération des factures</a>");
 $cts->add($lst,true);
}

$lst = new itemlist("Gestion des produits");
$lst->add("<a href=\"admin.php?page=addproduit\">Ajouter un produit</a>");
$lst->add("<a href=\"admin.php?page=addtype\">Ajouter un type de produit</a>");
$lst->add("<a href=\"admin.php?page=produits\">Liste des produits et des types de produits</a>");
$cts->add($lst,true);

$lst = new itemlist("Gestion des comptoirs");
foreach( $site->admin_comptoirs as $id => $nom )
 $lst->add("<a href=\"admin.php?id_comptoir=$id\">".$nom."</a>");
$cts->add($lst,true);

$lst = new itemlist("Comptabilité");
foreach( $site->admin_comptoirs as $id => $nom )
 $lst->add("<a href=\"compta.php?id_comptoir=$id\">".$nom."</a>");
$cts->add($lst,true);

$site->add_contents($cts);
$site->end_page();
?>
