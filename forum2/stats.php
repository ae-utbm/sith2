<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");


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

  $rs = new requete(new mysqlae(), $req);


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


$site = new site();

if (!$site->user->is_in_group ("gestion_ae")) 
     error_403();

$site->start_page("forum", "Statistiques du forum de l'AE");

$cts = new contents("Top 10 des meilleurs posteurs",
		    "<img src=\"stats.php?toptenimg\" alt=\"topten\" />");

$site->add_contents($cts);

$site->end_page();


?>