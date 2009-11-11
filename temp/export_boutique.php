<?
require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

$db = new mysqlae ();

$req = new requete($db,
'SELECT id_utilisateur, nom_utl, prenom_utl, email_utl, pass_utl, hash_utl, addresse_utl
FROM utilisateurs
WHERE type = \'srv\'');

while(list($id,$nom,$prenom,$email,$pass,$hash,$adresse)=$req->get_row())
{
  echo "INSERT INTO utilisateurs (id_utilisateur, nom_utl, prenom_utl, email_utl, pass_utl, hash_utl, adresse_utl) VALUES('$id','$nom','$prenom','$email','$pass','$hash','$adresse');\n";
}

?>
