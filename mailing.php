<?php

require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");

if ( isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on" )
{
  echo "ERROR HTTPS REQUIRED";
  exit();
}

$db = new mysqlae ();

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

$req = new requete($db,"SELECT * FROM mailing");
while ( $row = $req->get_row() )
{
    if ($row["role"] >= 2) {
        $ml[$row["nom_unix_asso"]] .= $row["email_utl"] . " ";
    }
}
foreach ($ml as $k => $v) {
    echo $k . ": ".$v;
    echo "\n";
}

