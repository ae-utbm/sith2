<?php

require_once($topdir."include/rss.inc.php");
require_once($topdir . "include/entities/news.inc.php");


class rssfeednews extends rssfeed
{
  var $db;
  var $pubUrl;
  
  function rssfeednews ( &$db )
  {
    $this->db = $db;  
    $this->pubUrl = "http://ae.utbm.fr/";
    $this->rssfeed();
  }


  function output_news ( $req, &$ids )
  {
  	if ( $req->lines == 0 ) return;
  	
  	while ( $row = $req->get_row() )
  	{
  		echo "<item>\n";
  		echo "<title>".htmlspecialchars($row["titre_nvl"],ENT_NOQUOTES,"UTF-8")."</title>\n";
  		echo "<link>".$this->pubUrl."news.php?id_nouvelle=".$row["id_nouvelle"]."</link>\n";
  		echo "<description>".htmlspecialchars($row["resume_nvl"],ENT_NOQUOTES,"UTF-8")."</description>\n";
  		echo "<pubDate>".gmdate("D, j M Y G:i:s T",strtotime($row["date_nvl"]))."</pubDate>\n";
  		echo "<guid>http://ae.utbm.fr/news.php?id_nouvelle=".$row["id_nouvelle"]."</guid>\n";
  		echo "</item>\n";	
  		
		  $ids[] = $row["id_nouvelle"];
  	}
  }

}

class rssfeednewshome extends rssfeednews
{
  
  function rssfeednewshome ( &$db )
  {
    $this->rssfeednews($db);
    $this->title = "AE UTBM";
    $this->description = "Les dernières nouvelles de la vie étudiante de l'UTBM";
    $this->link = "http://ae.utbm.fr/";
  }
  
  function output_items ()
  {
    $ids = array(0);
    
    $sql = new requete($this->db,"SELECT * FROM nvl_nouvelles " .
    		"INNER JOIN nvl_dates ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
    		"WHERE nvl_nouvelles.type_nvl='".NEWS_TYPE_APPEL."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
    		"NOW() > nvl_dates.date_debut_eve AND NOW() < nvl_dates.date_fin_eve");
    
    $this->output_news($sql,$ids);
    
    $sql = new requete($this->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso FROM nvl_nouvelles " .
    		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
    		"WHERE type_nvl='".NEWS_TYPE_NOTICE."' AND modere_nvl='1' AND asso_seule_nvl='0' AND " .
    		"DATEDIFF(NOW(),date_nvl) < 14 " .
    		"LIMIT 3");
    		
    $this->output_news($sql,$ids);		
    		
    $ids = array(0);		
    		
    $sql = new requete($this->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
    		"FROM nvl_dates " .
    		"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
    		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
    		"WHERE (type_nvl='".NEWS_TYPE_EVENT."' "./*OR type_nvl='".NEWS_TYPE_HEBDO."'*/") AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
    		"NOW() < nvl_dates.date_fin_eve " .
    		"ORDER BY nvl_dates.date_debut_eve " .
    		"LIMIT 5");
    		
    $this->output_news($sql,$ids);		
    		
    $sql = new requete($this->db,"SELECT nvl_nouvelles.*,asso.nom_unix_asso,nvl_dates.date_debut_eve,nvl_dates.date_fin_eve " .
    		"FROM nvl_dates " .
    		"INNER JOIN  nvl_nouvelles ON (nvl_dates.id_nouvelle=nvl_nouvelles.id_nouvelle) " .
    		"LEFT JOIN asso ON asso.id_asso = nvl_nouvelles.id_asso " .
    		"WHERE type_nvl='".NEWS_TYPE_EVENT."' AND  modere_nvl='1' AND asso_seule_nvl='0' AND " .
    		"nvl_dates.id_nouvelle NOT IN (".implode(",",$ids).") AND " .
    		"NOW() < nvl_dates.date_debut_eve " .
    		"ORDER BY nvl_dates.date_debut_eve " .
    		"LIMIT 10");			
    
    $this->output_news($sql,$ids);
  }
  
}

class rssfeednewsclub extends rssfeednews
{
  var $asso;
  
  function rssfeednewsclub ( &$db, &$asso, $pubUrl )
  {
    $this->rssfeednews($db);
    $this->title = $asso->nom;
    $this->description = "Les dernières nouvelles de ".$asso->nom;
    $this->link = $pubUrl;
    $this->pubUrl = $pubUrl;
    $this->asso = $asso;
  }

  function output_items ()
  {
    $ids = array(0);
    
    $req = new requete($this->db,"SELECT * FROM nvl_nouvelles ".
      "WHERE id_asso='".mysql_real_escape_string($this->asso->id)."' ".
      "AND `modere_nvl`='1' ".
      "ORDER BY date_nvl DESC ".
      "LIMIT 30");
    
    $this->output_news($req,$ids);
  }
  
}
?>