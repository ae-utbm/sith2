<?php
$topdir = "../";
require_once($topdir. "include/site.inc.php");
$site = new site ();
$req = new requete($site->db,
'SELECT id_groupe FROM `pedag_groupe_utl` u
LEFT JOIN `pedag_groupe` g USING(id_groupe)
 WHERE g.id_groupe IS NULL');
while(list($grp)=$req->get_row())
{
  $req2=new requete($site->dbrw,
'DELETE FROM `pedag_groupe_utl`
WHERE id_groupe=\''.$grp.'\'');
}
?>
