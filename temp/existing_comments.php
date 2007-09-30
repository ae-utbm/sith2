<?php

$topdir = "../";

include($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once($topdir. "trombi/include/entities/commentaire.inc.php");

$site = new site();
$site->start_page("none", "Commentaires existants");

$cts = new contents("Commentaires existant");

$req = new requete($site->db,"SELECT * FROM trombi_commentaire WHERE 1");

$tbl = new sqltable("listcomment",
                      "Commentaires", $req, "",
                      "id_commentaire",
                      array("commentaire"=>"Commentaire"),
                      array(), array(), array( )
                    );
                    
$cts->add($tbl,true);
$site->add_contents($cts);

$site->end_page ();

?>