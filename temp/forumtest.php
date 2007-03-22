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
      if ( ! $this->mysql('forum', '7v6nfzli', 'localhost', 'UTBM'))
	return FALSE;
  }
}


$site= new site ();

$site->start_page("accueil","Bienvenue");

require_once($topdir . "include/cts/toggle_tree.inc.php");

$sqlconn = new mysqlforum();

$bordel = get_childs(0);


$req = new requete ($sqlconn,
		    "SELECT `forum_id`, `forum_name`
                       FROM `utbm_forums` 
                       WHERE `forum_main` = 0
                       AND `auth_read` = 0
                       AND `auth_view` = 0
                       ORDER BY `forum_order`");

while ($rs = $req->get_row())
{
  $site->add_contents(new toggle_tree($rs['forum_name'], get_childs($rs['forum_id']), null));
}

//$cts = new toggle_tree ("Forum de l'AE",$bordel, null);
//$site->add_contents($cts);


$site->end_page();


function get_childs($id)
{
  global $sqlconn;

  $req2 = new requete($sqlconn,
		      "SELECT `forum_id`, `forum_name`
                       FROM `utbm_forums` 
                       WHERE `forum_main` = $id
                       AND `auth_read` = 0
                       AND `auth_view` = 0
                       ORDER BY `forum_order`");


      if ($req2->lines == null)
	return null;

      while ($rs = $req2->get_row())
	{
	  $childs = get_childs($rs['forum_id']);

	  
	  if ($childs != null)
	    $title = "<b>" .utf8_encode($rs['forum_name']) . "</b>";

	  else
	    $title = utf8_encode($rs['forum_name']);


	  $bordel[] = array('title' => $title, 'childs' => $childs);

	}

      return $bordel;
}
?>
