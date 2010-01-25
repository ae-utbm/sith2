<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/forum.inc.php");
require_once($topdir . "include/entities/sujet.inc.php");
require_once($topdir . "include/entities/message.inc.php");

require_once($topdir . "include/entities/news.inc.php");
require_once($topdir . "include/entities/sondage.inc.php");
require_once($topdir . "sas2/include/cat.inc.php");

require_once($topdir . "include/cts/forum.inc.php");

$site = new site ();
$site->add_css("css/forum.css");

$cts = new contents();
$cts->cssclass="liner";
if ( $site->user->is_valid() )
  $cts->buffer = "<p class=\"center\">Connecté en tant que ".($site->user->surnom!=null ? $site->user->surnom : $site->user->alias )." - <a href=\"../user.php?page=edit\">Mon profil</a> - <a href=\"search.php?page=unread\">Messages non lus</a></p>";
else
  $cts->buffer = "<p class=\"center\">Non connecté - <a href=\"../index.php\">Se connecter</a> - <a href=\"../newaccount.php\">Creer un compte</a></p>";
$site->add_contents($cts);
unset($cts);

$forum = new forum($site->db);
$forum->load_by_id(1);

if ( $_REQUEST["page"] == "unread" )
{
  $site->allow_only_logged_users("forum");

  $site->start_page("forum","Messages non lus");

  $cts = new contents($forum->get_html_link()." / <a href=\"search.php?page=unread\">Messages non lus</a>");

  $cts->add_paragraph(
  "<a href=\"search.php?page=unread\">".
    "<img src=\"".$wwwtopdir."images/icons/16/reload.png\" class=\"icon\" alt=\"\" />Actualiser".
  "</a> ".
  "<a href=\"./?action=setallread\">".
    "<img src=\"".$wwwtopdir."images/icons/16/valid.png\" class=\"icon\" alt=\"\" />Marquer tout comme lu".
  "</a> ".
  "<a href=\"search.php\">".
    "<img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher".
  "</a>"
  ,"frmtools");


  $query = "SELECT frm_sujet.*, ".
      "frm_message.date_message, " .
      "frm_message.id_message, " .
      "COALESCE(
        dernier_auteur_etu_utbm.surnom_utbm,
        CONCAT(dernier_auteur.prenom_utl,' ',dernier_auteur.nom_utl)
      ) AS `nom_utilisateur_dernier_auteur`, " .
      "dernier_auteur.id_utilisateur AS `id_utilisateur_dernier`, " .
      "COALESCE(
          premier_auteur_etu_utbm.surnom_utbm,
          CONCAT(premier_auteur.prenom_utl,' ',premier_auteur.nom_utl)
        ) AS `nom_utilisateur_premier_auteur`, " .
      "premier_auteur.id_utilisateur AS `id_utilisateur_premier`, " .
      "1 AS `nonlu`, " .
      "titre_forum AS `soustitre_sujet`, " .
      "frm_sujet_utilisateur.etoile_sujet AS `etoile` " .
      "FROM frm_sujet " .
      "INNER JOIN frm_forum USING(id_forum) ".
      "LEFT JOIN frm_message ON ( frm_message.id_message = frm_sujet.id_message_dernier ) " .
      "LEFT JOIN utilisateurs AS `dernier_auteur` ON ( dernier_auteur.id_utilisateur=frm_message.id_utilisateur ) " .
      "LEFT JOIN utilisateurs AS `premier_auteur` ON ( premier_auteur.id_utilisateur=frm_sujet.id_utilisateur ) ".
      "LEFT JOIN utl_etu_utbm AS `dernier_auteur_etu_utbm` ON ( dernier_auteur_etu_utbm.id_utilisateur=frm_message.id_utilisateur ) " .
      "LEFT JOIN utl_etu_utbm AS `premier_auteur_etu_utbm` ON ( premier_auteur_etu_utbm.id_utilisateur=frm_sujet.id_utilisateur )" .
      "LEFT JOIN frm_sujet_utilisateur ".
        "ON ( frm_sujet_utilisateur.id_sujet=frm_sujet.id_sujet ".
        "AND frm_sujet_utilisateur.id_utilisateur='".$site->user->id."' ) ".
      "WHERE ";

  if( is_null($site->user->tout_lu_avant))
    $query .= "(frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
              "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ";
  else
    $query .= "((frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
              "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ".
              "AND frm_message.date_message > '".date("Y-m-d H:i:s",$site->user->tout_lu_avant)."') ";

  if ( !$forum->is_admin( $site->user ) )
  {
    $grps = $site->user->get_groups_csv();
    $query .= "AND ((droits_acces_forum & 0x1) OR " .
      "((droits_acces_forum & 0x10) AND id_groupe IN ($grps)) OR " .
      "(id_groupe_admin IN ($grps)) OR " .
      "((droits_acces_forum & 0x100) AND frm_forum.id_utilisateur='".$site->user->id."')) ";
  }


  $query_fav = $query."AND frm_sujet_utilisateur.etoile_sujet='1' ";
  $query_fav .= "ORDER BY frm_message.date_message DESC ";
  $query_fav .= "LIMIT 75 ";

  $query .= "AND ( frm_sujet_utilisateur.etoile_sujet IS NULL OR frm_sujet_utilisateur.etoile_sujet!='1' ) ";
  $query .= "ORDER BY frm_message.date_message DESC ";
  $query .= "LIMIT 75 ";

  /*$query .= "ORDER BY frm_message.date_message DESC ";
  $query .= "LIMIT 100 ";*/

  $req = new requete($site->db,$query_fav);
  if ( $req->lines > 0 )
  {
    $cts->add_title(2,"Sujets favoris avec des messages non lus");
    $rows = array();
    while ( $row = $req->get_row() )
      $rows[] = $row;

    $cts->add(new sujetslist($rows, $site->user, "./", null, null,true));
    $cts->add_paragraph("&nbsp;");
  }


  $req = new requete($site->db,$query);
  if ( $req->lines > 0 )
  {
    $cts->add_title(2,"Sujets avec des messages non lus");
    $rows = array();
    while ( $row = $req->get_row() )
      $rows[] = $row;

    $cts->add(new sujetslist($rows, $site->user, "./", null, null,true));
  }

  $site->add_contents($cts);

  $site->end_page();
  exit();
}

if ( isset($_REQUEST["pattern"] ) )
{
  /*$pattern = ereg_replace("(e|é|è|ê|ë|É|È|Ê|Ë)","(e|é|è|ê|ë|É|È|Ê|Ë)",$_REQUEST["pattern"]);
  $pattern = ereg_replace("(a|à|â|ä|À|Â|Ä)","(a|à|â|ä|À|Â|Ä)",$pattern);
  $pattern = ereg_replace("(i|ï|î|Ï|Î)","(i|ï|î|Ï|Î)",$pattern);
  $pattern = ereg_replace("(c|ç|Ç)","(c|ç|Ç)",$pattern);
  $pattern = ereg_replace("(u|ù|ü|û|Ü|Û|Ù)","(u|ù|ü|û|Ü|Û|Ù)",$pattern);
  $pattern = ereg_replace("(n|ñ|Ñ)","(n|ñ|Ñ)",$pattern);
  $sqlpattern = mysql_real_escape_string($pattern);

  $sql = "SELECT frm_sujet.*, frm_message.id_message, frm_message.contenu_message, frm_message.date_message ".
         "FROM frm_message INNER JOIN frm_sujet USING ( id_sujet ) WHERE ";

  $first=true;

  $words = explode(" ",$sqlpattern);
  foreach ( $words as $word )
  {
    if ( $first )
      $first=false;
    else
      $sql .= " AND ";

    $sql .= "(contenu_message REGEXP '$word' OR titre_sujet REGEXP '$word' OR soustitre_sujet REGEXP '$word')";

  }

  $sql .= " ORDER BY frm_message.id_message DESC ";
  $sql .= "LIMIT 50";
  */

  $sql = "SELECT MATCH (titre_message,contenu_message) AGAINST ('".mysql_real_escape_string($_REQUEST["pattern"])."') AS deg, frm_sujet.*, frm_message.id_message, frm_message.contenu_message, frm_message.date_message ".
         "FROM frm_message INNER JOIN frm_sujet USING ( id_sujet ) WHERE ";
  $sql .= "MATCH (titre_message,contenu_message) AGAINST ('".mysql_real_escape_string($_REQUEST["pattern"])."') ";
  $sql .= "ORDER BY date_message DESC ";
  $sql .= "LIMIT 50";

  $req = new requete($site->db,$sql);



  $site->start_page("forum","Recherche ".htmlentities($_REQUEST["pattern"],ENT_COMPAT,"UTF-8"));

  $cts = new contents($forum->get_html_link()." / <a href=\"search.php\">Recherche</a> / <a href=\"search.php?pattern=".urlencode($_REQUEST["pattern"])."\">".htmlentities($_REQUEST["pattern"],ENT_COMPAT,"UTF-8")."</a>");


  //$cts->add(new sujetslist($rows, $site->user, "./", null, null, false));

    $id_sujet=null;

    $cts->buffer .= "<ul class=\"frmsujetres\">";

    while ( $row = $req->get_row() )
    {
      if (   $id_sujet!=$row['id_sujet'] )
      {
        if ( !is_null($id_sujet) )
          $cts->buffer .= "</ul>";
        $cts->buffer .=
        "<li class=\"sujet\"><a href=\"".$wwwtopdir."forum2/?id_sujet=".$row['id_sujet']."\">".
        "<img src=\"".$wwwtopdir."images/icons/16/sujet.png\" class=\"icon\" alt=\"\" /> <b>".
        $row['titre_sujet']."</b></a></li>";
        $cts->buffer .= "<ul class=\"frmmessagesres\">";
      }

      $cts->buffer .= "<li><a href=\"".$wwwtopdir."forum2/?id_message=".$row['id_message']."#msg".$row['id_message']."\">".substr($row['contenu_message'],0,120)."...</a> <span>- ".human_date(strtotime($row['date_message']))."</span></li>";

      $id_sujet=$row['id_sujet'];
    }
    if ( !is_null($id_sujet) )
      $cts->buffer .= "</ul>";
    $cts->buffer .= "</ul>";



  $site->add_contents($cts);

  $site->end_page();
  exit();

}

$site->start_page("forum","Recherche");

$cts = new contents($forum->get_html_link()." / <a href=\"search.php\">Recherche</a>");

$frm = new form("frmsearch",$wwwtopdir."forum2/search.php");
$frm->add_text_field("pattern","");
$frm->add_submit("search","Rechercher");
$frm->set_focus("pattern");
$cts->add($frm);

$site->add_contents($cts);

$site->end_page();
exit();

?>
