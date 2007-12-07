<?php
/* Copyright 2006
 * - Laurent Colnat
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site();
/*$site->add_css("temp/calendar.css");
$site->add_js("temp/calendar.js");*/

if( isset($_REQUEST['get_cal']) )
{
	$cal = new frm_calendar($site->db);
	echo $cal->html_render();
	exit;
}

$site->start_page("accueil", "Test calendrier");
$site->add_css("temp/calendar.css");
$site->add_js("temp/calendar.js");
$cal = new calendar($site->db);

$cts = new contents("Test calendrier");

$cts->puts("
<script language='javascript'>
function opencal(_ref)
{
	var ref = document.getElementById(_ref);
	var elem = document.getElementById('calendar');
	var pos = findPos(ref);
	
	elem.style.display = 'block';
	elem.style.left = 0;
	elem.style.top = 0;
	openInContents('calendar', './little_calendar2.php', 'get_cal'); 
}

function closecal()
{
	var elem = document.getElementById('calendar');
	elem.style.display = 'none';

	return true;
}
</script>
");


$cts->add_paragraph("Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed placerat, justo in faucibus fringilla, quam tellus faucibus tortor, sit amet viverra risus odio nec justo. Vestibulum condimentum, ante ac vehicula faucibus, tellus est volutpat leo, a congue ipsum justo quis mi. Curabitur facilisis nonummy nunc. Nam non justo eu nibh posuere ornare. Sed ante nisi, congue interdum, feugiat quis, vehicula ut, dui. Praesent elit ipsum, tristique vel, convallis at, sagittis at, enim. Fusce nibh. Praesent semper leo id justo. In eu diam id justo ultricies pretium. Nullam eu eros. Pellentesque neque. Nulla ultrices.");
$cts->add_paragraph("Vestibulum aliquam nonummy odio. Curabitur hendrerit iaculis dui. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Praesent ornare pulvinar enim. Integer ac urna eget felis fermentum sodales. Maecenas lacinia ligula sit amet enim. Nullam vitae lorem. Nunc iaculis orci in felis. Fusce dapibus, elit eu mollis vestibulum, diam pede sodales erat, sit amet tempus est urna in nisl. Cras malesuada odio in felis. Integer volutpat. Vestibulum lacus turpis, consequat vitae, mattis in, mattis id, arcu. Praesent vehicula nisi at dui");
$cts->puts("
<div id=\"calendar\" class=\"container\">&nbsp;</div>
<div id=\"call\" class=\"call\" onclick=\"opencal('call');\"> <img src=\"".$topdir."images/icons/16/ical.png\" /> </div>
");
$cts->add_paragraph("Etiam in sem ac velit condimentum pretium. Aliquam felis elit, ultricies in, semper nec, condimentum et, quam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed varius magna lacinia est. Nam scelerisque magna. Sed in quam at urna vehicula pretium. Nunc sagittis. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas elementum varius odio. Integer quis libero quis libero ornare aliquet. Nunc hendrerit nunc non nibh. Donec ullamcorper augue in quam. Etiam tortor libero, rhoncus vel, fringilla id, hendrerit at, nisl. Cras placerat enim vel ligula. Phasellus vel ligula vitae libero viverra euismod. Nam ut tellus.");
$cts->add_paragraph("Curabitur tincidunt ornare lacus. Nulla mauris risus, pharetra id, luctus fermentum, sagittis viverra, ante. Sed orci purus, lobortis id, varius vel, rhoncus et, orci. Etiam consequat accumsan dolor. Proin molestie nisl non ipsum. In odio. Mauris sit amet magna. Aliquam est ligula, volutpat sit amet, adipiscing id, adipiscing eu, sapien. Nam ornare ligula quis lacus. Quisque nec velit dapibus arcu dapibus aliquet. Nulla mauris. Vivamus erat nunc, sagittis ut, posuere eu, imperdiet vel, libero. Pellentesque venenatis eleifend nunc. Mauris nonummy fringilla orci. Nulla facilisi. Aliquam in eros nec mauris dignissim fringilla. Vivamus luctus. Cras et massa. Praesent tincidunt nulla eu arcu.");

$site->add_contents($cts);

$site->end_page();

?>
