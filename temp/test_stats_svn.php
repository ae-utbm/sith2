<?php

$topdir="../";

include($topdir."include/graph.inc.php");

$cam=new camembert(600,380,array(),2,0,0,0,0,0,0,10,150);

$svn=exec("/usr/share/php5/exec/svn_stats.sh");
$svn=explode("|",$svn,-1);
for($i=0;$i<count($svn);$i++)
{
  if(!empty($svn[$i]))
  {
		$tmp=explode(" ",$svn[$i]);
    $cam->data($tmp[1],$tmp[0]);
  }
}
$cam->png_render();
exit();
?>
