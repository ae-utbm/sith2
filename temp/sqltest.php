<?
$topdir = "../";

require_once($topdir . "include/site.inc.php");

$base = new mysqlae();

$req = new requete($base, "SELECT `id_comment` FROM `edu_uv_comments` WHERE `state_comment` = 1");

print_r($req);

?>
