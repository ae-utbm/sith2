<?php

$topdir = "../";


require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/news.inc.php");
require_once($topdir . "include/cts/edt.inc.php");

class mysqlforum extends mysql 
{

  function mysqlforum ()
  {
    // Tschuut on a rien vu ...
      if ( ! $this->mysql('forum', '7v6nfzli', 'localhost', 'forum'))
	return FALSE;
     
  }
}


$site= new site ();

$site->start_page("accueil","Bienvenue");

require_once($topdir . "include/cts/toggle_tree.inc.php");

$sqlconn = new mysqlforum();
/*
$sqlconn = new mysqlforum();

$req = new requete($sqlconn,
		   "SELECT `forum_id`, `forum_name`
                    FROM `utbm_forums` WHERE `forum_main` = 0
                    ORDER BY `forum_id`");

while ($rs = $req->get_row())
{
  //     $res[] = $rs;
  $bordel[] = array('title' => utf8_encode($rs['forum_name']), 'childs' => get_childs($rs['id_forum']));
}

$site->add_contents(new contents("","<pre>". print_r($res, true) . "</pre>"));
*/

$bordel = get_childs(0);
//$site->add_contents(new contents("", "<pre>" . print_r($bordel, true) . "</pre>"));

$cts = new toggle_tree ("test",$bordel, null);

$site->add_contents($cts);


$site->end_page();


function get_childs($id)
{
  global $sqlconn;

  $req2 = new requete($sqlconn,
		      "SELECT `forum_id`, `forum_name`
                       FROM `utbm_forums` 
                       WHERE `forum_main` = $id");


      if ($req2->lines == null)
	return null;

      while ($rs = $req2->get_row())
	{
	  $bordel[] = array('title' => utf8_encode($rs['forum_name']), 'childs' => get_childs($rs['forum_id']));
	}
      return $bordel;
}
?>
