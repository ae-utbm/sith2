<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/news.inc.php");

$site = new site();

$news= new nouvelle($site->db,$site->dbrw);


$req = new requete($site->db,"SELECT * FROM nvl_nouvelles");

while ( $row = $req->get_row() )
{
  $news->_load($row);
  $news->update_references($news->resume."\n".$news->contenu);
}


?>
