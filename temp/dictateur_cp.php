<?php

$topdir="../";
include($topdir. "include/site.inc.php");

$site = new site ();

$req = new requete($site->db, "SELECT `id_utilisateur`, `cpostal_utl` FROM `utilisateurs` ".
                              "WHERE `id_ville` IS NULL AND `id_pays` IS NULL AND (`cpostal_utl` IS NOT NULL AND `cpostal_utl` != ''");
echo "<pre>\n";
while(list($id,$cp)=$req->get_row())
{
  $_req = new requete($site->db, "SELECT `id_ville` FROM `loc_ville` ".
                                 "WHERE `cpostal_ville` = '"mysql_real_escape_string($cp)"' LIMIT 1";
  if($_req->lines!=1)
    continue;

  $v=$_req->get_row();
  echo "updating utl : ".$id."\n";
  new update($site->dbrw,"utilisateurs",array('id_ville' => $v['id_ville'], 'id_pays' => 1), array('id_utilisateur' => $id));
}
echo "</pre>";
?>
