<?
$topdir = "../";
require_once($topdir . "include/entities/utilisateur.inc.php");
require_once($topdir . "include/entities/std.inc.php");

$site = new site ();
$user = new utilisateur($site->db,$site->dbrw);
$user->load_all_by_id($_REQUEST["id_utilisateur"]);

echo $user->id."\n";
echo $user->id_ville."\n";
echo $user->id_pays."\n";
echo $user->id_ville_parents."\n";
echo $user->id_pays_parents."\n";

echo "--------\n";

$user = new utilisateur($site->db,$site->dbrw);
$user->load_by_id($_REQUEST["id_utilisateur"]);
$user->load_all_extras();

echo $user->id."\n";
echo $user->id_ville."\n";
echo $user->id_pays."\n";
echo $user->id_ville_parents."\n";
echo $user->id_pays_parents."\n";


?>
