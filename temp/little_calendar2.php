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
	$cal = new calendar($site->db);
	echo $cal->html_render();
	exit;
}


$cal = new calendar($site->db);

$cts = new contents();
$cts->puts("<style>
.call
{
	position: absolute;
	align: center;
	top: 50px;
	cursor:pointer; 
	text-align: center;
	z-index: 500;
}

.closecal
{
	position: absolute;
	top: 0px;
	right: 0px;
	cursor: pointer;
}

.container
{
	position: absolute;
	background: #eeeeee;
	border: 1px solid blue;
	display: none;
}
</style>");

$cts->puts("
<script language='javascript'>
function opencal(_ref)
{
	var ref = document.getElementById(_ref);
	var elem = document.getElementById('calendar');
	var pos = findPos(ref);
	
	elem.style.display = 'block';
	elem.style.left = pos[0] + 20;
	elem.style.top = pos[1];
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

$cts->puts("
<div id=\"calendar\" class=\"container\">&nbsp;</div>
<div id=\"call\" class=\"call\" onclick=\"opencal('call'); openInContents('calendar', './little_calendar2.php', 'get_cal');\"> <img src=\"".$topdir."images/icons/16/ical.png\" /> </div>
");


$site->add_contents($cts);

$site->popup_end_page();

?>
