<?php
/**
 * Import de l'ancienne base vers la nouvelle
 */
$topdir="../";
require_once($topdir."include/site.inc.php");
/* on inclu tout pour voir deja si ya pas d erreur syntaxique */
require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/uv_comment.inc.php");
require_once("include/cursus.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/edt.inc.php");

/* ancien systeme */
//require_once($topdir."include/entities/uv.inc.php");

$site = new site();

  

$site->end_page();
?>
