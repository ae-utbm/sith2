<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->add_css("css/pedagogie.css");
$site->start_page("services", "AE Pédagogie");

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";

/***********************************************************************
 * Actions
 */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
{
  print_r($_REQUEST);
  
  //$site->redirect("./uv.php?id=".$uv->id."&action=edit#guide");
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new')
{
  $path .= " / "."Ajouter une UV";
  $cts = new contents($path);
  $cts->add_paragraph("Vous pouvez ici ajouter une UV au guide des UV. Bien
  que quelques vérifications automatiques seront faites, nous vous incitons
  à bien vous assurer que la fiche n'existe pas déjà. De plus, nous vous 
  demandons de n'ajouter *que* des UV réelles de l'UTBM, et non pas vos
  permanences de foyer, etc (utilisez pour cela le planning à cet effet).
  Merci de votre participation !");
  
  $frm = new form("newuv", "uv.php?action=save", true, "post");
  $frm->add_text_field("code", "Code", "", false, 4, false, true, "(format XX00)");
  $frm->add_text_field("intitule", "Intitulé");
  $frm->add_text_field("responsable", "Responsable");
  
  $avail_type=array();
  foreach($_TYPE as $type=>$desc)
    $avail_type[$type] = $desc['long'];
  $frm->add_select_field("type", "Catégorie", $avail_type);
  
  $avail_sem=array();
  foreach($_SEMESTER as $sem=>$desc)
    $avail_sem[$sem] = $desc['long'];
  $frm->add_select_field("semestre", "Semestre(s) d'ouverture", $avail_sem);
  
  $frm->add_checkbox("tc_avail", "UV ouverte aux TC", true);
  $frm->add_text_field("alias_of", "Alias de", "", false, 4, false, true, "(exemple : si vous ajoutez l'UV 'XE03', inscrivez ici 'LE03')");
  
  $frm->add_submit("saveuv", "Enregistrer l'UV & éditer la fiche");
  $cts->add($frm);
  
  $site->add_contents($cts);
  $site->end_page();
}

/***********************************************************************
 * Affichage detail UV
 */
if($_REQUEST['id'])
{
  $uv = new uv($site->db, $site->dbrw, $_REQUEST['id']);
  if(!$uv->is_valid())
    $site->redirect('./');

  $cts = new contents($path);

  $tabs = array(
            array("", "pedagogie/uv.php?id=".$uv->id, "Informations générales"),
            array("candidatures", "pedagogie/uv.php?id=".$uv->id."&view=commentaires", "Commentaires"),
            array("general", "pedagogie/uv.php?id=".$uv->id."&view=suivi", "Séances & Élèves"),
            array("profil", "pedagogie/uv.php?id=".$uv->id."&view=ressources", "Ressources")
          );
  $cts->add(new tabshead($tabs, $_REQUEST['view']));
  $site->add_contents($cts);
  
  $site->end_page();
}

/***********************************************************************
 * Affichage guide des UV
 */

$tabs = array(array("", "pedagogie/uv.php", "Guide des UV"));
foreach($_DPT as $dpt=>$desc)
  $tabs[] = array($dpt, "pedagogie/uv.php?dept=".$dpt, $desc['short']);

/**
 * Affichage 'sommaire' par departement
 */
if($_REQUEST['dept'])
{
  if(array_key_exists($_REQUEST['dept'], $_DPT))
    $dept = $_REQUEST['dept'];
  else
    $site->redirect('./');
  
  $path .= " / "."<a href=\"".$topdir."pedagogie/uv.php?dept=$dept\"><img src=\"".$topdir."images/icons/16/forum.png\" class=\"icon\" /> ".$_DPT[$dept]['short']." </a>";
  $cts = new contents($path);
  $cts->add(new tabshead($tabs, $_REQUEST['dept']));
  
  $uvlist = uv::get_list($site->db, null, $dept);
  $cts->add(new uv_dept_table($uvlist));
  
  $cts->add(new sqltable("uvlist_".$dept, "UV de ".$_DPT[$dept]['long'], $uvlist, "", 'id_uv',
                          array("code"=>"Code",
                                "intitule"=>"Intitulé",
                                "type"=>"Type",
                                "responsable"=>"Responsable",
                                "semestre"=>"Ouverture")
                          ), true);
  
  $site->add_contents($cts);
  $site->end_page();
}

/* affichage par defaut de la page : guide des UV */
$path .= " / "."Guide des UV";
$cts = new contents($path);
$cts->add(new tabshead($tabs, $_REQUEST['dept']));

$cts->add_paragraph("Bienvenue sur la version \"site AE\" du guide des UV.
Nous vous rappelons que tout comme le reste de la partie pédagogie, toutes 
les informations que vous pouvez trouver ici sont fournies uniquement à
titre indicatif et que seules les informations issues des documents
officiels de l'UTBM (notamment le guide des UV et le récapitulatif
de vos crédits) font foi.");

$cts->add_paragraph("Nous ne sommes pour le moment pas en mesure d'assurer
la parfaite synchronisation des données avec le guide des UV, les informations
que vous pouvez trouver peuvent donc être dépassées, voire des UV manquer.
Vous êtes invités à contribuer à l'utilité de ce site en mettant à jour
les fiches d'UV (bouton `modifier`) et/ou en ajoutant les UV manquantes :");
$cts->puts("<input type=\"button\" onclick=\"location.href='uv.php?action=new';\" value=\"+ Ajouter une UV\" />");

foreach($_DPT as $dept=>$desc){
  $cts->add_title(2,"<a id=\"dept_".$dept."\" href=\"./uv.php?dept=$dept\">".$desc['long']."</a>");

  $uvlist = uv::get_list($site->db, null, $dept);
  $cts->add(new uv_dept_table($uvlist));
}

$site->add_contents($cts);
$site->end_page();
?>
