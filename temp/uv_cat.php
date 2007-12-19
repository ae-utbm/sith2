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

function categorize($iduv, $id_dept, $cat)
{
  global $dbrw;

  /* risque de merder (clé primaire) */
  $ins = new insert($dbrw, 'edu_uv_dept',
		    array('id_uv' => $id_uv,
			  'id_dept' => $id_dept,
			  'cat_uv' => $cat));
  if ($ins->lines != 1)
    {
      new update($dbrw, 'edu_uv_dept',
		 array('cat_uv' => $cat),
		 array('id_uv' => $id_uv,
		       'id_dept' => $id_dept));
    }
}

?>