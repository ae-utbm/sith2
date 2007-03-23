<?
$topdir="../";
require_once($topdir."sas2/include/sas.inc.php");
require_once($topdir."sas2/include/mosaic.inc.php");

$site = new sas();

$Mosaic = new ImageMosaic($site->dbrw,3);

if ( isset($_REQUEST["forcegen"]) )
{
  $Mosaic->generate_palette(); 
  $Mosaic->store_palette(); 
}
else
  $Mosaic->load_palette(); 

if ( $Mosaic->load_image(100,75,"http://ae.utbm.fr/var/img/matmatronch/1649.jpg") )
{
  if ( isset($_REQUEST["output"]) && $_REQUEST["output"] =="jpeg" )
  {
    $Mosaic->output_image ( 60, "out.jpg" );
    echo "<a href=\"out.jpg\"><img src=\"out.jpg\" border=\"0\" /></a>";  
  }
  else
    $Mosaic->output_html();
}


?>