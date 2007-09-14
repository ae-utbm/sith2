<?php
/** @file
 *
 * @brief Export iCal des emplois du temps.
 *
 */

/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";

include($topdir. "include/site.inc.php");
require_once ($topdir . "include/entities/edt.inc.php");

$db = new mysqlae();
$edt = new edt($db);

$id_user = intval($_REQUEST['idusr']);

isset($_REQUEST['semestre']) ? $semestre = $_REQUEST['semestre'] : $semestre = (date("m") > 6 ? "A" : "P") . date("y");

echo $id . "  " . $semestre."<br/>";

$edt->load($id, $semestre);

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "CALSCALE:GREGORIAN\n";
echo "METHOD:PUBLISH\n";
echo "X-WR-CALNAME:Emploi du temps\n";
echo "X-WR-TIMEZONE:Europe/Paris\n";
echo "BEGIN:VTIMEZONE
TZID:Europe/Paris
X-LIC-LOCATION:Europe/Paris
END:VTIMEZONE\n";

/* strtotime() ne parle qu'anglais ... */
$days = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
$shortdays = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");

foreach ($edt->edt_arr as $seance)
{
  /* Automne : premier [jour de la semaine] trouvé apres le premier septembre */
  if ($semestre[0] == "P")
    {
      $start = date("Ymd", strtotime("next " . $days[$seance['jour_seance']], strtotime(date("Y-09-01"))));
      $until = date("Ymd", strtotime("last " . $days[$seance['jour_seance']], strtotime(date("Y-07-01"))));
    }
  /* printemps, let's say mi février */
  else
    {
      $start = date("Ymd", strtotime("next " . $days[$seance['jour_seance']], strtotime(date("Y-02-15"))));
      $until = date("Ymd", strtotime("last " . $days[$seance['jour_seance']], strtotime(date("Y-01-16"))));
    }
  $start .= "T";
  $end = $start;
  
  $start .= str_replace(":", "", $seance['hr_deb_seance']);
  $end   .= str_replace(":", "", $seance['hr_fin_seance']);
  
  $until .= "T" .str_replace(":", "", $seance['hr_fin_seance']) . "Z";

  switch($seance['semaine_seance'])
    {
    case 'AB':
      $freq = 1;
      break;
    default:
      $freq = 2;
    }
  /* on bourrine sur la sortie standard */
  echo "BEGIN:VEVENT
DTSTART;TZID=Europe/Paris:$start
DTEND;TZID=Europe/Paris:$end
RRULE:FREQ=WEEKLY;UNTIL=$until;INTERVAL=$freq;WKST=MO;BYDAY=".$shortdays[$seance['jour_seance']]."
CLASS:PUBLIC
DESCRIPTION:
LOCATION:".$seance['salle_seance']."
SEQUENCE:1
STATUS:CONFIRMED
SUMMARY:".$seance['nom_uv'] . " " .$seance['type_seance']."
TRANSP:OPAQUE
END:VEVENT\n";
}
echo "END:VCALENDAR\n";

?>