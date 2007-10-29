<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/page.inc.php");

$site = new site();

new requete($site->dbrw,"TRUNCATE TABLE wiki");
new requete($site->dbrw,"TRUNCATE TABLE wiki_lock");
new requete($site->dbrw,"TRUNCATE TABLE wiki_ref_file");
new requete($site->dbrw,"TRUNCATE TABLE wiki_ref_missingwiki");
new requete($site->dbrw,"TRUNCATE TABLE wiki_ref_wiki");
new requete($site->dbrw,"TRUNCATE TABLE wiki_rev");

new insert($site->dbrw,"wiki", array (
  "id_utilisateur" => 142,
  "id_groupe" => 7,
  "id_groupe_admin" => 7,
  "droits_acces_wiki" => 1905,
  "id_wiki_parent" => NULL,
  "id_asso" => NULL,
  "id_rev_last" => 1,
  "name_wiki" => "",
  "fullpath_wiki" => "",
  "namespace_behaviour" => 1,
  "section_wiki"=>NULL));

new insert($site->dbrw,"wiki_rev", array (
  "id_wiki" => 1,
  "id_utilisateur_rev" => 142,
  "date_rev" => date("Y-m-d H:i:s"),
  "contents_rev" => "Bienvenue sur Wiki2",
  "title_rev" => "Wiki2",
  "comment_rev" => "Big-bang"));  


?>