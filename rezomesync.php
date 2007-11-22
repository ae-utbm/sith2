<?php

$topdir = "./";
require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");

if ( $_SERVER["REMOTE_ADDR"] != "127.0.1.1" )
{
  echo "ERROR HTTPS REQUIRED";
  exit();
}

$db = new mysqlae ("rw");

if ( !$db->dbh )
{
  echo "ERROR DB UNAVAILABLE";
  exit();
}

$valid = new requete($db,
  "SELECT `key` ".
  "FROM `sso_api_keys` ".
  "WHERE `key` = '".mysql_real_escape_string($_REQUEST["key"])."'");

if ( $valid->lines != 1 )
{
  echo "ERROR KEY NOT VALID\n";
  exit();
}

?>
