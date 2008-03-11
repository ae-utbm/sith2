<?
$topdir="../";

include($topdir. "include/site.inc.php");
 
$site = new site ();
for($m=0;$m<4;$m++)
{
$timing["method$m"] -= microtime(true);
for($i=0;$i<30;$i++)
{
  $site->load_session($_COOKIE['AE2_SESS_ID'],$m);
}
$timing["method$m"] += microtime(true);
}

echo "<pre>";
print_r($timing);
echo "</pre>";

?>