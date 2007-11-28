<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
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
 
$topdir = "./";
require_once($topdir. "include/site.inc.php");

$site = new site();
/*HTTP/1.1 200 OK
Content-Type: text/calendar; charset=UTF-8
Cache-Control: no-cache, no-store, max-age=0, must-revalidate
Pragma: no-cache
Expires: Fri, 01 Jan 1990 00:00:00 GMT
Transfer-Encoding: chunked
Date: Wed, 28 Nov 2007 07:46:17 GMT
Server: GFE/1.3*/

header("Content-Type: text/calendar; charset=utf-8");
header("Content-Disposition: filename=ae-events.ics");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "X-WR-CALNAME:AE EVENEMENTS\n";
echo "PRODID:-//AE UTBM//AE2 v1//EN\n";
echo "X-WR-RELCALID:http://ae.utbm.fr/ical.php\n";
echo "X-WR-TIMEZONE:Europe/Paris\n";
echo "CALSCALE:GREGORIAN\n";
echo "METHOD:PUBLISH\n";

echo "BEGIN:VTIMEZONE
TZID:Europe/Paris
X-LIC-LOCATION:Europe/Paris
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100d
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
";


$events = new requete ($site->db,
  "SELECT `nvl_dates`.*,`nvl_nouvelles`.* FROM `nvl_dates` " .
  "INNER JOIN `nvl_nouvelles` on `nvl_nouvelles`.`id_nouvelle`=`nvl_dates`.`id_nouvelle`" .
  "WHERE (`date_fin_eve` >= '" . date("Y-m-d",time()-(60*60*24*30)) ." 00:00:00') AND
  (`nvl_nouvelles`.`modere_nvl` > 0)");

function escape_ical ( $str )
{
  $str=preg_replace('/([\,\\\\])/u','\\\\$1', $str);
  $str=preg_replace('/\n/u','\\n', $str);
  $str=preg_replace('/\r/u','', $str);
  return $str;
}

while ($ev = $events->get_row ())
{
  echo "BEGIN:VEVENT\n";
  echo "UID:http://ae.utbm.fr/news.php?id_nouvelle=".$ev["id_nouvelle"]."&date=".$ev["date_debut_eve"]."\n";
  echo "SUMMARY:".escape_ical($ev['titre_nvl'])."\n";
  echo "DESCRIPTION:".escape_ical($ev['resume_nvl'])."\n";
  
  $st = strtotime($ev['date_debut_eve']);
  $end = strtotime($ev['date_fin_eve']);
  
  if ( $ev["type_nvl"] == 3 || ($end-$st) > (60*60*24) )
  {
    echo "DTSTART;TZID=Europe/Paris;VALUE=DATE:".date("Ymd",$st)."\n";
    echo "DTEND;TZID=Europe/Paris;VALUE=DATE:".date("Ymd",$end)."\n";
  }
  else
  {
    echo "DTSTART;TZID=Europe/Paris:".date("Ymd",$st)."T".date("His",$st)."\n";
    echo "DTEND;TZID=Europe/Paris:".date("Ymd",$end)."T".date("His",$end)."\n";
  }
  
  echo "END:VEVENT\n";
}
			
			
echo "END:VCALENDAR\n";
?>