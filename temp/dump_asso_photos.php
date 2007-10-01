<?php
/*
  vive le taff à l'arrache !!!!!
  licence agréée la-rache.com
 */

$topdir = "../";

if( !isset($_REQUEST["id_asso"]) || empty($_REQUEST["id_asso"]) )
  exit();

require_once($topdir. "include/site.inc.php");
require_once($topdir. "sas2/include/photo.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
$site = new site ();

$asso = new asso($site->db,$site->dbrw);
$asso->load_by_id($_REQUEST["id_asso"]);
if ( $asso->id < 1 )
{
  exit();
}

$user = new utilisateur($site->db,$site->dbrw);
/* le lion, c'est notre utilisater de référence */
$user->load_by_id(3538);


$req = new requete($site->db,"SELECT `id_photo` FROM `sas_photos` WHERE (`id_asso_photographe`='".$asso->id."' OR `meta_id_asso_ph`='".$asso->id."') AND `incomplet`='0'");

$photo = new photo($site->db,$site->dbrw);

$bouh = false;

exec("/bin/mkdir /tmp/".$asso->nom_unix);
while ( list($id) = $req->get_row() )
{
  $photo->load_by_id($id);
  if ($photo->is_valid() && $photo->type_media != MEDIA_VIDEOFLV)
  {
    if ($photo->is_right($user,DROIT_LECTURE))
    {
      $bouh=true;
      exec("/bin/cp ".$photo->get_abs_path().$photo->id.".jpg /tmp/".$asso->nom_unix."/".$photo->id.".jpg");
    }
  }
}

if($bouh)
{
  exec("/bin/tar czf /tmp/".$asso->nom_unix.".tar.gz /tmp/".$asso->nom_unix);
  exec("/bin/mv /tmp/".$asso->nom_unix.".tar.gz /var/www/ae/accounts/equipeinfo/");
  exec("/bin/rm -Rf /tmp/".$asso->nom_unix);
}


echo "<a href='http://ae.utbm.fr/equipeinfo/".$asso->nom_unix.".tar.gz'>ici</a>";

?>
