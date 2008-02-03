<?php

echo file_exists(realpath("busybox"));
echo "<br />";
exec(realpath("busybox"));

?>
