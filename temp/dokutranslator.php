<?
/* translator wiki => doku
 */

/*
 * to be translated :
 * - news
 * - pages
 * - boxes
 */
$topdir = "../";

require_once $topdir."include/mysql.inc.php";
require_once $topdir."include/mysqlae.inc.php";

$db=new mysqlae('rw');


function translate($text)
{
  // dos2unix
  $text = str_replace("\r\n","\n",$text);
  $text = str_replace("\n\r","\n",$text);
  $text = str_replace("\r","\n",$text);
  //italic
  $text = preg_replace("/''(.+?)''/", "//$1//", $text);
  //breakline
  $text = preg_replace("/%%%/", "\\\\\\", $text);
  // bold
  $text = preg_replace("/\+\+(.+?)\+\+/", "**$1**", $text);
  $text = preg_replace("/__(.+?)__/", "**$1**", $text);
  // deleted
  $text = preg_replace("/--(.+?)--/", "<del>$1</del>", $text);

  // tables and titles
  $text = preg_replace("/@(.+?)@([0-9])>>/", "@$1@", $text);

  $lines = split("\n",$text);
  for ($l=0; $l<count($lines); $l++)
  {
    $line = $lines[$l];
    $line = preg_replace("/\|\|/", "|", preg_replace("/^\|\|(.+?)$/","| $1 |",$line));
    $line = preg_replace("/^\!\!\!(.+?)$/", "====== $1 ======", $line);
    $line = preg_replace("/^\!\!(.+?)$/", "===== $1 =====", $line);
    $line = preg_replace("/^\!(.+?)$/", "==== $1 ====", $line);
    $line = preg_replace("/^-(.+?)$/", "  * $1", $line);
    $line = preg_replace("/^\*(.+?)$/", "  * $1", $line);
    $line = preg_replace("/^\#(.+?)$/", "  * $1", $line);

    // images
    $line = preg_replace("/\(\((.+?)\|(.+?)\|d\|(.+?)\)\)/i", "PHRO $1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|r\|(.+?)\)\)/i", "PHRO $1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|g\|(.+?)\)\)/i", "PHRO$1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|l\|(.+?)\)\)/i", "PHRO$1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|d\)\)/i", "PHRO $1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|r\)\)/i", "PHRO $1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|g\)\)/i", "PHRO$1|$2 PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\|l\)\)/i", "PHRO$1|$2 PHRF", $line);
    $line = preg_replace("/\(\((.+?)\|(.+?)\)\)/", "PHRO$1|$2PHRF", $line);
    $line = preg_replace("/\(\((.+?)\)\)/", "PHRO$1PHRF", $line);

    // links
    if (preg_match("/PHRO(.+?)PHRF/",$line))
    {
      $line = preg_replace("/\[\s*PHRO(.+?)PHRF\s*\|(.+?)\]/","ORHP$2|PHRO$1PHRFFRHP",$line);
    }
    else
    {
      $line = preg_replace("/\[(.+?)\|(.+?)\|(.+?)\|(.+?)\]/", "ORHP$2|$1FRHP", $line);
      $line = preg_replace("/\[(.+?)\|(.+?)\|(.+?)\]/", "ORHP$2|$1FRHP", $line);
      $line = preg_replace("/\[(.+?)\|(.+?)\]/", "ORHP$2|$1FRHP", $line);
      $line = preg_replace("/\[(.+?)\]/", "ORHP$1FRHP", $line);
    }

    $line = preg_replace("/PHRO/", "{{", $line);
    $line = preg_replace("/PHRF/", "}}", $line);
    $line = preg_replace("/ORHP/", "[[", $line);
    $line = preg_replace("/FRHP/", "]]", $line);

    $lines[$l] = $line;
  }
  $text = join("\n",$lines);

  //ancres : bein y'a pas dans mon parser donc ...
  $text = preg_replace("/~~(.+?)~~/", "", $text);

  return $text;
}


echo "<pre>\n";

/* ------------------------------------- NEWS ------------------------------------- */
echo "============================ Performing news translation ... ============================\n";
$req=new requete($db,
                 "SELECT `id_nouvelle`, `resume_nvl`, `contenu_nvl`
                  FROM `nvl_nouvelles` ORDER BY `id_nouvelle`");

if ($req->lines >0)
{
	while ($row = $req->get_row())
	{
    echo ">> dealing with news id : ".$row['id_nouvelle']."\n";
    $content = translate($row['contenu_nvl']);
    $resume = translate($row['resume_nvl']);
    $req2 = new update ($db,
                        "nvl_nouvelles",
                        array ("resume_nvl" => $resume,
                               "contenu_nvl" => $content),
                        array("id_nouvelle"=>$row['id_nouvelle'])
                       );

    echo "   - done\n";
  }
}


/* ------------------------------------- PAGES ------------------------------------- */
/*
echo "\n\n";
echo "============================ Performing pages translation ... ============================\n";
$table ="pages";
$req=new requete($db,
                 "SELECT `id_page`, `texte_page`
                  FROM `pages` ORDER BY `id_page`");

if ($req->lines >0)
{
  while ($row = $req->get_row())
  {
    echo ">> dealing with page id : ".$row['id_page']."\n";
    $contenu = translate($row['texte_page']);
    $req2 = new update ($db,
                        "pages",
                        array ("texte_page" => $contenu),
                        array("id_page"=>$row['id_page'])
                       );
    echo "   - done\n";
  }
}



echo "\n\n";
*/
/* ------------------------------------- BOXES ------------------------------------- */
/*
echo "============================ Performing boxes translation ... ============================\n";
$table = "site_boites";
$req=new requete($db,
                 "SELECT `nom_boite`, `contenu_boite`
                  FROM `site_boites` ORDER BY `nom_boite`");

if ($req->lines >0)
{
  while ($row = $req->get_row())
  {
    echo ">> dealing with box : ".$row['nom_boite']."\n";
    $contenu = translate($row['contenu_boite']);
    $req2 = new update ($db,
                        "site_boites",
                        array ("contenu_boite" => $contenu),
                        array("nom_boite"=>$row['nom_boite'])
                       );
    echo "   - done\n";
  }
}



echo "\n\n";
echo "============================ END ============================\n";


echo "</pre>\n";
*/
?>
