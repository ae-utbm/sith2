<?php
header("Location: ../");
$topdir="../../";
include($topdir."include/i/site.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");

$dbpg = new mysqlpg ();

$sqlbase = "SELECT pg_liste.*," .
      "pg_cat1.nom AS nom_cat1, pg_cat1.id AS id_cat1," .
      "pg_cat2.nom AS nom_cat2, pg_cat2.id AS id_cat2, " .
      "pg_cat3.nom AS nom_cat3, pg_cat3.id AS id_cat3," .
      "pg_voie.nom AS nom_voie, pg_voie.id AS id_voie, " .
      "pg_voie_type.nom AS nom_voie_type, pg_voie_type.id AS id_voie_type," .
      "pg_secteur2.nom AS nom_secteur, pg_secteur2.id AS id_secteur " .
      "FROM pg_liste " .
      "INNER JOIN pg_cat3 ON pg_cat3.id =pg_liste.cat " .
      "INNER JOIN pg_cat2 ON pg_cat2.id =pg_cat3.id_cat2 " .
      "INNER JOIN pg_cat1 ON pg_cat1.id =pg_cat2.id_cat1 " .
      "LEFT JOIN pg_voie ON pg_voie.id=pg_liste.voie " .
      "LEFT JOIN pg_voie_type ON pg_voie_type.id=pg_voie.id_type " .
      "LEFT JOIN pg_secteur2 ON pg_secteur2.id=pg_liste.secteur " .
      "WHERE ( pg_liste.status = 1 AND pg_liste.print_web = 1 ) ";

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

$key=1;

$site->start_page("pg");

$home = new icontents();
$home->add_title(1,"AE - Petit Geni");

if ( isset($_REQUEST["id_fiche"]) )
{
  $req = new requete($dbpg,$sqlbase."AND pg_liste.id='".mysql_real_escape_string($_REQUEST["id_fiche"])."' LIMIT 1");

  if ( $req->lines == 1 )
  {
    $row = $req->get_row();

    $home->add_title(2,htmlentities($row['nom']));

    $adresse = ($row['no']?($row['no'].", "):'') . ($row['nom_voie_type']?(($row['nom_voie_type'][strlen($row['nom_voie_type'])-1] == "'")?($row['nom_voie_type']):($row['nom_voie_type']." ")):'') . $row['nom_voie'];

    $home->puts("<p>");
    $home->puts("Tel: <a href=\"tel:" . htmlentities($row['tel'])  ."\" telbook=\"".htmlentities($row['nom'])."\" accesskey=\"1\">&#59106; " . htmlentities($row['tel'])  ."</a><br />");
    $home->puts("Fax: " . htmlentities($row['fax'])  ."<br />");
    $home->puts("Adresse: " . htmlentities($adresse)  ."<br />");
    $home->puts("Secteur: " . htmlentities($row['nom_secteur'])  ."<br />");

    if ( $row['email'] )
      $home->puts("Email: <a href=\"mailto:" . htmlentities($row['email'])  ."\" accesskey=\"2\">&#59107; " . htmlentities($row['email'])  ."</a><br />");

    if ( $row['reduc_petitgeni'] )
      $home->puts("Reduction petit g&eacute;ni: <font color=\"blue\">" . htmlentities($row['reduc_petitgeni'])  ."</font><br />");

    if ( $row['horaire'] )
      $home->puts("Horraire: " . htmlentities($row['horaire'])  ."<br />");

    $home->puts("</p>");


    if ( $row['description'] )
      $home->add_paragraph($row['description']);

    $home->add_paragraph("&#59108; <a href=\"index.php?id_cat3=".$row['id_cat3']."&amp;sid=".$site->sid."\" accesskey=\"3\">".htmlentities($row['nom_cat1'])." / ".htmlentities($row['nom_cat2'])." / ".htmlentities($row['nom_cat3'])."</a>");

  }
  else
    $home->add_paragraph("<font color=\"red\">Page invalide</font>","center");

}
else if ( isset($_REQUEST["id_cat1"]) )
{
  $sql1 = new requete($dbpg,"SELECT nom,id FROM pg_cat1 WHERE id='".mysql_real_escape_string($_REQUEST["id_cat1"])."'");

  if ( $sql1->lines == 1 )
  {

    list($nom,$id_cat1) = $sql1->get_row();

    $home->add_title(2,"<a href=\"index.php?sid=".$site->sid."\">Le guide</a> / <a href=\"index.php?id_cat1=$id_cat1&amp;sid=".$site->sid."\">".htmlentities($nom)."</a>");

    $sql2 = new requete($dbpg,"SELECT nom,id FROM pg_cat2 WHERE id_cat1='$id_cat1' ORDER BY nom");


    $home->puts("<p>");

    while ( list($nom,$id_cat2) = $sql2->get_row() )
    {
      if ( $key > 9 )
        $home->puts(" <a href=\"index.php?id_cat2=$id_cat2&amp;sid=".$site->sid."\">".htmlentities($nom)."</a><br/>");
      else
        $home->puts($chars[$key]." <a href=\"index.php?id_cat2=$id_cat2&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($nom)."</a><br/>");
      $key++;
    }
    $home->puts("</p>");

  }
  else
    $home->add_paragraph("<font color=\"red\">Page invalide</font>","center");
}
elseif ( isset($_REQUEST["id_cat2"]) )
{

  $sql = new requete($dbpg,"SELECT pg_cat1.nom, pg_cat1.id," .
      "pg_cat2.nom, pg_cat2.id " .
      "FROM pg_cat2 " .
      "INNER JOIN pg_cat1 ON pg_cat1.id =pg_cat2.id_cat1 " .
      "WHERE pg_cat2.id='".mysql_real_escape_string($_REQUEST["id_cat2"])."'");

  if ( $sql->lines == 1 )
  {
    list($nom1,$id_cat1,$nom2,$id_cat2) = $sql->get_row();

    $home->add_title(2,"<a href=\"index.php?sid=".$site->sid."\">Le guide</a> / <a href=\"index.php?id_cat1=$id_cat1&amp;sid=".$site->sid."\">".htmlentities($nom1)."</a> / <a href=\"index.php?id_cat2=$id_cat2&amp;sid=".$site->sid."\">".htmlentities($nom2)."</a>");

    $sql3 = new requete($dbpg,"SELECT nom,id FROM pg_cat3 WHERE id_cat2='$id_cat2' ORDER BY nom");


    $home->puts("<p>");
    while ( list($nom,$id_cat3) = $sql3->get_row() )
    {
      if ( $key > 9 )
        $home->puts(" <a href=\"index.php?id_cat3=$id_cat3&amp;sid=".$site->sid."\">".htmlentities($nom)."</a><br/>");
      else
        $home->puts($chars[$key]." <a href=\"index.php?id_cat3=$id_cat3&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($nom)."</a><br/>");
      $key++;
    }
    $home->puts("</p>");
  }
  else
    $home->add_paragraph("<font color=\"red\">Page invalide</font>","center");
}
elseif ( isset($_REQUEST["id_cat3"]) )
{
  $sql = new requete($dbpg,"SELECT pg_cat1.nom, pg_cat1.id," .
      "pg_cat2.nom, pg_cat2.id, " .
      "pg_cat3.nom, pg_cat3.id " .
      "FROM pg_cat3 " .
      "INNER JOIN pg_cat2 ON pg_cat2.id =pg_cat3.id_cat2 " .
      "INNER JOIN pg_cat1 ON pg_cat1.id =pg_cat2.id_cat1 " .
      "WHERE pg_cat3.id='".mysql_real_escape_string($_REQUEST["id_cat3"])."'");

  if ( $sql->lines == 1 )
  {
    list($nom1,$id_cat1,$nom2,$id_cat2,$nom3,$id_cat3) = $sql->get_row();

    $home->add_title(2,"<a href=\"index.php?sid=".$site->sid."\">Le guide</a> / <a href=\"index.php?id_cat1=$id_cat1&amp;sid=".$site->sid."\">".htmlentities($nom1)."</a> / <a href=\"index.php?id_cat2=$id_cat2&amp;sid=".$site->sid."\">".htmlentities($nom2)."</a>/ <a href=\"index.php?id_cat3=$id_cat3&amp;sid=".$site->sid."\">".htmlentities($nom3)."</a>");

    $home->puts("<p>");
    $req = new requete($dbpg,$sqlbase."AND cat='".mysql_real_escape_string($_REQUEST["id_cat3"])."' ORDER BY pg_liste.nom");

    while ( $row = $req->get_row() )
    {
      if ( $key > 9 )
        $home->puts(" <a href=\"index.php?id_fiche=".$row['id']."&amp;sid=".$site->sid."\">".htmlentities($row['nom'])."</a><br/>");
      else
        $home->puts($chars[$key]." <a href=\"index.php?id_fiche=".$row['id']."&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($row['nom'])."</a><br/>");
      $key++;
    }
    $home->puts("</p>");
  }
  else
    $home->add_paragraph("<font color=\"red\">Page invalide</font>","center");
}
elseif ( isset($_REQUEST["simplesearch"]) && !empty($_REQUEST["pattern"]) )
{



  $home->add_title(2,"Resultats pour ".htmlentities($_REQUEST["pattern"],ENT_NOQUOTES,"UTF-8"));

  $patterns=explode(" ",$_REQUEST["pattern"]);

  $reqf="";

  foreach ( $patterns as $value )
  {
    $value = utf8_decode(mysql_real_escape_string($value));
    if ( $reqf ) $reqf .= " AND ";
    $reqf .= "(pg_liste.nom REGEXP '[[:<:]]".$value."[[:>:]]' OR ".
        "pg_liste.description REGEXP '[[:<:]]".$value."[[:>:]]')";
  }

  $req = new requete($dbpg,"SELECT pg_cat1.nom, pg_cat1.id," .
      "pg_cat2.nom, pg_cat2.id, " .
      "pg_cat3.nom, pg_cat3.id " .
      "FROM pg_cat3 " .
      "INNER JOIN pg_cat2 ON pg_cat2.id =pg_cat3.id_cat2 " .
      "INNER JOIN pg_cat1 ON pg_cat1.id =pg_cat2.id_cat1 " .
      "WHERE pg_cat3.nom LIKE '%".utf8_decode(mysql_real_escape_string($_REQUEST["pattern"]))."%' LIMIT 3");

  $home->add_title(3,"Cat&eacute;gories");

  if ( $req->lines == 0 )
    $home->add_paragraph("Aucun r&eacute;sultats");
  else
  {
    $home->puts("<p>");
    while ( list($nom1,$id_cat1,$nom2,$id_cat2,$nom3,$id_cat3) = $req->get_row() )
    {
      $home->puts($chars[$key]." <a href=\"index.php?id_cat3=$id_cat3&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($nom1." / ".$nom2." / ".$nom3)."</a><br/>");
      $key++;
    }
    $home->puts("</p>");
  }
  $req = new requete($dbpg,$sqlbase."AND ($reqf) ORDER BY pg_liste.nom LIMIT 6");

  $home->add_title(3,"Fiches");
  if ( $req->lines == 0 )
    $home->add_paragraph("Aucun r&eacute;sultats");
  else
  {
    $home->puts("<p>");
    while ( $row = $req->get_row() )
    {
      $home->puts($chars[$key]." <a href=\"index.php?id_fiche=".$row['id']."&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($row['nom'])."</a><br/>");
      $key++;
    }
    $home->puts("</p>");
  }

  $home->add_paragraph("* <a href=\"index.php?sid=".$site->sid."\" accesskey=\"*\">Nouvelle recherche</a>");

}
else
{
  $home->add_title(2,"Recherche");
  $frm = new iform("index.php");
  $frm->add_text_field("pattern","Quoi?","",true);
  $frm->add_submit("simplesearch","Rechercher");
  $home->add($frm);

  $home->add_title(2,"Navigation");

  $sql1 = new requete($dbpg,"SELECT nom,id FROM pg_cat1 ORDER BY ordre");

  $home->puts("<p>");
  while ( list($nom,$id_cat1) = $sql1->get_row() )
  {
    $home->puts($chars[$key]." <a href=\"index.php?id_cat1=$id_cat1&amp;sid=".$site->sid."\" accesskey=\"$key\">".htmlentities($nom)."</a><br/>");
    $key++;
  }
  $home->puts("</p>");
}

$site->add_contents($home);
$site->end_page();

?>
