<?php
/*
  vive le taff à l'arrache !!!!!
  licence agréée la-rache.com
 */

if(!isset($_SERVER['SCRIPT_FILENAME']))
{
  $_SERVER['SCRIPT_FILENAME']="/var/www/ae2/taiste/temp";
  $topdir=$_SERVER['SCRIPT_FILENAME']."/../";
}
else
  $topdir = "../";

if( !isset($_REQUEST["id_asso"]) || empty($_REQUEST["id_asso"]) )
  exit();

require_once($topdir. "include/site.inc.php");
require_once($topdir. "sas2/include/photo.inc.php");
require_once($topdir. "sas2/include/cat.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/sas.inc.php");
$site = new site ();

$user = new utilisateur($site->db,$site->dbrw);
/* le lion, c'est notre utilisater de référence */
$user->load_by_id(3538);
$grps = $user->get_groups_csv();

function get_catphoto($id_catph)
{
  global $user;
  global $grps;
  global $site;
  $photos = array();
  $cat = new catphoto($site->db,$site->dbrw);
  $cat->load_by_id($id_catph);
  if ( !$cat->is_valid() )
    return $photos;

  /* on récupère les photos de la catégorie */
  $sqlph = $cat->get_photos ( $cat->id, $user, $grps, "sas_photos.id_photo");
  while ( list($id) = $sqlph->get_row() )
    $photos[] = $id;

  /* on récupère les sous catégories */
  $cats = $cat->get_all_categories($user);
  foreach ( $cats as $row )
  {
    $subcatph = get_catphoto($row["id_catph"]);
    if(!empty($subcatph))
    {
      foreach($subcatph as $id)
      {
        if(!in_array($id,$photos))
          $photos[] = $id;
      }
    }
  }
  return $photos;
}

if(isset($_REQUEST["id_asso"]) && !isset($_REQUEST["id_catph"]))
{
  $asso = new asso($site->db,$site->dbrw);
  $asso->load_by_id($_REQUEST["id_asso"]);
  if ( $asso->id < 1 )
    exit();

  $photos = array();
  $req = new requete($site->db,"SELECT `id_photo` FROM `sas_photos` WHERE (`id_asso_photographe`='".$asso->id."' OR `meta_id_asso_ph`='".$asso->id."') AND `incomplet`='0'");
  while ( list($id) = $req->get_row() )
  {
    $photos[]=$id;
  }
}
elseif(isset($_REQUEST["id_catph"]) && isset($_REQUEST["id_asso"]))
{
  $asso = new asso($site->db,$site->dbrw);
  $asso->load_by_id($_REQUEST["id_asso"]);
  if ( $asso->id < 1 )
    exit();

  $cat = new catphoto($site->db,$site->dbrw);
  $cat->load_by_id($_REQUEST["id_catph"]);
  if ( !$cat->is_valid() )
    exit();

  $photos = array();
  $photos = get_catphoto($_REQUEST["id_catph"]);
}
else
  exit();

$photo = new photo($site->db,$site->dbrw);

$bouh = false;


if(empty($photos))
  exit();

exec("/bin/mkdir /tmp/".$asso->nom_unix);

foreach($photos as $id)
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
  exec("/bin/mv /tmp/".$asso->nom_unix.".tar.gz /var/www/var/");
  exec("/bin/rm -Rf /tmp/".$asso->nom_unix);
}


echo "<a href='http://ae.utbm.fr/var/".$asso->nom_unix.".tar.gz'>ici</a>";

?>
