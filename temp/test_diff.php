<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/diff.inc.php");

$site = new site ();

$site->start_page("services","AE - Recherche et D�veloppement");

$intro = new contents("Edition liste au format pdf");


$_old = "Feu est un g�nie, mais faut pa le dire
sinon il va se faire exploiter
par les vilains luttins de la foret";
$_new = "Feu est un g�nie, mais faut pas le dire
sinon il va se faire exploiter
comme �a devrait �tre interdit
par les vilains luttins de la foret";

$df  = new Diff($_old,$_new);
$tdf = new TableDiffFormatter();

$intro->add_paragraph("<table class=\"diff\">\n".$tdf->format($df)."</table>");


$site->add_contents($intro);
$site->add_contents($cts);
$site->end_page();


?>
