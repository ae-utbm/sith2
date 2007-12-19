<?
/*
 * Mise automatique d'une uv dans une catégorie
 *
 *
 *
 *
 */

header("Content-type: text/plain");

$topdir = "../";
require_once($topdir . "include/site.inc.php");

$dbrw = new mysqlae("rw");

/*
echo "--- EXPRESSION COMMUNICATION ---\n";


$req_EC = "SELECT `id_uv`, `code_uv` FROM `edu_uv` 
                      WHERE 
                            `code_uv` LIKE 'LC%'
                      OR
                            `code_uv` LIKE 'LE%'
                      OR
                            `code_uv` LIKE 'LG%'
                      OR
                            `code_uv` LIKE 'LI%'
                      OR
                            `code_uv` LIKE 'LJ%'
                      OR
                            `code_uv` LIKE 'LK%'
                      OR
                            `code_uv` LIKE 'LR%'
                      OR
                            `code_uv` LIKE 'LS%'
                      OR
                            `code_uv` LIKE 'LZ%'
                      OR
                            `code_uv` LIKE 'XC%'
                      OR
                            `code_uv` LIKE 'XE%'
                      OR
                            `code_uv` LIKE 'XG%'
                      OR
                            `code_uv` LIKE 'XI%'
                      OR
                            `code_uv` LIKE 'XJ%'
                      OR
                            `code_uv` LIKE 'XK%'
                      OR
                            `code_uv` LIKE 'XR%'
                      OR
                            `code_uv` LIKE 'XS%'
                      OR
                            `code_uv` LIKE 'XZ%'
                      OR
                            `code_uv` IN ('AV', 'SL')";




$req = new requete($dbrw, $req_EC);

while ($rs = $req->get_row())
{
  echo $rs['id_uv'] . " " . $rs['code_uv'] . "\n";
  categorize($rs['id_uv'], "Humanites", "EC");

}

echo "--- CULTURES GENERALES ---\n";

$req_CG = "SELECT `id_uv`, `code_uv` FROM `edu_uv` WHERE
                 `code_uv` LIKE 'AR__'
                OR `code_uv` LIKE 'DR__'
                OR `code_uv` LIKE 'EC__'
                OR `code_uv` LIKE 'EE__'
                OR `code_uv` LIKE 'EI__'
                OR `code_uv` LIKE 'EV__'
                OR `code_uv` LIKE 'GE__'
                OR `code_uv` LIKE 'GO__'
                OR `code_uv` LIKE 'HE__'
                OR `code_uv` LIKE 'HT__'
                OR `code_uv` LIKE 'MG__'
                OR `code_uv` = 'MR'
                OR `code_uv` LIKE 'PA00'
                OR `code_uv` LIKE 'PH__'
                OR `code_uv` LIKE 'SC__'
                OR `code_uv` LIKE 'SI__'
                OR `code_uv` LIKE 'SO__'
                OR `code_uv` LIKE 'SP__'
                OR `code_uv` LIKE 'TI__'";

$req = new requete($dbrw, $req_CG);

while ($rs = $req->get_row())
{
  echo $rs['id_uv'] . " " . $rs['code_uv'] . "\n";
  categorize($rs['id_uv'], "Humanites", "CG");
}


echo "--- UV EXTERIEURES ---\n";

$req_ext = "SELECT `id_uv`, `code_uv` FROM `edu_uv`
            WHERE
                   `code_uv` = 'LF0_'
              OR
                   `code_uv` = 'LZ0_'
              OR
                   `code_uv` = 'PA01'";

$req = new requete($dbrw, $req_ext);

while ($rs = $req->get_row())
{
  echo $rs['id_uv'] . " " . $rs['code_uv'] . "\n";
  categorize($rs['id_uv'], "Humanites", "EX");
}
*/


$reqtc_cs = "SELECT `id_uv` FROM `edu_uv` WHERE `code_uv` IN ('AC20', 'CM11', 'CM19', 'CM21', 'CM22', 'MT11', 'MT12', 'MT18', 'MT19', 'MT20', 'MT21', 'MT25', 'MT26', 'MT27', 'PS11', 'PS12', 'PS18', 'PS19', 'PS20', 'PS21', 'PS25', 'PS26', 'PS27', 'PS28', 'PS29', 'SQ20','SQ28')";

$req = new requete($dbrw, $reqtc_cs);

while ($rs = $req->get_row())
{
  categorize($rs['id_uv'], "TC", "CS");
}

$reqtc_tm = "SELECT `id_uv` FROM `edu_uv` WHERE `code_uv` IN ('EL20', 'EL21', 'LO10', 'LO11', 'LO19', 'LO20', 'LO21', 'LO22', 'LO27', 'MQ21', 'MQ22', 'PM11', 'PM18', 'SY20', 'TF20', 'TN13', 'TN18', 'TN19', 'TN20', 'TN21', 'TN22', 'TW20')";

$req = new requete($dbrw, $reqtc_tm);

while ($rs = $req->get_row())
{
  categorize($rs['id_uv'], "TC", "TM");
}
 
$reqtc_ex = "SELECT `id_uv` FROM `edu_uv` WHERE `code_uv` IN ('ST00', 'ST10', 'ST20')";

$req = new requete($dbrw, $reqtc_ex);

while ($rs = $req->get_row())
{
  categorize($rs['id_uv'], "TC", "EX");
}


function categorize($iduv, $id_dept, $cat)
{
  global $dbrw;

  /* risque de merder (clé primaire) */
  $ins = new insert($dbrw, 'edu_uv_dept',
		    array('id_uv' => $iduv,
			  'id_dept' => $id_dept,
			  'uv_cat' => $cat));
  if ($ins->lines != 1)
    {
      new update($dbrw, 'edu_uv_dept',
		 array('uv_cat' => $cat),
		 array('id_uv' => $iduv,
		       'id_dept' => $id_dept));
    }
}

?>