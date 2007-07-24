<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();


for ($i = 1; $i <= 321 ; $i++)
{
  if ($i < 98)
    $dept = 'Humas';
  else if ($i < 151)
    $dept = 'TC';
  else if ($i < 196)
    $dept = 'GESC';
  else if ($i < 237)
    $dept = 'GI';
  else if ($i < 277)
    $dept = 'IMAP';
  else if ($i < 322)
    $dept = 'GMC';
      
  $req = new insert($site->dbrw,
		    'edu_uv_dept',
		    array('id_uv' => $i, 'id_dept' => $dept));

}

/* ST40 */
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'GMC'));
/* ST50 */
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'GMC'));

/* SQ40 */
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'GMC'));

/* MT30 */
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'GMC'));

?>