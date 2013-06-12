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
    function planningv ( $titre, $db, $id_planning, $start, $end)
    {
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
	$gaps_names = $planning->get_gaps_names();
	$this->buffer .= "<table>\n<tr>\n";
	$names = array();
	$this->buffer .= "<th>$planning->name</th>";
	while( list( $name ) = $gaps_names->get_row() )
	{
		$names[] = $name;
		$this->buffer .= "<th>$name</th>\n";
	}
	$this->buffer .= "</tr>\n";
	list( $last_time ) = $gaps_time->get_row();
	while( list( $time ) = $gaps_time->get_row())
	{
		$this->buffer .= "<tr>\n<td>".date("H:i",strtotime($last_time))."-".date("H:i",strtotime($time))."</td>";
		foreach($names as $name)
		{
			$this->buffer .= "<td>";
			$gaps->go_first();
			$has_gap = false;
			$count = 0;
			while( list( $gap_id, $gap_start, $gap_end, $gap_name, $gap_count) = $gaps->get_row())
			{
				if($gap_name === $name && $gap_start <= $last_time && $gap_end >= $time)
				{
					$has_gap = true;
					foreach(  $gaps_data[$gap_id] as $gap_data)
					{
						$count++;
						$this->buffer .= ($count==1?"":", ").$gap_data[1];
					}
					if($count < $gap_count)
					{
						$this->buffer .= " ".($gap_count - $count)." personne(s) manquante(s)";
					}
				}
			}
			if(!$has_gap)
				$this->buffer .= "N/A";
			$this->buffer .= "</td>";
		}
		
		$this->buffer .= "</tr>\n";
		$last_time = $time;
	}
	

        $this->buffer .= "\n</table>\n";

    }


}



?>
