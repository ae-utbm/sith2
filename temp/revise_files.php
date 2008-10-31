<?php

$topdir="../";
require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");

$dbrw = new mysqlae ("rw");

new requete($dbrw,"TRUNCATE TABLE d_file_rev");

$req = new requete($dbrw,"SELECT * FROM d_file");

while ( $row = $req->get_row() )
{
  echo "Processing file ".$row["id_file"]."<br/>";

  $sql = new insert($dbrw, "d_file_rev", array(
	  "id_file"=>$row["id_file"],
	  "id_utilisateur_rev_file"=>$row["id_utilisateur"],
	  "date_rev_file"=>$row["date_modif_file"],
	  "filesize_rev_file"=>$row["taille_file"],
	  "mime_type_rev_file"=>$row["mime_type_file"]));

  $id_rev_file = $sql->get_id();

  new update ($dbrw,"d_file",array("id_rev_file_last"=>$id_rev_file),array("id_file"=>$row["id_file"]));

  if ( file_exists($topdir."var/files/".$row["id_file"]) )
  {
    if ( file_exists($topdir."var/files/".$row["id_file"].".".$id_rev_file))
      unlink($topdir."var/files/".$row["id_file"].".".$id_rev_file);

    copy($topdir."var/files/".$row["id_file"],$topdir."var/files/".$row["id_file"].".".$id_rev_file);
  }
  else
    echo "WARNING: no such file<br/>";
}

?>
