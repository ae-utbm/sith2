<?php

$topdir="../";
include($topdir."include/i/site.inc.php");

$Erreur = null;

if ( isset($_REQUEST["connect"]) && $_REQUEST["username"] != "" )
{

  switch ($_REQUEST["domain"])
  {
    case "utbm" :
      $site->user->load_by_email($_REQUEST["username"]."@utbm.fr");
    break;
    case "assidu" :
      $site->user->load_by_email($_REQUEST["username"]."@assidu-utbm.fr");
    break;
    case "id" :
      $site->user->load_by_id($_REQUEST["username"]);
    break;
    case "autre" :
      $site->user->load_by_email($_REQUEST["username"]);
    break;
    case "alias" :
      $site->user->load_by_alias($_REQUEST["username"]);
    break;
    default :
      $site->user->load_by_email($_REQUEST["username"]."@utbm.fr");
    break;
  }

  if ( ($site->user->id == -1) || !$site->user->is_password($_POST["password"]) )
    $Erreur = "Nom d'utilisateur ou mot de passe invalide.";

  else if ( $site->user->hash != "valid" )
    $Erreur = "Votre compte n'a pas ete validé.";

  else
  {
    $forever=false;
    $site->connect_user($forever);
  }
}




$site->start_page("accueil");

$home = new icontents();

if ( $site->user->id > 1 )
{
  if ( isset( $_REQUEST["simplesearch"]) && !empty($_REQUEST["pattern"]) && $site->user->ae )
  {
    $home->add_title(1,"AE - Matmatronch");

    $pattern = $_REQUEST["pattern"];
    $home->add_title(2,"Resultat pour ".htmlentities($pattern,ENT_NOQUOTES,"UTF-8"));

    $pattern = ereg_replace("(e|é|è|ê|ë|É|È|Ê|Ë)","(e|é|è|ê|ë|É|È|Ê|Ë)",$_REQUEST["pattern"]);
    $pattern = ereg_replace("(a|à|â|ä|À|Â|Ä)","(a|à|â|ä|À|Â|Ä)",$pattern);
    $pattern = ereg_replace("(i|ï|î|Ï|Î)","(i|ï|î|Ï|Î)",$pattern);
    $pattern = ereg_replace("(c|ç|Ç)","(c|ç|Ç)",$pattern);
    $pattern = ereg_replace("(u|ù|ü|û|Ü|Û|Ù)","(u|ù|ü|û|Ü|Û|Ù)",$pattern);
    $pattern = ereg_replace("(n|ñ|Ñ)","(n|ñ|Ñ)",$pattern);
    $sqlpattern = mysql_real_escape_string($pattern);

    $req = new requete($site->db, "SELECT utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE CONCAT(`prenom_utl`,' ',`nom_utl`) REGEXP '^".$sqlpattern."' " .
        "UNION SELECT utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE CONCAT(`nom_utl`,' ',`prenom_utl`) REGEXP '^".$sqlpattern."' " .
        "UNION SELECT utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE `alias_utl`!='' AND `alias_utl` REGEXP '^".$sqlpattern."' " .
        "UNION SELECT utilisateurs.* " .
        "FROM `utl_etu_utbm` " .
        "INNER JOIN `utilisateurs` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
        "WHERE `surnom_utbm`!='' AND `surnom_utbm` REGEXP '^".$sqlpattern."' ");

    $nbutils = $req->lines;

    if ( $nbutils > 0 )
    {
      $req = new requete($site->db, "SELECT CONCAT(`prenom_utl`,' ',`nom_utl`),'1' as `method`, utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE CONCAT(`prenom_utl`,' ',`nom_utl`) REGEXP '^".$sqlpattern."' " .
        "UNION SELECT CONCAT(`nom_utl`,' ',`prenom_utl`),'2' as `method`, utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE CONCAT(`nom_utl`,' ',`prenom_utl`) REGEXP '^".$sqlpattern."' " .
        "UNION SELECT `alias_utl`, '3' as `method`, utilisateurs.* " .
        "FROM `utilisateurs` " .
        "WHERE `alias_utl`!='' AND `alias_utl` REGEXP '^".$sqlpattern."' " .
        "UNION SELECT `surnom_utbm`, '4' as `method`, `utilisateurs`.* " .
        "FROM `utl_etu_utbm` " .
        "INNER JOIN `utilisateurs` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
        "WHERE `surnom_utbm`!='' AND `surnom_utbm`!=`alias_utl` AND `surnom_utbm` REGEXP '^".$sqlpattern."' " .
        "ORDER BY 1 LIMIT 9");

      $home->add_paragraph("$nbutils resultats");
      $key = 1;

      $chars = array (
      1=>"&#59106;",
      2=>"&#59107;",
      3=>"&#59108;",
      4=>"&#59109;",
      5=>"&#59110;",
      6=>"&#59111;",
      7=>"&#59112;",
      8=>"&#59113;",
      9=>"&#59114;");

      while ( $row = $req->get_row() )
      {

        if ( $row["method"] > 2 )
        {
          $nom = $row['prenom_utl']." ".$row['nom_utl']." : ".$row[0];
        }
        else
        {
          $nom = $row[0];
        }

        $home->puts($chars[$key]."<a href=\"user.php?id_utilisateur=".$row['id_utilisateur']."&amp;pattern=".
          urlencode($_REQUEST["pattern"])."&amp;sid=".$site->sid."\" accesskey=\"$key\"> ".
          htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a><br/>");

        $key++;
      }

    }
    else
    {
      $home->add_paragraph("<font color=\"red\">Aucun resultat</font>","center");

      $home->add_title(2,"Nouvelle recherche");
      $frm = new iform("index.php");
      $frm->add_text_field("pattern","Qui?","prenom.nom",true);
      $frm->add_submit("simplesearch","Rechercher");
      $home->add($frm);
    }

  }
  else
  {
    $home->add_title(1,"AE.utbm.fr pour mobile");

    $home->add_title(2,"Matmatronch");
    if ( !$site->user->ae )
      $home->add_paragraph("<font color=\"red\">Ce service est r&eacute;serve aux cotisants de l'AE</font>","center");

    else
    {
      $frm = new iform("index.php");
      $frm->add_text_field("pattern","Qui?","prenom.nom",true);
      $frm->add_submit("simplesearch","Rechercher");
      $home->add($frm);
    }

    $home->puts("<br />");

    $home->add_title(2,"Le petit g&eacute;ni");
    $frm = new iform("pg/index.php");
    $frm->add_text_field("pattern","Quoi?","",true);
    $frm->add_submit("simplesearch","Rechercher");
    $home->add($frm);

    $home->add_paragraph("&#59106; <a href=\"pg/?sid=".$site->sid."\" accesskey=\"1\">Petit g&eacute;ni par cat&eacute;gories</a>");




    $home->add_title(2,"Comptoirs");

    $req = new requete ($site->dbrw,
           "UPDATE `cpt_tracking` SET `closed_time`='".date("Y-m-d H:i:s")."'
            WHERE `activity_time` <= '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
            AND `closed_time` IS NULL");

    $req = new requete ($site->dbrw,
           "SELECT MAX(activity_time),id_comptoir
            FROM `cpt_tracking`
            WHERE `activity_time` > '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
            AND `closed_time` IS NULL
            GROUP BY id_comptoir");

    while ( list($act,$id) = $req->get_row() )
      $activity[$id]=strtotime($act);

    $req = new requete ($site->dbrw,
           "SELECT id_comptoir, nom_cpt
            FROM cpt_comptoir
            WHERE type_cpt='0' AND id_comptoir != '4'
            ORDER BY nom_cpt");

    $home->puts("<p>");

    while ( list($id,$nom) = $req->get_row() )
    {
      $led = "green";
      $descled = "ouvert";
      if ( !isset($activity[$id]) )
      {
        $led = "red";
        $descled = "ferm&eacute; (pas d'activit&eacute;)";
      }
      elseif ( time()-$activity[$id] > 600 )
      {
        $led = "yellow";
        $descled = "ouvert (peu d'activit&eacute;)";
      }

      $home->puts("<img src=\"../images/leds/".$led."led.png\" /> $nom : $descled<br/>");

    }
    $home->puts("</p>");

    if ( !$site->expirable && isset($_GET["sid"]) )
      $home->add_paragraph("Vous pouvez ajouter cette page a vos favoris pour acceder directement au site (sans se re-connecter)");




  }
}
else
{
  $home->add_title(1,"AE.utbm.fr pour mobile [version beta]");

  $home->add_paragraph("Ce site vous permet de consulter le matmatronch et le petit g&eacute;ni grace a un telephone i-mode(tm) ou WAP 2");

  $home->add_title(2,"Le petit g&eacute;ni");

  $frm = new iform("pg/index.php");
  $frm->add_text_field("pattern","Quoi?","",true);
  $frm->add_submit("simplesearch","Rechercher");
  $home->add($frm);

  $home->add_paragraph("&#59106; <a href=\"pg/?sid=".$site->sid."\" accesskey=\"1\">Petit g&eacute;ni par cat&eacute;gories</a>");

  $home->add_title(2,"Se connecter");

  if ( !is_null($Erreur) )
    $home->add_paragraph("<font color=\"red\">".htmlentities($Erreur,ENT_NOQUOTES,"UTF-8")."</font>","center");

  $frm = new iform("index.php");
  $frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"));
  $frm->add_text_field("username","Utilisateur","prenom.nom",true);
  $frm->add_password_field("password","Mot de passe","",true);
  $frm->add_submit("connect","Se connecter");
  $home->add($frm);
}
$site->add_contents($home);
$site->end_page();

?>
