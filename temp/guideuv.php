<?
include("../include/extdb/xml.inc.php");

$cts = file_get_contents("http://webct.utbm.fr/wct_files/Referentiel/GuideUv.xml");

$parsed = new u007xml($cts);


// GI

echo "<h1>GI</h1>";

$table = &$parsed->arrOutput[0]['childrens'][6]['childrens'][3]['childrens'];

$i = 0;
foreach ($table as $array)
{
  if ($array['nodename'] != 'TABLE')
    continue;

  $i++;

  if ($i < 3)
    continue;

  //  print_r($array);
  
  /* 1ere ligne tableau */
  $details_uv1 = &$array['childrens'][0]['childrens'];
  
  $code_uv  = $details_uv1[0]['nodevalue'];
  $descr_uv = $details_uv1[1]['nodevalue'] . $details_uv1[2]['nodevalue'];

  /* 2eme ligne tableau */
  $details_uv2 = &$array['childrens'][1]['childrens'];
  
  $nb_heures_uv = $details_uv2[0]['nodevalue'];
  $AP_creds = $details_uv2[1]['nodevalue'];

  /* 3eme ligne tableau */
  $details_uv3 = &$array['childrens'][2]['childrens'];

  $objs_uv = $details_uv3[0]['nodevalue'];
  $prog_uv = $details_uv3[1]['nodevalue'] .". ". $details_uv3[2]['nodevalue'];

  
  echo "<pre>";
  echo $code_uv . "\n";
  echo $descr_uv . "\n";
  echo $nb_heures_uv . "\n";
  echo $AP_creds . "\n";
  echo $objs_uv . "\n";
  echo $prog_uv . "\n";
  echo "\n</pre>\n";
  
}



// GESC
echo "<h1>GESC</h1>";

$table = &$parsed->arrOutput[0]['childrens'][5]['childrens'];
$nbtablequiserventarien = 4;


$i = 0;

foreach ($table as $array)
{
  if ($array['nodename'] != 'TABLE')
    continue;

  $i++;

  if ($i < $nbtablequiserventarien)
    continue;

  //  print_r($array);
  
  $details_uv1 = &$array['childrens'][0]['childrens'];
  
  $code_uv  = $details_uv1[0]['nodevalue'];
  $descr_uv = $details_uv1[1]['nodevalue'] . $details_uv1[2]['nodevalue'];

  $details_uv2 = &$array['childrens'][1]['childrens'];
  
  $nb_heures_uv = $details_uv2[0]['nodevalue'];
  $AP_creds = $details_uv2[1]['nodevalue'];

  $details_uv3 = &$array['childrens'][2]['childrens'];

  $objs_uv = $details_uv3[0]['nodevalue'];
  $prog_uv = $details_uv3[1]['nodevalue'] .". ". $details_uv3[2]['nodevalue'];


  
  echo "<pre>".$code_uv . "\n";
  echo $descr_uv . "\n";
  echo $nb_heures_uv . "\n";
  echo $AP_creds . "\n";
  echo $objs_uv . "\n";
  echo $prog_uv . "\n";
  echo "\n</pre>";
  

}

// TC
echo "<h1>TC</h1>";

$table = &$parsed->arrOutput[0]['childrens'][3]['childrens'][1]['childrens'];
$nbtablequiserventarien = 5;


$i = 0;

foreach ($table as $array)
{
  if ($array['nodename'] != 'TABLE')
    continue;

  $i++;

  if ($i < $nbtablequiserventarien)
    continue;

  //  print_r($array);
  
  $details_uv1 = &$array['childrens'][0]['childrens'];
  
  $code_uv  = $details_uv1[0]['nodevalue'];
  $descr_uv = $details_uv1[1]['nodevalue'] . $details_uv1[2]['nodevalue'];

  $details_uv2 = &$array['childrens'][1]['childrens'];
  
  $nb_heures_uv = $details_uv2[0]['nodevalue'];
  $AP_creds = $details_uv2[1]['nodevalue'];

  $details_uv3 = &$array['childrens'][2]['childrens'];

  $objs_uv = $details_uv3[0]['nodevalue'];
  $prog_uv = $details_uv3[1]['nodevalue'] .". ". $details_uv3[2]['nodevalue'];


    
  echo "<pre>".$code_uv . "\n";
  echo $descr_uv . "\n";
  echo $nb_heures_uv . "\n";
  echo $AP_creds . "\n";
  echo $objs_uv . "\n";
  echo $prog_uv . "\n";
  echo "\n</pre>";
  

}


// Humas
echo "<h1>Humanités</h1>";

$table = &$parsed->arrOutput[0]['childrens'][2]['childrens'][5]['childrens'];
$nbtablequiserventarien = 5;


$i = 0;

foreach ($table as $array)
{
  if ($array['nodename'] != 'TABLE')
    continue;

  $i++;

  if ($i < $nbtablequiserventarien)
    continue;

  //  print_r($array);
  
  $details_uv1 = &$array['childrens'][0]['childrens'];
  
  $code_uv  = $details_uv1[0]['nodevalue'];
  $descr_uv = $details_uv1[1]['nodevalue'] . $details_uv1[2]['nodevalue'];

  $details_uv2 = &$array['childrens'][1]['childrens'];
  
  $nb_heures_uv = $details_uv2[0]['nodevalue'];
  $AP_creds = $details_uv2[1]['nodevalue'];

  $details_uv3 = &$array['childrens'][2]['childrens'];

  $objs_uv = $details_uv3[0]['nodevalue'];
  $prog_uv = $details_uv3[1]['nodevalue'] .". ". $details_uv3[2]['nodevalue'];


    
  echo "<pre>".$code_uv . "\n";
  echo $descr_uv . "\n";
  echo $nb_heures_uv . "\n";
  echo $AP_creds . "\n";
  echo $objs_uv . "\n";
  echo $prog_uv . "\n";
  echo "\n</pre>";
  
}

// GMC
echo "<h1>GMC</h1>";

$table = &$parsed->arrOutput[0]['childrens'][7]['childrens'][1]['childrens'];
$nbtablequiserventarien = 6;


$i = 0;

foreach ($table as $array)
{
  if ($array['nodename'] != 'TABLE')
    continue;

  $i++;

  if ($i < $nbtablequiserventarien)
    continue;

  //  print_r($array);
  
  $details_uv1 = &$array['childrens'][0]['childrens'];
  
  $code_uv  = $details_uv1[0]['nodevalue'];
  $descr_uv = $details_uv1[1]['nodevalue'] . $details_uv1[2]['nodevalue'];

  $details_uv2 = &$array['childrens'][1]['childrens'];
  
  $nb_heures_uv = $details_uv2[0]['nodevalue'];
  $AP_creds = $details_uv2[1]['nodevalue'];

  $details_uv3 = &$array['childrens'][2]['childrens'];

  $objs_uv = $details_uv3[0]['nodevalue'];
  $prog_uv = $details_uv3[1]['nodevalue'] .". ". $details_uv3[2]['nodevalue'];


    
  echo "<pre>".$code_uv . "\n";
  echo $descr_uv . "\n";
  echo $nb_heures_uv . "\n";
  echo $AP_creds . "\n";
  echo $objs_uv . "\n";
  echo $prog_uv . "\n";
  echo "\n</pre>";
  
}

// IMAP
echo "<h1>IMAP</h1>";

echo "Les imaps, ca merde en xml ...\n";

  



?>