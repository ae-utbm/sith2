<?
require_once("../pedagogie/include/pedagogie.inc.php");

function p($d){
  foreach($d as $r)
		echo implode(" . ", $r)."<br />";
}

$data = array(
  array("LO41", 1, "LO41 bleh", "s"=>"A2005"),      
  array("LO42", 9, "LO42 bleh", "s"=>"A2008"),      
  array("LO43", 4, "LO43 bleh", "s"=>"P2004"),      
  array("LO44", 2, "LO44 bleh", "s"=>"A2002"),      
  array("LO45", 1, "LO45 bleh", "s"=>"P2005"),      
  array("LO46", 5, "LO46 bleh", "s"=>"P2008"),      
  array("LO47", 9, "LO47 bleh", "s"=>"A2008"),      
  array("LO48", 2, "LO48 bleh", "s"=>"P2006"),      
);

$result = $data;

p($data);
echo "<br />  <br />\n";

sort_by_semester($result, "s");

p($result);

?>
