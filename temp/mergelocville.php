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

print_r($bigarray);


?>

