<?php
/** @file
 *
 * @brief Outil de rétrocompatibilité PhpBB / SiteAE
 *  pedrov pour AE R&D.
 *
 */

/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "../";


require_once($topdir . "include/site.inc.php");
require_once($topdir . "include/cts/toggle_tree.inc.php");
require_once($topdir . "include/lib/bbcode.inc.php");


$sqlconn = new mysqlforum();


if (isset($_REQUEST['f']))
{
  $fm = intval($_REQUEST['f']);

  $title_forum = new requete($sqlconn, "SELECT `forum_name`
                                        FROM `utbm_forums`
                                        WHERE `forum_id` = $fm");

  $title_forum = $title_forum->get_row();
  $title_forum = $title_forum[0];

      echo "<h1>" . utf8_encode($title_forum) . "</h1>\n";



  if ($_REQUEST['page'])
    $page = intval($_REQUEST['page']) * 30;
  else
    $page = 0;

  /* we mimic next request in order to get the same number of results */
  $nbpage = new requete($sqlconn, "SELECT COUNT(`topic_id`) FROM `utbm_topics` 
                                   INNER JOIN `utbm_users` AS `user1` ON `user1`.`user_id` = `utbm_topics`.`topic_poster`
                                   INNER JOIN `utbm_users` AS `user2` ON `user2`.`user_id` = `utbm_topics`.`topic_last_poster`
                                   WHERE `utbm_topics`.`forum_id` = $fm");
  $np = $nbpage->get_row();
  $np = $np[0];
  
  $req = new requete($sqlconn,
		     "SELECT `utbm_topics`.`topic_id`
                           , `utbm_topics`.`topic_title`
                           , `user1`.`username` AS `initiator_username`
                           , `user2`.`username` AS `lastposter_username`
                          FROM `utbm_topics`
                          INNER JOIN `utbm_users` AS `user1` ON `user1`.`user_id` = `utbm_topics`.`topic_poster`
                          INNER JOIN `utbm_users` AS `user2` ON `user2`.`user_id` = `utbm_topics`.`topic_last_poster`
                          WHERE `utbm_topics`.`forum_id` = $fm
                          ORDER BY `topic_last_time` DESC
                          LIMIT $page, 30");
  if ($req->lines == 0)
    return;

  echo "<ul>";
      
  while ($rs = $req->get_row())
    {
      echo "<li><b><a href=\"javascript:thread_display(".$rs['topic_id'].", -1)\">".utf8_encode($rs['topic_title'])."</a></b>, \n";
      echo "Par <i>" . utf8_encode($rs['initiator_username']) . "</i>, dernière participation par <i>".
	utf8_encode($rs['lastposter_username'])."</i></li>\n";
    }
  echo "</ul>\n";
 
  /* pagination */
  echo "<br /><p style=\"text-align: right;\">";
  if ($np > 30)
    {
      echo "pages : ";
      
      $p = ($page / 30);
      $ttp = ($np / 30);
      
      for ($i = 0; $i < $ttp ; $i++)
	{
	  if ($i == $p)
	    {
	      echo "<b>". ($i+1)." </b>";
	      continue;
	    }
	  echo "<a href=\"javascript:forum_display(". $fm .", ".$i.")\">". ($i+1)." </a>";
	}
      
      echo ($p > 0 ? "  | <a href=\"javascript:forum_display('". $fm ."','". ($p - 1) ."')\">Précédente</a> | " : "") .
	($p < ($ttp - 1) ? "<a href=\"javascript:forum_display('". $fm ."','". ($p + 1) ."')\">Suivante</a> | " : "");

    }

  echo " | <a href=\"javascript:floatfrm_close()\">Fermer</a></p>\n";
  return;
}

if (isset($_REQUEST['t']))
{
      $thread = intval($_REQUEST['t']);

      $title_topic = new requete($sqlconn, "SELECT `topic_title`
                                            FROM `utbm_topics`
                                            WHERE `topic_id` = $thread");

      $title_topic = $title_topic->get_row();
      $title_topic = $title_topic[0];

      echo "<h1>" . utf8_encode($title_topic) . "</h1>\n";

      $nbpage = new requete($sqlconn, "SELECT COUNT(`post_id`) 
                                       FROM `utbm_posts` 
                                       INNER JOIN `utbm_posts_text` USING(`post_id`)
                                       INNER JOIN `utbm_users` ON `utbm_users`.`user_id` = `utbm_posts`.`poster_id` 
                                       WHERE `topic_id` = $thread");
      $np = $nbpage->get_row();
      $np = $np[0];

      /* dans le cas d'une selection par defaut (-1), on va direct à la dernière page */
      if ($_REQUEST['page'] >= 0)
	$page = intval($_REQUEST['page']) * 10;
      else
	$page =  intval($np / 10) * 10;
      
      $squiouel = new requete($sqlconn,
			      "SELECT `utbm_posts`.`post_id`
                                      , `utbm_posts_text`.`post_subject`
                                      , `utbm_posts_text`.`post_sub_title`
                                      , `utbm_posts_text`.`post_text`
                                      , `utbm_users`.`username`
                                      , `utbm_users`.`user_avatar`
                                      , `utbm_users`.`user_avatar_type`
                               FROM `utbm_posts`
                               INNER JOIN `utbm_posts_text` USING(`post_id`)
                               INNER JOIN `utbm_users` ON `utbm_users`.`user_id` = `utbm_posts`.`poster_id`
                               WHERE `utbm_posts`.`topic_id` = $thread
                               ORDER BY `utbm_posts`.`post_time` ASC
                               LIMIT $page, 10");
      if ($squiouel->lines > 0)
	{
	      	  echo "<table>\n";

	  while ($rs = $squiouel->get_row())
	    {
		  
	      if (strlen($rs['user_avatar']) > 0)
		{
		  echo "
<tr>
<td class=\"left\"><b>".utf8_encode($rs['username'])."</b><br/>".
		  "<img alt=\"avatar\" src=\"".(($rs['user_avatar_type'] == 1) ? "http://ae.utbm.fr/forum/images/avatars/" : "") .$rs['user_avatar']."\" /></td>
    <td class=\"right\"><h3>".utf8_encode($rs['post_subject'])."</h3>
<p>".bbcode(utf8_encode($rs['post_text']))."</p></td>
</tr>
";   
		}
	      else
		echo "
<tr><td class=\"left\"><b>".
		  utf8_encode($rs['username'])."</b></td>
    <td class=\"right\"><h3>".utf8_encode($rs['post_subject'])."</h3>
<p>".bbcode(utf8_encode($rs['post_text']))."</p></td>
</tr>
";

	    }
	      	  echo "</table>\n";

	}


      echo "<br /><p style=\"text-align: right;\">";
      if ($np > 10)
	{
	  echo "pages : ";
	  $p = ($page / 10);
	  $ttp = ($np / 10);
	  
	  for ($i = 0; $i < $ttp ; $i++)
	    {
	      if ($i == $p)
		{
		  echo "<b>". ($i+1)." </b>";
		  continue;
		}
	      echo "<a href=\"javascript:thread_display(". $thread .", ".$i.")\">". ($i+1)." </a>";
	    }
      
	  echo ($p > 0 ? "  | <a href=\"javascript:thread_display('". $thread ."','". ($p - 1) ."')\">Précédente</a> | " : "") .
	    ($p <= ($ttp - 1) ? "<a href=\"javascript:thread_display('". $thread ."','". ($p + 1) ."')\">Suivante</a> | " : "");

	  //	  for ($i = 0; $i < $np; $i += 10)
	  //  echo ("<a href=\"javascript:thread_display(". $thread .", ". ($i / 10) .")\">". ($i / 10 + 1) ."</a>\n");
	}
            echo "| <a href=\"javascript:thread_close()\">Fermer</a></p>\n";

      return;
}

$site= new site ();

$site->start_page("forum","Forum de l'AE");



$scripts .=
"
<style type=\"text/css\">

#cts1 table
{
  margin-bottom: 5px; 
  padding: 0;
}

#cts1 tr
{
  margin-bottom: 5px;
}

#cts1 td.left
{
  width: 90px; 
  vertical-align: top;
  border: 1px #374a70 solid; 
  background-color: #ecf4fe;
}

#cts1 td.right
{
  border: 1px #374a70 solid;
  padding: 5px;
}

#cts1 li
{
  margin-bottom: 0.40em;
}

</style>


<script type=\"text/javascript\">

document.getElementById(\"cts1\").style.display = \"none\";

</script>\n";


// Ce contents ne sert à rien, juste
// à englober tout mon bordel.
$site->add_contents(new contents("", "<center><h1>Forum de l'AE : Par les étudiants, pour les étudiants, ".
				 "lue par des millions de lecteurs, et même par des spammeurs ...</h1></center>\n" . $scripts));




$bordel = get_childs(0);


$req = new requete ($sqlconn,
		    "SELECT `forum_id`, `forum_name`
                       FROM `utbm_forums` 
                       WHERE `forum_main` = 0
                       AND `auth_read` = 0
                       AND `auth_view` = 0
                       AND `forum_id` != 207
                       ORDER BY `forum_order`");

while ($rs = $req->get_row())
{
  $site->add_contents(new toggle_tree($rs['forum_name'], get_childs($rs['forum_id']), null));
}



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

	  /* enfants ? */
	  if ($childs != null)
	    {
	      $title = "<b>" .utf8_encode($rs['forum_name']) . "</b>";
	      $jsonclick = null;
	    }
	  /* pas d'enfant */
	  else
	    {
	      $title = utf8_encode($rs['forum_name']);
	      $jsonclick = "javascript:forum_display(".$rs['forum_id'].", 0);";
	    }

	  $bordel[] = array('title' => $title, 'childs' => $childs, 'jsOnclick' => $jsonclick);

	}

      return $bordel;
}

?>
