<?
$topdir="../";
require_once($topdir."sas2/include/sas.inc.php");
require_once($topdir."sas2/include/mosaic.inc.php");

$site = new sas();

$req = new requete($site->db,"select r<<16|g<<8|b from sas_palette");

$n=0;

while ( list($rgb) = $req->get_row() )
{
  echo "<div style=\"float:left;width:10px;height:10px;background:#".sprintf("%06x",$rgb).";\"></div>";
  $n++;
  if ( $n==80 )
  {
    $n=0;
    echo "<div style=\"clear:both;\"></div>\n";
  }
}

?>
