<?
// Exceptionnellement encod� en ISO Latin 1 (encodage du PG)


$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/mysqlpg.inc.php");

$dbpg = new mysqlpg ();

$req = new requete($dbpg,"TRUNCATE TABLE `pg_motclef`");
$req = new requete($dbpg,"TRUNCATE TABLE `pg_motclef_cat3`");
$req = new requete($dbpg,"TRUNCATE TABLE `pg_motclef_liste`");

$motclefs = array();
$nalnum = "\. _\n\r,;:'\!\?\(\)\-";
function extract_matching_liste ( $text, $id )
{
  global $motclefs, $dbpg,$nalnum;

  while ( eregi("(^|[$nalnum])([^$nalnum]+)($|[$nalnum])(.*)",$text,$regs) )
  {
    $text = $regs[4];

    $motclef = strtolower($regs[2]);
    $base = $motclef;

    $motclef = ereg_replace("(e|�|�|�|�|�|�|�|�)","e",$motclef);
    $motclef = ereg_replace("(a|�|�|�|�|�|�)","a",$motclef);
    $motclef = ereg_replace("(i|�|�|�|�)","i",$motclef);
    $motclef = ereg_replace("(c|�|�)","c",$motclef);
    $motclef = ereg_replace("(u|�|�|�|�|�|�)","u",$motclef);
    $motclef = ereg_replace("(n|�|�)","n",$motclef);

    if ( isset($motclefs[$motclef]) )
      $id_motclef = $motclefs[$motclef];

    else
    {
		  $sql = new insert ($dbpg,"pg_motclef",array("nom_motclef"=>$motclef,"titre_motclef"=>$base));
			$id_motclef = $sql->get_id();
			$motclefs[$motclef] = $id_motclef;
			echo "<p>$motclef</p>\n";
    }

		$sql = new insert ($dbpg,"pg_motclef_liste",array("id_motclef"=>$id_motclef,"id_liste"=>$id));

  }
}

$req = new requete($dbpg,"SELECT id,nom,description FROM pg_liste WHERE ( pg_liste.status = 1 AND pg_liste.print_web = 1 )");
while ( list($id,$nom,$description) = $req->get_row() )
{
  extract_matching_liste ( $nom, $id );
  extract_matching_liste ( $description, $id );
}

?>
