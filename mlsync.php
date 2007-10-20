<?php

$topdir = "../";
require_once($topdir. "include/mysqlae.inc.php");

if ( !$GLOBALS["is_using_ssl"] )
{
  echo "ERROR: HTTPS REQUIRED";
  exit();
}

$db = new mysqlae ("rw");

$valid = new requete($db,
  "SELECT `key` ".
  "FROM `sso_api_keys` ".
  "WHERE `key` = '".mysql_real_escape_string($_REQUEST["key"])."'");

if ( $valid->lines != 1 )
{
  echo "ERROR: KEY NOT VALID\n";
  exit();
}

if ( isset($_REQUEST["done"]) )
{
  $num = intval($_REQUEST["done"]);
  new requete($db,"DELETE FROM ml_todo WHERE num_todo <= $num");
  echo "ACK ".$num;
  exit();  
}

$req = new requete($db,"SELECT * FROM ml_todo ORDER BY num_todo");

while ( $row = $req->get_row() )
{
  echo $row["num_todo"]." ".$row["action_todo"]." ".$row["ml_todo"];
  if ( !is_null($row["email_todo"] )
    echo " ".$row["email_todo"];
  echo "\n";
}

?>