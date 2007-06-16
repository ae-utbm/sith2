<?

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/graph.inc.php");

$site = new site();


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
                 COUNT(`id_message`) DESC";
$rs = new requete($site->db, $req);


$datas = array("utilisateurs" => "Nbmessages");

while ($plouf = $rs->get_row())
{
  $datas[] = array($plouf['alias_utl'] => $plouf['totmesg']);
}


header("Content-Type: Text/plain");

$hist = new histogram($datas, "Messages par utilisateurs");

file_get_contents($hist->data_file);

$hist->destroy();


?>