<?php

require_once($topdir."include/cts/board.inc.php");

class loginerror extends board
{
  
  function loginerror()
  {
    global $wwwtopdir;
    
    $_SESSION['session_redirect'] = $_SERVER["REQUEST_URI"];
    
    $this->board("Veuillez vous identifier","loginerror");  
    
  	$frm = new form("connect2",$wwwtopdir."connect.php",true,"POST","Vous avez déjà un compte");
  	$frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"));
  	$frm->add_text_field("username","Utilisateur","prenom.nom","",27);
  	$frm->add_password_field("password","Mot de passe","","",27);
  	$frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
  	$frm->add_submit("connectbtn2","Se connecter");
    $this->add($frm,true);	
	
    $cts = new contents("Créer un compte");
    $cts->add_paragraph("Pour acceder à cette page vous devez posséder un compte.<br/>La création d'un compte nécessite que vous possédiez une addresse e-mail pour pouvoir l'activer.<br/> Le fait que vous soyez membre ou non de l'utbm vous donnera plus ou moins de droits d'accès sur le site. Un compte vous permettra au minimum de pouvoir utiliser job étu, e-boutic, et de poster des messages sur les forums publics.");
    $cts->add_paragraph("<a href=\"".$wwwtopdir."newaccount.php\">Créer un compte</a>");
    $this->add($cts,true);	
  }
}


?>