<?php

$topdir="../";

require_once($topdir."sas2/include/sas.inc.php");
$site = new sas();

		header("Content-Type: text/html; charset=utf-8");

/**
 * Script de vérification des catégories du SAS
 */

echo "<h1>AE2/SAS: Auto repair</h1>";

$sql = new requete($site->db,"SELECT * FROM sas_cat_photos");

echo "<h2>Vérification des catégories</h2>";

echo "<ul>\n";

while ( $row = $sql->get_row() )
{
  // Verification des droits

  $nvdroits=$row['droits_acces_catph'];

  $allow_cat = ($row['droits_acces_catph'] & 0x444);
  $allow_photos = ($row['droits_acces_catph'] & 0x888);

  $have_photos=0;
  $have_cat=0;

  $req = new requete($site->db,"SELECT COUNT(*) FROM sas_photos WHERE id_catph='".$row['id_catph']."'");
  list($have_photos) = $req->get_row();

  $req = new requete($site->db,"SELECT COUNT(*) FROM sas_cat_photos WHERE id_catph_parent='".$row['id_catph']."'");
  list($have_cat) = $req->get_row();

  if ( $allow_cat && $allow_photos )
  {
    echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème</b> : authorise des photos et des catégories.</li>";

    if ( $have_cat || !$have_photos )
    {
      $nvdroits = $nvdroits & ~0x888;
      $allow_photos=false;
    }
    else
    {
      $nvdroits = $nvdroits & ~0x444;
      $allow_cat=false;
    }

  }
  elseif ( !$allow_cat && !$allow_photos )
    echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème non solvable</b> : n'authorise ni les photos ni les catégories.</li>";

  if ( $have_photos && !$allow_photos )
  {

    if ( !$have_cat )
    {
      echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème</b> : possède des photos mais ne les authorises pas.</li>";

      if ( $nvdroits & 0x1 )
        $nvdroits = ($nvdroits & ~0x444) | 0x888;
      else
        $nvdroits = ($nvdroits & ~0x44F) | 0x880;

      $allow_cat=false;
    }
    else
      echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème non solvable</b> : possède des photos mais ne les authorises pas.</li>";
  }

  if ( $have_cat && !$allow_cat )
  {
    if ( !$have_photos )
    {
      echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème</b> : possède des catégories mais ne les authorises pas.</li>";
      if ( $nvdroits & 0x1 )
        $nvdroits = ($nvdroits & ~0x888) | 0x444;
      else
        $nvdroits = ($nvdroits & ~0x88F) | 0x440;

      $allow_photos=false;
    }
    else
      echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème non solvable</b> : possède des catégories mais ne les authorises pas.</li>";

  }

  if ( $nvdroits & ~DROIT_MASKCAT )
  {
    echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème</b> : possède des droits non valides.</li>";
    $nvdroits = $nvdroits & DROIT_MASKCAT;
  }

  if ( $nvdroits != $row['droits_acces_catph'] )
  {
    echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>mise à jour des droits</b></li>";
    $req = new requete($site->dbrw,"UPDATE sas_cat_photos SET `droits_acces_catph`='".$nvdroits."' WHERE id_catph='".$row['id_catph']."'");
    $row['droits_acces_catph'] = $nvdroits;

    $allow_cat = ($row['droits_acces_catph'] & 0x444);
    $allow_photos = ($row['droits_acces_catph'] & 0x888);
  }


  if ( is_null($row['date_debut_catph']) && $allow_photos )
  {
    echo "<li>".$row['id_catph']." ".$row['nom_catph']." : <b>problème non solvable</b> : authorise les photos mais ne possède pas de date.</li>";
  }

  /** @todo Vérifier l'existance du parent, l'existance de l'association liée ... */

}
echo "</ul>\n";


?>
