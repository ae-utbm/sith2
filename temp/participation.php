<?php
$topdir="../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/cts/progressbar.inc.php");
$site = new site();

$site->start_page("", "Participation élections" );
$cts = new contents("Évolution de la participation dans le temps");
$req = new requete($site->db,
       "SELECT ".
       "  id_election".
       ", nom_elec".
       ", date_fin ".
       "FROM vt_election ".
       "WHERE id_groupe=10000 ".
       "ORDER BY date_debut, date_fin");
$elections = array();
while(list($id,$nom,$fin)=$req->get_row())
{
  $req2 = new requete($site->db,
    "SELECT ".
    "COUNT(DISTINCT(id_utilisateur)) as nb ".
    "FROM `ae_cotisations` ".
    "WHERE ".
    "`date_fin_cotis` > '".$fin."' ".
    "AND `date_cotis` < '".$fin."' ");
  list($max)=$req2->get_row();
  $req2 = new requete($site->db,
    "SELECT ".
    "COUNT(*) ".
    "FROM `vt_a_vote` ".
    "WHERE `id_election` =".$id);
  list($nb)=$req2->get_row();
  $participation = round(($nb/$max)*100,0);
  $prog = new progressbar($participation,$nom);
  $cts->add($prog,true);
}
$site->add_contents($cts);
$site->end_page();

?>
