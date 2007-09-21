<?

$topdir = "../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/pgsqlae.inc.php");

header("Content-Type: text/plain");

$db = new mysqlae();
$pgdb = new pgsqlae();

$pgrq = new pgrequete($pgdb, 
		      "SELECT name_loc, countryc_loc,
                              AsText(the_geom)
                              FROM worldloc LIMIT 10");
			      
			      
$bigarray = $pgrq->get_all_rows();


$convert_ccode = file_get_contents("./loc");
$convert_ccode = explode("\n", $convert_ccode);

foreach($convert_ccode as $line)
{
  $tmp = explode("\t", $line);
  $countries[$tmp[0]] = $tmp[5];
}

print_r($countries);

?>

