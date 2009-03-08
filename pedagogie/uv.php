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
 * along with this program; if not, write to the Free Softwareus
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/cts/selectbox.inc.php");
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/cts/pedagogie.inc.php");

$site = new site();
$site->add_js("pedagogie/pedagogie.js");
$site->add_css("css/pedagogie.css");
$site->start_page("services", "AE Pédagogie");

$path = "<a href=\"".$topdir."uvs/\"><img src=\"".$topdir."images/icons/16/lieu.png\" class=\"icon\" />  Pédagogie </a>";

/* compatibilite sqltable bleh */
if(isset($_REQUEST['id_uv']))
  $_REQUEST['id'] = $_REQUEST['id_uv'];

/***********************************************************************
 * Actions
 */
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
{
  print_r($_REQUEST);
  $uv = new uv($site->db, $site->dbrw);
  
  $uv->add($_REQUEST['code'], $_REQUEST['intitule'], $_REQUEST['type'], $_REQUEST['responsable'], $_REQUEST['semestre'], $_REQUEST['tc_avail']);
  
  if(!$uv->is_valid())
    exit; //ouais faudra trouver mieux :)
  
  if(isset($_REQUEST['alias_of']) && !empty($_REQUEST['alias_of']))
    $uv->set_alias_of($_REQUEST['alias_uv']);
    
  $site->redirect("./uv.php?id=".$uv->id."&action=edit#guide");
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
  $frm->add_text_field("code", "Code", "", true, 4, false, true, "(format XX00)");
  $frm->add_text_field("intitule", "Intitulé", "", true, 36);
  $frm->add_text_field("responsable", "Responsable");
  
  $avail_type=array();
  foreach($_TYPE as $type=>$desc)
    $avail_type[$type] = $desc['long'];
  $frm->add_select_field("type", "Catégorie", $avail_type);
  
  $avail_sem=array();
  foreach($_SEMESTER as $sem=>$desc)
    $avail_sem[$sem] = $desc['long'];
  $frm->add_select_field("semestre", "Semestre(s) d'ouverture", $avail_sem, SEMESTER_AP);
    
  $frm->add_text_field("alias_of", "Alias de", "", false, 4, false, true, "(exemple : si vous ajoutez l'UV 'XE03', inscrivez ici 'LE03')");
  $frm->add_checkbox("tc_avail", "UV ouverte aux TC", true);
  
  $frm->add_submit("saveuv", "Enregistrer l'UV & éditer la fiche");
  $cts->add($frm);
  
  $site->add_contents($cts);
  $site->end_page();
  exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
{ 
  $uv = new uv($site->db, $site->dbrw, intval($_REQUEST['id']));
  if(!$uv->is_valid())
    $site->redirect('./');
  
  $uv->load_extra();

  $path .= " / "."<img src=\"".$topdir."images/icons/16/forum.png\" class=\"icon\" />";
  $stop = count($uv->dept);
  for($i=0; $i<$stop; $i++){
    $path .= "<a href=\"uv.php?dept=".$uv->depts[$i]."\"> ".$_DPT[$uv->depts[$i]]['short']."</a>";
    if($i+1 < $stop) $path .= ",";
  }
  	
  $path .= " / "."<a href=\"./uv.php?id=$uv->id\"><img src=\"".$topdir."images/icons/16/emprunt.png\" class=\"icon\" /> $uv->code</a>";
  $path .= " / "."Éditer";
  $cts = new contents($path);
  $cts->add_paragraph("");
  
  /** 
   * informations principales
   */
  $frm = new form("editmain", "uv.php?action=save", true, "post", "Informations principales");
  $frm->add_text_field("code", "Code", $uv->code, true, 4);
  $frm->add_text_field("intitule", "Intitulé", $uv->intitule, true);
  $frm->add_text_field("responsable", "Responsable", $uv->responsable);
  
  $avail_type=array();
  foreach($_TYPE as $type=>$desc)
    $avail_type[$type] = $desc['long'];
  $frm->add_select_field("type", "Catégorie", $avail_type);
  
  $avail_sem=array();
  foreach($_SEMESTER as $sem=>$desc)
    $avail_sem[$sem] = $desc['long'];
  $frm->add_select_field("semestre", "Semestre(s) d'ouverture", $avail_sem, SEMESTER_AP);
    
  $frm->add_text_field("alias_of", "Alias de", "", false, 4, false, true, "(exemple : si vous ajoutez l'UV 'XE03', inscrivez ici 'LE03')");
  $frm->add_checkbox("tc_avail", "UV ouverte aux TC", true);
  
  $frm->add_submit("saveuv", "Enregistrer les modifications");
  $cts->add($frm, true, false, "main", false, true);
  
  /**
   * infos du guide 
   */
  unset($frm);
  $frm = new form("editextra", "uv.php?action=save", true, "post", "Informations du guide des UV");
  $frm->add_info("Ces informations sont très importantes car elles permettent de plannifier les séances de C/TD/TP");
    $subfrm = new subform("charge");
    $subfrm->add_text_field("c", "Nombre d'heures de : cours", $uv->guide['c'], false, 2);
    $subfrm->add_text_field("td", "TD", $uv->guide['td'], false, 2);
    $subfrm->add_text_field("tp", "TP", $uv->guide['tp'], false, 2);
    $subfrm->add_text_field("the", "THE", $uv->guide['the'], false, 2);
  $frm->add($subfrm, false, false, false, false, true);
    
  $frm->add_text_field("credits", "Nombre de crédits ECTS", $uv->credits, true, 2);
  $frm->add_text_area("objectifs", "Objectifs de l'UV", $uv->guide['objectifs']);
  $frm->add_text_area("programme", "Programme de l'UV", $uv->guide['programme']);
  
  $frm->add_submit("saveuv", "Enregistrer les modifications");
  $cts->add($frm, true, false, "guide", false, true);
  
  /**
   * Informations relatives
   */
  /* ajout dept, filieres, alias... */
  unset($frm);
  $frm = new form("editrelative", "uv.php?action=save", true, "post", "Informations relatives");
  
  $avail_dept=array();
  $already_dept=array();
  foreach($_DPT as $dept=>$desc){
    if(in_array($dept, $uv->dept))
      $already_dept[$dept] = $desc['long'];
    else
      $avail_dept[$dept] = $desc['long'];
  }
  $frm->add(new selectbox("dept", "Départements", $avail_dept, null, null, $already_dept, 120));
  
  //$sql = new requete($site->db, "SELECT `id_cursus`, `intitule` FROM `pedag_cursus` WHERE ");
  $frm->add(new selectbox("cursus", "Cursus", null, null, null, null, 120));
  
  $frm->add_submit("saveuv", "Enregistrer les modifications");
  $cts->add($frm, true, false, "relative", false, true);
  $site->add_contents($cts);
  $site->end_page();
  exit;
}

if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'popup'
    && isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_seance')
{
  $uv = new uv($site->db, $site->dbrw, $_REQUEST['id']);
  if(!$uv->is_valid())
    $site->redirect('uv.php');
    
  $cts = new contents("Ajout d'une séance de ".$uv->code);
  
  if(isset($_REQUEST['save'])){
    /** on va dire que ca a marche */

    
    $type = $_REQUEST['type'];
    $num = $_REQUEST['num'];
    $freq = $_REQUEST['freq'];
    $semestre = $_REQUEST['semestre'];
    $jour = $_REQUEST['jour'];
    $debut = $_REQUEST['hdebut'].":".$_REQUEST['mdebut'];
    $fin = $_REQUEST['hfin'].":".$_REQUEST['mfin'];
    $salle = $_REQUEST['salle'];
    
    $id_groupe =  $uv->add_group($type, $num, $freq, $semestre, $jour, $debut, $fin, $salle);
    
    $texte = $_GROUP[$type]['long']." n°$num du ".get_day($jour)." de $debut à $fin en $salle";
    $cts->puts("<script type='text/javascript'>
    function ret(){
      var o = new Option('$texte', '$id_groupe');
      o.onclick = function(e){ edt.disp_freq_choice('".$uv->id."_".$type."', $freq, ".$uv->id.", $type); };
      o.selected = true;
      window.opener.document.getElementById('".$_REQUEST['calling']."').options.add(o);
      self.close();
    }
  </script>");
    $cts->add_paragraph("Votre séance de ".$_GROUP[$type]['long']." de ".$uv->code." du ".get_day($jour)." à bien été enregistrée.");
    $cts->add_paragraph("Merci de votre participation.");
    $cts->add_paragraph("<input type=\"submit\" class=\"isubmit\" "
                    ."value=\"Continuer\" "
                    ."onclick=\"ret();\"/>");
  }
  /** formulaire d ajout */
  else{
    if(isset($_REQUEST['type']))  $type = $_REQUEST['type'];
    else                          $type = null;
    
    if(isset($_REQUEST['semestre']))  $semestre = $_REQUEST['semestre'];
    else                              $semestre = SEMESTER_NOW;
      
    $cts->add(new add_seance_box($uv->id, $type, $semestre));
  }
  
  $site->add_contents($cts);
  $site->popup_end_page(); 
  exit;
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
            array("commentaires", "pedagogie/uv.php?id=".$uv->id."&view=commentaires", "Commentaires"),
            array("suivi", "pedagogie/uv.php?id=".$uv->id."&view=suivi", "Séances & Élèves"),
            array("ressources", "pedagogie/uv.php?id=".$uv->id."&view=ressources", "Ressources")
          );
  $cts->add(new tabshead($tabs, $_REQUEST['view']));
  
  /**
   * onglet commentaires
   */
  if(isset($_REQUEST['view']) && $_REQUEST['view'] == 'commentaires'){
    require_once("include/cts/uv_comment.inc.php");
    require_once("include/uv_comment.inc.php");
    
    if($uv->load_comments()){
      
      foreach($uv->comments as $commentid){
        $author = new pedag_user($site->db);
        $author->load_by_id($comment->id_utilisateur);
        if(!$author->is_valid())
          print_r($author);
        $cts->add(new uv_comment_box(new uv_comment($site->db, $site->dbrw, $commentid), $uv, $site->user, $author));
      }
      
    }
    
  }else if(isset($_REQUEST['view']) && $_REQUEST['view'] == 'suivi'){
    
  }else if(isset($_REQUEST['view']) && $_REQUEST['view'] == 'ressources'){
    
  }else{
    
  } 
  
  $site->add_contents($cts);
  $site->end_page();
  exit;
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
  $cts->add_paragraph("");
  
  $uvlist = uv::get_list($site->db, null, $dept);
  $cts->add(new uv_dept_table($uvlist));
  
  $cts->add(new sqltable("uvlist_".$dept, "UV de ".$_DPT[$dept]['long'], $uvlist, "", 'id_uv',
                          array("code"=>"Code",
                                "intitule"=>"Intitulé",
                                "type"=>"Type",
                                "responsable"=>"Responsable",
                                "semestre"=>"Ouverture"),
                          array(), array()
                          ), true);
  
  $site->add_contents($cts);
  $site->end_page();
  exit;
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
