<?

$topdir = "../";

require_once ($topdir . "include/site.inc.php");

$db = new mysqlae("rw");

$req = "SELECT `id_uv`, `id_utilisateur`, `note_obtention_uv`
          FROM `edu_uv_comments`";


$req = new requete($db, $req);

if ($req->lines > 0)
{
  while ($rs = $req->get_row())
    {
      new insert($db, "edu_uv_obtention",
		 array("id_uv" => $rs['id_uv'],
		       "id_etudiant" => $rs['id_utilisateur'],
		       "note_obtention" => $rs['note_obtention_uv']));
    }
}


?>
