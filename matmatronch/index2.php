<?php

$topdir = "./../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/cts/gallery.inc.php");
require_once($topdir. "include/cts/special.inc.php");

$site = new site ();

$site->allow_only_logged_users("matmatronch");

if ( !$site->user->ae )
{
  $site->start_page("matmatronch","MatMaTronch");
  $cts = new contents("Accès limité");
  $cts->add_paragraph("L'accès à la recherche avancée du matmatronch est réservée aux cotisants AE. Vous pouvez tout de même utiliser le moteur de recherche rapide se trouvant en haut à gauche de la page.");
  $site->add_contents($cts);
  $site->end_page(); 
  exit();
}

$GLOBALS["utbm_roles"][""]="Tous";
$GLOBALS["utbm_departements"][""]="Tous";

$is_admin = ( $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));

$site->start_page("matmatronch","MatMaTronch");
$cts = new contents("Recherche Mat'Matronch");


if ( $_REQUEST["action"] == "search" )
{
  $elements = array();
  $order = "ORDER BY `nom_utl`,`prenom_utl`";
  
  
  if ( $_REQUEST["nom"] )
    $elements[] = "`nom_utl` REGEXP '". stdentity::_fsearch_prepare_sql_pattern($_REQUEST["nom"])."'";
    
  if ( $_REQUEST["prenom"] )
    $elements[] = "`prenom_utl` REGEXP '". stdentity::_fsearch_prepare_sql_pattern($_REQUEST["prenom"])."'";
    
  if ( $_REQUEST["surnom"] )
  {
    $p = stdentity::_fsearch_prepare_sql_pattern($_REQUEST["surnom"]);
    $elements[] = "`surnom_utbm` REGEXP '$p' OR `alias_utl` REGEXP '$p'";
    $order = "ORDER BY `surnom_utbm`,`alias_utl`,`nom_utl`,`prenom_utl`";
  }
   
  if ( $_REQUEST["date_naissance"] > 1 )
    $elements[] = "`date_naissance_utl`='".date("Y-m-d",$_REQUEST["date_naissance"])."'";
  
  if ( $_REQUEST["sexe"] && $_REQUEST["sexe"] > 0 )
    $elements[] = "`sexe_utl`='".mysql_escape_string($_REQUEST["sexe"])."'";
    
  if ( $_REQUEST["role"] )
    $elements[] = "`role_utbm`='".mysql_escape_string($_REQUEST["role"])."'";

  if ( $_REQUEST["departement"] )
    $elements[] = "`departement_utbm`='".mysql_escape_string($_REQUEST["departement"])."'";

  if ( $_REQUEST["semestre"] && $_REQUEST["role"] == "etu" )
    $elements[] = "`semestre_utbm`='".intval($_REQUEST["semestre"])."'";  
      
  if ( !empty($_REQUEST["promo"]) && $_REQUEST["promo"] > 0 )
    $elements[] = "`promo_utbm`='".mysql_escape_string($_REQUEST["promo"])."'";
  
  if ( !empty($_REQUEST["numtel"]) )
  {
    $tel = mysql_escape_string(telephone_userinput($_REQUEST["numtel"]));
    $elements[] = "`tel_maison_utl`='$tel' OR `tel_portable_utl`='$tel'";
  }

  if ( count($elements) > 0 )
  {
    $cts->add_title(2,"Résultats");
    
    if ( !isset($_REQUEST["inclus_ancien"]) )
      $elements[] = "`ancien_etudiant_utl`='0'";

    if ( !isset($_REQUEST["inclus_nutbm"]) )
      $elements[] = "`utbm_utl`='1'";
      
    if ( !$is_admin )
      $elements[] = "`publique_utl`='1'";    
      
    $req = new requete($site->db,"SELECT COUNT(`utilisateurs`.`id_utilisateur`) " .
        "FROM `utilisateurs` " .
        "LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
        "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
        "WHERE "  .implode(" AND ",$elements));
        
    list($count) = $req->get_row();
      
    if ( $count == 0 )
      $cts->add_paragraph("Aucun resultat ne correspond aux critères.");
      
    elseif ( $count > 350 )
      $cts->add_paragraph("Votre recherche est trop imprécise, $count personnes correspondent aux critères.");
      
    else
    {
      $npp=18;
      $page = intval($_REQUEST["page"]);
      
      if ( $page)
        $st=$page*$npp;
      else
        $st=0;
        
      if ( $st > $count )
        $st = floor($count/$npp)*$npp;   
        
      $req = new requete($site->db,"SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.*, `utilisateurs`.`id_ville` as `id_ville`, `utl_etu`.`id_ville` as `ville_parents`, `utilisateurs`.`id_pays` as `id_pays`, `utl_etu`.`id_pays` as `pays_parents` " .
        "FROM `utilisateurs` " .
        "LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
        "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
        "WHERE "  .implode(" AND ",$elements)." $order LIMIT $st,$npp");
      
      $user = new utilisateur():
        
      $gal = new gallery();
      
      while ( $row = $req->get_row() )
      {
        $user->_load_all($row);
        $gal->add_item(new userinfov2($user));
      }
      
      $tbl->add($gal);
        
      //TODO:pagination
    }
  }
}

//TODO:implémenter la recherche par edt

$frm = new form("mmtprofil","index2.php",true,"POST","Recherche par profil");
$frm->add_hidden("action","search");
$frm->add_text_field("nom","Nom");  
$frm->add_text_field("prenom","Prenom");
$frm->add_text_field("surnom","Surnom");  
$frm->add_radiobox_field("sexe","Sexe",array(1=>"Homme",2=>"Femme",0=>"Indifférent"),0, -1);
$frm->add_select_field("role","Role",$GLOBALS["utbm_roles"],"etu");  
$frm->add_select_field("departement","Departement",$GLOBALS["utbm_departements"],"");
$frm->add_text_field("semestre","Semestre","");
$frm->add_radiobox_field("promo", "Promo", $site->user->liste_promos("Toutes"), 0, -1);
$frm->add_date_field("date_naissance","Date de naissance");
$frm->add_checkbox("inclus_ancien","Inclure les anciens",false);
$frm->add_checkbox("inclus_nutbm","Inclure les non-utbm",false);
//TODO:améliorer la présentation
//TODO:recherche par ville/dep/region/pays d'origine ?
$frm->add_submit("go","Rechercher");
$cts->add($frm,true);

$frm = new form("mmtedt","index2.php",true,"POST","Recherche par emploi du temps");
$frm->add_hidden("action","search");
//TODO:le formulaire
$frm->add_submit("go","Rechercher");
$cts->add($frm,true);

$frm = new form("mmtinv","index2.php",true,"POST","Recherche inversée");
$frm->add_hidden("action","search");
$frm->add_text_field("numtel","Numéro de téléphone");
$frm->add_submit("go","Rechercher");
$cts->add($frm,true);


$site->add_contents($cts);
$site->end_page();

?>