<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/extdb/web2.inc.php");
$site = new site ();

$site->start_page("accueil","Bienvenue");

$user = new utilisateur($site->db);
$user->load_by_id(2626);
$flick = new flickr_info($user, "justpearly");


$site->add_contents($flick->get_cts_latest_photos());


$site->end_page();



?>
