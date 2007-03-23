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

require_once($topdir."include/news.inc.php");


class newslist extends itemlist
{
	/**
	 * Génère une lsite de nouvelle
	 * @param $title Titre
	 * @param $db Connecion à la base de donnée
	 * @param $cond condition SQl sur les nouvelles à afficher
	 * @param $nb nombre de nouvelles à lister
	 */
	function	newslist ( $title, $db, $cond=false, $nb=10, $class="boxnewslist" )
	{	
		global $topdir;
		$this->title = $title;
		$this->class=$class;
		
		$sql = new requete($db,"SELECT * FROM nvl_nouvelles " .
				"INNER JOIN nvl_dates ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
				"WHERE nvl_nouvelles.type_nvl='".NEWS_TYPE_APPEL."' AND modere_nvl='1' AND " .
				"NOW() > nvl_dates.date_debut_eve AND NOW() < nvl_dates.date_fin_eve");
		
		while ( $row = $sql->get_row() )
		{
			if ( strlen($row["titre_nvl"]) > 35 )
				$row["titre_nvl"] = substr($row["titre_nvl"],0,32)."...";
				
			$titre = "<a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
				htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
				"</a>"; 

			$this->add($titre);
		}
		
		$sql = new requete($db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso FROM nvl_nouvelles " .
				"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
				"WHERE type_nvl='".NEWS_TYPE_NOTICE."' AND modere_nvl='1' AND " .
				"DATEDIFF(date_nvl,NOW()) < 14 " .
				"LIMIT 3");
				
		while ( $row = $sql->get_row() )
		{
			if ( strlen($row["titre_nvl"]) > 35 )
				$row["titre_nvl"] = substr($row["titre_nvl"],0,32)."...";
				
			$titre = "<a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
				htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
				"</a>"; 

			$this->add($titre);
		}
				
		$ids = array(0);		
				
		$sql = new requete($db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
				"FROM nvl_dates " .
				"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
				"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
				"WHERE (type_nvl='".NEWS_TYPE_EVENT."' OR type_nvl='".NEWS_TYPE_HEBDO."') AND  modere_nvl='1' AND " .
				"NOW() < nvl_dates.date_fin_eve " .
				"ORDER BY nvl_dates.date_debut_eve " .
				"LIMIT 5");
				
		while ( $row = $sql->get_row() )
		{
			if ( strlen($row["titre_nvl"]) > 30 )
				$row["titre_nvl"] = substr($row["titre_nvl"],0,27)."...";
			
			$titre = date("d/m",strtotime($row["date_debut_eve"]))." - <a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
				htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
				"</a>"; 

			
			$this->add($titre);
			$ids[]=$row["id_nouvelle"];
		}
				
		$sql = new requete($db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
				"FROM nvl_dates " .
				"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
				"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
				"WHERE type_nvl='".NEWS_TYPE_EVENT."' AND  modere_nvl='1' AND " .
				"nvl_dates.id_nouvelle NOT IN (".implode(",",$ids).") AND " .
				"NOW() < nvl_dates.date_debut_eve " .
				"ORDER BY nvl_dates.date_debut_eve " .
				"LIMIT 10");
		
		while ( $row = $sql->get_row() )
		{
			if ( strlen($row["titre_nvl"]) > 30 )
				$row["titre_nvl"] = substr($row["titre_nvl"],0,27)."...";
			
			$titre = date("d/m",strtotime($row["date_debut_eve"]))." - <a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
				htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
				"</a>"; 
		
			$this->add($titre);
			$ids[]=$row["id_nouvelle"];
		}

		
	}
}


/**
 * Conteneur d'un flux de nouvelles
 */
class newsflow extends contents
{
	/**
	 * Génère un flux denouvelle
	 * @param $title Titre
	 * @param $db Connecion à la base de donnée
	 * @param $cond condition SQl sur les nouvelles à afficher
	 * @param $pages affichage paginé
	 * @param $allfirst affiche toutes les nouvelles avec leur image et leur resumé
	 * @param $nnp nombre d enouvelles par pages/limite si non pagnié
	 */
	function	newsflow ( $title, $db, $cond, $url, $paged=true, $allfirst=true, $npp=10 )
	{
		global $topdir;
		
		$wiki = new wiki2xhtml();
		
		$this->title = $title;
		$npp = intval($npp);
		if ( strstr($url,"?"))
			$url = $url."&";
		else
			$url =$url."?";
			// htmlentities(,ENT_NOQUOTES,"UTF-8");
		if ( $paged )
		{
			$page = intval($_REQUEST["page"]);
	
			$req = new requete($db,"SELECT COUNT(`id_nouvelle`) " .
				"FROM `nvl_nouvelles` " .
				"LEFT JOIN `asso` AS `asso1` ON `asso1`.`id_asso`=`nvl_nouvelles`.`id_asso` " .
				"LEFT JOIN `asso` AS `asso2` ON `asso1`.`id_asso_parent`=`asso2`.`id_asso` " .
				"LEFT JOIN `asso` AS `asso3` ON `asso2`.`id_asso_parent`=`asso3`.`id_asso` " .
				"WHERE ".
				"($cond) ".
				"AND `modere_nvl`='1'");
			
			list($max) = $req->get_row();
			
			if ( $page)
				$st=$page*$npp;
			else
				$st=0;
			
			if ( $st > $max )
				$st = floor($max/$npp)*$npp;
		}
		else
			$st=0;
			
		$req = new requete($db,"SELECT " .
			"IF ( ( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND DATEDIFF(date_debut_eve,NOW()) < 5
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			) IS NULL,'2100-01-01',( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND DATEDIFF(date_debut_eve,NOW()) < 5
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			)), " .
			"( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			) AS `next_date`, " .
			"( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve DESC LIMIT 1 
			) AS `last_date`, " .
			"`id_nouvelle`,`titre_nvl`,`date_nvl`,`resume_nvl`,`asso1`.`nom_unix_asso` " .
			"FROM `nvl_nouvelles` " .
			"LEFT JOIN `asso` AS `asso1` ON `asso1`.`id_asso`=`nvl_nouvelles`.`id_asso` " .
			"LEFT JOIN `asso` AS `asso2` ON `asso1`.`id_asso_parent`=`asso2`.`id_asso` " .
			"LEFT JOIN `asso` AS `asso3` ON `asso2`.`id_asso_parent`=`asso3`.`id_asso` " .
			"WHERE ".
			"($cond) ".
			"AND `modere_nvl`='1' ORDER BY 1,`date_nvl` DESC LIMIT $st,$npp");

		$first=true;
		$n=0;
		$this->buffer .= "<div class=\"news\">";
		while ( $row = $req->get_row() )
		{
			$date = strtotime($row["date_nvl"]);
			
			if ( $row["next_date"] )
				$event_date = strtotime($row["next_date"]);
			elseif( $row["last_date"] )
				$event_date = strtotime($row["last_date"]);
			else
				$event_date = null;
			
			if ( $first  || $allfirst )
			{
				
				if ( $n )
					$this->buffer .= "<div class=\"newsitem first altfirst\">";
				else
					$this->buffer .= "<div class=\"newsitem first\">";
				$img = $topdir."var/img/logos/".$row["nom_unix_asso"].".icon.png";
				if ( !file_exists($img) )
					$img = $topdir."images/default/news.icon.png";
				$this->buffer .= "<img src=\"$img\" class=\"newsimg\" alt=\"\" />";
				
				$this->buffer .= "<a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
						htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
						"</a>";
				if ( $event_date )
					$this->buffer .= " <span class=\"newsdate\">- ".strftime("%A %d %B à %H:%M",$event_date)."</span>";
				$this->buffer .= "<br/>" .
						"<span class=\"resume\">".$wiki->transform($row["resume_nvl"])."</span><div class=\"clearboth\"></div></div>";	
				$first = false;
				$n=$n^1;
			}
			else
			{
				$this->buffer .= "<div class=\"newsitem\"><a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
				htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
				"</a>"; 
				if ( $event_date )
					$this->buffer .= " <span class=\"newsdate\">- ".strftime("%A %d %B à %H:%M",$event_date)."</span>";	
				$this->buffer .= "</div>";	
			}
		}
		$this->buffer .= "</div>";
		
		if ( ($max > $npp) && $paged )
		{
			$tabs = array();
			$i=0;
			while ( $i < $max )
			{
				$n = $i/$npp;
				$tabs[]=array($n,$url."page=".$n,$n+1 );
				$i+=$npp;	
			}
			$this->add(new tabshead($tabs, $page, "_bottom"));
		}
	}
	
	
	
}

/**
 * Conteneur d'un flux de nouvelles
 */
class newsflow2 extends contents
{
	/**
	 * Génère un flux denouvelle
	 * @param $title Titre
	 * @param $db Connecion à la base de donnée
	 * @param $cond condition SQl sur les nouvelles à afficher
	 * @param $nbfirst nombre de nouvelle en valeur 
	 * @param $nb Nombre total de nouvelles
	 */
	function	newsflow2 ( $title, $db, $cond, $nbfirst, $nb )
	{
		global $topdir;
		
		$wiki = new wiki2xhtml();
		
		$this->title = $title;

			
		$req = new requete($db,"SELECT " .
			"IF ( ( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND DATEDIFF(date_debut_eve,NOW()) < 5
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			) IS NULL,'2100-01-01',( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND DATEDIFF(date_debut_eve,NOW()) < 5
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			)), " .
			"( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE date_debut_eve > NOW() 
			AND nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve LIMIT 1 
			) AS `next_date`, " .
			"( SELECT date_debut_eve 
			FROM nvl_dates 
			WHERE nvl_dates.`id_nouvelle`=`nvl_nouvelles`.`id_nouvelle` 
			ORDER BY date_debut_eve DESC LIMIT 1 
			) AS `last_date`, " .
			"`id_nouvelle`,`titre_nvl`,`date_nvl`,`resume_nvl`,`asso1`.`nom_unix_asso` " .
			"FROM `nvl_nouvelles` " .
			"LEFT JOIN `asso` AS `asso1` ON `asso1`.`id_asso`=`nvl_nouvelles`.`id_asso` " .
			"LEFT JOIN `asso` AS `asso2` ON `asso1`.`id_asso_parent`=`asso2`.`id_asso` " .
			"LEFT JOIN `asso` AS `asso3` ON `asso2`.`id_asso_parent`=`asso3`.`id_asso` " .
			"WHERE ".
			"($cond) ".
			"AND `modere_nvl`='1' ORDER BY 1,`date_nvl` DESC LIMIT $nb");

		$n=0;
		$left_nws=array();
		$this->buffer .= "<div class=\"news\">";
		
		
		
		while ( $row = $req->get_row() )
		{
			$date = strtotime($row["date_nvl"]);
			
			if ( $row["next_date"] )
				$event_date = strtotime($row["next_date"]);
			elseif( $row["last_date"] )
				$event_date = strtotime($row["last_date"]);
			else
				$event_date = null;
			
			if ( $n < $nbfirst )
			{
				if ( $nbfirst == 4 && $n == 0 )
					$this->buffer .= "<div class=\"news_left\">";
				elseif ( $nbfirst == 4 && $n == 2 )
					$this->buffer .= "<div class=\"news_right\">";
				
				
				$this->buffer .= "<div class=\"newsitem first\">";
				$img = $topdir."var/img/logos/".$row["nom_unix_asso"].".icon.png";
				if ( !file_exists($img) )
					$img = $topdir."images/default/news.icon.png";
				$this->buffer .= "<img src=\"$img\" class=\"newsimg\" alt=\"\" />";
				
				$this->buffer .= "<a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
						htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8").
						"</a>";
				if ( $event_date )
					$this->buffer .= " <span class=\"newsdate\">- ".strftime("%A %d %B à %H:%M",$event_date)."</span>";
				$this->buffer .= "<br/>" .
						"<span class=\"resume\">".$wiki->transform($row["resume_nvl"])."</span><div class=\"clearboth\"></div></div>";	
			
				if ( $nbfirst == 4 && ($n%2)==1 )
					$this->buffer .= "</div>";
			}
			else
			{
				$nws = "<a href=\"".$topdir."news.php?id_nouvelle=".$row["id_nouvelle"]."\">".
						htmlentities($row["titre_nvl"],ENT_NOQUOTES,"UTF-8");
						
				if ( $event_date )
					$nws .= " (".strftime("%d %B",$event_date).")";
				$nws .= "</a>";	
				
				$left_nws[] = $nws;
				
			}
			$n++;
		}
		$this->buffer .= "<div class=\"newsleft\">Autres nouvelles: ".implode(" - ",$left_nws)."</div>";
		$this->buffer .= "</div>";
	}
}

$GLOBALS["news_channels"] = 
	array( 
		13 => "Grandes activités et événements",
		9 => "Pôle technique",
		12 => "Pôle entraide et humanitaire",
		11 => "Pôle culturel",
		10 => "Pôle artistique"
	);


?>
