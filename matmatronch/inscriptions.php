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

if (XMLRPC_USE)
{
  require_once($topdir. 'include/inscriptions/xmlrpc.inc');

  /** Notre authentification auprÃ¨s de l'API XML-RPC */
   define('API_URI', 'inscriptions.php');
  define('API_HOST', 'ae.utbm.fr');
  define('API_PORT', 443);
  define('API_PROTOCOL', 'https');

  require_once($topdir. 'include/inscriptions/xmlrpc-client.inc.php');
  $ch = new ClientHelper ("mmt", "08084e11"); 
}


require_once($topdir. "include/site.inc.php");

$site = new site ();

if ( !$site->user->is_asso_role ( 27, 1 ) )
  error_403();

$site->start_page ("none", "Inscriptions nouvel étudiant au mat'matronch");

function save_infos($nom, $prenom, $sexe, $email, $emailutbm, $droit_image, $semestre, $branche, $date_naissance)
{
	global $site, $ch;
  /* on va lui creer un compte utilisateur */
  $user = new utilisateur($site->db, $site->dbrw);

 mmt_inscr_error($user->new_utbm_user($nom,$prenom,$email,$emailutbm,null,null,$semestre,$branche,null,true,$droit_image==true,"UTBM",$date_naissance,$sexe));
	  //mmt_inscr_error("Erreur lors de l'ajout de l'utilisateur dans la base de données du Mat'Matronch !",2);

  if ($user->id < 0)
	  mmt_inscr_error("Erreur lors de l'ajout de l'utilisateur dans la base de données du Mat'Matronch !",2);
  else
  {
   /* ici on update la base XMLRPC */
    if ( XMLRPC_USE )
      $ret = $ch->addUser($nom, $prenom, ($emailutbm!=null)?$emailutbm:null, $sexe, $branche, $semestre, date("Y-m-d",$date_naissance));

    if ( XMLRPC_USE && $ret == FALSE) 
      mmt_inscr_error("Erreur lors de la mise à à jour de la base de données commune");
	else
		  add_other_infos_form($user);
  }
}

if ($_POST['notconfirm'])
	$_POST['action'] = "";

if ($_POST['confirm'])
	save_infos(
		$_REQUEST['nom'],
		$_REQUEST['prenom'],
		$_REQUEST['sexe'],
		$_REQUEST['email'],
		null,
		$_REQUEST['droit_image']==true,
		$_REQUEST['semestre'],
		$_REQUEST['branche'],
		$_REQUEST['date_naissance']
			);

function enleveaccents($chaine)
{
     $string = strtr($chaine,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");

     return $string;
}

function is_email_utbm($adresse)
{
	$atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
	$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
	$regexp = '/^' . $atom . '+' . '(\.' . $atom . '+)*' . '@' . '(' . $domain . '{1,63}\.)+' . $domain . '{2,63}$/i';

	$frags = explode("@",trim($adresse));

	$i = 0;
	while ($frags[$i])
	{
		$frags[$i] = enleveaccents($frags[$i]);
		$i++;
	}

	if ($frags[1] == "utbm.fr" && preg_match($regexp,trim(enleveaccents($adresse))) && count(explode(".",$frags[0]))=="2")
		return TRUE;
	else
		return FALSE;
}

function mmt_inscr_error($s, $level = 1, $ret = 1)
{
	global $topdir,$site;

	if ( $level == 1 )
	{
	  $cts = new contents("INFOS");
	  $img = "info.png";
	}
	else
	{
	  $cts = new contents("WARNING");
	  $img = "delete.png";
	}
	if ($ret)
	    $cts->set_toolbox(new toolbox(array($topdir."matmatronch/inscriptions.php"=>utf8_encode("Retour"))));
    $cts->add_paragraph("<img src=\"".$topdir."images/actions/".$img."\">&nbsp;&nbsp;".utf8_encode("<strong>".$s."</strong>"));
    $site->add_contents($cts,true);

	if ($level == 2)
	{
		  $site->end_page();
		  exit();
	}
}

function add_new_form()
{
  $cts = new contents(utf8_encode("Mat'Matronch - Ajout d'un étudiant"));

  $frm = new form("newstudent","inscriptions.php",true,"POST","Inscription d'un nouvel Ã©tudiant UTBM");
  $frm->add_hidden("action","newstudent");
  if ( $ErreurNewStudent )
    $frm->error($ErreurNewStudent);

  $frm->add_text_field("nom","Nom","",true);

  $frm->add_text_field("prenom","Prenom","",true);

  $frm->add_text_field("emailutbm","Adresse e-mail (UTBM si possible)","",true,false,false,true);

  $frm->add_radiobox_field("sexe","Sexe",array(1=>"Homme",2=>"Femme"),1,false,true);

  $frm->add_info("&nbsp;");

  $frm->add_date_field("date_naissance","Date de naissance",strtotime("1986-01-01"),true);
 
    $frm->add_info("&nbsp;");
	$frm->add_select_field("branche","Branche",array("TC"=>"TC","GI"=>"GI","GSP"=>"IMAP","GSC"=>"GESC","GMC"=>"GMC","Enseignant"=>"Enseignant","Administration"=>"Administration","Autre"=>"Autre"),"TC",null,true);
  $frm->add_text_field("semestre","Semestre","1",true);

  $frm->add_info("&nbsp;");

  $frm->add_checkbox("droit_image","Droit Ã  l'image",false);
  $frm->add_info("&nbsp;");
  $frm->add_submit("submit","Enregistrer");
  $cts->add($frm,true);

  return $cts;
}

function add_other_infos_form ($user = null)
{
	global $topdir;

	if ($user && $user->id >0)
	{
		Header ("Location: ".$topdir."user.php?id_utilisateur=".$user->id."&page=edit");
		exit();
	}
	else
		mmt_inscr_error("Utilisateur Invalide, veuillez vérifier les infos nom, prénom et email données !!!",2);
}

/** Actions */

if ($_REQUEST["action"] == "newstudent")
{
  if (is_email_utbm($_REQUEST['emailutbm']))
  {
	  $email = $_REQUEST['emailutbm'];
	  $email_utbm = $_REQUEST['emailutbm'];
	  save_infos($_REQUEST['nom'],$_REQUEST['prenom'],$_REQUEST['sexe'],$_REQUEST['email'],$_REQUEST['emailutbm'],$_REQUEST['droit_image'],$_REQUEST['semestre'],$_REQUEST['branche'],$_REQUEST['date_naissance']);
  }
  else
  {
		mmt_inscr_error("Vous avez entré une adresse email non conforme aux standarts UTBM, êtes vous-sur de vouloir continuer",1,0);
		$conf_frm = new form("confirm","inscriptions.php",true,"POST","Confirmation");
		$conf_frm->add_info("Etes-vous sur de vouloir continuer avec l'email : " . $_REQUEST['emailutbm']);
		$conf_frm->add_hidden("nom",$_REQUEST['nom']);
		$conf_frm->add_hidden("prenom",$_REQUEST['prenom']);
		$conf_frm->add_hidden("sexe",$_REQUEST['sexe']);
		$conf_frm->add_hidden("branche",$_REQUEST['branche']);
		$conf_frm->add_hidden("semestre",$_REQUEST['semestre']);
		$conf_frm->add_hidden("date_naissance",$_REQUEST['date_naissance']);
		$conf_frm->add_hidden("droit_image",$_REQUEST['droit_image']);
		$conf_frm->add_hidden("email",$_REQUEST['emailutbm']);
		$conf_frm->add_submit("confirm","Oui");
		$conf_frm->add_info("&nbsp;");
		$conf_frm->add_submit("notconfirm","NON");
		$site->add_contents($conf_frm,true);
  }
}
else
  $site->add_contents(add_new_form());

$site->end_page ();

?>
