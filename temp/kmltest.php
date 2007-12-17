<?


$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");

$code_dept = $_REQUEST['code_dept'];

echo get_kml_dept(new pgsqlae(), $code_dept);

?>