<?php
$topdir = "../";
include($topdir."include/graph.inc.php");

$color=array('9B29B0',
             'AF54C0',
             'C37FD0',
             'D7A9DF',
             'EBD4EF',
             '3D6795',
             '6485AA',
             '8BA4BF',
             'B1C2D5',
             'D8E1EA',
             '1C9980',
             '49AD99',
             '77C2B3',
             'A4D6CC',
             'D2EBE6');

$valeur=array('50',
              '30',
              '14',
              '47',
              '82',
              '74',
              '44',
              '10',
              '25',
              '65',
							'35');

$cam=new camembert(500,500, array(0 => '#ffffff'));

for ($i=0; $i<count($valeur); $i++)
  $cam->data($valeur[$i],$color[$i], '');

$cam->png_render();

?>
