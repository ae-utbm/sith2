<?php
$topdir = "";
require_once($topdir. "include/site.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae"))
  $site->error_forbidden();

$site->start_page ("none", "Restauration des droits sur les pages des club");

$req = new requete($site->db, "SELECT `nom_unix_asso`, `id_asso` FROM `asso`");
while ( $row = $req->get_row() )
{
  $id=20000+$row['id_asso'];
  $req2 = new requete($site->db,
              "SELECT `id_groupe` FROM `pages` WHERE `nom_page`='activites-".$row['nom_unix_asso']."'");
  if ( $req2->lines = 1 )
  {
    $val=$req2->get_row();

    if ($val['id_groupe'] != $id )
      $req3 = new requete($site->dbrw,
                   "UPDATE `pages` SET `id_groupe` = $id ".
                   "WHERE `nom_page`='activites-".$row['nom_unix_asso']."'");
  }
}
exit();

?>
