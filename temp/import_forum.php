<?

/**
 *
 * Importation PHPbb -> AE2 
 *
 * Pedrov pour AE R&D
 *
 */


$topdir = "../";

require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");


$sqlf = new mysqlforum ();
$sqlae = new mysqlae ("rw");


$ae2_f_tables = array("frm_forum","frm_message","frm_sujet_utilisateur","frm_sujet");
		 

echo "<pre>\n";

/******* STEP 1 *****/

echo "<b>emptying AE2 forum tables ...</b>";

foreach ($ae2_f_tables as $table)
     new requete($sqlae,"TRUNCATE TABLE `".$table."`");

echo " Done.\n";

/******* STEP 2 *****/
echo "\n";
echo "<b>Fetching forums from old base ...</b>\n";

$id_forum_old_to_new = array();

function import_forum ( $id_forum_parent, $phpbb_id, $name, $description, $order  )
{
  global $sqlf,$sqlae,$id_forum_old_to_new;
  $my_id=0;
  
  $req2 = new requete($sqlf,
		      "SELECT `forum_id`, `forum_name`, `forum_order`, `forum_desc`
                       FROM `utbm_forums` 
                       WHERE `forum_main` = $phpbb_id
                       AND `auth_read` = 0
                       AND `auth_view` = 0
                       ORDER BY `forum_order`");
                       
  $categorie= 0;
  
  if ( $req2->lines > 0 )
    $categorie=1;
                       
  $req = new insert ($sqlae,
            "frm_forum", array(
              "titre_forum"=>utf8_encode($name),
              "description_forum"=>utf8_encode($description),
              "categorie_forum"=>$categorie,
              "id_forum_parent"=>$id_forum_parent,
              "id_asso"=>null,
              "id_utilisateur"=>null,
              "id_groupe"=>31,
              "id_groupe_admin"=>31,
              "droits_acces_forum"=>0xFDD,
              "id_sujet_dernier"=>null,
              "nb_sujets_forum"=>0,
              "ordre_forum"=>$order
            ));

  echo "Added ".$name." ($categorie)\n";

  $my_id = $req->get_id();

  if ( !$categorie )
    $id_forum_old_to_new[$phpbb_id] = $my_id;

  while ($rs = $req2->get_row())
    import_forum($my_id,$rs['forum_id'],$rs['forum_name'],$rs['forum_desc'],$rs['forum_order']);

}

$req = new requete ($sqlf,
		    "SELECT `forum_id`, `forum_name`, `forum_order`, `forum_desc`
                     FROM `utbm_forums` 
                     WHERE `forum_main` = 0
                       AND `auth_read` = 0
                       AND `auth_view` = 0
                       AND `forum_id` != 207
                     ORDER BY `forum_order`");
                     
$req2 = new insert ($sqlae,
          "frm_forum", array(
            "titre_forum"=>"Forum",
            "description_forum"=>"",
            "categorie_forum"=>1,
            "id_forum_parent"=>null,
            "id_asso"=>null,
            "id_utilisateur"=>null,
            "id_groupe"=>31,
            "id_groupe_admin"=>31,
            "droits_acces_forum"=>0xFDD,
            "id_sujet_dernier"=>null,
            "nb_sujets_forum"=>0,
            "ordre_forum"=>0
          ));
          
$my_id = $req2->get_id();


while ($rs = $req->get_row())
  import_forum($my_id,$rs['forum_id'],$rs['forum_name'],$rs['forum_desc'],$rs['forum_order']);

echo " Done.\n";

$id_utilisateur_old_to_new = array();
$old_user_names = array();

/******* STEP 3 *****/
echo "\n";
echo "<b>Building translation table for users ...</b>\n";

$req = new requete ($sqlf,"SELECT `user_id`, `username`, `user_email` FROM utbm_users");

while ($row = $req->get_row())
{
  echo "Look for ".$row['username']." (".$row['user_email'].") ";
  
  $old_user_names[$row['user_id']]=$row['username'];

  $sreq = new requete($sqlae, "SELECT utilisateurs.id_utilisateur,nom_utl,prenom_utl FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
				"WHERE `utilisateurs`.`email_utl` = '" . mysql_real_escape_string($row['user_email']) . "' OR " .
				"`utl_etu_utbm`.`email_utbm` = '" . mysql_real_escape_string($row['user_email']) . "' " .
				"LIMIT 1");
				
	if ( $sreq->lines == 1 && $row['user_email'] != "" )
	{
	  list($id,$nom,$prenom) = $sreq->get_row();
    echo ": it is $prenom $nom";
    $id_utilisateur_old_to_new[$row['user_id']] = $id;
	}
  else
  {
    
    $sreq = new requete($sqlae, "SELECT id_utilisateur,nom_utl,prenom_utl FROM `utilisateurs` ".
				"WHERE `alias_utl` = '" . mysql_real_escape_string($row['username']) . "' ");
				
	  if ( $sreq->lines == 1 )
	  {
	    list($id,$nom,$prenom) = $sreq->get_row();
      echo ": it is $prenom $nom";
      $id_utilisateur_old_to_new[$row['user_id']] = $id;
	  }
    else
    {
      echo ": <b>none match</b>";
    }
  }
  echo "\n";
}
echo " Done.\n";

// Yak
$id_utilisateur_old_to_new[8] = 4;

/******* STEP 4 *****/
echo "\n";
echo "<b>Importing topics and messages ...</b>\n";

$req = new requete ($sqlf,"SELECT `topic_id`, `forum_id`, `topic_title`, `topic_poster`, `topic_time`, `topic_sub_title`, `topic_duration` FROM utbm_topics ORDER BY `topic_time`");

while ($row = $req->get_row())
{
  if ( isset($id_forum_old_to_new[$row['forum_id']]) )
  {
    $id_forum = $id_forum_old_to_new[$row['forum_id']];
    
    $id_utilisateur = null;
    if ( isset($id_utilisateur_old_to_new[$row['topic_poster']]) )
      $id_utilisateur = $id_utilisateur_old_to_new[$row['topic_poster']];
    
    $areq = new insert ($sqlae,
              "frm_sujet", array(
                "id_utilisateur"=>$id_utilisateur,
                "id_forum"=>$id_forum,
                "titre_sujet"=>utf8_encode($row['topic_title']),
                "soustitre_sujet"=>utf8_encode($row['topic_sub_title']),
                "type_sujet"=>1,
                "icon_sujet"=>"",
                "date_sujet"=>date("Y-m-d H:i:s",$row['topic_time']),
                "id_message_dernier"=>null,
                "nb_messages_sujet"=>0,
                "date_fin_annonce_sujet"=>$row['topic_duration']<1?null:date("Y-m-d H:i:s",$row['topic_duration']),
                "id_utilisateur_moderateur"=>4, /*YAK*/
                "id_nouvelle"=>null,
                "id_catph"=>null,
                "id_sondage"=>null
              ));
              
    $id_sujet = $areq->get_id();

    $preq = new requete ($sqlf,"SELECT * FROM utbm_posts INNER JOIN utbm_posts_text USING(post_id) WHERE topic_id=".$row['topic_id']." ORDER BY `post_time`");
    $count=0;
    $last=null;
    while ($row = $preq->get_row())
    {
      $id_utilisateur = null;
      $text = str_replace(":".$row['bbcode_uid'],"",$row['post_text']);
      $text = utf8_encode(str_replace("[quot=","[quote=",$text));      
      
      if ( isset($id_utilisateur_old_to_new[$row['poster_id']]) )
        $id_utilisateur = $id_utilisateur_old_to_new[$row['poster_id']];      
      elseif ( isset($old_user_names[$row['poster_id']]) )
      {
        $text = "Message originellement posté par ".$old_user_names[$row['poster_id']]."\n\n".$text;
      }

      $areq = new insert ($sqlae,
            "frm_message", array(
              "id_utilisateur"=>$id_utilisateur,
              "id_sujet"=>$id_sujet,
              "titre_message"=>utf8_encode($row['post_subject']),
              "contenu_message"=>$text, // enleve les bbcode_uid 
              "date_message"=>date("Y-m-d H:i:s",$row['post_time']),
              "syntaxengine_message"=>"bbcode",
              "id_utilisateur_moderateur"=>4
            ));

      $count++;
      $last = $areq->get_id();
    }
    
    $ureq = new update ($sqlae, "frm_sujet", 
        array("id_message_dernier"=> $last,"nb_messages_sujet"=>$count),
        array("id_sujet"=>$id_sujet) );      
  }
}

echo " Done.\n";

/******* STEP 5 *****/
echo "\n";
echo "<b>Update pre-calculated fields...</b>\n";

require_once ($topdir . "include/entities/std.inc.php");
require_once ($topdir . "include/entities/forum.inc.php");

$forum = new forum($sqlae,$sqlae);

foreach ( $id_forum_old_to_new as $id_forum )
{
  $forum->load_by_id($id_forum);
  $forum->update_last_sujet();
}
echo " Done.\n";


echo "</pre>\n";



?>
