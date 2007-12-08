<?php

/** @file
 *
 * @brief Classe de gestion du calendrier.
 *
 */

/* Copyright 2004
 * - Maxime Petazzoni <maxime POINT petazzoni CHEZ bulix POINT org>
 * - Alexandre Belloni <alexandre POINT belloni CHEZ utbm POINT fr>
 * - Thomas Petazzoni <thomas POINT petazzoni CHEZ enix POINT org>
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

require_once($topdir."include/entities/news.inc.php");

/** Classe d'affichage du calendrier */
class calendar extends stdcontents
{
	var $db, $date, $events;
	
	var $weekdays = array ("Lu", "Ma", "Me", "Je", "Ve", "Sa", "Di");
	
	var $months = array ("Janvier", "Février", "Mars", "Avril",
	"Mai", "Juin", "Juillet", "Août", "Septembre",
	"Octobre", "Novembre", "Décembre");
	
	var $id_asso;
	
	
	/** Constructeur de la classe
	*/
	function calendar (&$db,$id_asso=null)
	{
		$this->db = $db;
		
		/* Si les paramètres temporels sont donnés, on les utilise */
		if ($_GET['caldate'] != "")
			$this->date = strtotime($_GET['caldate']);
		
		/* Sinon, on prend le timestamp courant */
		else
			$this->date = time();
		
		$this->events = "";
		$this->title = "Calendrier";
		$this->id_asso = $id_asso;
		
		
	}
	
	/** Affichage du calendrier
	*/
	function html_render ()
	{
		global $topdir,$wwwtopdir;
		
		/* On extrait le jour, le mois, l'année et le nombre du jours du
		* mois courant a partir du timestamp */
		$day = date("j", $this->date);
		$month = date("n", $this->date);
		$year = date("Y", $this->date);
		$days = date("t", $this->date);
		
		//
		if ( $topdir == $wwwtopdir )
		  $this->buffer = "<p class=\"ical\"><a href=\"".$wwwtopdir."article.php?name=ical\"><img src=\"".$wwwtopdir."images/icons/16/ical.png\" alt=\"iCalendar\" /></a></p>";
		else
		  $this->buffer = "";
		$this->buffer .= "<div class=\"calendarhead\">\n";
		$this->buffer .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\">\n";
		
		$prevmonth = $month - 1;
		$nextmonth = $month + 1;
		
		if ($prevmonth < 10)
			$prevmonth = "0" . $prevmonth;
			
		if ($nextmonth < 10)
			$nextmonth = "0" . $nextmonth;
		
		$prevdate = $year . "-" . $prevmonth . "-" . $day;
		$nextdate = $year . "-" . $nextmonth . "-" . $day;
		
		if ($month == 1)
			$prevdate = $year-1 . "-" . "12" . "-" . $day;
			
		if ($month == 12)
			$nextdate = $year+1 . "-" . "1"  . "-" . $day;
		
		$this->buffer .= "<tr>\n";
		$this->buffer .= "<td class=\"month\"><a href=\"?caldate=$prevdate\" onclick=\"return !openInContents('sbox_body_calendrier','".$wwwtopdir."gateway.php','class=calendar&amp;caldate=$prevdate&amp;topdir=$wwwtopdir');\">&laquo;</a></td>\n";
		$this->buffer .= "<td class=\"month\" colspan=\"5\">" . $this->months[$month-1] . " " . $year . "</td>\n";
		$this->buffer .= "<td class=\"month\"><a href=\"?caldate=$nextdate\" onclick=\"return !openInContents('sbox_body_calendrier','".$wwwtopdir."gateway.php','class=calendar&amp;caldate=$nextdate&amp;topdir=$wwwtopdir');\">&raquo;</a></td>\n";
		$this->buffer .= "</tr>\n";
		
		/* Affichage des jours de la semaine */
		$this->buffer .= "<tr>";
		foreach ($this->weekdays as $day)
			$this->buffer .= "<td class=\"weekday\">$day</td>";
		$this->buffer .= "</tr>\n";
		
		$this->buffer .= "</table>\n";
		$this->buffer .= "</div>\n";
		
		/* Partie principale du calendrier : les jours du mois */
		$this->buffer .= "<div class=\"calendar\">\n";
		$this->buffer .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\">\n";
		
		/* On cherche le premier jour du mois dans la semaine */
		$first = date("w", mktime(0, 0, 0, $month, 1, $year)) - 1;
		
		if($first < 0)
		$first += 7;
		
		$current_day = 0;
		
		/* La première semaine */
		$this->buffer .= "<tr>";
		for ($i=0 ; $i<$first ; $i++)
			$this->buffer .= "<td class=\"day\"></td>";
		for ($i=0 ; $i< 7-$first ; $i++)
			$this->day ($year, $month, ++$current_day);
		$this->buffer .= "</tr>\n";
		
		/* Les autres jours du mois */
		while (($days - $current_day > 0) &&
		($days - $current_day > 6))
		{
			$this->buffer .= "<tr>";
			for ($i=0 ; $i < 7 ; $i++)
				$this->day ($year, $month, ++$current_day);
			$this->buffer .= "</tr>\n";
		}
		
		/* last week */
		$this->buffer .= "<tr>";
		
		for ($i=$current_day ; $i < $days ; $i++)
			$this->day ($year, $month, ++$current_day);
			
		for ($j=0 ; $j < 7-$i ; $j++)
			$this->buffer .= "<td class=\"day\"></td>";
			
		$this->buffer .= "</tr>\n";
		$this->buffer .= "</table>\n";
		
		/* On affiche les evenements */
		$this->buffer .= $this->events;
		$this->buffer .= "</div>\n";
		
		return $this->buffer;
	}
	
	/** Affichage d'un jour.
	*
	* @param year L'année
	* @param month Le mois
	* @param day Le jour
	*/
	function day ($year, $month, $day)
	{
		global $topdir,$wwwtopdir;
		
		
		
		
		$style = "day";
		
		/* on construit une date mysql */
		$date = $this->sql_date(mktime(0, 0, 0, $month, $day, $year));
		
		/* le jour suivant */
		$date2 = $this->sql_date(mktime(0, 0, 0, $month, $day + 1, $year));
		
		
		$sql = "SELECT `nvl_dates`.*,`nvl_nouvelles`.* FROM `nvl_dates` " .
			"INNER JOIN `nvl_nouvelles` on `nvl_nouvelles`.`id_nouvelle`=`nvl_dates`.`id_nouvelle`" .
			"WHERE  modere_nvl='1' ".
			"AND `nvl_dates`.`date_debut_eve` <= '" . mysql_escape_string($date2) ." 05:59:59' " .
			"AND `nvl_dates`.`date_fin_eve` >= '" . mysql_escape_string($date) ." 06:00:00' ";
		
		if ( is_null($this->id_asso) )
		  $sql .= "AND id_canal='".NEWS_CANAL_SITE."' ";
		else
		  $sql .= "AND id_asso='".mysql_real_escape_string($this->id_asso)."' ";

    $event = new requete($this->db,$sql);
		
		/* Si oui, on change le style de la case, et on ajoute l'évenement */
		if ($event->lines > 0)
		{
			$idx=3;
			
			while ($ev = $event->get_row ())
			{
				if ( $ev["type_nvl"] == 1 )
					$idx = 1;
				elseif ( $ev["type_nvl"] == 2 && $idx == 3 )
					$idx = 2;
			}
			
			$style .= " event$idx";		  
		  
		  if ( $idx != 3 )
		  {
		    $event->go_first();
		    
  			$this->events .= "<dl class=\"event\" id=\"calev-$date\">\n";
  			while ($ev = $event->get_row ())
  			{
  				$this->event_add ($ev,$date);
  				$js = " onmouseover=\"show_obj_top('calev-$date'); \"";
  				$js .= " onmouseout=\"hide_obj('calev-$date');\"";
  			}
  			$this->events .= "</dl>\n";
		  }
		}
		
		/* Si le jour demandé est aujourd'hui, on active la case */
		if ($date == $this->sql_date (time()))
		$style .= " active";
		
		/* On affiche la case */ 
		if($event->lines > 0)
		{
			$this->buffer .= "<td class=\"$style\"$js><a href=\"" . $wwwtopdir . "events.php?day=" . $date . "\">" . $day . "</a></td>";
		}
		else
		{
			$this->buffer .= "<td class=\"$style\"$js>" . $day . "</td>";
		}
	}
	
	function event_add ($ev,$date)
	{
		$start = split(" ", $ev['date_debut_eve']);
		$dstart = $start[0];
		$start = split(":", $start[1]);
		$end = split(" ", $ev['date_fin_eve']);
		$dend = $end[0];
		
		$end = split(":", $end[1]);
		
		if ( $ev["type_nvl"] == 1 )
		  $idx = 1;
		elseif ( $ev["type_nvl"] == 2 )
			$idx = 2;		
		else
		  return;
		  
		$this->events .= " <dt class=\"e$idx\">" . htmlentities($ev['titre_nvl'], ENT_QUOTES, "UTF-8") . "</dt>\n";
		$this->events .= " <dd class=\"e$idx\">";
		
		if ( $dstart == $date && $dend == $date )
			$this->events .= "De ".$start[0] . ":" . $start[1] . " à " . $end[0] . ":" . $end[1];
		else if ( $dstart == $date )
			$this->events .= "A partir de ".$start[0] . ":" . $start[1];
		else if ( $dend == $date )
			$this->events .= "Jusqu'à ".$end[0] . ":" . $end[1];
		
		$this->events .= "</dd>\n";
	}
	
	/** Créé une date de type SQL à partir d'un timestamp
	*
	* @param time Un timestamp
	*
	* @return La date au format SQL YYYY-MM-DD
	*/
	function sql_date ($time)
	{
		return strftime("%Y-%m-%d", $time);
	}
}

class frm_calendar extends calendar
{
	function html_render()
	{

		global $topdir,$wwwtopdir;
		
		/* On extrait le jour, le mois, l'année et le nombre du jours du
		* mois courant a partir du timestamp */
		$day = date("j", $this->date);
		$month = date("n", $this->date);
		$year = date("Y", $this->date);
		$days = date("t", $this->date);
		
	  $this->buffer = "<div class=\"closecal\" onclick=\"closecal();\" >X</div>";
		$this->buffer .= "<div class=\"calendarhead\">\n";
		$this->buffer .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\">\n";
		
		$prevmonth = $month - 1;
		$nextmonth = $month + 1;
		
		if ($prevmonth < 10)
			$prevmonth = "0" . $prevmonth;
			
		if ($nextmonth < 10)
			$nextmonth = "0" . $nextmonth;
		
		$prevdate = $year . "-" . $prevmonth . "-" . $day;
		$nextdate = $year . "-" . $nextmonth . "-" . $day;
		
		if ($month == 1)
			$prevdate = $year-1 . "-" . "12" . "-" . $day;
			
		if ($month == 12)
			$nextdate = $year+1 . "-" . "1"  . "-" . $day;
		
		$this->buffer .= "<tr>\n";
		$this->buffer .= "<td class=\"month\"><a href=\"?caldate=$prevdate\" onclick=\"return !openInContents('calendar','".$wwwtopdir."temp/little_calendar2.php','get_cal&amp;caldate=$prevdate');\">&laquo;</a></td>\n";
		$this->buffer .= "<td class=\"month\" colspan=\"5\">" . $this->months[$month-1] . " " . $year . "</td>\n";
		$this->buffer .= "<td class=\"month\"><a href=\"?caldate=$nextdate\" onclick=\"return !openInContents('calendar','".$wwwtopdir."temp/little_calendar2.php','get_cal&amp;caldate=$nextdate');\">&raquo;</a></td>\n";
		$this->buffer .= "</tr>\n";
		
		/* Affichage des jours de la semaine */
		$this->buffer .= "<tr>";
		foreach ($this->weekdays as $day)
			$this->buffer .= "<td class=\"weekday\">$day</td>";
		$this->buffer .= "</tr>\n";
		
		$this->buffer .= "</table>\n";
		$this->buffer .= "</div>\n";
		
		/* Partie principale du calendrier : les jours du mois */
		$this->buffer .= "<div class=\"calendar\">\n";
		$this->buffer .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\">\n";
		
		/* On cherche le premier jour du mois dans la semaine */
		$first = date("w", mktime(0, 0, 0, $month, 1, $year)) - 1;
		
		if($first < 0)
		$first += 7;
		
		$current_day = 0;
		
		/* La première semaine */
		$this->buffer .= "<tr>";
		for ($i=0 ; $i<$first ; $i++)
			$this->buffer .= "<td class=\"day\"></td>";
		for ($i=0 ; $i< 7-$first ; $i++)
			$this->day ($year, $month, ++$current_day);
		$this->buffer .= "</tr>\n";
		
		/* Les autres jours du mois */
		while (($days - $current_day > 0) &&
		($days - $current_day > 6))
		{
			$this->buffer .= "<tr>";
			for ($i=0 ; $i < 7 ; $i++)
				$this->day ($year, $month, ++$current_day);
			$this->buffer .= "</tr>\n";
		}
		
		/* last week */
		$this->buffer .= "<tr>";
		
		for ($i=$current_day ; $i < $days ; $i++)
			$this->day ($year, $month, ++$current_day);
			
		for ($j=0 ; $j < 7-$i ; $j++)
			$this->buffer .= "<td class=\"day\"></td>";
			
		$this->buffer .= "</tr>\n";
		$this->buffer .= "</table>\n";
		
		/* On affiche les evenements */
		$this->buffer .= $this->events;
		$this->buffer .= "</div>\n";
		
		return $this->buffer;
	}
	
function day ($year, $month, $day)
	{
		global $topdir,$wwwtopdir;
		
			
		$style = "day";
		
		/* on construit une date mysql */
		$date = $this->sql_date(mktime(0, 0, 0, $month, $day, $year));
		
		/* le jour suivant */
		$date2 = $this->sql_date(mktime(0, 0, 0, $month, $day + 1, $year));
		
	
		/* Si le jour demandé est aujourd'hui, on active la case */
		if ($date == $this->sql_date (time()))
		$style .= " active";

		$js = "onclick=\"return_val('input_1', $day + '/' + $month + '/' + $year + ' 20:00');\"";
		
		$this->buffer .= "<td class=\"$style\" style=\"cursor: pointer;\" $js >" . $day . "</td>";
	}
	
}

?>
