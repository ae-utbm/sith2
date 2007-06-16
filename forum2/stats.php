<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site();

if (!$site->user->is_in_group ("gestion_ae"))
{ 
  error_403();
}   

if (isset($_REQUEST['toptenimg']))
{
  require_once($topdir. "include/graph.inc.php");
  $req = "SELECT 
                COUNT(`id_message`) as totmesg
              , `utilisateurs`.`alias_utl`
          FROM 
                `frm_message`
          INNER JOIN 
                `utilisateurs`
          USING (`id_utilisateur`)
          GROUP BY 
                 `id_utilisateur`
          ORDER BY 
                 COUNT(`id_message`) DESC LIMIT 10";

  $rs = new requete($site->db, $req);


  $datas = array("utilisateur" => "Nbmessages");
  

  while ($plouf = $rs->get_row())
    {
      $plouf['alias_utl'] = explode(' ', $plouf['alias_utl']);
      $plouf['alias_utl'] = $plouf['alias_utl'][0];
      
      $datas[utf8_decode($plouf['alias_utl'])] = $plouf['totmesg'];
    }
  
  
  $hist = new histogram($datas, "Top 10");
  
  $hist->png_render();
  
  $hist->destroy();
  
  exit();

}

if (isset($_REQUEST['mesgbyday']))
{
  require_once($topdir. "include/graph.inc.php");
  $query =
    "SELECT 
            DATE_FORMAT(date_message,'%Y-%m-%d') AS `datemesg`
            , COUNT(id_message) AS `nbmesg` 
     FROM 
            `frm_message` 
     WHERE 
            `date_message` >= '2007-01-01' 
     GROUP BY 
            `datemesg`";

  $req = new requete($site->db, $query);

  $i = 0;

  while ($rs = $req->get_row())
    {
      if (($i % 30) == 0)
	$xtics[$i]  = $rs['datemesg'];
      $coords[] = array('x' => $i,
			'y' => $rs['nbmesg']);
      $i++;
    }
  
  
  $grp = new graphic("",
		     "messages par jour",
		     $coords,
		     false,
		     $xtics);

  $grp->png_render();

  $grp->destroy_graph();

  exit();
}



$site->start_page("forum", "Statistiques du forum de l'AE");

$cts = new contents("Statistiques du forum");
$cts->add_title(1, "Top 10 des posteurs");
$cts->add_paragraph("<center><img src=\"./stats.php?toptenimg\" alt=\"top10\" /></center>");

$cts->add_title(1, "Messages postés depuis le début de l'année 2007");
$cts->add_paragraph("<center><img src=\"./stats.php?mesgbyday\" alt=\"Messages par jour\" /></center>");

$site->add_contents($cts);

$site->end_page();


?>