<?
function p($d){
  foreach($d as $r)
		echo implode(" . ", $r)."<br />";
}

function c($n, $m){
  preg_match("/^([AP])([0-9]{4})$/", $n[3], $s1);
  preg_match("/^([AP])([0-9]{4})$/", $m[3], $s2);

	/* comparaisons sur les annees, puis si egalite sur les A/P */
  if($s1[2] < $s2[2])
		return -1;
  else if ($s1[2] > $s2[2])
    return 1;
	else{
    /* si < : A pour 1 et P pour 2 */
    if($s1[1] < $s2[1])
		  return 1;
	  else 
		  return -1;
  }
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
