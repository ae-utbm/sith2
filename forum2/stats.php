<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site();

if (!$site->user->is_in_group ("gestion_ae"))
{ 
  $site->error_forbidden();
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
  if (!isset($_REQUEST['db']))
    $db = date("Y")."-01-01";
  else
    $db = $_REQUEST['db'];

  if (!isset($_REQUEST['de']))
    $de = date("Y-m-d");
  else
    $de = $_REQUEST['de'];

  $db = mysql_real_escape_string($db);
  $de = mysql_real_escape_string($de);


  require_once($topdir. "include/graph.inc.php");
  $query =
    "SELECT 
            DATE_FORMAT(date_message,'%Y-%m-%d') AS `datemesg`
            , COUNT(id_message) AS `nbmesg` 
     FROM 
            `frm_message` 
     WHERE 
            `date_message` >= '".$db."'
     AND
            `date_message` <= '".$de."'
     GROUP BY 
            `datemesg`";

  $req = new requete($site->db, $query);

  $i = 0;

  $step = (int) ($req->lines / 5);

  while ($rs = $req->get_row())
    {
      if (($i % $step) == 0)
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

$cts->add_title(1, "Messages postés depuis le début de l'année");
$cts->add_paragraph("<center><img src=\"./stats.php?mesgbyday\" alt=\"Messages par jour\" /></center>");

$cts->add_title(1, "Messages postés les 30 derniers jours");

/* statistiques sur 30 jours */
$db = date("Y-m-d", time() - (30 * 24 * 3600));

$cts->add_paragraph("<center><img src=\"./stats.php?mesgbyday&db=".$db."&de=".date("Y-m-d").
		    "\" alt=\"Messages par jour\" /></center>");

$site->add_contents($cts);

$site->end_page();


?>