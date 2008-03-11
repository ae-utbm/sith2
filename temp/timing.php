<?
$topdir="../";

include($topdir. "include/site.inc.php");
 
$site = new site ();

for($i=0;$i<20;$i++)
{
  $site->load_session($_COOKIE['AE2_SESS_ID']);
}

echo "<pre>";
print_r($timing);
echo "</pre>";

?>