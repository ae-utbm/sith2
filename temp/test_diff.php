<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/lib/diff.inc.php");

$site = new site ();

$site->add_css("css/diff.css");

$site->start_page("services","AE - Recherche et Développement");

$intro = new contents("Edition liste au format pdf");


$_old = "Feu est un génie, mais faut pa le dire
sinon il va se faire exploiter
par les vilains luttins de la foret
c'est la fête.";
$_new = "Feu est un génie, mais faut pas le dire
sinon il va se faire exploiter
comme ça devrait être interdit
par les vilains luttins de la foret
c'est un fait.";

$df  = new Diff($_old,$_new);
$tdf = new TableDiffFormatter();

$intro->add_paragraph("<table class=\"diff\">\n".$tdf->format($df)."</table>");


$site->add_contents($intro);
$site->end_page();


?>
