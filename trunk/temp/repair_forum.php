<?php

$topdir="../";

require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");
require_once ($topdir . "include/entities/std.inc.php");
require_once ($topdir . "include/entities/forum.inc.php");

$sql = new mysqlae ("rw");

$forum = new forum($sql,$sql);

$req = new requete($sql,"SELECT * FROM frm_forum");

echo "<pre>";

while ( $row = $req->get_row() )
{
  echo "update_last_sujet ".$row['titre_forum']."\n";
  $forum->_load($row);
  $forum->update_last_sujet();
}

echo "</pre>";

?>
