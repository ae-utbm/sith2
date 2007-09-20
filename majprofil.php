<?php

/**
 * Mise à jour rapide de profil
 */

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/entities/ville.inc.php");
require_once($topdir . "include/entities/pays.inc.php");
require_once($topdir . "include/entities/carteae.inc.php");

$site = new site ();

$ville = new ville($site->db);
$pays = new pays($site->db);

if ( isset($_REQUEST["id_utilisateur"]) && 
     ( !$site->user->is_valid() || $_REQUEST["id_utilisateur"] != $site->user->id ) )
{
  if ( $site->user->is_valid() )
  {
    $site->user->id=null;
    new delete($site->dbrw, "site_sessions", array("id_session"=>$_COOKIE['AE2_SESS_ID']) );
  }
  
  if ( isset($_REQUEST["hash"]) )
  {
    $site->user->load_by_id($_REQUEST["id_utilisateur"]);
  
    if ( $site->user->is_valid() && $site->user->hash == $_REQUEST["hash"] )
    {
      $site->user->validate();
      $site->connect_user();
    }
    else
    {
      $site->user->id=null;
    }
  }
  elseif ( isset($_REQUEST["token"]) )
  {
    $site->user->id=null;
    $this->load_session($_REQUEST["token"]);  
    if ( $site->user->is_valid() )
    {
      new delete($site->dbrw, "site_sessions", array("id_session"=>$_REQUEST["token"]) );
      $site->connect_user();
    }
  }
  
  if ( !$site->user->is_valid() )
  {
  	$site->start_page("matmatronch","Erreur");
  	$site->add_contents(new error("Impossible d'ouvrir une session","Merci de vérifier le lien dans l'email qui vous a été adressé. Attention: ce lien ne peut être utilisé qu'une seule fois."));
  	$site->end_page(); 	
  	exit();
  }  
  
}

$site->allow_only_logged_users("matmatronch");

$user = &$site->user;
$user->load_all_extra();
$ville->load_by_id($user->id_ville);
$pays->load_by_id($user->id_pays);
  
if ( $_REQUEST["action"] == "majprofil" )
{
  $type = intval($_REQUEST["type"]);
  
  if ( $type < 5 && !$user->utbm )
    $type=6;
  
  if ( $type == 1 ) // Etudiant
  {
    $user->became_etudiant ( "UTBM", false, true );
    
    $user->role = "etu";
    $user->promo_utbm=$_REQUEST["promo_etu"];
    $user->departement=$_REQUEST["departement_etu"];
  
    $user->surnom=$_REQUEST["surnom_etu"];
    $user->sexe=$_REQUEST["sexe_etu"];
    $user->date_naissance=$_REQUEST["date_naissance_etu"];
    
    $user->addresse=$_REQUEST["addresse_etu"];
  
    $user->tel_maison=$_REQUEST["tel_maison_etu"];
    $user->tel_portable=$_REQUEST["tel_portable_etu"];
    
    if ( $_REQUEST['id_ville_etu'] )
    {
      $ville->load_by_id($_REQUEST['id_ville_etu']);
      $user->id_ville = $ville->id;
      $user->id_pays = $ville->id_pays;
    }
    
  }
  elseif ( $type == 2 ) // Diplomé
  {
    $user->became_etudiant ( "UTBM", true, true );
    
    $user->role = "etu";
    if ( $_REQUEST["date_diplome"] )
      $user->date_diplome_utbm = $_REQUEST["date_diplome"];
    else
      $user->date_diplome_utbm=time();
    
    $user->promo_utbm=$_REQUEST["promo_dip"];
    $user->departement=$_REQUEST["departement_dip"];
  
    $user->surnom=$_REQUEST["surnom_dip"];
    $user->sexe=$_REQUEST["sexe_dip"];
    $user->date_naissance=$_REQUEST["date_naissance_dip"];
    
    $user->addresse=$_REQUEST["addresse_dip"];
  
    $user->tel_maison=$_REQUEST["tel_maison_dip"];
    $user->tel_portable=$_REQUEST["tel_portable_dip"];
    
    if ( $_REQUEST['id_ville_dip'] )
    {
      $ville->load_by_id($_REQUEST['id_ville_dip']);
      $user->id_ville = $ville->id;
      $user->id_pays = $ville->id_pays;
    }
    else
    {
      $user->id_ville = null;
      $user->id_pays = $_REQUEST['id_pays_dip'];
    }
  }
  elseif ( $type == 3 ) // Ancien pas diplomé
  {
    $user->became_etudiant ( "UTBM", true, true );
    
    $user->role = "etu";
    $user->surnom=$_REQUEST["surnom_anc"];
    $user->sexe=$_REQUEST["sexe_anc"];
    $user->date_naissance=$_REQUEST["date_naissance_anc"];
    
    $user->addresse=$_REQUEST["addresse_anc"];
  
    $user->tel_maison=$_REQUEST["tel_maison_anc"];
    $user->tel_portable=$_REQUEST["tel_portable_anc"];
    
    if ( $_REQUEST['id_ville_anc'] )
    {
      $ville->load_by_id($_REQUEST['id_ville_anc']);
      $user->id_ville = $ville->id;
      $user->id_pays = $ville->id_pays;
    }
    else
    {
      $user->id_ville = null;
      $user->id_pays = $_REQUEST['id_pays_anc'];
    }
    
  }
  elseif ( $type == 4 ) // Enseignant/Administratif/employé utbm
  {
    $user->became_notetudiant();
    
    $user->departement=$_REQUEST["departement_adm"];
    $user->role=$_REQUEST["role_adm"];
    $user->tel_maison=$_REQUEST["tel_maison_adm"];
    $user->addresse=$_REQUEST["addresse_adm"];
    
    if ( $_REQUEST['id_ville_adm'] )
    {
      $ville->load_by_id($_REQUEST['id_ville_adm']);
      $user->id_ville = $ville->id;
      $user->id_pays = $ville->id_pays;
    }
    
  }
  elseif ( $type == 5 ) // Etudiant autre
  {
    $user->became_etudiant ( $user->nom_ecole_etudiant, false, true );
    
  }
  elseif ( $type == 6 ) // Autre
  {
    $user->became_notetudiant();
  }
  
  $user->saveinfos();
  
  $site->start_page("matmatronch","Mise à jour du profil");
  $cts = new contents($user->prenom." ".$user->nom." : profil mis à jour");
  
  
  $cts->add_title(2,"Mettre à jour les autres informations me concernant");
  $cts->add_paragraph("<a href=\"user.php?page=edit\">Modification de mon profil</a>");
  
  if ( $user->utbm && !file_exists("/var/www/ae/www/ae2/var/img/matmatronch/" . $user->id .".identity.jpg") )
  {
    $cts->add_title(2,"Vous n'avez pas de photo d'identité");
    
    $cts->add_paragraph("Votre photo d'identité serait la bienvenue pour la prochaine édition du matmatronch.");
    
    if ( $user->ae )
      $cts->add_paragraph("De plus elle est indispensable pour l'édition de votre carte AE.");
      
    $cts->add_paragraph("Pour pouvoir mettre votre photo d'identité sur le site, vous avez deux possibilités :");
      
    $cts->add(new itemlist(false,false,array(
    "Charger la photo au format informatique (fichier JPEG ou PNG) : <a href=\"user.php?see=photos&page=edit\">Charger le fichier</a>",
    "Deposer une photo d'identité au bureau de l'AE (Belfort, Sevenans, Montbéliard), ou la faire parevenir à l'AE par courrier interne, en inscrivant au dos de la photo votre nom, prénom et le numéro <b>".$user->id."</b>"
    ))); 
    
  }
  
  if ( $user->ae )
  {
    $carte = new carteae($this->db);
    $carte->load_by_utilisateur($user->id);
    
    if ( $carte->is_valid() )
    {
      if ( $carte->etat_vie_carte == CETAT_ATTENTE &&
        !file_exists("/var/www/ae/www/ae2/var/img/matmatronch/" . $this->user->id .".identity.jpg") )
      {
        $cts->add_title(2,"Votre carte AE");
        $cts->add_paragraph("Vous devez ajouter une photo pour que votre carte AE soit imprimée.");
        
        $cts->add_paragraph("En attendant, vous pouvez utiliser votre numéro de carte (nottament aux bars) : ".$carte->id.$carte->cle);
      }
      elseif ($carte->etat_vie_carte < CETAT_CIRCULATION )
      {
        $cts->add_title(2,"Votre carte AE");
        
        $lieu = "Belfort";
        $this->user->load_all_extra();
        if ( $this->user->departement == "tc" || 
          $this->user->departement == "gmc" || 
          $this->user->departement == "edim" )
          $lieu = "Sévenans";

        if ( $carte->etat_vie_carte == CETAT_AU_BUREAU_AE )
          $cts->add_paragraph("Votre carte AE est prête. Elle vous attends au bureau de l'AE de $lieu.");
        else
          $cts->add_paragraph("Votre carte AE est en cours de préparation, elle sera prochainement disponible au bureau de l'AE de $lieu.");
          
        $cts->add_paragraph("En attendant, vous pouvez utiliser votre numéro de carte (nottament aux bars) : ".$carte->id.$carte->cle);
      }
    }
    
  }
  
  
  
  $site->add_contents($cts);
  $site->end_page();
  exit();
}




$site->start_page("matmatronch","Mise à jour du profil");
$cts = new contents($user->prenom." ".$user->nom." : Mise à jour du profil");

$type=6;

if ( $user->utbm && $user->etudiant )
  $type=1;
elseif ( $user->utbm && $user->ancien_etudiant )
{
  if ( !is_null($user->date_diplome_utbm) && $user->date_diplome_utbm < time() )
    $type=2;
  else
    $type=3;
}
elseif ( $user->utbm )
  $type=4;
elseif ( $user->etudiant || $user->ancien_etudiant )
  $type=5;


$cts->add_paragraph("Choisissez le profil qui vous correspond, puis complétez les informations :");

$frm = new form("majprofil","majprofil.php",true,"POST","");
$frm->add_hidden("action","majprofil");

if ( $user->utbm )
{
  $sfrm = new form("type",null,null,null,"Etudiant à l'UTBM");

  $sfrm->add_select_field("promo_etu","Promo",$user->liste_promos("-"),$user->promo_utbm);
  $sfrm->add_select_field("departement_etu","Departement",$GLOBALS["utbm_departements"],$user->departement);

  $sfrm->add_text_field("surnom_etu","Surnom (utbm)",$user->surnom);
  $sfrm->add_select_field("sexe_etu","Sexe",array(1=>"Homme",2=>"Femme"),$user->sexe);
  $sfrm->add_date_field("date_naissance_etu","Date de naissance",$user->date_naissance);
  
  $sfrm->add_text_field("addresse_etu","Adresse personelle",$user->addresse);

  $sfrm->add_entity_smartselect ("id_ville_etu","Ville (France)", $ville,true);
          
  $sfrm->add_text_field("tel_maison_etu","Telephone (fixe)",$user->tel_maison);
  $sfrm->add_text_field("tel_portable_etu","Telephone (portable)",$user->tel_portable);




  $frm->add($sfrm,false,true, $type==1, 1,false,true);
  $sfrm = new form("type",null,null,null,"Diplomé de l'UTBM");
 
  $sfrm->add_select_field("promo_dip","Promo",$user->liste_promos("-"),$user->promo_utbm);
  $sfrm->add_date_field("date_diplome","Date d'obtention du diplome",$user->date_diplome_utbm);
  $sfrm->add_select_field("departement_dip","Departement",$GLOBALS["utbm_departements"],$user->departement);
      
  $sfrm->add_text_field("surnom_dip","Surnom (utbm)",$user->surnom);
  $sfrm->add_select_field("sexe_dip","Sexe",array(1=>"Homme",2=>"Femme"),$user->sexe);
  $sfrm->add_date_field("date_naissance_dip","Date de naissance",$user->date_naissance);
  
  $sfrm->add_text_field("addresse_dip","Adresse personelle",$user->addresse);

  $sfrm->add_entity_smartselect ("id_ville_dip","Ville (France)", $ville,true);
  $sfrm->add_entity_smartselect ("id_pays_dip","ou pays", $pays,true);
  
  $sfrm->add_text_field("tel_maison_dip","Telephone (fixe)",$user->tel_maison);
  $sfrm->add_text_field("tel_portable_dip","Telephone (portable)",$user->tel_portable);
      
      
      
  $frm->add($sfrm,false,true, $type==2, 2,false,true);
  $sfrm = new form("type",null,null,null,"Ancien etudiant de l'UTBM");
  
  $sfrm->add_text_field("surnom_anc","Surnom (utbm)",$user->surnom);
  $sfrm->add_select_field("sexe_anc","Sexe",array(1=>"Homme",2=>"Femme"),$user->sexe);
  $sfrm->add_date_field("date_naissance_anc","Date de naissance",$user->date_naissance);
  
  $sfrm->add_text_field("addresse_anc","Adresse personelle",$user->addresse);

  $sfrm->add_entity_smartselect ("id_ville_anc","Ville (France)", $ville,true);
  $sfrm->add_entity_smartselect ("id_pays_anc","ou pays", $pays,true);
  $sfrm->add_text_field("tel_maison_anc","Telephone (fixe)",$user->tel_maison);
  $sfrm->add_text_field("tel_portable_anc","Telephone (portable)",$user->tel_portable);
  
  
  
  $frm->add($sfrm,false,true, $type==3, 3,false,true);
  $sfrm = new form("type",null,null,null,"Enseignant, personnel administratif ou employé de l'UTBM");
  
  unset($GLOBALS["utbm_roles"]["etu"]);
  
  $sfrm->add_select_field("departement_adm","Departement",$GLOBALS["utbm_departements"],$user->departement);
  $sfrm->add_select_field("role_adm","Role",$GLOBALS["utbm_roles"],$user->role);
  $sfrm->add_text_field("tel_maison_adm","Telephone (poste)",$user->tel_maison);
  
  $sfrm->add_text_field("addresse_adm","Bureau",$user->addresse);
  $sfrm->add_entity_smartselect ("id_ville_adm","Ville", $ville,true);
  
  $frm->add($sfrm,false,true, $type==4, 4,false,true);
}

$sfrm = new form("type",null,null,null,"Etudiant (hors UTBM)");

$frm->add($sfrm,false,true, $type==5, 5,false,true);

$sfrm = new form("type",null,null,null,"Autre (hors UTBM)");

$frm->add($sfrm,false,true, $type==6, 6,false,true);
  
$frm->add_submit("record","Enregistrer");

if ( !$user->utbm )
  $cts->add_paragraph("<b>Si vous êtes membre de l'UTBM (étudiant, enseignant ...)</b>, veuillez <a href=\"user.php?see=email&page=edit\">renseigner votre adresse e-mail utbm</a> pour pouvoir accéder au profil qui vous est le plus approprié.");

$cts->add($frm);

$site->add_contents($cts);
$site->end_page();

?>
