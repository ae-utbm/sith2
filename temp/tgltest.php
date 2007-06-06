<?php
$topdir = "../";

require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/edt.inc.php");

$site= new site ();

$site->start_page("accueil","Bienvenue");


require_once($topdir . "include/cts/toggle_tree.inc.php");

$bordel = array(0 => array('title' => 'first',
			   'childs' => array(0 => array('title' => '1blabla'),
					     1 => array('title' => '2truc',
							'childs' => array(0 => array('title' => 'machin'),
									  1 => array('title' => 'bidule'))))),
		1 => array('title' => 'second'));


$cts = new toggle_tree ("test",$bordel, null);

$site->add_contents($cts);


$site->end_page();

?>