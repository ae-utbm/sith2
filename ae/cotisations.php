<?php
/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Ã‰tudiants de
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
$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/cotisation.inc.php");
require_once($topdir. "include/cts/special.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") )
  error_403();

if (date("m-d") < "02-15")
{
  $date1 = date("Y") . "-02-15";
  $date2 = date("Y") . "-08-15";
}
else
{
  if (date("m-d") < "08-15")
    {
      $date1 = date("Y") . "-08-15";
      $date2 = date("Y") + 1 . "-02-15";
    }
  else
    {
      $date1 = date("Y") + 1 . "-02-15";
      $date2 = date("Y") + 1 . "-08-15";
    }
}

$site->start_page ("none", "Gestion des cotisations");

function add_search_form()
{
  global $topdir, $ch;
  $cts = new contents("Gestion des cotisations");

  $frm = new form("searchstudent","cotisations.php",true,"POST",utf8_encode("Recherche d'un étudiant"));
  $frm->add_hidden("action","searchstudent");

  $sub_frm_ident = new form ("searchstudentident",null,null,null,utf8_encode("Par son Nom ou Prénom : "));
  $sub_frm_ident->add_text_field("nom","Nom");
  $sub_frm_ident->add_text_field("prenom","Prenom");
  $frm->add($sub_frm_ident,false,false,false,false,false,true,true);

  $frm->add_info("&nbsp;");

  $sub_frm_email = new form ("searchstudentemail",null,null,null,utf8_encode("Par son E Mail : "));
  $sub_frm_email->add_text_field("email","Adresse e-mail");
  $frm->add($sub_frm_email,false,false,false,false,false,true,true);

  $frm->add_info("&nbsp;");

  $sub_frm_cbarre = new form("searchstudentcbarre",null,null,null,utf8_encode("Par le code barre de sa carte AE : "));
  $sub_frm_cbarre->add_text_field("numcarte","Carte AE");
  $frm->add($sub_frm_cbarre,false,false,false,false,false,true,true);

  $frm->add_info("&nbsp;");
  $frm->add_submit("submit","Envoyer");
  $cts->add($frm,true);

  return $cts;
}

function add_new_form($id = null)
{
  global $topdir, $ch;
  
  global $date1, $date2;

  $cts = new contents("Gestion des cotisations");

  $frm = new form("newstudent","cotisations.php",true,"POST","Inscription d'un nouvel Ã©tudiant UTBM ou administratif UTBM");
  $frm->add_hidden("action","newstudent");
  if ( $ErreurNewStudent )
    $frm->error($ErreurNewStudent);

  $sub_frm_ident = new form("ident",null,null,null,utf8_encode("Identité"));

  $sub_frm_ident->add_text_field("nom","Nom","",true);

  $sub_frm_ident->add_text_field("prenom","Prenom","",true);

  $sub_frm_ident->add_text_field("emailutbm","Adresse e-mail (UTBM si possible)","",true,false,false,true);

  $frm->add($sub_frm_ident);
  $frm->add_info("&nbsp;");

  $sub_frm_cotiz = new form("cotisation",null,null,null,utf8_encode("Cotisation"));
  $sub_frm_cotiz->add_select_field("cotiz","Cotisation",array( 0=>"1 Semestre, 15 Euros, jusqu'au $date1", 1=>"2 Semestres, 28 Euros, jusqu'au $date2" ),1);
  $sub_frm_cotiz->add_select_field("paiement","Mode de paiement",array(1 => "Ch&egrave;que", 2 => "CB", 3 => "Liquide", 4 => "Administration"));
  $sub_frm_cotiz->add_info("&nbsp;");

  $sub_frm_cotiz_ecole = new form("ecoleform",null,null,null,utf8_encode("Etudiant"));
  //$sub_frm_cotiz_ecole->add_hidden("etudiant",true);
  //$sub_frm_cotiz_ecole->add_checkbox("etudiant","Etudiant",true);
  $sub_frm_cotiz_ecole->add_text_field("ecole","Ecole","UTBM",true);

  $sub_frm_cotiz->add($sub_frm_cotiz_ecole,false,true,true,"ecole",false,true,true);

  $sub_frm_cotiz_other = new form("ecoleform",null,null,null,utf8_encode("Prof/Administratif"));
  //$sub_frm_cotiz_other->add_hidden("etudiant",false);
  $sub_frm_cotiz->add($sub_frm_cotiz_other,false,true,false,"other",false,true,false);

  $sub_frm_cotiz->add_info("&nbsp;");
  $sub_frm_cotiz->add_checkbox("droit_image","Droit Ã  l'image",false);
  $sub_frm_cotiz->add_checkbox("cadeau","Cadeau",false);
  $sub_frm_cotiz->add_info("&nbsp;");

  $frm->add($sub_frm_cotiz);

  $frm->add_submit("submit","Enregistrer");
  $cts->add($frm,true);

  return $cts;
}

function add_user_info_form ($user = null)
{

  $sub_frm = new form("infosmmt",null,null,null,utf8_encode("Informations complémentaires"));
  $sub_frm->add_info("&nbsp;");
  $sub_frm->add_select_field("sexe","Sexe",array(1=>"Homme",2=>"Femme"),$user->sexe);
  if ($user->date_naissance)
    $sub_frm->add_date_field("date_naissance","Date de naissance",$user->date_naissance,false,false,true);
  else
    $sub_frm->add_date_field("date_naissance","Date de naissance",strtotime("1986-01-01"),false,false,true);

 if ($user->utbm)
	{
 $sub_frm->add_select_field("branche","Branche",array("TC"=>"TC","GI"=>"GI","GSP"=>"IMAP","GSC"=>"GESC","GMC"=>"GMC","Enseignant"=>"Enseignant","Administration"=>"Administration","Autre"=>"Autre"),$user->branche);
  $sub_frm->add_text_field("semestre","Semestre",$user->semestre?$user->semestre:"1");
  $sub_frm->add_text_field("filiere","Filiere",$user->filiere);		$sub_frm->add_select_field("promo","Promo",array(0=>"-",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10"),$user->promo_utbm?$user->promo_utbm:8);
	}
  $sub_frm->add_text_field("addresse","Adresse",$user->addresse);
 
  $sub_frm->add_text_field("ville","Ville",$user->ville);
  $sub_frm->add_text_field("cpostal","Code postal",$user->cpostal);
  $sub_frm->add_text_field("pays","Pays",$user->pays?$user->pays:"FRANCE");
  $sub_frm->add_text_field("tel_maison","Telephone (fixe)",$user->tel_maison);
  $sub_frm->add_info("et/ou");
  $sub_frm->add_text_field("tel_portable","Telephone (portable)",$user->tel_portable);
  $sub_frm->add_text_field("citation","Citation",$user->citation);
  $sub_frm->add_text_field("ville_parents","Ville parents",$user->ville_parents);
  $sub_frm->add_text_field("cpostal_parents","Code postal parents",$user->cpostal_parents);
  $sub_frm->add_info("&nbsp;");

  return $sub_frm;
}

/** Actions */

if ( $_REQUEST["action"] == "cadeau" )
{
  $cotisation = new cotisation($site->db,$site->dbrw);
  $cotisation->load_by_id($_REQUEST["id_cotisation"]);

  if ( $cotisation->id > 0 )
    $cotisation->mark_cadeau();
}

elseif ( $_REQUEST["action"] == "savecotiz" )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST['id_utilisateur']);

  if ( $user->id < 0 ) {
    header("Location: " . $topdir . "404.php");
    exit();
  } else {
    $cotisation = new cotisation($site->db,$site->dbrw);
    if ( $_REQUEST["cotiz"] == 0 ) {
      $date_fin = strtotime($date1);
      $prix_paye = 1500;
    } else {
      $date_fin = strtotime($date2);
      $prix_paye = 2800;
    }

    $cotisation->load_lastest_by_user ( $user->id );
    $cotisation->add ( $user->id, $date_fin, $_REQUEST["paiement"], $prix_paye );

    $a_pris_cadeau = $_REQUEST["cadeau"] == true;

    if ($a_pris_cadeau && $cotisation->id > 0)
      $cotisation->mark_cadeau();

    $user->load_all_extra();
    $user->droit_image = $_REQUEST["droit"]==true;
    $user->sexe = $_REQUEST['sexe'];
    $user->surnom = $_REQUEST['surnom'];
    $user->date_naissance = $_REQUEST['date_naissance'];
    $user->addresse = $_REQUEST['addresse'];
    $user->ville = $_REQUEST['ville'];
    $user->cpostal = $_REQUEST['cpostal'];
    $user->pays = $_REQUEST['pays'];
    $user->tel_maison = telephone_userinput($_REQUEST['tel_maison']);
    $user->tel_portable = telephone_userinput($_REQUEST['tel_portable']);
    $user->date_maj = time();

    if ( $user->etudiant )
      {
        $user->citation = $_REQUEST['citation'];
        $user->adresse_parents = $_REQUEST['adresse_parents'];
        $user->ville_parents = $_REQUEST['ville_parents'];
        $user->cpostal_parents = $_REQUEST['cpostal_parents'];
        $user->tel_parents = NULL;
        $user->nom_ecole_etudiant = "UTBM";
      }
    if ( $user->utbm )
      {
        $user->surnom = $_REQUEST['surnom'];
        $user->semestre = $_REQUEST['semestre'];
        $user->branche = $_REQUEST['branche'];
        $user->filiere = $_REQUEST['filiere'];
        $user->promo_utbm = $_REQUEST['promo'];
        $user->date_diplome_utbm = NULL;
      }
    $user->saveinfos();

    $info = new contents("Nouvelle cotisation","<img src=\"".$topdir."images/actions/done.png\">&nbsp;&nbsp;La cotisation a bien &eacute;t&eacute; enregistr&eacute;e.<br /><a href=\"" . $topdir . "ae/cotisations.php\">Retour</a>");


    $list = new itemlist();
    $list->add("<a href=\"" . $topdir . "ae/cotisations.php\">Retour aux cotisations</a>");
    $list->add("<a href=\"" . $topdir . "user.php?id_utilisateur=" . $user->id . "\">Retour &agrave; l'utilisateur</a>");
    $info->add($list);

    $info->set_toolbox(new toolbox(array($topdir . "ae/cotisations.php" => "Retour")));
    $site->add_contents($info);
  }
}

elseif ( $_REQUEST["action"] == "searchstudent" )
{
  $conds="";

  $user = array();
  if ( $_REQUEST['search_id'] && XMLRPC_USE )
    $user = $ch->getById($_REQUEST['search_id']);

  if ( $_REQUEST["nom"] )
	{
          $by = "nom";
          $on = $_REQUEST['nom'];
          if ($on)
            $conds .= " AND utilisateurs.nom_utl LIKE '".mysql_real_escape_string($on)."%'";
	}
  if ( $_REQUEST["prenom"] )
	{
          $by = "prénom";
          $on = $_REQUEST['prenom'];
          if ($on)
            $conds .= " AND utilisateurs.prenom_utl LIKE '".mysql_real_escape_string($on)."%'";
	}
  if ( $_REQUEST["email"] )
	{
          $by = "E Mail";
          $on = $_REQUEST['email'];
          if ($on)
            $conds .= " AND (`utilisateurs`.`email_utl` = '" . mysql_real_escape_string($on) . "' OR " .
              "`utl_etu_utbm`.`email_utbm` = '" . mysql_real_escape_string($on) . "') ";
	}
  if ( $_REQUEST["numcarte"] )
	{
          $by = "Code Barre Carte AE";
          list($num,$extra)=explode(" ",$_REQUEST["numcarte"]);
          $on = intval($num);
          $conds .= " AND ae_carte.id_carte_ae = '". mysql_real_escape_string($on)."'";
	}
  if ( isset($_REQUEST['id_utilisateur']) && ($_REQUEST['id_utilisateur'] > 0))
  {
	  $by = "Identifiant AE";
	  $on = intval($_REQUEST['id_utilisateur']);
	  $conds .= " AND utilisateurs.id_utilisateur = '" . mysql_real_escape_string($on) . "'";
	}

      $req = new requete($site->db,"SELECT utilisateurs.nom_utl AS nom_utilisateur," .
                         "utilisateurs.prenom_utl AS prenom_utilisateur, ".
                         "utilisateurs.id_utilisateur AS id_utl, utilisateurs.ae_utl, ae_cotisations.date_fin_cotis, " .
                         "utl_etu_utbm.branche_utbm, utl_etu_utbm.semestre_utbm" .
                         ", ae_cotisations.a_pris_cadeau ".
                         "FROM utilisateurs " .
                         "LEFT JOIN ae_cotisations ON (utilisateurs.id_utilisateur=ae_cotisations.id_utilisateur AND ae_cotisations.date_fin_cotis > NOW()) " .
                         "LEFT JOIN ae_carte ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
                         "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
                         "WHERE 1 $conds " .
                         "ORDER BY utilisateurs.nom_utl, utilisateurs.prenom_utl");

      $nb = $req->lines;
      if ($nb == 1)
	{
          $res = $req->get_row();

          $user = new utilisateur($site->db,$site->dbrw);

          $user->load_by_id($res['id_utl']);
          if ( $user->id < 0 )
            {
              header("Location: 404.php");
              exit();
            }

          if ( $user->id > 0 )
            {
              $user->load_all_extra();


              $cts = new contents("Cotisant ".$user->prenom." ".$user->nom);

              $cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>"Rechercher un autre cotisant")));

              $cts->add(new image($user->prenom . " " . $user->nom,$topdir."/var/img/matmatronch/".$user->id.".identity.jpg","fiche_image"));
              $cts->add_paragraph(
                                  "<b>". $user->prenom . " " . $user->nom . "</b><br/>" .
                                  $user->surnom."<br/>\n".
                                  date("d/m/Y",$user->date_naissance) . "<br />" .
                                  $user->addresse . "<br />" .
                                  $user->cpostal . " " . $user->ville ." (" . $user->pays . ")<br/>" .
                                  $user->tel_maison . "<br/>" .
                                  $user->tel_portable . "<br/>"
                                  );

              $cts->add_paragraph("Fiche utilisateur : ".classlink($user));

              $cts->add_paragraph("&nbsp;");

              $frm = new form("newcotiz","cotisations.php?id_utilisateur=".$user->id,true,"POST","Nouvelle cotisation");
              $frm->add_hidden("action","newcotiz");
              $frm->add_select_field("cotiz","Cotisation",array( 0=>"1 Semestre, 15 Euros, $date1", 1=>"2 Semestres, 28 Euros, $date2" ),1);
              $frm->add_select_field("paiement","Mode de paiement",array(1 => "ChÃ¨que", 2 => "CB", 3 => "Liquide", 4 => "Administration"));
              $frm->add_checkbox("droit_image","Droit Ã  l'image",$user->droit_image);
              $frm->add_checkbox("a_pris_cadeau",utf8_encode("Cadeau distribué"),false);
              $frm->add_submit("submit","Enregistrer");
              $cts->add($frm,true);


              $req = new requete($site->db,
                                 "SELECT *".
                                 "FROM `ae_cotisations` " .
                                 "WHERE `id_utilisateur`='".$user->id."' AND `date_fin_cotis` < NOW() " .
                                 "ORDER BY `date_cotis` DESC");

              $tbl = new sqltable(
                                  "listcotiz_effectue",
                                  utf8_encode("Cotisations effectuées"), $req, "cotisations.php?id_utilisateur=".$user->id,
                                  "id_cotisation",
                                  array("date_cotis"=>"Le",
                                        "date_fin_cotis"=>"Jusqu'au",
                                        "a_pris_cadeau"=>"Cadeau"),
                                  array(), array(), array("a_pris_cadeau"=>array(0=>"Non pris",1=>"Pris"))
                                  );
              $cts->add($tbl,true);

              $req = new requete($site->db,
                                 "SELECT *".
                                 "FROM `ae_cotisations` " .
                                 "WHERE `id_utilisateur`='".$user->id."' AND (`a_pris_cadeau` = '0' OR `a_pris_carte` = '0') " .
                                 "ORDER BY `date_cotis` DESC LIMIT 1");

              if ($req->lines)
		{
                  $tbl = new sqltable(
                                      "listcotiz_encours",
                                      utf8_encode("Cotisation en cours"), $req, "cotisations.php?id_utilisateur=".$user->id,
                                      "id_cotisation",
                                      array("date_cotis"=>"Le",
                                            "date_fin_cotis"=>"Jusqu'au",
                                            "a_pris_cadeau"=>"Cadeau"),
                                      array("Action"=>"Marquer le cadeau pris"), array(), array("a_pris_cadeau"=>array(0=>"Non pris",1=>"Pris"))
                                      );
                  $cts->add($tbl,true);
		}

              $site->add_contents($cts);
            }

	}
      else if ($nb == 0)
	{
          $cts_2 = add_new_form($_REQUEST['search_id']);
          $cts_2->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>utf8_encode("Rechercher un cotisant"))));
          $site->add_contents($cts_2);
	}
      else if ($nb > 1 && !XMLRPC_USE)
	{

          $res = $req->get_row();

          $tbl = new sqltable(
                              "listcotiz",
                              utf8_encode("$nb Résultats de la recherche de cotisants par $by sur $on"), $req, "cotisations.php",
                              "id_utilisateur",
                              array("nom_utilisateur"=>"Nom",
                                    "prenom_utilisateur"=>utf8_encode("Prénom"),
                                    "branche_utbm"=>"Branche",
                                    "semestre_utbm"=>"Semestre",
                                    "ae_utl"=>"Cotisant",
                                    "date_fin_cotis"=>"Jusqu'au",
                                    "a_pris_cadeau"=>"Cadeau"),
                              array("pagecotis"=>"Nouvelle cotisation","cadeau"=>"Marquer le cadeau comme pris"), array(), array("ae_utl"=>array(0=>"Non",1=>"Oui"),"a_pris_cadeau"=>array(0=>"NON Pris",1=>"Pris"))
                              );
          $site->add_contents($tbl);
	}
    }
}

elseif ($_REQUEST['action'] == "add")
{
  $cts = add_new_form();
  $cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']=>utf8_encode("Rechercher un cotisant"))));
  $site->add_contents($cts);
}

elseif ($_REQUEST['action'] == "modifyUser" && $_POST['search_id'])
  $cts = add_new_form($_POST['search_id']);

elseif ( $_REQUEST["action"] == "newcotiz" )
{
  $user = new utilisateur($site->db,$site->dbrw);

  $user->load_by_id($_REQUEST['id_utilisateur']);
  if ( $user->id < 0 )
    {
      header("Location: 404.php");
      exit();
    }

  if ( $user->id > 0 )
    {
      $user->load_all_extra();
      $cts = new contents(utf8_encode("Mise à jour des infos indispensable pour l'impression de la carte AE"));
      $frm = new form("infos","cotisations.php?id_utilisateur=".$user->id,true,"POST",null);
      $frm->add_hidden("action","savecotiz");
      if ( $user->utbm )
	{
          $frm->add_text_field("nom","Nom",$user->nom,true,false,false,false);
          $frm->add_text_field("prenom",utf8_encode("Prénom"),$user->prenom,true,false,false,false);
          $frm->add_text_field("surnom","Surnom",$user->surnom);

          $sub_frm = add_user_info_form($user);

          $frm->add($sub_frm,false,false,false,false,false,true,true);
	}
      else
        $frm->add_info("Cotisant non UTBM");

      $frm->add_hidden("cotiz",$_POST['cotiz']);
      $frm->add_hidden("paiement",$_POST['paiement']);
      $frm->add_hidden("droit_image",$_POST['droit_image']);
      $frm->add_hidden("cadeau",$_REQUEST["cadeau"]);
      $frm->add_submit("submit","Enregistrer");
      $cts->add($frm);
      $site->add_contents($cts);
    }
}

elseif ($_REQUEST["action"] == "newstudent")
{
  /* on va lui creer un compte utilisateur */
  $user = new utilisateur($site->db, $site->dbrw);

  /* Si on a le nom d'une ecole parce que c'est un etudiant */
  $email_utbm_needed = false;
  if ($_REQUEST['ecoleform'] == "ecole")
    {
      $etudiant = true;
      $nom_ecole = $_REQUEST['ecole'];
      if ($_REQUEST['ecole'] == "UTBM")
        $email_utbm_needed = true;
    }
	/* cas d'un prof */
	elseif ($_REQUEST['ecoleform'] == "other")
	{
		$etudiant = false;
		$nom_ecole = "UTBM";
		$email_utbm_needed = true;
	}
  else
    {
      $nom_ecole = null;
      $etudiant = false;
    }

  /* Verif UTBM */
  if ($email_utbm_needed && !CheckEmail($_REQUEST['emailutbm'],1))
    {
      $cts = new contents("WARNING");
      $cts->set_toolbox(new toolbox(array("javascript:history.go(-1);"=>utf8_encode("Retour"))));
      $cts->add_paragraph("<img src=\"".$topdir."images/actions/info.png\">&nbsp;&nbsp;Il faut un email UTBM valide pour inscrire un &eacute;tudiant, professeur ou administratif UTBM !");
      $site->add_contents($cts,true);
      $site->end_page();
      exit();
    }

  /* Verif de disponibilite */
  if (!$user->is_email_avaible($_REQUEST['emailutbm']))
    {
      $cts = new contents("WARNING");
      $cts->set_toolbox(new toolbox(array("javascript:history.go(-1);"=>utf8_encode("Retour"))));
      $cts->add_paragraph("<img src=\"".$topdir."images/actions/info.png\">&nbsp;&nbsp;L'email existe d&eacute;j&agrave; v&eacute;rifier que l'utilisateur ne figure pas dans la liste de la base de donn&eacute;es commune !");
      $site->add_contents($cts,true);
      $site->end_page();
      exit();
    }

  $email = null;
  $emailutbm = null;

  $email = $_REQUEST['emailutbm'];
  if ((($_REQUEST['ecoleform'] == "ecole") && ($nom_ecole == "UTBM")) ||
      ($_REQUEST['ecoleform'] == "other")) {
    $emailutbm = $_REQUEST['emailutbm'];
  }

  $user->new_utbm_user($_REQUEST['nom'],
                       $_REQUEST['prenom'],
                       $email, $emailutbm,
                       null,null,null,null,null,
                       $etudiant,
                       $_REQUEST['droit_image']==true,
                       $nom_ecole);

  if ($user->id < 0)
    {
      $cts = new contents("WARNING");
      $cts->set_toolbox(new toolbox(array("javascript:history.go(-1);"=>utf8_encode("Retour"))));
      $cts->add_paragraph("<img src=\"".$topdir."images/actions/delete.png\">&nbsp;&nbsp;Probleme lors de l'ajout de l'utilisateur".$user->id);
      $site->add_contents($cts,true);
      $site->end_page();
      exit();
    }
  else
    {
      $user->load_all_extra();
      $cts = new contents(utf8_encode("Mise à jour des infos indispensable pour l'impression de la carte AE"));
      $frm = new form("infos","cotisations.php?id_utilisateur=".$user->id,true,"POST",null);
      $frm->add_hidden("action","savecotiz");
      $frm->add_text_field("nom","Nom",$user->nom,true,false,false,false);
      $frm->add_text_field("prenom",utf8_encode("Prénom"),$user->prenom,true,false,false,false);
      $frm->add_text_field("surnom","Surnom (facultatif) ",$user->surnom);
      $sub_frm = add_user_info_form($user);
      $frm->add($sub_frm,false,false,false,false,false,true,true);

      $frm->add_hidden("cotiz",$_POST['cotiz']);
      $frm->add_hidden("paiement",$_POST['paiement']);
      $frm->add_hidden("droit",$_REQUEST["droit_image"]);
      $frm->add_hidden("cadeau",$_REQUEST["cadeau"]);
      $frm->add_submit("submit","Enregistrer");
      $cts->add($frm);
      $site->add_contents($cts);
    }
}

else
{
  $cts = add_search_form();
  $cts->set_toolbox(new toolbox(array($_SERVER['SCRIPT_NAME']."?action=add"=>utf8_encode("Insérer un nouveau cotisant"))));
  $cts->add(add_new_form());
  $site->add_contents($cts);
}

$site->end_page ();

?>
