<?php
$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/edt_img.inc.php");

$site= new site ();

$site->start_page("accueil","Bienvenue");


if (isset($_GET['generate']) && (is_array($_SESSION['edt'])))
{
  $edt_img = new edt_img("Pedrov", $_SESSION['edt']);
  $edt_img->generate ();
}

$ct = new contents("Emploi du temps");
$ct->add_paragraph("<center><img src=\"".$topdir."temp/newedt.php?generate=1\" alt=\"edt\" /></center>");

unset($_SESSION['edt']);
$string = file_get_contents($topdir. "temp/edt.txt");


$lines = explode("\n", $string);
foreach ($lines as $line)
{
  $seance = explode (",", $line);
  $hr     = explode ("-", $seance[1] == 'C' ? $seance[3] : $seance[4]);
  $tag = $seance[1] == 'C' ? $seance[2] : $seance[3];

  switch($tag)
    {
    case "LUNDI":
      $tag = "Lundi";
      break;
    case "MARDI":
      $tag = "Mardi";
      break;
    case "MERCREDI":
      $tag = "Mercredi";
      break;
    case "JEUDI":
      $tag = "Jeudi";
      break;
    case "VENDREDI":
      $tag = "Vendredi";
      break;
    case "SAMEDI":
      $tag = "Samedi";
      break;
    }

  switch ($seance[1])
    {
    case "C":
	$type  = "Cours";
      break;
    case "T":
	$type = "TP";
      break;
    case "D":
      $type = "TD";
      break;
    }

  $edt[] = array("semaine_seance" => $seance[count($seance) - 1],
		 "hr_deb_seance"  => $hr[0],
		 "hr_fin_seance"  => $hr[1],
		 "jour_seance"    => $tag,
		 "type_seance"    => $type,
		 "grp_seance"     => $seance[1] == 'C' ? null : $seance[2],
		 "nom_uv"         => $seance[0],
		 "salle_seance"   => $seance[count($seance) - 2]);
}

$_SESSION['edt'] = $edt;
//$ct->add_paragraph("<pre>" . print_r($edt, true) . "</pre>");
$site->add_contents($ct);
$site->end_page();


?>