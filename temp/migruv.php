<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

/*
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

$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 194, 'id_dept' => 'GMC'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 195, 'id_dept' => 'GMC'));

$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 160, 'id_dept' => 'GMC'));

$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'GI'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'IMAP'));
$req = new insert($site->dbrw,
		  'edu_uv_dept',
		  array('id_uv' => 150, 'id_dept' => 'GMC'));
*/

$conasses = array("DR01" => "Introduction à l'étude du droit",
		  "EV00" => "Initiation à l'environnement et à ses problématiques",
		  "LZ00" => "Langue non enseignée à l'UTBM",
		  "PH01" => "Initiation à la philosophie",
		  "PH06" => "Introduction à la philosophie des sciences",
		  "SC01" => "De la psychologie cognitive à l'ergonomie cognitive",
		  "SP03" => "Gestion de sa vie physique à travers le sport de compétition",
		  "LO20" => "Initiation à la programmation pour les STI-STL",
		  "MT25" => "Applications de l'algèbre et de l'analyse à la géométrie",
		  "PM11" => "Accompagnement à la conduite du raisonnement scientifique",
		  "ST20" => "Stage à l'étranger",
		  "SY20" => "Initiation à l'automatique",
		  "TW54" => "Travaux à caractères industriels et d'innovation",
		  "IA52" => "Systèmes à base de connaissances",
		  "RE52" => "Réseaux informatiques : du protocole à l'application",
		  "TW52" => "Travaux à caractères industriels et d'innovation",
		  "PR41" => "Fabrication assistée par ordinateur de 2 à 5 axes",
		  "MN50" => "Introduction à l'optimisation des structures mécaniques",
		  "TW51" => "Travaux à caractères industriels et d'innovation");


foreach ($conasses as $key => $value)
     $req = new update($site->dbrw,
		       'edu_uv',
		       array('intitule_uv' => $value), array('code_uv' => $key));

?>
