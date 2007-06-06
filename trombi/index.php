<?php

/* Copyright 2007
 *
 * - Sebastien WATTIEZ < webast2 at gmail dot com >
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

$topdir = "../";


require_once($topdir."comptoir/include/defines.inc.php");

include($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/special.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir . "include/entities/carteae.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");
require_once($topdir . "include/entities/ville.inc.php");
require_once($topdir . "include/entities/pays.inc.php");



$UserBranches = array("TC"				=> "TC",
                      "GI"				=> "GI",
                      "GSP"				=> "IMAP",
                      "GSC"				=> "GESC",
                      "GMC"				=> "GMC",
                      "Enseig"			=> "Enseignant",
                      "Admini"			=> "Administration",
                      "Autre"			=> "Autre");
                      

function userTrombiDisplay ($user, $admin = false) 
{
  global $topdir, $UserBranches;
  static $numFiche=0;
  $numFiche++;
  
  $imgclass="noimg";
  $img = $topdir."images/icons/128/unknown.png";
  $date_prise_vue = "";
    
  if (file_exists($topdir."/var/img/matmatronch/".$user->id.".jpg")) 
  {
    $img = $topdir."/var/img/matmatronch/".$user->id.".jpg?";
    $imgclass="mmtimg";
  } 
  elseif (file_exists($topdir."/var/img/matmatronch/".$user->id.".identity.jpg")) 
  {
    $img = $topdir."/var/img/matmatronch/".$user->id.".identity.jpg?";
    $imgclass="idimg";
  }
  
  $buffer = "<div class=\"userfullinfo\">\n"
         ."	<h2 class=\"nom\">".htmlentities($user->prenom." ".$user->nom,ENT_COMPAT,"UTF-8")."</h2>\n"
         ."	<div class=\"photo\">\n"
         ."		<img src=\"$img\" id=\"mmtphoto$numFiche\" class=\"$imgclass\" alt=\"Photo de ".htmlentities($user->prenom." ".$user->nom,ENT_COMPAT,"UTF-8")."\" />\n"
         ."	</div>\n";	
  
  if ($user->surnom)
    $buffer .= "	<p class=\"surnom\">&laquo; ". htmlentities($user->surnom,ENT_COMPAT,"UTF-8") . " &raquo;</p>\n";
  elseif ($user->alias) 
    $buffer .= "	<p class=\"surnom\">&laquo; ". htmlentities($user->alias,ENT_COMPAT,"UTF-8") . " &raquo;</p>\n";
      
  $buffer .= '<p class="naissance" style="width: 170px; text-align: right">';
  if ($user->date_naissance != strtotime("01 january 1970")) 
  {		
    if ($user->sexe == 1) 
      $buffer .= "N&eacute; ";
    else $buffer .= "N&eacute;e ";
      $buffer .= "le " . strftime("%d %B %Y", $user->date_naissance) . "</p>\n";
  }
  
  $buffer .= '<p class="departement">'.$user->branche.$user->semestre.'(ex:GI05)<span class="filiere">Filière : '.$user->filiere.'</span></p>';
  
  if ($user->branche && $user->nom_ecole_etudiant == "UTBM") 
  {
    /*$buffer .= "<div class=\"branches\" style=\"float: left; width: 200px;\">\n";
    $buffer .= "<img src=\"".$topdir."images/utbmlogo.gif\" style=\"position: relative; float: left; width: 65px;\">\n";
    $buffer .= "<div class=\"branches_info\" style=\"position: relative; width: 300px;\">\n";
    $buffer .= "<br/><b>".$UserBranches[$user->branche]."\n";*/
    
    if ($user->branche!="Enseignant" && $user->branche!="Administration" && $user->branche!="Autre" && $user->branche!="TC")
    {
      if (isset($user->date_diplome_utbm) && !(empty($user->date_diplome_utbm)) && ($user->date_diplome_utbm != "0000-00-00") && ($user->date_diplome_utbm < time()))
        if ($user->sexe == 1) 
          $buffer .= " Dipl&ocirc;m&eacute;</b>\n";
        else  $buffer .= " Dipl&ocirc;m&eacute;e</b>\n";
      else
        $buffer .= sprintf("%02d",$user->semestre)."</b>\n";
      if ($user->filiere)
      {
        $buffer .= "<br/>Filière : <br/>\n";
        $buffer .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$user->filiere . "\n";
      }
    }
    
    $buffer .="</b>\n";
    $buffer .= "</div>\n";
    
    $buffer .= "</div>\n";
  }
    
  $buffer .= "</div>\n\n\n\n";
  $buffer .= "</div>\n";
  
  return $buffer;
}


$site = new site();

$site->add_css("css/userfullinfo.css");

if (!$site->user->id)
  error_403();

$site->start_page ("none", "Trombi AE ");

$cts = new contents("Trombino Promo " . $site->user->promo_utbm);

$tabs = array(
        array("","ae/jetons.php", "Informations"),
        array("retour","ae/jetons.php?view=retour", "Messages"),
        array("listing","ae/jetons.php?view=listing", "Version papier"),
        );
$cts->add(new tabshead($tabs,$_REQUEST["view"]));

if (isset($_REQUEST['id_utilisateur']))
{
  
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  
  if (!$user->is_valid())
    $site->error_not_found("matmatronch");
    
  $can_edit = ($user->id==$site->user->id || $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));
  
  if ($user->id != $site->user->id && !$site->user->utbm && !$site->user->ae)
    $site->error_forbidden("matmatronch","group",10001);
    
  if (!$user->publique && !$can_edit)
    $site->error_forbidden("matmatronch","private");    
}
else
{
  $user = &$site->user;
  $can_edit = true;
}


$info = userTrombiDisplay($user);
$cts->puts($info);

$cts->puts('<br/>');
$cts->add_title(2,"TODO");
$cts->puts('-home<br/>-onglets<br/>-listing membre promo alphabetique<br/>');

$req = new requete($site->db,
                   "SELECT `id_utilisateur`, `promo_utbm`, "
                  ."CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_utilisateur` "
                  ."FROM `utl_etu_utbm` "
                  ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                  ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                  ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                  ."ORDER BY `visites` DESC "
                  ."LIMIT 0 , 30");
                  
if ($req->lines == 0)
{
  $tbl = new error("Aucun resultat","");
}
else
{
  $tbl = new sqltable("listresult",
                      "Liste des promo " . $site->user->promo_utbm,
                      $req,
                      "../user.php",
                      "id_utilisateur", 
                      //array("id_utilisateur"=>"Id", "promo_utbm"=>"Promo", "departement_utbm"=>"Departement", "nom_utl"=>"Nom"),
                      array("nom_utilisateur"=>"Nom"),
                      array(), 
                      array(), 
                      array()
                     );
}
	
	
//$cts->add($tbl);
 
$site->add_contents($cts);

echo "<!-- USER DEFINI ";
print_r($user);
echo "-->";

echo "<!-- USER COURANT ";
print_r($site->user);
echo "-->";

$site->add_contents($tbl);

$site->end_page ();

?>

