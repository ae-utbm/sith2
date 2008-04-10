<?php

require_once($topdir. "include/site.inc.php");

$site = new site();

echo $site->get_param("backup_server",false);

?>
