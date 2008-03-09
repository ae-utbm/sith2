<?
$topdir = "../";

require_once($topdir . "include/site.inc.php");

$base = new mysqlae();

$req = new requete($base, "SELECT * FROM utilisateurs WHERE `nom_utl` = 'SFSMLVKDNFLBKM'");

print_r($req);

?>