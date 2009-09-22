<?php
// check if vache is up
$ping = @fopen("http://barty.me.aeinfo.net/list_heldmsgs.php",'r');
if(!$ping && !file_exists("/tmp/vachedown"))
  @touch("/tmp/vachedown");
elseif($ping && file_exists("/tmp/vachedown"))
  @unlink("/tmp/vachedown");
?>
