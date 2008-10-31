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

/*
// EDIM
$req_rn = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('MT32', 'PS30', 'TN31')";

$req = new requete($dbrw, $req_rn);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'EDIM', 'RN');


$req_cs = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('AG80', 'CP80', 'CP81',
                  'CP82', 'DI80', 'DI81', 'EG80', 'EL40',
                  'IN80', 'MA80', 'MA81', 'MQ80', 'MT80', 'PR80', 'PS80')";

$req = new requete($dbrw, $req_cs);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'EDIM', 'CS');


$req_ex = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('ST00', 'ST40', 'ST50')";

$req = new requete($dbrw, $req_ex);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'EDIM', 'EX');


// GESC

$req_rn = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('MT30')";

$req = new requete($dbrw, $req_rn);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GESC', 'RN');


$req_cs = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('EL47', 'EL48', 'EN40', 'EN41', 'EN42', 'IF40', 'MC43', 'MN42', 'MT41', 'SQ40', 'SY45', 'SY46', 'SY47')";

$req = new requete($dbrw, $req_cs);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GESC', 'CS');


$req_tm = "SELECT `id_uv` FROM `edu_uv` WHERE
            `code_uv` IN ('AT50', 'AT51', 'AT52', 'EL53', 'EL54', 'EL55', 'EL56', 'EL57', 'EL59', 'EL60', 'EN50', 'ER50', 'ER51', 'ER52', 'GP52', 'MI43', 'MQ55', 'OM58', 'RE54', 'SM51', 'SM53', 'SM54', 'SM55', 'SM56', 'SM57', 'SY50', 'TO54', 'TR57', 'TW54', 'TX54')";

$req = new requete($dbrw, $req_tm);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GESC', 'TM');
$req_ex = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('ST00', 'ST40', 'ST50')";

$req = new requete($dbrw, $req_ex);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GESC', 'EX');



// GI
$req_rn = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('MT30')";

$req = new requete($dbrw, $req_rn);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GI', 'RN');


$req_cs = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('AG41', 'BD40', 'IA41', 'IN41', 'IN42', 'LO41', 'LO42', 'LO43', 'LO44', 'MI41', 'MI43', 'MT41', 'MT42', 'MT43', 'MT44', 'RE41', 'SQ40')";

$req = new requete($dbrw, $req_cs);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GI', 'CS');


$req_tm = "SELECT `id_uv` FROM `edu_uv` WHERE
            `code_uv` IN ('AG51', 'BD50', 'CP42', 'GL51', 'GL52', 'GL53', 'GL54', 'IA52', 'IA54', 'IN52', 'IN54', 'IN55', 'IN56', 'LO51', 'LO52', 'LO53', 'MI51', 'MT51', 'RE51', 'RE52', 'RE53', 'RE55', 'RE56', 'TL51', 'TL52', 'TO52', 'TR52', 'TR53', 'TR54', 'TW52', 'TX52', 'VI50', 'VI51')";

$req = new requete($dbrw, $req_tm);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GI', 'TM');
$req_ex = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('ST00', 'ST40', 'ST50')";

$req = new requete($dbrw, $req_ex);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GI', 'EX');


// IMAP

$req_rn = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('MT30')";

$req = new requete($dbrw, $req_rn);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'IMAP', 'RN');


$req_cs = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('CP44', 'EL40', 'EL41', 'FQ40', 'GP40', 'MA43', 'MQ45', 'OM43', 'OM44', 'PR41', 'PR44', 'PR45', 'SQ40', 'SY40', 'SY41', 'TF41')";

$req = new requete($dbrw, $req_cs);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'IMAP', 'CS');


$req_tm = "SELECT `id_uv` FROM `edu_uv` WHERE
            `code_uv` IN ('CF51', 'CP55', 'EL52', 'FQ54', 'GP51', 'GP52', 'GP53', 'GP54', 'IA53', 'MA60', 'MC55', 'MC56', 'MC57', 'MN54', 'OM50', 'OM52', 'OM53', 'OM56', 'OM57', 'OM59', 'PR51', 'SY52', 'SY53', 'TN54', 'TN57', 'TO53', 'TW53', 'TX53')";

$req = new requete($dbrw, $req_tm);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'IMAP', 'TM');
$req_ex = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('ST00', 'ST40', 'ST50')";

$req = new requete($dbrw, $req_ex);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'IMAP', 'EX');



// GMC


$req_rn = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('EL30', 'MQ30', 'MT30', 'MT31')";

$req = new requete($dbrw, $req_rn);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GMC', 'RN');


$req_cs = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('AG43', 'CP41', 'CP42', 'CP46', 'EL40', 'MA41', 'MC42', 'MN41', 'MQ41', 'MQ42', 'MT40', 'MT41', 'SQ40', 'TE41', 'TF40', 'TN40')";

$req = new requete($dbrw, $req_cs);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GMC', 'CS');


$req_tm = "SELECT `id_uv` FROM `edu_uv` WHERE
            `code_uv` IN ('CP51', 'CP53', 'CP56', 'FQ51', 'FQ52', 'FQ53', 'FQ59', 'MA42', 'MA50', 'MA56', 'MA57', 'MA58', 'MA59', 'MC51', 'MC53', 'MC58', 'MC59', 'MC60', 'MN50', 'MN51', 'MN52', 'MN53', 'MQ51', 'SY53', 'TE51', 'TE52', 'TF51', 'TF52', 'TN51', 'TO51', 'TW51', 'TX51')";

$req = new requete($dbrw, $req_tm);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GMC', 'TM');
$req_ex = "SELECT `id_uv` FROM `edu_uv`
            WHERE
               `code_uv` IN ('ST00', 'ST40', 'ST50')";

$req = new requete($dbrw, $req_ex);

while ($rs = $req->get_row())
     categorize($rs['id_uv'], 'GMC', 'EX');










*/

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
