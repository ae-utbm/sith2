<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");


$site = new site();


if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}


$site->add_contents(new contents("Emplois du temps",
				 "Système de gestion des emplois du temps.
                                  </br> <b>ET PAF !</b>"));

$site->end_page();

?>
