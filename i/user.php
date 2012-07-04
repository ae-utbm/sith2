<?php

$topdir="../";
include($topdir."include/i/site.inc.php");
include($topdir."include/entities/ville.inc.php");
include($topdir."include/entities/pays.inc.php");

$UserBranches = array("TC"             => "TC",
                      "GI"             => "GI",
                      "GSP"            => "IMAP",
                      "GSC"            => "GESC",
                      "GMC"            => "GMC",
                      "Enseig"     => "Enseignant",
                      "Admini" => "Administration",
                      "Autre"          => "Autre");

if ( !$site->user->is_valid() || !$site->user->ae )
{
  header("Location: ".$topdir."i/");
  exit();
}

$user = new utilisateur( $site->db );
$user->load_by_id($_REQUEST["id_utilisateur"]);
if ( $user->id < 1 )
{
  header("Location: ".$topdir."i/");
  exit();
}
  $user->load_all_extra();

if ( isset($_REQUEST["vcf"]) )
{
  header("Content-Type: text/x-vcard");
  header('Content-Disposition: attachment; filename="'.addslashes($user->prenom." ".$user->nom.".vcf").'"');
  $user->output_vcard();
  exit();
}

$site->start_page("accueil");

$fiche = new icontents();

$fiche->add_title(1,"AE - Matmatronch");

$fiche->add_title(2,htmlentities($user->prenom." ".$user->nom,ENT_COMPAT,"UTF-8"));


if (file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.i.jpg"))
{
  $fiche->puts("<p align=\"center\"><img src=\"/var/img/matmatronch/".$user->id.".identity.i.jpg\" /></p>");
}
else if (file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg"))
{
  $src =  "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.jpg";
  $dest = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".identity.i.jpg";
  print_r(exec("/usr/share/php5/exec/convert $src -thumbnail 64x64 -quality 95 $dest"));

  $fiche->puts("<p align=\"center\"><img src=\"/var/img/matmatronch/".$user->id.".identity.i.jpg\" /></p>");
}
else if (file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".i.jpg"))
{
  $fiche->puts("<p align=\"center\"><img src=\"/var/img/matmatronch/".$user->id.".i.jpg\" /></p>");
}
else if (file_exists("/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".jpg"))
{
  $src =  "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".jpg";
  $dest = "/var/www/ae/www/ae2/var/img/matmatronch/".$user->id.".i.jpg";
  print_r(exec("/usr/share/php5/exec/convert $src -thumbnail 64x64 -quality 95 $dest"));

  $fiche->puts("<p align=\"center\"><img src=\"/var/img/matmatronch/".$user->id.".i.jpg\" /></p>");
}


$fiche->puts("<p>");

if ( $user->surnom )
  $fiche->puts("Surnom: ".htmlentities($user->surnom,ENT_COMPAT,"UTF-8")."<br />");
elseif ( $user->alias )
  $fiche->puts("Surnom: ".htmlentities($user->alias,ENT_COMPAT,"UTF-8")."<br />");

if ( $user->date_naissance )
{
  if ( $user->sexe == 1 )
    $fiche->puts("N&eacute; ");
  else
    $fiche->puts("N&eacute;e ");

  $fiche->puts("le : ". date("d/m/Y", $user->date_naissance)."<br />");
}

$ville = new ville($site->db);
$pays = new pays($site->db);

$ville->load_by_id($user->id_ville);
$pays->load_by_id($user->id_pays);

if ( $user->tel_maison )
  $fiche->puts("Fixe: <a href=\"tel:" . htmlentities($user->tel_maison,ENT_COMPAT,"UTF-8")  ."\" telbook=\"".htmlentities($user->prenom." ".$user->nom,ENT_COMPAT,"UTF-8")."\" accesskey=\"1\">&#59106;</a> " . htmlentities($user->tel_maison,ENT_COMPAT,"UTF-8")  ."<br />");
if ( $user->tel_portable )
  $fiche->puts("Portable: <a href=\"tel:" . htmlentities($user->tel_portable,ENT_COMPAT,"UTF-8") ."\" telbook=\"".htmlentities($user->prenom." ".$user->nom,ENT_COMPAT,"UTF-8")."\" accesskey=\"2\">&#59107;</a> " . htmlentities($user->tel_portable,ENT_COMPAT,"UTF-8") ."<br />");

if ( $user->addresse || $ville->is_valid() || $pays->is_valid() )
{
  $fiche->puts("Adresse: ");

  if ( $user->addresse )
    $fiche->puts(htmlentities($user->addresse,ENT_COMPAT,"UTF-8"));
  if ( $ville->is_valid() )
    $fiche->puts("<br />".htmlentities($ville->cpostal,ENT_COMPAT,"UTF-8") . " " . htmlentities($ville->nom,ENT_COMPAT,"UTF-8"));
  if ( $pays->is_valid() )
    $fiche->puts("<br />".htmlentities(strtoupper($pays->nom),ENT_COMPAT,"UTF-8"));

  $fiche->puts("<br />\n");
}

if ( $user->branche && isset($UserBranches[$user->branche]) )
{
  $fiche->puts("Departement: ".$UserBranches[$user->branche]);
  if ( $user->branche!="Enseig" && $user->branche!="Admini" && $user->branche!="Autre" )
  {
    $fiche->puts(sprintf("%02d",$user->semestre));
    if ( $user->filiere )
      $fiche->puts(" - ".htmlentities($user->filiere,ENT_COMPAT,"UTF-8")."");
  }
  $fiche->puts("<br />\n");
}

if ( $user->promo_utbm > 0 )
  $fiche->puts("Promo ".sprintf("%02d",$user->promo_utbm)."<br />");

$fiche->puts("</p>\n");

$fiche->add_paragraph("<a href=\"user.php?vcf&amp;id_utilisateur=".$user->id."&amp;sid=".$site->sid."\" accesskey=\"3\">&#59108; vCard</a>");

$site->add_contents($fiche);
$site->end_page();


?>
