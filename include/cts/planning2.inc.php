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

/**
 * Affiche un planning hebdomadaire
 *
 * @author Julien Etelain
 * @ingroup display_cts
 */
class planningv extends stdcontents
{

    var $planning;
    /**
     * Génére un planning hebdomadaire
     * @param $titre Titre du contenu
     * @param $db Connection à la base de donnée
     */
    function planningv ( $titre, $db, $id_planning, $start, $end, $force_single_column = false)
    {
	setlocale(LC_ALL, 'fr_FR');
        $this->title=false;

	$planning = new planning2($db, $db);
	$planning->load_by_id($id_planning);

	$gaps = $planning->get_gaps($start, $end);

	while( list( $gap_id, $gap_start, $gap_end, $gap_name, $gap_count) = $gaps->get_row())
	{
		$users = $planning->get_users_for_gap($gap_id,$start);
		while( list( $utl, $nom_utl ) = $users->get_row() ){
			$gaps_data[$gap_id][$utl][0] = $utl;
			$gaps_data[$gap_id][$utl][1] = $nom_utl;
		}
	}

	$gaps_time = $planning->get_gaps_time($start, $end);

	$week_start = $planning->get_week_start($start);
	list( $start ) = $gaps_time->get_row();
	$end = $start;
	while( list($tmp) = $gaps_time->get_row() )
		$end = $tmp;
	$start = strtotime($start) + $week_start;
	$end = strtotime($end) + $week_start;

	$is_multi_day = true;
	if( date("Y m d",$start) === date("Y m d",$end) || $force_single_column)
		$is_multi_day = false;

	$gaps_names = $planning->get_gaps_names();
	$names = array();
	if($is_multi_day)
	{
		$this->buffer .= "<table class=\"pl2_multi\">\n<tr>\n";
		while( $start < $end )
		{
			$this->buffer .= "<th class=\"pl2_day_name\">".date("l d/m",$start)."</th>";
			$start += 24*3600;
		}
		$this->buffer .= "</tr><tr><td><table class=\"pl2_mono\"><tr><th></th>";
	}
	else
	{
		$this->buffer .= "<table class=\"pl2_mono\">\n<tr>\n";
		$this->buffer .= "<th></th>";
	}
	while( list( $name ) = $gaps_names->get_row() )
	{
		$names[] = $name;
		$this->buffer .= "<th class=\"pl2_gap_name\">$name</th>\n";
	}
	$this->buffer .= "</tr>\n";
	$gaps_time->go_first();
	list( $last_time ) = $gaps_time->get_row();
	while( list( $time ) = $gaps_time->get_row())
	{
	changement:
		$back_time = $time;
		if(!( date("Y m d",strtotime($time)) === date("Y m d",strtotime($last_time)) || $force_single_column))
		{
			$time = date("Y-m-d 23:59:59",strtotime($last_time));
		}
		$this->buffer .= "<tr>\n<td class=\"pl2_horaires\">".date("H:i",strtotime($last_time))."-".date("H:i",strtotime($time))."</td>";
		foreach($names as $name)
		{
			$buffer = "";
			$gaps->go_first();
			$has_gap = false;
			$total_gap = 0;
			$count = 0;
			while( list( $gap_id, $gap_start, $gap_end, $gap_name, $gap_count) = $gaps->get_row())
			{
				if($gap_name === $name && $gap_start <= $last_time && $gap_end >= $time)
				{
					$has_gap = true;
					$total_gap += $gap_count;
					foreach(  $gaps_data[$gap_id] as $gap_data)
					{
						$count++;
						$buffer .= ($count==1?"":", ").$gap_data[1];
					}
				}
			}
			if($count < $total_gap)
			{
				$buffer .= ($count?" et ":"").($total_gap - $count)." personne".(($total_gap - $count)>=2?"s":"");
			}
			
			if($has_gap)
			{
				if($count < $total_gap)
					$this->buffer .= "<td class=\"pl2_gap_partial\">".$buffer."</td>";
				else
					$this->buffer .= "<td class=\"pl2_gap_full\">".$buffer."</td>";
			}
			else
				$this->buffer .= "<td class=\"pl2_no_gap\"></td>";
		}
		
		$this->buffer .= "</tr>\n";
		if(!( date("Y m d",strtotime($back_time)) === date("Y m d",strtotime($last_time)) || $force_single_column))
		{
			$this->buffer .= "</table></td><td><table class=\"pl2_mono\"><tr><th></th>";
			foreach($names as $name)
				$this->buffer .= "<th>$name</th>\n";
			$this->buffer .= "</tr>";
			$time = $back_time;
			$last_time = date("Y-m-d 00:00:00",strtotime($time));
			goto changement;
		}
		else
			$last_time = $time;
	}
	
	if($is_multi_day)
	{
		$this->buffer .= "</table></td></tr>";
	}

        $this->buffer .= "\n</table>\n";

    }


}



?>