<?
function p($d){
  foreach($d as $r)
		echo implode(" . ", $r)."<br />";
}

function c($n, $m){
  preg_match("/^([AP])([0-9]{4})$/", $n[3], $s1);
  preg_match("/^([AP])([0-9]{4})$/", $m[3], $s2);

print_r($s1);
print_r($s2);
/*
  $s1= $n[3][0];
  $y1= intval(array_slice($n[3], 1));
  $s2= $m[3][0];
  $y2= intval(array_slice($m[3], 1));

echo $n, $s1, $y1, $m, $s2, $y2, "\n";
*/
  if($n[1] < $m[1])
		return -1;
  else
    return 1;
}


$data = array(
  array("LO41", 1, "LO41 bleh", "A2005"),      
  array("LO42", 9, "LO42 bleh", "A2008"),      
  array("LO43", 4, "LO43 bleh", "P2004"),      
  array("LO44", 2, "LO44 bleh", "A2002"),      
  array("LO45", 1, "LO45 bleh", "P2005"),      
  array("LO46", 5, "LO46 bleh", "P2008"),      
  array("LO47", 9, "LO47 bleh", "A2008"),      
  array("LO48", 2, "LO48 bleh", "P2006"),      
);

$result = $data;

p($data);
echo "<br />  <br />\n";

usort($result, 'c');

p($result);

?>
