<?php
/*
  vive le taff à l'arrache !!!!!
  licence agréée la-rache.com
 */

$topdir = "../";


require_once($topdir. "include/site.inc.php");
require_once($topdir. "sas2/include/photo.inc.php");
$site = new site ();

$user = new utilisateur($site->db,$site->dbrw);
$user->load_by_id(3538);


$req = new requete($site->db,"SELECT `id_photo` FROM `sas_photos` WHERE `id_asso_photographe`='43' AND `incomplet`='0'");

$photo = new photo($site->db,$site->dbrw);

$bouh = false;

exec("/bin/mkdir /tmp/reflex");
while ( list($id) = $req->get_row() )
{
  $photo->load_by_id($id);
  if ($photo->is_valid() && $photo->type_media != MEDIA_VIDEOFLV)
  {
    if ($photo->is_right($user,DROIT_LECTURE))
    {
      $bouh=true;
      if(!exec("/bin/cp ".$photo->get_abs_path().$photo->id." /tmp/reflex/".$photo->id.".jpg"))
        echo "/bin/cp ".$photo->get_abs_path().$photo->id." /tmp/reflex/".$photo->id.".jpg";
    }
  }
}

if($bouh)
{
  exec("/bin/tar czf /tmp/reflex.tar.gz /tmp/reflex");
}




?>
