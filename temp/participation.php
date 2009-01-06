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
       ", date_debut ".
       ", date_fin ".
       "FROM vt_election ".
       "WHERE id_groupe=10000 ".
       "ORDER BY date_debut, date_fin");
$elections = array();
while(list($id,$nom,$deb,$fin)=$req->get_row())
{
  $req2 = new requete($site->db,
    "SELECT ".
    "COUNT(DISTINCT(id_utilisateur)) as nb ".
    "FROM `ae_cotisations` ".
    "WHERE ".
    "`date_fin_cotis` > '".$fin."' ".
    "AND `date_cotis` < '".$fin."' ");
  list($cot)=$req2->get_row();

  $req2 = new requete($site->db,
    "SELECT ".
    "COUNT(*) as nb ".
    "FROM `vt_a_vote` ".
    "WHERE `id_election` =".$id);
  list($vot)=$req2->get_row();

  $cts2 = new contents($nom);
  $lst = new itemlist();
  $lst->add("Début : ".date("d/m/Y H:i",strtotime($deb)));
  $lst->add("Fin : ".date("d/m/Y H:i",strtotime($fin)));
  $lst->add("Cotisants : ".$cot);
  $lst->add("Votants : ".$vot);
  $cts2->add($lst);

  $part = round(($vot/$cot)*100,1);
  $prog = new progressbar($part);
  $cts2->add($prog);
  $cts->add($cts2,true);
}
$site->add_contents($cts);
$site->end_page();

?>
