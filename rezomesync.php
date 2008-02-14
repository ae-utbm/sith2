<?php

$topdir = "./";
require_once($topdor. "include/site.inc.php");
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

function testLogin($message)
{
  $simplexml = new SimpleXMLElement($message->str);
  $login = $simplexml->param[0];
  $password = $simplexml->param[1];

  $site = new site();

  $site->user->load_by_email($login);

  if($site->user->is_valid())
  {
    if($site->user->is_password($password))
      $return = true;
    else
      $return = false;
  }
  else
  {
    $return = false;
  }
  
  $response = <<<XML
<testLoginResponse>
  <result>$return</result>
</testLoginResponse>
XML;
  
  return array("return" => $response);
}

function inscription($message)
{

}

$service = new WSService(array("operations" => array("testLogin", "inscription")));
$service->reply();

?>
