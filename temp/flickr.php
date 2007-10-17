<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/extdb/web2.inc.php");
$site = new site ();

$site->start_page("accueil","Bienvenue");

$user = new utilisateur($site->db);
$user->load_by_id(2989);
$flick = new flickr_info($user, "maximeh");



$site->add_contents(new contents("debug", $flick));

$site->end_page();



?>