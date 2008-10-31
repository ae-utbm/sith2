<?php

exit();

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/page.inc.php");

$site = new site();

$oldpage= new page($site->db);
$newpage = new page_wikized($site->db,$site->dbrw);

$req = new requete($site->db,"SELECT * FROM pages ORDER BY nom_page");

while ( $row = $req->get_row() )
{
  $oldpage->_load($row);

  $newpage->id_utilisateur = $oldpage->id_utilisateur;
  $newpage->id_groupe = $oldpage->id_groupe;
  $newpage->id_groupe_admin = $oldpage->id_groupe_admin;
  $newpage->droits_acces = $oldpage->droits_acces;

  $res = $newpage->add ( $site->user, $oldpage->nom, $oldpage->titre, $oldpage->texte, $oldpage->section );

  if ( $res )
    echo $oldpage->nom." : Sucessfully imported<br/>";
  else
    echo $oldpage->nom." : Error<br/>";
}


?>
