<?


$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/lieu.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");

$code_dept = $_REQUEST['code_dept'];

header("Content-type: application/vnd.google-earth.kml+xml");
header("Content-Disposition: filename=ae_utbm_dept".$code_dept.".kml");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<kml xmlns=\"http://earth.google.com/kml/2.1\">";

echo "<Document id=\"ae_utbm_fr_deptcontour\">";
echo "<name>ae utbm - Contour de departement</name>";
echo "<Placemark id=\"dept_".$code_dept."\">";
echo "<name>Département ".$code_dept."</name>";
echo "<description>Contours du département Francais ".$code_dept 
."</description>";
echo get_kml_dept(new pgsqlae(), $code_dept);

echo "</Placemark>";
echo "</Document>";
echo "</kml>";



?>