<?php

/* Copyright 2006
 *
 * - Maxime Petazzoni < sam at bulix dot org >
 * - Laurent Colnat < laurent dot colnat at utbm dot fr >
 * - Julien Etelain < julien at pmad dot net >
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

$topdir = "./";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/assoclub.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir . "include/carteae.inc.php");
require_once($topdir . "include/cotisation.inc.php");

$site = new site ();
$site->add_css("css/userfullinfo.css");
if ( $site->user->id < 1 )
  error_403();

if ( isset($_REQUEST['id_utilisateur']) )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  if ( $user->id < 0 )
    {
      header("Location: 404.php");
      exit();
    }

  $can_edit = ( $user->id==$site->user->id || $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));

  if ( $user->id != $site->user->id && !$site->user->utbm && !$site->user->ae )
    error_403("reserved");
    
  if ( !$user->publique && !$can_edit )
    error_403("reserved");
    
}
else
{
  $user = &$site->user;
  $can_edit = true;
}
 
if ( isset($_REQUEST['magicform']) && $_REQUEST['magicform']['name'] == "pass_reinit" && $site->user->is_in_group("gestion_ae") )
{
  if ( $GLOBALS["svalid_call"] && ( !empty($user->email_utbm) || !empty($user->email) ) )
	{
	  if ( $user->email_utbm )
		  $email = $user->email_utbm;
		else
		  $email = $user->email;
	  $pass = genere_pass(10);
		$user->invalidate();
		$user->change_password($pass);
		$body = "Bonjour,
Votre mot de passe sur le site de l'Association des Étudiants de
l'UTBM a été réinitialisé.

Votre nouveau mot de pass est : " . $pass . "

Pour valider ce nouveau mot de passe, veuillez vous rendre à l'adresse
http://ae.utbm.fr/confirm.php?id=" . $user->id . "&hash=" . $user->hash . "

Vous pouvez changer votre mot de passe en vous connectant sur le site
(http://ae.utbm.fr) puis en sélectionnant \"Informations
personnelles\".

L'équipe info AE";

    $ret = mail($email, "[Site AE] Réinitialisation du mot de passe", $body);
	}
}

if ( $_REQUEST["action"] == "delete" && $can_edit && isset($_REQUEST["id_membership"]))
{
  $_REQUEST["view"]="assos";
  list($id_asso,$date_debut) = explode(",",$_REQUEST["id_membership"]);
  $asso = new asso($site->db,$site->dbrw);
  $asso->load_by_id($id_asso);
  $asso->remove_member($user->id, strtotime($date_debut));
}

elseif ( $_REQUEST["action"] == "saveinfos" && $can_edit )
{
  if ( $_REQUEST["alias"] && !$user->is_alias_avaible($_REQUEST["alias"]) )
    {
      $ErreurMAJ = "Alias d&eacute;j&agrave;  utilis&eacute;";
      $_REQUEST["page"] = "edit";
    }
  else
    {
      $user->nom = $_REQUEST['nom'];
      $user->prenom = $_REQUEST['prenom'];
      $user->alias = $_REQUEST['alias'];
      $user->sexe = $_REQUEST['sexe'];
      $user->date_naissance = $_REQUEST['date_naissance'];
      $user->addresse = $_REQUEST['addresse'];
      $user->ville = $_REQUEST['ville'];
      $user->cpostal = $_REQUEST['cpostal'];
      $user->pays = $_REQUEST['pays'];
      $user->tel_maison = $_REQUEST['tel_maison'];
      $user->tel_portable = $_REQUEST['tel_portable'];
      $user->date_maj = time();
      
      $user->publique = isset($_REQUEST["publique"]);
      $user->publique_mmtpapier = isset($_REQUEST["publique_mmtpapier"]);

      if ( $user->etudiant || $user->ancien_etudiant )
        {
          $user->citation = $_REQUEST['citation'];
          $user->pays_parents = $_REQUEST['pays_parents'];
          $user->adresse_parents = $_REQUEST['adresse_parents'];
          $user->ville_parents = $_REQUEST['ville_parents'];
          $user->cpostal_parents = $_REQUEST['cpostal_parents'];
          $user->tel_parents = $_REQUEST['tel_parents'];
          $user->nom_ecole_etudiant = $_REQUEST['nom_ecole'];
        }
      if ( $user->utbm )
        {
          $user->surnom = $_REQUEST['surnom'];
          $user->semestre = $_REQUEST['semestre'];
          $user->branche = $_REQUEST['branche'];
          $user->filiere = $_REQUEST['filiere'];
          $user->promo_utbm = $_REQUEST['promo'];

          if ( $_REQUEST['date_diplome'] < time() && $_REQUEST['date_diplome'] != 0 && $_REQUEST['date_diplome'] != "" )
            $user->date_diplome_utbm = $_REQUEST['date_diplome'];
          else
            $user->date_diplome_utbm = NULL;
        }
      if ($user->saveinfos())
        {
          header("Location: ".$topdir."user.php?id_utilisateur=".$user->id);
          exit();
        }

    }
}

elseif ( $_REQUEST["action"] == "changepassword" && $can_edit )
{
  if ( $_REQUEST["ae2_password"] && ($_REQUEST["ae2_password"] == $_REQUEST["ae2_password2"]) )
    $user->change_password($_REQUEST["ae2_password"]);
  else
    $_REQUEST["page"] = "edit";

}

elseif ( $_REQUEST["action"] == "addparrain" && $can_edit )
{
  $user2 = new utilisateur($site->db);
  $user2->load_by_id($_REQUEST["id_utilisateur_parrain"]);
  if ( $user2->id > 0 )
    {
		  if ( $user2->id == $user->id )
			  $ErreurParrain = "On joue pas au boulet !";
      else
			  $user->add_parrain($user2->id);
    }
  else
    $ErreurParrain = "Utilisateur inconnu.";
}

elseif ( $_REQUEST["action"] == "addfillot" && $can_edit )
{
  $user2 = new utilisateur($site->db);
  $user2->load_by_id($_REQUEST["id_utilisateur_fillot"]);
  if ( $user2->id > 0 )
    {
		  if ( $user2->id == $user->id )
			  $ErreurParrain = "On joue pas au boulet !";
		  else
        $user->add_fillot($user2->id);
    }
  else
    $ErreurFillot = "Utilisateur inconnu.";
}

elseif ( $_REQUEST["action"] == "setgroups" &&
         (($site->user->is_in_group("gestion_ae")
           && ( $site->user->is_in_group("root") || $site->user->id != $user->id ))||$site->user->is_in_group("root")) )
{
  $req = new requete($site->db,
                     "SELECT `groupe`.`id_groupe`, `groupe`.`nom_groupe`, `utl_groupe`.`id_utilisateur` ".
                     "FROM `groupe` " .
                     "LEFT JOIN `utl_groupe` ON (`groupe`.`id_groupe`=`utl_groupe`.`id_groupe` " .
                     " AND `utl_groupe`.`id_utilisateur`='".$user->id."' ) " .
                     "ORDER BY `groupe`.`nom_groupe`");

  while ( $row=$req->get_row())
    {
      $new=$_REQUEST["groups"][$row["id_groupe"]]==true;
      $old=$row["id_utilisateur"]!="";
      if ( $new != $old )
        {
          if ( $new )
            $user->add_to_group($row["id_groupe"]);
          else
            $user->remove_from_group($row["id_groupe"]);
        }
    }

}

elseif ( $_REQUEST["action"]=="addme" )
{
  $asso = new asso($site->db,$site->dbrw);
  $asso->load_by_id($_REQUEST["id_asso"]);

  if ( $asso->id > 0 && $asso->id_parent )
    {

      if ( ($_REQUEST["date_debut"] <= time()) && ($_REQUEST["date_debut"] > 0) )
        $asso->add_actual_member ( $user->id, $_REQUEST["date_debut"], ROLEASSO_MEMBRE, $_REQUEST["role_desc"] );
      else
        $ErreurAddMe = "Donn&eacute;es invalides";
    }
  else
    $ErreurAddMe = "Non autoris&eacute; sur cette association.";

}

elseif ( $_REQUEST["action"]=="addmeformer" )
{
  $asso = new asso($site->db,$site->dbrw);
  $asso->load_by_id($_REQUEST["id_asso"]);

  if ( $asso->id > 0 )
    {
      if ($asso->id_parent < 1 && $_REQUEST["role"] < 2)
        $ErreurAddMeFormer = "Non autoris&eacute; sur cette association.";

      elseif ( isset($GLOBALS['ROLEASSO'][$_REQUEST["role"]]) &&
               ($_REQUEST["former_date_debut"] < $_REQUEST["former_date_fin"]) &&
               ($_REQUEST["former_date_fin"] < time()) && ($_REQUEST["former_date_debut"] > 0) )
        $asso->add_former_member ( $user->id, $_REQUEST["former_date_debut"],
                                   $_REQUEST["former_date_fin"], $_REQUEST["role"], $_REQUEST["role_desc"] );
      else
        $ErreurAddMeFormer = "DonnÃ©es invalides";
    }
}

elseif ( $_REQUEST["action"] == "delete" && $_REQUEST["mode"] == "parrain" && $can_edit  )
{
  $user->remove_parrain($_REQUEST["id_utilisateur2"]);
}

elseif ( $_REQUEST["action"] == "delete" && $_REQUEST["mode"] == "fillot" && $can_edit  )
{
  $user->remove_fillot($_REQUEST["id_utilisateur2"]);
}

elseif ( $_REQUEST["action"] == "changeemail" && $can_edit  )
{
  if ( !CheckEmail($_POST["email"], 3) )
    {
      $ErreurMail="Adresse email invalide.";
      $_REQUEST["page"] = "edit";
      $_REQUEST["open"]="email";
    }
  else
    $user->set_email($_POST["email"], $site->user->is_in_group("gestion_ae"));
}

elseif ( $_REQUEST["action"] == "changeemailutbm" && $can_edit  )
{
  if ( !CheckEmail($_POST["email_utbm"], 1) && !CheckEmail($_POST["email_utbm"], 2) )
    {
      $ErreurMailUtbm="Adresse email invalide : prenom.nom@utbm.fr ou prenom.nom@assidu-utbm.fr";
      $_REQUEST["page"] = "edit";
      $_REQUEST["open"]="email";
    }
  elseif ( !$user->utbm )
    {
      $user->became_utbm($_POST["email_utbm"], $site->user->is_in_group("gestion_ae"));
    }
  else
    $user->set_email_utbm($_POST["email_utbm"], $site->user->is_in_group("gestion_ae"));
}

elseif ( $_REQUEST["action"] == "reprint" && $site->user->is_in_group("gestion_ae") )
{
  $carte = new carteae($site->db,$site->dbrw);
  $carte->load_by_utilisateur($user->id);
  $carte->set_state(CETAT_ATTENTE);
}

if ( $_REQUEST["action"] == "setphotos" && $can_edit )
{
	$dest_idt = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg";
	/*if( isset($_REQUEST['delete_idt']) && file_exists($dest_idt))
		unlink($dest_idt);*/
	if ( is_uploaded_file($_FILES['idtfile']['tmp_name'])  )
    {
      $src = $_FILES['idtfile']['tmp_name'];
      $dest = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg";
      exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 $dest_idt");
    }

	$dest_mmt = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".jpg";
	if( isset($_REQUEST['delete_mmt']) && file_exists($dest_mmt))
		unlink($dest_mmt);
	if ( is_uploaded_file($_FILES['mmtfile']['tmp_name'])  )
    {
      $src = $_FILES['mmtfile']['tmp_name'];
      exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 $dest_mmt");
    }
	$_REQUEST["page"] = "edit";
	$_REQUEST["open"] = "photo";
}

if ( $_REQUEST["action"] == "setblouse" && $can_edit )
{
	$dest = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".blouse.jpg";
	$dest_mini = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".blouse.mini.jpg";
	if( isset($_REQUEST['delete_blouse']) && file_exists($dest))
	{
		unlink($dest);
		unlink($dest_mini);
	}
	if ( is_uploaded_file($_FILES['blousefile']['tmp_name'])  )
    {
      $src = $_FILES['blousefile']['tmp_name'];
      exec("/usr/share/php5/exec/convert $src -thumbnail 1600x1600 -quality 80 $dest");
      exec("/usr/share/php5/exec/convert $src -thumbnail 225x300 -quality 90 $dest_mini");
    }
  $_REQUEST["page"] = "edit";
  $_REQUEST["open"] = "blouse";
}

$tabs = array(array("","user.php?id_utilisateur=".$user->id, "Informations"),
              array("parrain","user.php?view=parrain&id_utilisateur=".$user->id, "Parrains"),
              array("assos","user.php?view=assos&id_utilisateur=".$user->id, "Associations"),
              array(-1,"sas2/user.php?id_utilisateur=".$user->id, "Photos"));

if (  $user->id==$site->user->id || $site->user->is_in_group("gestion_ae") )
{
  $tabs[]=array(-1,"reservation.php?id_utilisateur=".$user->id, "Reservations");
  $tabs[]=array(-1,"emprunts.php?id_utilisateur=".$user->id, "Emprunts");
}

if ( ( $site->user->is_in_group("gestion_ae") && ( $site->user->is_in_group("root") || $site->user->id != $user->id ) ) || $site->user->is_in_group("root"))
  $tabs[]=array("groups","user.php?view=groups&id_utilisateur=".$user->id, "Groupes");



if ( $_REQUEST["page"] == "edit" && $can_edit )
{
  $site->start_page("matmatronch",$user->prenom." ".$user->nom);

  $user->load_all_extra();
  $cts = new contents($user->prenom." ".$user->nom);

  $cts->add(new tabshead($tabs,$_REQUEST["view"]));

  $frm = new form("infoperso","user.php?id_utilisateur=".$user->id,true,"POST","Informations personelles");
  $frm->add_hidden("action","saveinfos");
  if ( $ErreurMAJ )
    $frm->error($ErreurMAJ);
  if ($site->user->is_asso_role ( 27, 1 ) || $site->user->is_in_group("gestion_ae") )
    {
      $frm->add_text_field("nom","Nom",$user->nom,true,false,false,true);
      $frm->add_text_field("prenom","Prenom",$user->prenom,true,false,false,true);
    }
  else
    {
      $frm->add_text_field("nom","Nom",$user->nom,true,false,false,false);
      $frm->add_text_field("prenom","Prenom",$user->prenom,true,false,false,false);
      $frm->add_hidden("nom", $user->nom);
      $frm->add_hidden("prenom", $user->prenom);
    }
  $frm->add_text_field("alias","Alias",$user->alias);
  if ( $user->utbm )
    $frm->add_text_field("surnom","Surnom (utbm)",$user->surnom);
  $frm->add_select_field("sexe","Sexe",array(1=>"Homme",2=>"Femme"),$user->sexe);
  $frm->add_date_field("date_naissance","Date de naissance",$user->date_naissance);

  if ( $user->utbm )
    {
      $frm->add_select_field("branche","Branche",array("TC"=>"TC","GI"=>"GI","GSP"=>"IMAP","GSC"=>"GESC","GMC"=>"GMC","Enseignant"=>"Enseignant","Administration"=>"Administration","Autre"=>"Autre"),$user->branche);
      $frm->add_text_field("semestre","Semestre",$user->semestre);
    }
  $subfrm1 = new form("infocontact",null,null,null,"Adresse et tÃ©lÃ©phone");

  $subfrm1->add_text_field("addresse","Adresse",$user->addresse);
  $subfrm1->add_text_field("ville","Ville",$user->ville);
  $subfrm1->add_text_field("cpostal","Code postal",$user->cpostal);
  if ($user->pays)
    $subfrm1->add_text_field("pays","Pays",$user->pays);
  else
    $subfrm1->add_text_field("pays","Pays","FRANCE");
  $subfrm1->add_text_field("tel_maison","Telephone (fixe)",$user->tel_maison);
  $subfrm1->add_text_field("tel_portable","Telephone (portable)",$user->tel_portable);
  $frm->add ( $subfrm1, false, false, false, false, false, true, false );

  if ( $user->etudiant || $user->ancien_etudiant )
    {
      $subfrm2 = new form("infoextra",null,null,null,"Informations suppl&eacute;mentaires");
      $subfrm2->add_text_field("citation","Citation",$user->citation,false,"60");
      $subfrm2->add_text_field("nom_ecole","Ecole",$user->nom_ecole_etudiant);
      $frm->add ( $subfrm2, false, false, false, false, false, true, false );

      $subfrm3 = new form("infoparents",null,null,null,"Informations sur les parents");
      $subfrm3->add_text_field("adresse_parents","Adresse parents",$user->adresse_parents);
      $subfrm3->add_text_field("ville_parents","Ville parents",$user->ville_parents);
      $subfrm3->add_text_field("cpostal_parents","Code postal parents",$user->cpostal_parents);
      $subfrm3->add_text_field("pays_parents","Pays parents",$user->pays_parents);
      $subfrm3->add_text_field("tel_parents","T&eacute;l&eactue;phone parents",$user->tel_parents);
      $frm->add ( $subfrm3, false, false, false, false, false, true, false );
    }

  if ( $user->utbm )
    {
      $subfrm4 = new form("infoutbm",null,null,null,"Informations UTBM");

      $subfrm4->add_text_field("filiere","Filiere",$user->filiere);
      $subfrm4->add_select_field("promo","Promo",array(0=>"-",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10"),$user->promo_utbm);
      $subfrm4->add_date_field("date_diplome","Date d'obtention du diplome",($user->date_diplome_utbm!=NULL)?$user->date_diplome_utbm:null);
      $frm->add ( $subfrm4, false, false, false, false, false, true, false );
    }
    
  $frm->add_checkbox ( "publique", "Rendre mon profil publique : Apparaitre dans le matmatronch en ligne.", $user->publique );
  $frm->add_checkbox ( "publique_mmtpapier", "Autoriser la publication de mon profil dans le matmatronch papier.", $user->publique_mmtpapier );
    
  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true);

  $frm = new form("changeemail","user.php?id_utilisateur=".$user->id,true,"POST","Adresse email");
  if ( $ErreurMail )
    $frm->error($ErreurMail);
  $frm->add_hidden("action","changeemail");
  $frm->add_info("<b>ATTENTION</b>: Votre compte sera d&eacute;sactiv&eacute; et votre session sera ferm&eacute;e jusqu'a validation du lien qui vous sera envoye par email &agrave; l'adresse que vous pr&eacute;ciserez !");

  $frm->add_text_field("email","Adresse email",$user->email,true);
  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true, false, "chmailbx", false, true, $_REQUEST["open"]=="email");

  $frm = new form("changeemailutbm","user.php?id_utilisateur=".$user->id,true,"POST","Adresse email UTBM ou ASSIDU");
  if ( $ErreurMailUtbm )
    $frm->error($ErreurMailUtbm);
  $frm->add_hidden("action","changeemailutbm");
  $frm->add_info("<b>ATTENTION</b>: Votre compte sera d&eacute;sactiv&eacute; et votre session sera ferm&eacute;e jusqu'a validation du lien qui vous sera envoye par email &agrave; l'adresse que vous pr&eacute;ciserez !");
  $frm->add_text_field("email_utbm","Adresse email",$user->email_utbm?$user->email_utbm:"prenom.nom@utbm.fr",true);
  /*	if ( !$user->utbm )
		$frm->add_checkbox("etudiant","Je suis Ã©tudiant");*/
  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true, false, "chmailutbx", false, true, $_REQUEST["open"]=="emailutbm");

  $frm = new form("changepassword","user.php?id_utilisateur=".$user->id,true,"POST","Changer de mot de passe");
  $frm->add_hidden("action","changepassword");
  $frm->add_password_field("ae2_password","Mot de passe","",true);
  $frm->add_password_field("ae2_password2","Repetez le mot de passe","",true);
  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true, false, "chpssbx", false, true, false);


  $frm = new form("setphotos","user.php?id_utilisateur=".$user->id."#setphotos",true,"POST","Changer mes photos persos");
  $frm->add_hidden("action","setphotos");
  //$frm->add_info("<b>ATTENTION</b>: Apr&egrave;s l'envoi d'une nouvelle photo, il se peut que le cache de votre navigateur ne se mette pas &agrave; jour imm&eacute;diatemment. Pour cela, faites sur l'image : <i>Clic droit &gt; Voir l'image</i> et forcez un raffraichissement de la page (avec Ctrl+Shift+R). Votre photo devrait se mettre &agrave; jour.");

  $subfrm = new form("mmt",null,null,null,"Photo matmatronch");
  if ( file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".jpg") )
	{
  	$exif = @exif_read_data("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".jpg", 0, true);
    if ( $exif["FILE"]["FileDateTime"] )
		  $date_prise_vue = $exif["FILE"]["FileDateTime"];
		$subfrm->add_info("<img src=\"".$topdir."var/img/matmatronch/".$user->id.".jpg?".$date_prise_vue."\" alt=\"\" width=\"100\" /><br/>");
  }
	$subfrm->add_file_field ( "mmtfile", "Fichier" );
	$subfrm->add_checkbox("delete_mmt","Supprimer ma photo matmatronch");
  $frm->add ( $subfrm );

  $subfrm = new form("idt",null,null,null,"Photo identit&eacute; (carte AE)");

  if ( file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg") )
    {
		  $date_prise_vue="";
		  $exif = @exif_read_data("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg", 0, true);
			if ( $exif["FILE"]["FileDateTime"] )
			  $date_prise_vue = $exif["FILE"]["FileDateTime"];
      $subfrm->add_info("<img src=\"".$topdir."var/img/matmatronch/".$user->id.".identity.jpg?".$date_prise_vue."\" alt=\"\" width=\"100\" /><br/>");
      if ($site->user->is_asso_role ( 27, 1 ) || $site->user->is_in_group("gestion_ae"))
	  {
        $subfrm->add_file_field ( "idtfile", "Fichier" );
		$carte = new carteae($site->db);
		$carte->load_by_utilisateur($site->user->id);
		if ( !$carte->is_valid() )
			$subfrm->add_checkbox("delete_idt","Supprimer la photo d'identit&eacute;");
	  }
    }
  else
    $subfrm->add_file_field ( "idtfile", "Fichier" );

  $frm->add ( $subfrm );
  $frm->add_submit("save","Enregistrer");

  $cts->add($frm,true, false, "setphotosbx", false, true, $_REQUEST["open"]=="photo");

  $frm = new form("setblouse","user.php?id_utilisateur=".$user->id."#setblouse",true,"POST","Changer la photo de ma blouse");
  $frm->add_hidden("action","setblouse");
  $subfrm = new form("blouse",null,null,null,"Photo de la blouse");

  if ( file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".blouse.mini.jpg") )
    $subfrm->add_info("<img src=\"".$topdir."var/img/matmatronch/".$user->id.".blouse.mini.jpg\" alt=\"\" width=\"100\" /><br/>");
  $subfrm->add_file_field ( "blousefile", "Fichier" );
  $subfrm->add_checkbox("delete_blouse","Supprimer la photo de ma blouse");
  $frm->add ( $subfrm );
  $frm->add_submit("save","Enregistrer");

  $cts->add($frm,true, false, "setblousebx", false, true, $_REQUEST["open"]=="blouse");

  $site->add_contents($cts);//200x251
  $site->end_page();
  exit();
}

$site->start_page("matmatronch", $user->prenom . " " . $user->nom );

if ($site->user->is_in_group("gestion_ae") && !$user->ae)
  $img = "<img src=\"".$topdir."images/carteae/mini_non_ae.png\">\n";
elseif ($site->user->is_in_group("gestion_ae") && $user->ae)
  $img = "<img src=\"".$topdir."images/carteae/mini_ae.png\">\n";

$cts = new contents ($img . " " . $user->prenom . " " . $user->nom );

$req = new requete($site->db, "SELECT `asso_membre`.`id_utilisateur` FROM `asso_membre` WHERE `asso_membre`.`id_asso` = '27' AND `asso_membre`.`date_fin` IS NULL AND `asso_membre`.`id_utilisateur` = '".$site->user->id."' AND `asso_membre`.`role` > 0 ORDER BY `asso_membre`.`role` DESC");

if ( $can_edit  || $req->lines )
  $cts->set_toolbox(new toolbox(array("user.php?id_utilisateur=".$user->id."&page=edit"=>"Modifier")));

$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if ( $_REQUEST["view"]=="parrain" )
{
  $cts->add_paragraph("<a href=\"family.php?id_utilisateur=".$user->id."\">".
                      "Arbre g&eacute;n&eacute;alogique parrains/fillots</a>");

  $req = new requete($site->db,
                "SELECT `utilisateurs`.`id_utilisateur` AS `id_utilisateur2`, " .
                "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur2` " .
                "FROM `parrains` " .
                "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`parrains`.`id_utilisateur` " .
                "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
                "WHERE `parrains`.`id_utilisateur_fillot`='".$user->id."'");

  $tbl = new sqltable(
                      "listasso",
                      "Parrain(s)/Marraine(s)", $req, "user.php?view=parrain&mode=parrain&id_utilisateur=".$user->id,
                      "id_utilisateur2",
                      array("nom_utilisateur2"=>"Parrain/Marraine"),
                      array("delete"=>"Enlever"), array(), array( )
                      );
  $cts->add($tbl,true);

  $req = new requete($site->db,
                "SELECT `utilisateurs`.`id_utilisateur` AS `id_utilisateur2`, " .
                "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur2` " .
                "FROM `parrains` " .
                "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`parrains`.`id_utilisateur_fillot` " .
                "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
                "WHERE `parrains`.`id_utilisateur`='".$user->id."'");

  $tbl = new sqltable(
                      "listasso",
                      "Fillot(s)/Fillote(s)", $req, "user.php?view=parrain&mode=fillot&id_utilisateur=".$user->id,
                      "id_utilisateur2",
                      array("nom_utilisateur2"=>"Fillot/Fillote"),
                      array("delete"=>"Enlever"), array(), array( )
                      );
  $cts->add($tbl,true);

  if ( $can_edit )
  {
    $frm = new form("addparrain","user.php?view=parrain&id_utilisateur=".$user->id,true,"POST","Ajouter un parrain/une marraine");
    $frm->add_hidden("action","addparrain");
    if ( $ErreurParrain ) $frm->error($ErreurParrain);
    $frm->add_user_fieldv2("id_utilisateur_parrain","Parrain");
    $frm->add_submit("addresp","Ajouter");
    $cts->add($frm,true);
  
  
    $frm = new form("addfillot","user.php?view=parrain&id_utilisateur=".$user->id,true,"POST","Ajouter un fillot/une fillote");
    $frm->add_hidden("action","addfillot");
    if ( $ErreurFillot ) $frm->error($ErreurFillot);
    $frm->add_user_fieldv2("id_utilisateur_fillot","Fillot");
    $frm->add_submit("addresp","Ajouter");
    $cts->add($frm,true);
  }

}
elseif ( $_REQUEST["view"]=="assos" )
{

  /* Associations en cours */
  $req = new requete($site->db,
                     "SELECT `asso`.`id_asso`, `asso`.`nom_asso`, " .
                     "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`, ".
                     "`asso_membre`.`date_debut`, " .
                     "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                     "FROM `asso_membre` " .
                     "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                     "WHERE `asso_membre`.`id_utilisateur`='".$user->id."' " .
                     "AND `asso_membre`.`date_fin` is NULL " .
                     "ORDER BY `asso`.`nom_asso`");
  if ( $req->lines > 0 )
    {
      $tbl = new sqltable(
                          "listasso",
                          "Associations actuelles", $req, "user.php?id_utilisateur=".$user->id,
                          "id_membership",
                          array("nom_asso"=>"Association","role"=>"Role","date_debut"=>"Depuis"),
                          $can_edit?array("delete"=>"Supprimer"):array(), array(), array("role"=>$GLOBALS['ROLEASSO100'] )
                          );
      $cts->add($tbl,true);
    }

  /* Anciennes assos */
  $req = new requete($site->db,
                     "SELECT `asso`.`id_asso`, `asso`.`nom_asso`, " .
                     "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`, ".
                     "`asso_membre`.`date_debut`, `asso_membre`.`date_fin`, " .
                     "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                     "FROM `asso_membre` " .
                     "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                     "WHERE `asso_membre`.`id_utilisateur`='".$user->id."' " .
                     "AND `asso_membre`.`date_fin` is NOT NULL " .
                     "ORDER BY `asso`.`nom_asso`,`asso_membre`.`date_debut`");
  if ( $req->lines > 0 )
    {
      $tbl = new sqltable(
                          "listassoformer",
                          "Associations", $req, "user.php?id_utilisateur=".$user->id,
                          "id_membership",
                          array("nom_asso"=>"Association","role"=>"Role","date_debut"=>"Date de d&eacute;but","date_fin"=>"Date de fin"),
                          $can_edit?array("delete"=>"Supprimer"):array(), array(), array("role"=>$GLOBALS['ROLEASSO100'] )
                          );
      $cts->add($tbl,true);
    }


  if ( $can_edit )
    {
      $frm = new form("addme","user.php?view=assos&id_utilisateur=".$user->id,false,"POST","Ajouter comme membre");
      if ( $ErreurAddMe )
        $frm->error($ErreurAddMe);
      $frm->add_hidden("action","addme");
      $frm->add_info("<b>Attention</b> : Si vous &ecirc;tes membre du bureau (tresorier, secretaire...) ou membre actif veuillez vous adresser au responsable de l'association/du club. Si vous &ecirc;tes le responsable, merci de vous adresser au bureau de l'AE.");
      $frm->add_entity_select ( "id_asso", "Association/Club", $site->db, "asso");
      $frm->add_date_field("date_debut","Depuis le",time(),true);
      $frm->add_submit("valid","Ajouter");
      $cts->add($frm,true);

      $frm = new form("addmeformer","user.php?view=assos&id_utilisateur=".$user->id,false,"POST","Ajouter comme un ancien membre");
      $frm->add_hidden("action","addmeformer");
      if ( $ErreurAddMeFormer )
        $frm->error($ErreurAddMeFormer);
      $frm->add_entity_select ( "id_asso", "Association/Club", $site->db, "asso");
      $frm->add_text_field("role_desc","Role (champ libre)","");
      $frm->add_select_field("role","Role",$GLOBALS['ROLEASSO']);
      $frm->add_date_field("former_date_debut","Date de d&eacute;but",-1,true);
      $frm->add_date_field("former_date_fin","Date de fin",-1,true);
      $frm->add_submit("valid","Ajouter");
      $cts->add($frm,true);
    }
}

elseif ( ($_REQUEST["view"]=="groups") &&
         (($site->user->is_in_group("gestion_ae") &&
           ( $site->user->is_in_group("root") || $site->user->id != $user->id ))||$site->user->is_in_group("root")) )
{
  $user->load_all_extra();
  /* groupes */
  $frm = new form("setattributes","user.php?view=groups&id_utilisateur=".$user->id,true,"POST","Attribus");
  $frm->add_hidden("action","setattributes");
  $frm->add_checkbox("ae","ae",$user->ae,true);
  $frm->add_checkbox("utbm","utbm",$user->utbm, !$user->email_utbm);
  $frm->add_checkbox("etudiant","etudiant",$user->etudiant);
  $frm->add_checkbox("ancien_etudiant","ancien_etudiant",$user->ancien_etudiant);

  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true);


  $req = new requete($site->db,
                     "SELECT `groupe`.`id_groupe`, `groupe`.`nom_groupe`, `utl_groupe`.`id_utilisateur` ".
                     "FROM `groupe` " .
                     "LEFT JOIN `utl_groupe` ON (`groupe`.`id_groupe`=`utl_groupe`.`id_groupe`" .
                     " AND `utl_groupe`.`id_utilisateur`='".$user->id."' ) " .
                     "ORDER BY `groupe`.`nom_groupe`");

  $frm = new form("setgroups","user.php?view=groups&id_utilisateur=".$user->id,true,"POST","Groupes");
  $frm->add_hidden("action","setgroups");
  while ( $row=$req->get_row())
    $frm->add_checkbox("groups|".$row["id_groupe"],entitylink ("group", $row["id_groupe"], $row["nom_groupe"] ),
                       $row["id_utilisateur"]!="");

  $frm->add_submit("save","Enregistrer");
  $cts->add($frm,true);
}

else
{
  if ( $site->user->id != $user->id )
	{
	  $stats = new requete($site->dbrw, "UPDATE `utl_etu` SET `visites`=`visites`+1 WHERE `id_utilisateur`=".$user->id);
	}
  $user->load_all_extra();
  
  //$cts->add(new userinfo($user,false,$site->user->is_in_group("gestion_ae"),true,$site->user->is_in_group("gestion_ae"),false));

  $cts->add(new userinfov2($user,"full",$site->user->is_in_group("gestion_ae")));

  if ( $site->user->id == $user->id && !$user->ae )
    {
      $cts->add_title(2, "Cotisation AE");
      $cts->add_paragraph("<img src=\"" . $topdir . "images/carteae/mini_non_ae.png\">" .
                          "<b><font color=\"red\">&nbsp;&nbsp;Attention, aucune cotisation " .
                          "&agrave; l'AE trouv&eacute;e !</font></b>");

      $cts->add_paragraph("<br/>R&eacute;flexe E-boutic ! <a href=\"" . $topdir .
                          "e-boutic/?cat=23\">Renouveler sa cotisation en ligne : </a><br /><br />");
      $cts->puts("<center><a href=\"".$topdir."e-boutic/?act=add&item=94&cat=23\"><img src=\"" .
                 $topdir . "images/comptoir/eboutic/prod-ae-an.png\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
      $cts->puts("<a href=\"".$topdir."e-boutic/?act=add&item=93&cat=23\"><img src=\"" . $topdir .
                 "images/comptoir/eboutic/prod-ae-sem.png\"></a></center>");
    }

  if ( $site->user->is_in_group("gestion_ae") )
    {
      $req = new requete($site->db, "SELECT " .
                         "CONCAT(`cpt_debitfacture`.`id_facture`,',',`cpt_produits`.`id_produit`) AS `id_factprod`, " .
                         "`cpt_debitfacture`.`id_facture`, " .
                         "`cpt_debitfacture`.`date_facture`, " .
                         "`asso`.`id_asso`, " .
                         "`asso`.`nom_asso`, " .
                         "`cpt_vendu`.`a_retirer_vente`, " .
                         "`cpt_vendu`.`a_expedier_vente`, " .
                         "`cpt_vendu`.`quantite`, " .
                         "`cpt_vendu`.`prix_unit`/100 AS `prix_unit`, " .
                         "`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`/100 AS `total`," .
                         "`cpt_produits`.`nom_prod`, " .
                         "`cpt_produits`.`id_produit` " .
                         "FROM `cpt_vendu` " .
                         "INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
                         "INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
                         "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
                         "WHERE `id_utilisateur_client`='".$user->id."' AND (`cpt_vendu`.`a_retirer_vente`='1' OR `cpt_vendu`.`a_expedier_vente`='1') " .
                         "ORDER BY `cpt_debitfacture`.`date_facture` DESC");

      if ($req->lines)
        {
          $items="";
          while ( $item = $req->get_row() )
            {
              if ( $item['a_retirer_vente'])
                {
                  $noms=array();

                  $req2 = new requete($site->db,
                                      "SELECT `cpt_comptoir`.`nom_cpt`
		  		FROM `cpt_mise_en_vente`
		  		INNER JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` = `cpt_mise_en_vente`.`id_comptoir`
		  		WHERE `cpt_mise_en_vente`.`id_produit` = '".$item['id_produit']."' AND `cpt_comptoir`.`type_cpt`!=1");

                  if ( $req2->lines != 0 )
                    while ( list($nom) = $req2->get_row() )
                      $noms[] = $nom;

                  $item["info"] = utf8_encode("A venir retirer à : ").implode(" ou ",$noms);
                }
              else if ( $item['a_expedier_vente'])
                {
                  $item["info"] = "En preparation";
                }
              $items[]=$item;
            }

          $cts->add(new sqltable(
                                 "listresp",
                                 utf8_encode("Commandes à retirer"), $items,
                                 $topdir."comptoir/encours.php?id_utilisateur=".$user->id,
                                 "id_factprod",
                                 array(
                                       "nom_prod"=>"Produit",
                                       "quantite"=>utf8_encode("Quantité"),
                                       "prix_unit"=>"Prix unitaire",
                                       "total"=>"Total",
                                       "info"=>""),
                                 array(),
                                 $site->user->is_in_group("gestion_ae")?array("retires"=>utf8_encode("Marquer comme retiré")):array(),
                                 array()), true);
        }
    }

  /* l'onglet AE */
  if ( $can_edit && $user->ae )
    {
      $cts->add_title(2, "Cotisation AE");

      if ( !file_exists("/var/www/ae/www/ae2/var/img/matmatronch/" . $user->id .".identity.jpg"))
        $cts->add_paragraph("<img src=\"".$topdir."images/actions/delete.png\"><b>ATTENTION</b>: " .
                            "<a href=\"user.php?page=edit&amp;id_utilisateur=".$user->id.
                            "&amp;open=photo#setphotos\">Photo d'identit&eacute; non pr&eacute;sente !</a>");

      $req = new requete($site->db, "SELECT `date_fin_cotis` FROM `ae_cotisations`
                                       WHERE `id_utilisateur`='".$user->id."'
                                       AND `date_fin_cotis` >= '" . date("Y-m-d") . "'
                                       ORDER BY `date_fin_cotis` DESC LIMIT 1");
      if ($req->lines > 1)
        $cts->add_paragraph("ATTENTION: Plusieurs cotisations en cours.");
      elseif ($req->lines != 1)
        $cts->add_paragraph("ATTENTION: Cotisation non enregistr&eacute;e ou etat non &agrave; jour.");

      else
      {
        $res = $req->get_row();
  
        $year = explode("-", $res['date_fin_cotis']);
        $year = $year[0];
        $cts->add_paragraph("<img src=\"" . $topdir . "images/carteae/mini_ae.png\">&nbsp;&nbsp;" .
                            "Cotisant(e) AE jusqu'au " .
                            HumanReadableDate($res['date_fin_cotis'], null, false) . " $year !");
  
        $req = new requete($site->db,"SELECT `id_carte_ae`, `etat_vie_carte_ae` FROM `ae_carte` INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` WHERE `ae_cotisations`.`id_utilisateur`='".$user->id."' AND `ae_carte`.`etat_vie_carte_ae`<".CETAT_EXPIRE."");
  
        $tbl = new sqltable(
                            "listasso",
                            "Ma carte AE", $req, "user.php?id_utilisateur=".$user->id,
                            "id_carte_ae",
                            array("id_carte_ae"=>"N°","etat_vie_carte_ae"=>"Etat"),
                            $site->user->is_in_group("gestion_ae")?array("reprint"=>"Re-imprimer carte"):array(),
                            array(), array("etat_vie_carte_ae"=>$EtatsCarteAE )
                            );
        $cts->add($tbl,true);
      }
    }
	if ( $site->user->is_in_group("gestion_ae") )
	{
	  $frm = new form("pass_reinit", "user.php?id_utilisateur=".$user->id, true, "POST", "R&eacute;initialiser le mot de passe");
		$frm->allow_only_one_usage();
		$frm->add_submit("valid","R&eacute;initialiser !");
		$cts->add($frm,true);
	}

  /* Associations en cours */
  $req = new requete($site->db,
                     "SELECT `asso`.`id_asso`, `asso`.`nom_asso`, " .
                     "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`,
                      `asso_membre`.`date_debut`, " .
                     "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                     "FROM `asso_membre` " .
                     "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                     "WHERE `asso_membre`.`id_utilisateur`='".$user->id."' " .
                     "AND `asso_membre`.`date_fin` is NULL " .
                     "ORDER BY `asso`.`nom_asso`");
  if ( $req->lines > 0 )
    {
      $tbl = new sqltable(
                          "listasso",
                          "Mes associations", $req, "user.php?id_utilisateur=".$user->id,
                          "id_membership",
                          array("nom_asso"=>"Association","role"=>"Role","date_debut"=>"Depuis"),
                          $can_edit?array("delete"=>"Supprimer"):array(), array(), array("role"=>$GLOBALS['ROLEASSO100'] )
                          );
      $cts->add($tbl,true);
    }

}

/* c'est tout */
$site->add_contents($cts);

$site->end_page();

?>
