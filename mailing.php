<?php

$topdir = "./";

require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");
require_once($topdir. "include/entities/mailing.inc.php");
require_once($topdir. "include/entities/utilisateur.inc.php");

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
    $mailing = new mailing($db);
    $mailing->load_by_id($row['id_mailing']);
    echo $mailing->get_full_name(),": ";
    foreach($mailing->get_subscribed_user() as $user_id) {
        $user = new utilisateur($db);
        $user->load_by_id($user_id);
        echo $user->email," ";
    }
    foreach($mailing->get_subscribed_email() as $mail)
        echo $mail,"  ";
    echo "\n";
}
