<?php
$topdir = "../";

require_once($topdir. "include/site.inc.php");
$site = new site ();

$req = new requete($site->db,
'SELECT count(`id_groupe`) AS nb, `id_uv`, `type`, `num_groupe`, `semestre`
FROM `pedag_groupe`
GROUP BY `id_uv`, `type`, `num_groupe`, `semestre`
HAVING (coint(`id_groupe`))>1');

while(list($nb,$uv,$type,$grp,$sem)=$req->get_row())
{

  $req2=new requete($site->db,
'SELECT `id_groupe`
FROM `pedag_groupe`
WHERE `id_uv`=\''.$uv.'\'
AND `type`=\''.$type.'\'
AND `num_groupe`=\''.$grp.'\'
AND `semestre`=\''.$sem.'\'');

  $_id=-1;
  while(list($id)=$req2->get_row())
  {
    if($_id==-1)
      $_id=$id;
    else
    {
      $req3 = new update($site->dbrw,
              'pedag_groupe_utl',
              array('id_group'=>$_id),
              array('id_group'=>$id));
      $req3 = new delete($site->dbrw,
              'pedag_groupe',
              array('id_group'=>$id));
    }
  }
}

$req = new requete($site->dbrw,
'ALTER TABLE `pedag_groupe` ADD UNIQUE `uniqueuh` (`id_uv`,`type`,`num_groupe`,`semestre`)');

?>
