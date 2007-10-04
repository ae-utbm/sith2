<?php
$topdir="../";
require_once($topdir. "include/site.inc.php");
$site = new site();
echo "youpi";

echo "<pre>\n";
print_r($site->user);
echo "</pre>";
exit();

?>
