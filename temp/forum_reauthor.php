<?

$topdir = "../";

require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

$sqlae = new mysqlae ("rw");

$u="Kai-Kai";
$uid=1909;

$key = "Message originellement posté par ".$u."\n\n";

$sql = new requete($sqlae,"SELECT id_message,contenu_message FROM frm_message WHERE id_utilisateur IS NULL AND contenu_message LIKE '".mysql_real_escape_string($key)."%'");

while ( $row = $sql->get_row() )
{
  $msg = substr($row["contenu_message"],strlen($key));

  echo "\"$msg\"<br/><br/><br/>";



  new update($sqlae,"frm_message",array("id_utilisateur"=>1909,"contenu_message"=>$msg),array("id_message"=>$row["id_message"]));



}


?>
