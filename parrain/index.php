<?php
/** @file
 *
 * @brief Page d'inscription au pré-parrainage pour les nouveaux
 *
 */

/* Copyright 2007
 * - Julien Ehrhart <julien POINT ehrhart CHEZ utbm POINT fr>
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

include($topdir. "include/site.inc.php");

$site = new site();

$site->start_page("services", "Pré-parrainage");

$d = date("d");
$m = date("m");
if ( $m <= 2 )
  $sem = "P".sprintf("%02d",(date("y",$time)));
elseif ( $m > 6 && $m < 9)
  $sem = "A".date("y",$time);
else
{
  $cts = new contents("Pré-parrainage",
                      "Pas de campagne de pré-parrainage pour le moment");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}


// seul les gens ayant un compte peuvent venir ici
if ( !$site->user->is_valid() )
{
  $cts = new contents("Pré-parrainage",
                      "Pour accéder à cette page veuillez <a href=\"../index.php\">connecter</a> ".
                      "ou <a href=\"../newaccount.php\">Creer un compte</a>.");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
// les anciens ne peuvent pas encore accéder à cette partie, il faudra mettre en place
// la page "choisi" ton fillot
// $user->etudiant || $user->ancien_etudiant ne peuvent pas s'appliquer ici et c'est
// chiant.
elseif ( $site->user->utbm || $site->user->ae )
{
  $cts = new contents("Pré-parrainage",
                      "Le module de pré-parrainage est en cours de dévelopement, merci de votre compréhention");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}

$req = new requete($this->db, "SELECT `id_utl` FROM `pre_parrainage` WHERE `id_utilisateur` = '".$site->user->id."' AND `semestre` = '".$sem."'LIMIT 1");
if($req->lines==1)
{
  $cts = new contents("Pré-parrainage",
                      "Vous êtes déjà inscrit à la campagne en cours");
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
// partie pour les bijoux.
else
{
  $cts = new contents("Pré-parrainage",
                      "Sur cette page, vous allez pouvoir vous inscrire pour le pré-parrainage");
  $cts->add_title(2,"Informations");
  $cts->add_paragraph("Le pré-parrainage permet aux nouveaux étudiants d'être accompagnés par un étudiant de ".
                      "de l'UTBM dans ses démarches administratives, la découverte de Belfort, ...");
  $site->add_contents($cts);
  if(isset($_POST["etape"]))
  {
    if($_POST["etape"] == 3)
    {
      $_cts = new contents("Inscription : Etape 3/3");
      $_cts->add_paragraph("Ton inscription au préparrainage est effective. Tu recevras de plus d'informations dans les semaines à venir.");
      if($_POST['departement'] == "tc")
      {
        new insert($this->dbrw,"pre_parrainage", array('semestre'=>$sem,'id_utilisateur' => $site->user->id,'tc'=>1,'branche'=>$_POST["voeux"]));
        $_cts->add_paragraph("TC et voeux : " $_POST["voeux"]);
        $site->add_contents($_cts);
        $site->end_page();
        exit();
      }
      else
      {
        new insert($this->dbrw,"pre_parrainage", array('semestre'=>$sem,'id_utilisateur' => $site->user->id,'tc'=>0,'branche'=>$_POST["branche"]));
        $_cts->add_paragraph("branche : " $_POST["branche"]);
        $site->add_contents($_cts);
        $site->end_page();
        exit();
      }
    }
    if($_POST["etape"] == 2)
    {
      if(empty($_POST["addresse"]))
        $Erreur = "Vous devez renseigner votre adresse";
      else
      {
        $user->addresse = $_POST['addresse'];
        if ( $_POST['id_ville'] )
        {
          $ville->load_by_id($_POST['id_ville']);
          $user->id_ville = $ville->id;
          $user->id_pays = $ville->id_pays;
        }
        else
        {
          $user->id_ville = null;
          $user->id_pays = $_POST['id_pays'];
        }
        $user->tel_maison = telephone_userinput($_POST['tel_maison']);
        $user->tel_portable = telephone_userinput($_POST['tel_portable']);
        $user->date_maj = time();
        if ($user->saveinfos())
        {
          $_cts = new contents("Inscription : Etape 2/3");
          $_cts->add_paragraph("Information relative à votre cursus.");
          $frm = new form("infocursus","index.php",true,"POST","Cursus envisagé");
          $frm->add_hidden("etape","3");
          $frm->add_info("Département à votre arrivée vous serez :");
          $TC = new form("departement",null,null,null,"en tronc commun (TC)");
          $voeux=array();
          foreach($GLOBALS["utbm_departements"] AS $key => $value)
          {
            if($key!="tc" && $key!="na")
              $voeux[$key]=$value;
          }
          $TC->add_select_field("voeux","Branche envisagée",$voeux,$user->departement);
          $frm->add($TC,false,true,1,"tc",false,true,true);
          $branche = new form("departement",null,null,null,"en branche :");
          $branche->add_select_field("branche","Quelle branche ?",$voeux,$user->departement);
          $frm->add($branche,false,true,1,"tc",false,true,true);
          $_cts->add($frm,,false,true,0,"branche",false,true);
          $site->add_contents($_cts);
          $site->end_page();
          exit();
        }
        else
        {
          $cts = new contents("Erreur",
                              "Une erreur s'est produite, veuillez recommencer.");
          $site->add_contents($cts);
        }
      }
    }
  }
}


$cts = new contents("Inscription : Etape 1/3");
$cts->add_paragraph("Vous êtes sur le point de vous inscrire au système de pré-parrainage.");
$frm = new form("verifinfo","index.php",true,"POST","Vérifications des informations personnelles");
$frm->add_hidden("etape","2");
if ( isset($Erreur) )
  $frm->error($Erreur);
$frm->add_info("Si les informations suivantes ne sont pas correctes veuillez mettre à jour votre ".
               "<a href=\"".$topdir."user.php?page=edit\">compte</a>.");
$frm->add_text_field("nom","Nom",$user->nom,true,false,false,false);
$frm->add_text_field("prenom","Prenom",$user->prenom,true,false,false,false);
$frm->add_text_field("email","Votre adresse email",$user->email,true,false,false,false);
$frm->add_text_field("addresse","Adresse",$user->addresse,$true);
$frm->add_entity_smartselect ("id_ville","Ville (France)", $ville,true);
$frm->add_entity_smartselect ("id_pays","ou pays", $pays,true);
$frm->add_text_field("tel_maison","Telephone (fixe)",$user->tel_maison);
$frm->add_text_field("tel_portable","Telephone (portable)",$user->tel_portable);
$frm->add_submit("save","Enregistrer");
$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();

?>
