<?
$topdir="../";

include($topdir. "include/site.inc.php");
 
$site = new site ();

for($i=0;$i<1000;$i++)
{
  for($m=0;$m<3;$m++)
{
$timing["method$m"] -= microtime(true);
  $site->load_session($_COOKIE['AE2_SESS_ID'],$m);
$timing["method$m"] += microtime(true);
}
}

echo "<pre>";
print_r($timing);
echo "</pre>";

?>