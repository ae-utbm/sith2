<?php

$topdir="../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/entities/asso.inc.php");

$site = new site ();
$site->start_page("bdf","Bureau Des Festivités");


$site->end_page();

?>