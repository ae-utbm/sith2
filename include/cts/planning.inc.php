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
 * Conteneur d'un planning hebdomadaire
 */ 
class weekplanning extends stdcontents
{
	var $get_page;
	
	/**
	 * Génére un planning hebdomadaire
	 * @param $titre Titre du contenu
	 * @param $db Connection à la base de donnée
	 * @param $sql Requete de selection SQL (SELECT ... FROM ... WHERE ....) (finir par WHERE 1 s'il n'y aucune condition)
	 * @param $idf Champ SQL d'identification
	 * @param $startf Champ SQL de debut
	 * @param $startf Champ SQL de fin
	 * @param $namef Champ SQL du nom
	 * @param $page Adresse de la page pour le suivant/précédent
	 * @param $infopage Adresse de la page d'information sur un élément
	 */
	function weekplanning ( $titre, $db, $sql, $idf, $startf, $endf, $namef, $page, $infopage, $extra="" )
	{
		$this->title=false;
		
		if (isset($_REQUEST["pstartdate"]))
			$start = strtotime($_REQUEST["pstartdate"]);
	
		if ( $start < 1)
			$start = strtotime(date("Y-m-d"));
			
		$end = $start + (6*24*60*60)+1;
		
		$req = new requete($db, $sql." AND $startf >= '".date("Y-m-d 00:00:00",$start)."' AND $endf <= '".date("Y-m-d 23:59:59",$end)."' $extra ORDER BY $startf");
		
		if ( strstr($page,"?"))
			$page = $page."&amp;";
		else
			$page = $page."?";

		if ( strstr($infopage,"?"))
			$infopage = $infopage."&amp;";
		else
			$infopage = $infopage."?";		
		
		if ( $n = strpos($startf,".") )
		  $startf = substr($startf,$n+1); 
		  
		if ( $n = strpos($endf,".") )
		  $endf = substr($endf,$n+1); 
		
		
		while ( $row = $req->get_row() )
		{
			$st = strtotime($row[$startf]);
			$ed = strtotime($row[$endf]);
			do {
				
				$endofday = strtotime(date("Y-m-d 23:59:59",$st));
				$day[date("Y-m-d",$st)][] = 
					array(
						$st, 
						min($endofday,$ed),
						$row[$idf],
						$row[$namef] 
						);
				$st=$endofday+1;
			} while ( $endofday < $ed );
		}
		
		
		
		$this->buffer .= "<table class=\"weekplanning\" width=\"100%\">\n<tr>\n";		
		$this->buffer .= "<td style=\"width:10%; text-align:center;\"><a href=\"".$page."pstartdate=".date("Y-m-d",strtotime(date("Y-m-d",$start)." -1 week"))."\">&laquo;</a></td>\n";
		$this->buffer .= "<td style=\"width:80%; text-align:center;\">$titre (".strftime("%A %d %B %G",$start).")</td>\n";
		$this->buffer .= "<td style=\"width:10%; text-align:center;\"><a href=\"".$page."pstartdate=".date("Y-m-d",strtotime(date("Y-m-d",$start)." +1 week"))."\">&raquo;</a></td>\n";
		$this->buffer .= "</tr>\n</table>\n";		
				
					
		$this->buffer .= "<table class=\"weekplanning\" width=\"100%\">\n";			
		/*$this->buffer .= "<tr class=\"planninghead\">";
		
		$this->buffer .= "</tr>";*/	
		$scale = 24*8;
		$height = floor((24*60*60/$scale)+20);
		$this->buffer .= "<tr>\n<td class=\"day\" style=\"width:9%; height:".$height."px;\">\n";
		$this->buffer .= "<div class=\"dayhead\" style=\"height:20px;\">&nbsp;</div>\n";
			
		for($i=0;$i<24;$i++)
		{
			$ln = floor(60*60/$scale);
			$this->buffer .= "<div style=\"border-top: 1px solid black; padding:1px; height:".($ln-3)."px; overflow:hidden;\">$i</div>\n";	
		}
		
		
		$this->buffer .= "</td>\n";
		for($i=$start;$i<$end;$i+=24*60*60)
		{
			$this->buffer .= "<td class=\"day\" style=\"width:13%; height:".$height."px; vertical-align:top;\">\n";
			$this->buffer .= "<div class=\"dayhead\" style=\"height:20px;\">".strftime("%A %d",$i)."</div>\n";
			
			$last=0;
			
		  if(!empty($day[date("Y-m-d",$i)]))
		  {
			  foreach ( $day[date("Y-m-d",$i)] as $row )
			  {
				  $st = floor(((date("H",$row[0])*60+date("i",$row[0]))*60)/$scale);

				  $ln = floor(($row[1]-$row[0])/$scale);
				  if ( $st != $last )
				    $this->buffer .= "<div style=\"height:".($st-$last)."px; overflow:hidden;\">&nbsp;</div>\n";
				
				
				  $this->buffer .= "<div style=\"border: 1px solid black; padding:1px; height:".($ln-4)."px; overflow:hidden;\"><a href=\"".$infopage.$idf."=".$row[2]."\"><i>".date("H:i",$row[0])."</i> ".$row[3]."</a></div>\n";
				  $last=$st+$ln;
			  }
		  }
			
			
			$this->buffer .= "</td>\n";
		}
		
		$this->buffer .= "</tr>\n</table>\n";

	}
	
	
} 
 
 
 
?>
