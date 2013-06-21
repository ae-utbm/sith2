<?php
/* Copyright 2006
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

/**
 * @file
 */
require_once($topdir."include/cts/standart.inc.php");

/**
 * Affiche un planning hebdomadaire
 *
 * @author Julien Etelain
 * @ingroup display_cts
 */
class planningv extends stdcontents
{

    var $planning;
    var $week_start;


    function make_mono($body,$used_names)
    {
	$buffer = "<table class=\"pl2_mono\">\n<tr>\n";
        $buffer .= "<th class=\"pl2_gap_name\"></th>";
	foreach($used_names as $name)
	{
		$buffer .= "<th class=\"pl2_gap_name\">$name</th>\n";
	}
	$buffer .= "</tr>";
	$buffer .= $body;
	$buffer .= "</table>";
	return $buffer;
    }
 
    function make_multi($body,$days)
    {
	$buffer = "<table class=\"pl2_multi\">\n<tr>\n";
	foreach($days as $day)
	{
		$buffer .= "<th class=\"pl2_day_name\">".strftime("%A %d/%m",strtotime($day)+(($planning->weekly)?($this->week_start):0))."</th>";
	}
	$buffer .= "</tr>\n<tr><td class=\"pl2_multi\">";
	$buffer .= $body;
	$buffer .= "</td></tr></table>";
	
	return $buffer;
	
    }

    /**
     * Génére un planning hebdomadaire
     * @param $titre Titre du contenu
     * @param $db Connection à la base de donnée
     */
    function planningv ( $titre, $db, $id_planning, $start, $end, $site, $force_single_column = false, $show_admin = false)
    {
	setlocale(LC_ALL, "fr_FR.UTF8");
        $this->title=false;

	$planning = new planning2($db, $db);
	$planning->load_by_id($id_planning);
	
	if(!$site->user->is_in_group_id($planning->group) && !$site->user->is_in_group_id($planning->admin_group) 
		&& !$planning->is_public && !$site->user->is_in_group("gestion_ae"))
	{
		$this->buffer .= "<p>Droits insuffisants pour lire ce planning</p>";
		return;
	}

	$gaps = $planning->get_gaps($start, $end);

	$gaps_data = array();
	while( list( $gap_id, $gap_start, $gap_end, $gap_name, $gap_count) = $gaps->get_row())
	{
		$gap_data = array();
		$users = $planning->get_users_for_gap($gap_id,$start);
		while( list( $utl, $nom_utl, $user_gap_id ) = $users->get_row() ){
			$gap_data[] = array( 0 => $utl, 1 => $nom_utl, 2=> $user_gap_id);
		}
		$gaps_data[$gap_id] = $gap_data;
	}

	$gaps_time = $planning->get_gaps_time($start, $end);

	$this->week_start = $planning->get_week_start($start);
	list( $start ) = $gaps_time->get_row();
	$end = $start;
	while( list($tmp) = $gaps_time->get_row() )
		$end = $tmp;
	if($planning->weekly)
	{
		$start = strtotime($start) + $this->week_start;
		$end = strtotime($end) + $this->week_start;
	}
	else
	{
		$start = strtotime($start);
		$end = strtotime($end);
	}

	$is_multi_day = true;
	if( date("Y m d",$start) === date("Y m d",$end) || $force_single_column)
		$is_multi_day = false;

	$gaps_names = $planning->get_gaps_names();
	$names = array();
	while( list( $name ) = $gaps_names->get_row() )
	{
		$names[] = $name;
	}
	$gaps_time->go_first();
	$days = array();
	$current_day = null;
	while(list( $time ) = $gaps_time->get_row())
	{
		if($current_day == null)
			$current_day = gmdate("Y-m-d 00:00:00",$time);
		while($current_day != gmdate("Y-m-d",strtotime($time)))
		{
			$days[$current_day][] = $gmdate("Y-m-d 23:59:59",strtotime($current_day));
			$current_day = gmdate("Y-m-d 00:00:00",strtotime($current_day)+86400);
			$days[$current_day][] = $gmdate("Y-m-d 00:00:00",strtotime($current_day));
		}

		if(!in_array($time,$days[$current_day],true))
		{
			$days[$current_day][] = $time;
		}
	}
	foreach($days as $day)
	{
		$day_buffer = "";
		$last_time = null;
		$used_names = array();
		foreach($day as $time)
		{
			list( $current_day ) = $day;
			$current_day_start = strtotime(date("Y-m-d 00:00:00",strtotime($current_day)));
			$current_day_end = strtotime(date("Y-m-d 23:59:59",strtotime($current_day)));
			foreach($names as $name)
                	{
                        	$gaps->go_first();
	                        while( list( $gap_id, $gap_start, $gap_end, $gap_name, $gap_count) = $gaps->get_row())
				{
					$gap_start = strtotime($gap_start);
					$gap_end = strtotime($gap_end);
        	                        if($gap_name === $name 
						&& ( ($gap_start >= $current_day_start && $gap_start <= $current_day_end)
						||  ($gap_end >= $current_day_start && $gap_end <= $current_day_end) 
						|| ($gap_start <= $current_day_start && $gap_end >= $current_day_end) ))
                	                        if(!in_array($name,$used_names,true))
                        	                        $used_names[] = $name;
				}
                	}
			$line_buffer = "";
			if($last_time == null)
			{
				$last_time = $time;
				continue;
			}
			$line_buffer .= "<tr>\n";
			$line_buffer .= "<td>".date("H:i", $last_time)." - ".date("H:i", $time)."</td>";
			$line_buffer .= "<td>";	
			
			
			$line_buffer .= "</td>";
			$line_buffer .= "</tr>\n";
		}
	}

	
	if($is_multi_day)
	{
		$this->buffer .= $this->make_multi($buffer_mono,$days);
	}
	else
		$this->buffer .= $buffer_mono;

    }


}



?>
