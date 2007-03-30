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
$topdir = "./";
include($topdir. "include/site.inc.php");
include($topdir. "include/assoclub.inc.php");
$site = new site ();
require_once($topdir . "include/news.inc.php");

header("Content-Type: text/xml; charset=utf-8");

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<rss version=\"2.0\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n";

echo "<channel>\n";
echo "<title>".htmlspecialchars("AE UTBM",ENT_NOQUOTES,"UTF-8")."</title>\n";
echo "<link>http://ae.utbm.fr/</link>\n";
echo "<pubDate>".gmdate("D, j M Y G:i:s T")."</pubDate>\n";
echo "<description>".htmlspecialchars("Les dernières nouvelles de la vie étudiante de l'UTBM",ENT_NOQUOTES,"UTF-8")."</description>\n";
		
function output_news ( $req, &$ids )
{
	if ( $req->lines == 0 ) return;
	
	while ( $row = $req->get_row() )
	{
		echo "<item>\n";
		echo "<title>".htmlspecialchars($row["titre_nvl"],ENT_NOQUOTES,"UTF-8")."</title>\n";
		echo "<link>http://ae.utbm.fr/news.php?id_nouvelle=".$row["id_nouvelle"]."</link>\n";
		echo "<description>".htmlspecialchars($row["resume_nvl"],ENT_NOQUOTES,"UTF-8")."</description>\n";
		echo "<pubDate>".gmdate("D, j M Y G:i:s T",strtotime($row["date_nvl"]))."</pubDate>\n";
		echo "<guid>http://ae.utbm.fr/news.php?id_nouvelle=".$row["id_nouvelle"]."</guid>\n";
		echo "</item>\n";	
		$ids[] = $row["id_nouvelle"];
	}
}

$ids = array(0);

$sql = new requete($site->db,"SELECT * FROM nvl_nouvelles " .
		"INNER JOIN nvl_dates ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
		"WHERE nvl_nouvelles.type_nvl='".NEWS_TYPE_APPEL."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
		"NOW() > nvl_dates.date_debut_eve AND NOW() < nvl_dates.date_fin_eve");

output_news($sql,$ids);

$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso FROM nvl_nouvelles " .
		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
		"WHERE type_nvl='".NEWS_TYPE_NOTICE."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
		"DATEDIFF(date_nvl,NOW()) < 14 " .
		"LIMIT 3");
		
output_news($sql,$ids);		
		
$ids = array(0);		
		
$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
		"FROM nvl_dates " .
		"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
		"WHERE (type_nvl='".NEWS_TYPE_EVENT."' OR type_nvl='".NEWS_TYPE_HEBDO."') AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
		"NOW() < nvl_dates.date_fin_eve " .
		"ORDER BY nvl_dates.date_debut_eve " .
		"LIMIT 5");
		
output_news($sql,$ids);		
		
$sql = new requete($site->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
		"FROM nvl_dates " .
		"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
		"WHERE type_nvl='".NEWS_TYPE_EVENT."' AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
		"nvl_dates.id_nouvelle NOT IN (".implode(",",$ids).") AND " .
		"NOW() < nvl_dates.date_debut_eve " .
		"ORDER BY nvl_dates.date_debut_eve " .
		"LIMIT 10");			

output_news($sql,$ids);
	
echo "</channel>\n";
echo "</rss>\n";
?>
