<?php

/** @file
 *
 * @brief La page principale avec l'affichage des 10 dernières news
 * modérées.
 *
 */

/* Copyright 2004,2006
 * - Alexandre Belloni <alexandre POINT belloni CHEZ utbm POINT fr>
 * - Thomas Petazzoni <thomas POINT petazzoni CHEZ enix POINT org>
 * - Julien Etelain <julien CHEZ pmad POINT net>
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

$topdir = "./";

if (!isset($_GET['pass']))
  Header("Location: http://ae.utbm.fr/streaming/");

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/newsflow.inc.php");
require_once($topdir . "include/assoclub.inc.php");
require_once($topdir . "include/news.inc.php");

$site = new site ();
$site->add_rss("Toute l'actualité de l'association des étudiants","rss.php");


$site->start_page("accueil","Bienvenue");

if ( $site->user->id <= 0 )
{
	require_once($topdir. "include/page.inc.php");
	$page = new page ($site->db);
	$page->load_by_name("presentation-short");
	$site->add_contents($page->get_contents());
}

/*$site->user->get_param("homemode",1)*/

	$cts = new contents("Les dernières nouvelles de la vie étudiante de l'UTBM");
	$cts->class="nvls";
	// 1: Les appels
	
	$sql = new requete($site->db,"SELECT * FROM nvl_nouvelles " .
			"INNER JOIN nvl_dates ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
			"WHERE nvl_nouvelles.type_nvl='".NEWS_TYPE_APPEL."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
			"NOW() > nvl_dates.date_debut_eve AND NOW() < nvl_dates.date_fin_eve");
					
	if ( $sql->lines > 0 )	
	{
		$cts->puts("<div class=\"newsappel\"><ul>\n");
		while ( $row = $sql->get_row())
		{
			$cts->puts("<li><a href=\"news.php?id_nouvelle=".$row['id_nouvelle']."\">".$row['titre_nvl']."</a></li>");
		}
		$cts->puts("</ul></div>\n");
	}
	
	// 2: Les infos
	
	$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso FROM nvl_nouvelles " .
			"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
			"WHERE type_nvl='".NEWS_TYPE_NOTICE."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
			"DATEDIFF(NOW(),date_nvl) < 14 " .
			"LIMIT 3");
			
	if ( $sql->lines > 0 )	
	{
		$cts->puts("<div class=\"newsnotices\">");
		$cts->puts("<h2>Informations</h2>\n");
		$cts->puts("<ul>\n");
		$n=0;
		while ( $row = $sql->get_row())
		{	
			if ( $row['id_asso'] )
			{
				$img = "/var/img/logos/".$row['nom_unix_asso'].".icon.png";
				if ( !file_exists("/var/www/ae/www".$img) )
					$img = "images/default/news.icon.png";		
			}
			else
				$img = "images/default/news.icon.png";
			
			$when = " ".date("d/m/Y",strtotime($row['date_nvl']));
			
			$cts->puts("<li class=\"nvlitm nvl$n\"><img src=\"$img\" alt=\"\"/><a href=\"news.php?id_nouvelle=".$row['id_nouvelle']."\" class=\"nvltitre\">".$row['titre_nvl']."</a> <span class=\"when\">$when</span><br/><span class=\"nvlresume\">".$row['resume_nvl']."</span><div class=\"clearboth\"></div></li>\n");
			$n = ($n+1)%2;	
		}
		$cts->puts("</ul></div>\n");	
			
	}
	
	// 3: En ce moment et les prochains jours
	$ids=array(0);
	
	$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
			"FROM nvl_dates " .
			"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
			"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
			"WHERE (type_nvl='".NEWS_TYPE_EVENT."' OR type_nvl='".NEWS_TYPE_HEBDO."') AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
			"NOW() < nvl_dates.date_fin_eve " .
			"ORDER BY nvl_dates.date_debut_eve " .
			"LIMIT 6");
			
	$cts->puts("<div class=\"newssoon\">");
	$cts->puts("<h2>Activités et événements aujourd'hui et dans les prochains jours</h2>\n");
	if ( $sql->lines > 0 )	
	{
		$prevday=null;
		$n=0;
		while( $row = $sql->get_row() )
		{
			$ids[] = $row["id_nouvelle"];
			$debut = strtotime($row['date_debut_eve']);
			$fin = strtotime($row['date_fin_eve']);
			$day = date("Y-m-d",$debut);
			
			if ( is_null($prevday) || $prevday != $day )
			{
				if ( !is_null($prevday))
					$cts->puts("</ul>\n");
					
				$cts->puts("<h3>".strftime("%A %d %B",$debut)."</h3>\n");
				$cts->puts("<ul>\n");
				$prevday=$day;	
				//$n=0;
			}
			
			if ( $row['id_asso'] )
			{
				$img = "<a href=\"asso.php?id_asso=".$row['id_asso']."\"><img src=\"/var/img/logos/".$row['nom_unix_asso'].".icon.png\" alt=\"\"/>";
				if ( !file_exists("/var/www/ae/www/ae2/var/img/logos/".$row['nom_unix_asso'].".icon.png") )
					$img = "<a href=\"asso.php?id_asso=".$row['id_asso']."\"><img src=\"images/default/news.icon.png\" alt=\"\"/></a>";		
			}
			else
			{
			  $img = "<img src=\"images/default/news.icon.png\" alt=\"\"/>";
			}
				
			if ( $day != date("Y-m-d",$fin) && (($fin-$debut) > (60*60*24)))	
				$hour = "de ".strftime("%H:%M",$debut) . " jusqu'au ".strftime("%A %d %B %H:%M",$fin);
			else
				$hour = "de ".strftime("%H:%M",$debut) . " jusqu'à ".strftime("%H:%M",$fin);

			$cts->puts("<li class=\"nvlitm nvl$n\">$img<a href=\"news.php?id_nouvelle=".$row['id_nouvelle']."\" class=\"nvltitre\">".$row['titre_nvl']."</a> <span class=\"hour\">$hour</span><br/><span class=\"nvlresume\">".$row['resume_nvl']."</span><div class=\"clearboth\"></div></li>\n");
		
		
			$n = ($n+1)%2;
		}
		
		$cts->puts("</ul>\n");
	}
	else
		$cts->puts("<p>Rien de prévu pour le moment...</p>\n");
		
	$cts->puts("</div>\n");
	
	
	$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
			"FROM nvl_dates " .
			"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
			"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
			"WHERE type_nvl='".NEWS_TYPE_EVENT."' AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
			"nvl_dates.id_nouvelle NOT IN (".implode(",",$ids).") AND " .
			"NOW() < nvl_dates.date_debut_eve " .
			"ORDER BY nvl_dates.date_debut_eve " .
			"LIMIT 10");
	
	
	if ( $sql->lines > 0 )
	{	
		$cts->puts("<div class=\"newsnottomiss\">");
		$cts->puts("<h2>Prochainement... à ne pas rater !</h2>\n");	
		$cts->puts("<ul>\n");
		
		while( $row = $sql->get_row() )
		{
			$debut = strtotime($row['date_debut_eve']);
			$hour = "le ".strftime("%A %d %B %G à %H:%M",$debut);
			$cts->puts("<li class=\"nvlttls\"><a href=\"news.php?id_nouvelle=".$row['id_nouvelle']."\">".$row['titre_nvl']."</a> <span class=\"hour\">$hour</span></li>");	
		}
		$cts->puts("</ul>\n");
		$cts->puts("</div>\n");
	}
	
	$site->add_contents($cts);

$site->end_page();

?>
