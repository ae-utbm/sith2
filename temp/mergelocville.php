<?

$_SERVER['SCRIPT_FILENAME']="/var/www/ae/www/ae2/temp";
$topdir = $_SERVER['SCRIPT_FILENAME']."/../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/pgsqlae.inc.php");

header("Content-Type: text/plain");

$db = new mysqlae();
$pgdb = new pgsqlae();

$convert_ccode = file_get_contents("./loc");
$convert_ccode = explode("\n", $convert_ccode);

foreach($convert_ccode as $line)
{
  $tmp = explode("\t", $line);
  $countries[$tmp[0]]['engname'] = $tmp[4];
}


foreach ($countries as $code => $name)
{
  if (($code == "") || ($code == " "))
    continue;

  $msql = new requete($db,
		      "SELECT id_pays FROM loc_pays WHERE nomeng_pays LIKE '".
		      mysql_real_escape_string($name['engname']) ."'");

  if ($msql->lines > 0)
    {
      $ret = $msql->get_row();
      $ctoget[] = $code;
      $countries[$code]['id_pays'] = $ret['id_pays'];
    }

}


/* on tape dans postgresql */


foreach ($ctoget as $country)
{

  /* on a déja ... */
  if ($country == 'FR')
    continue;

  $pgrq = new pgrequete($pgdb,
			"SELECT
                              name_loc,
                              countryc_loc,
                              AsText(the_geom) AS pos
                       FROM
                              worldloc
                       WHERE
                              countryc_loc = '".$country."'");


  $bigarray = $pgrq->get_all_rows();

  foreach ($bigarray as $location)
    {
      $idpays = $countries[$country]['id_pays'];
      $nomville = mysql_real_escape_string($location['name_loc']);

      $coords = $location['pos'];
      $coords = str_replace(array("POINT(", ")"), "", $coords);
      list($lat, $long) = explode(" ", $coords);

      $lat = deg2rad($lat);
      $long = deg2rad($long);

      $lat = str_replace(",", ".", $lat);
      $long = str_replace(",", ".", $long);

      echo "INSERT INTO `loc_ville` (`id_pays`, `nom_ville`, `lat_ville`, `long_ville`) VALUES ( ".$idpays.", '".$nomville."', '".$long."', '".$lat."');\n";

    }


  /* on s'arrete pour l'instant au premier pays, sinon ca va
   * tout tuer
   */
  //  exit();

}
//print_r($ctoget);
//print_r($countries);



?>

