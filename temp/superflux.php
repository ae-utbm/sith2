<?php
$topdir = '../';
//require_once($topdir. "include/site.inc.php");
//$site = new site();
print_r(unserialize(file_get_contents($topdir."var/cache/stream")));
print_r(unserialize(file_get_contents($topdir."var/cache/stream-prod")));

?>
