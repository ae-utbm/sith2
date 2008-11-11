<?
$topdir="../";
require_once($topdir."sas2/include/sas.inc.php");
require_once($topdir."sas2/include/mosaic.inc.php");

$site = new sas();

$ph = new photo($site->db,$site->dbrw);

$req = new requete($site->db, "SELECT * FROM `sas_photos` WHERE couleur_moyenne IS NULL LIMIT 10");

$st = microtime_float();

echo "<ul>";
while ( $row = $req->get_row())
{
  $ph->_load($row);
  $ph->_calcul_couleur_moyenne();
  echo "<li>".$ph->id." : #".sprintf("%06x",$ph->couleur_moyenne)."</li>";
}
echo "</ul>";

echo "<p>elapsed: ".(microtime_float()-$st)." sec</p>";

?>
