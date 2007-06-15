<?php

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/forum.inc.php");
require_once($topdir . "include/entities/sujet.inc.php");
require_once($topdir . "include/entities/message.inc.php");

require_once($topdir . "include/entities/news.inc.php");
require_once($topdir . "include/entities/sondage.inc.php");
require_once($topdir . "sas2/include/cat.inc.php");

require_once($topdir . "include/cts/forum.inc.php");

$site = new site ();

$site->add_css("css/forum.css");

$forum = new forum($site->db,$site->dbrw);
$pforum = new forum($site->db);
$sujet = new sujet($site->db,$site->dbrw);
$message = new message($site->db,$site->dbrw);

// Chargement des objets
if ( isset($_REQUEST["id_message"]) )
{
  $message->load_by_id($_REQUEST["id_message"]);
  if ( $message->is_valid() )
  {
    $sujet->load_by_id($message->id_sujet); 
    $forum->load_by_id($sujet->id_forum); 
  }
}
elseif ( isset($_REQUEST["id_sujet"]) )
{
  $sujet->load_by_id($_REQUEST["id_sujet"]); 
  if ( $sujet->is_valid() )
  {
    $forum->load_by_id($sujet->id_forum); 
  }
}
elseif ( isset($_REQUEST["id_forum"]) )
{
  $forum->load_by_id($_REQUEST["id_forum"]); 
}

if ( !$forum->is_valid() )
  $forum->load_by_id(1); // Le forum id=1 est la racine

if ( !$forum->is_right($site->user,DROIT_LECTURE) )
{
  header("Location: ".$wwwtopdir);
  exit();
}



if ( $_REQUEST["action"] == "setallread" )
{
  $site->allow_only_logged_users("forum");
  $site->user->set_all_read();
}


/* postage d'un nouveau sujet */
if ( $_REQUEST["action"] == "post" && !$forum->categorie )
{
  $site->allow_only_logged_users("forum");
  
  $_REQUEST["page"]="post";
  
  if ( !$_REQUEST["titre_sujet"] )
    $Erreur="Veuillez préciser un titre";
    
  elseif ( !$_REQUEST["subjtext"] )
    $Erreur="Veuillez saisir le texte du message";
  
  elseif ( $GLOBALS['svalid_call'] )
  {
  
    $type=SUJET_NORMAL;
    $date_fin_annonce=null;
    
    if ( $forum->is_admin($site->user) )
    {
      $type = $_REQUEST["subj_type"];
      if ( $type == SUJET_ANNONCESITE && 
        !$site->user->is_in_group("moderateur_forum") && 
        !$site->user->is_in_group("root") )
      {
        $type = SUJET_ANNONCE;
        $date_fin_annonce=$_REQUEST["date_fin_announce_site"];
      }  
      elseif ( $type == SUJET_ANNONCE )
        $date_fin_annonce=$_REQUEST["date_fin_announce"];
        
      elseif ( $type == SUJET_ANNONCESITE )
        $date_fin_annonce=$_REQUEST["date_fin_announce_site"];
    }
    
    $news = new nouvelle($site->db);
    $catph = new catphoto($site->db);
    $sdn = new sondage($site->db);
    
    if ( isset($_REQUEST["id_nouvelle"]) )
      $news->load_by_id($_REQUEST["id_nouvelle"]);
      
    elseif ( isset($_REQUEST["id_catph"]) )
      $catph->load_by_id($_REQUEST["id_catph"]);
  
    elseif ( isset($_REQUEST["id_sondage"]) )
      $sdn->load_by_id($_REQUEST["id_sondage"]);
  
    $sujet->create ( $forum, $site->user->id, $_REQUEST["titre_sujet"], $_REQUEST["soustitre_sujet"],
        $type,null,$date_fin_annonce,
        $news->id,$catph->id,$sdn->id );
        
    $message->create($forum,
				    $sujet,
				    $site->user->id,
				    $_REQUEST['titre_sujet'],
				    $_REQUEST['subjtext'],
				    $_REQUEST['synengine']);
  }
}
    
if ( $_REQUEST['page'] == 'delete' )
{
  $site->allow_only_logged_users("forum");
  if ( $message->is_valid() )
  {
    /* pas de confirmation à la suppression */
    if (($forum->is_admin($site->user))
        || ($message->id_utilisateur == $site->user->id))
    {
      $message_initial = new message($site->db);
      $message_initial->load_initial_of_sujet($sujet->id);   
         
      if ( $message_initial->id == $message->id ) // La supression du message initial, entraine la supression du sujet
        $sujet->delete($forum);
      else
        $ret =$message->delete($forum, $sujet);
        
      $cts = new contents("Suppression d'un message",
  		  "Message supprimé avec succès.");
    }
    else
      $cts = new contents("Suppression d'un message",
  			"Vous n'avez pas les autorisations nécessaires pour supprimer ce message.");
  			
    $site->add_contents($cts);
  }
  elseif ( $sujet->is_valid() )
  {
    if (($forum->is_admin($site->user))
        || ($sujet->id_utilisateur == $site->user->id))
    {
      $ret =$sujet->delete($forum);
      $cts = new contents("Suppression d'un sujet",
  		  "Sujet supprimé avec succès.");
    }
    else
      $cts = new contents("Suppression d'un Sujet",
  			"Vous n'avez pas les autorisations nécessaires pour supprimer ce sujet.");
  			
    $site->add_contents($cts);
  }
}

if ( $sujet->is_valid() )
  $path = $forum->get_html_link()." / ".$sujet->get_html_link();
else
  $path = $forum->get_html_link();

$pforum->load_by_id($forum->id_forum_parent);
while ( $pforum->is_valid() )
{
  $path = $pforum->get_html_link()." / ".$path;
  $pforum->load_by_id($pforum->id_forum_parent);
}
  
if ( $sujet->is_valid() )
{

  /* Edition d'un message (pour cela il faut que le sujet soit valide) */
  if ($_REQUEST['page'] == 'edit')
  {
    $site->allow_only_logged_users("forum");
    
    if ( $message->is_valid() ) // On edite un message
    {
      if ($message->id_utilisateur != $site->user->id && !$forum->is_admin($site->user))    
        $site->error_forbidden("forum","group");
      
      $site->start_page("forum",$sujet->titre);

      $frm = new form("frmedit", 
    		  "?page=commitedit&amp;id_sujet=".
    		  $sujet->id."&amp;".
    		  "id_message=".$message->id, 
    		  true);
      
      $frm->add_text_field("title", "Titre du message : ", $message->titre,false,80);
      $frm->add_select_field('synengine',
    			 'Moteur de rendu : ',
    			 array('bbcode' => 'bbcode (type phpBB)','doku' => 'Doku Wiki (recommandé)'),
    			 $message->syntaxengine);
      $frm->add_text_area("text", "Texte du message : ",$message->contenu,80,20);
      $frm->add_submit("submit", "Modifier");
      $frm->allow_only_one_usage();
      
      $cts = new contents($path." / Edition");
    
      $cts->add($frm);

      $site->add_contents($cts);
      $site->end_page();
      exit();
    }
    
    // On edite le sujet
    
    if ($sujet->id_utilisateur != $site->user->id && !$forum->is_admin($site->user))    
      $site->error_forbidden("forum","group");
      
    // Recupération du premier message du sujet
    $message->load_initial_of_sujet($sujet->id);  
      
    $site->start_page("forum",$sujet->titre);
    $cts = new contents($path." / Edition");
  
    $frm = new form("frmedit", 
  		  "?page=commitedit&amp;id_sujet=".$sujet->id, 
  		  true);
    
    if ( $forum->is_admin($site->user) )
    {
      if ( !$sujet->type ) 
        $sujet->type = SUJET_NORMAL;
      
      $sfrm = new form("subj_type",null,null,null,"Sujet normal");
      $frm->add($sfrm,false,true, $sujet->type==SUJET_NORMAL ,SUJET_NORMAL ,false,true);
      
      $sfrm = new form("subj_type",null,null,null,"Sujet épinglé, il sera toujours affiché en haut");
      $frm->add($sfrm,false,true, $sujet->type==SUJET_STICK ,SUJET_STICK ,false,true);
      
      $sfrm = new form("subj_type",null,null,null,"Annonce, le message sera affiché en haut dans un cadre séparé");
      $sfrm->add_datetime_field('date_fin_announce', 
    			   'Date de fin de l\'annonce',
    			   $sujet->date_fin_annonce);
      $frm->add($sfrm,false,true, $sujet->type==SUJET_ANNONCE ,SUJET_ANNONCE ,false,true);
      
      if ( $site->user->is_in_group("moderateur_forum") || $site->user->is_in_group("root") )
      {
        $sfrm = new form("subj_type",null,null,null,"Annonce du site, le message sera affiché en haut sur la première page du forum");
        $sfrm->add_datetime_field('date_fin_announce_site', 
      			   'Date de fin de l\'annonce',
      			   $sujet->date_fin_annonce);
        $frm->add($sfrm,false,true, $sujet->type==SUJET_ANNONCESITE ,SUJET_ANNONCESITE ,false,true);
      }
    }
    
    /**
     * @todo : edition des metas données
     */
    
    $frm->add_text_field("titre", "Titre : ", $sujet->titre,true,80);
    $frm->add_text_field("soustitre","Sous-titre du message (optionel) : ",$sujet->soustitre,false,80);    
    
    $frm->add_select_field('synengine',
  			 'Moteur de rendu : ',
  			 array('bbcode' => 'bbcode (type phpBB)','doku' => 'Doku Wiki (recommandé)'),
  			 $message->syntaxengine);
    $frm->add_text_area("text", "Texte du message : ",$message->contenu,80,20);
    $frm->add_submit("submit", "Modifier");
    $frm->allow_only_one_usage();

    $cts->add($frm);
  
    /**@todo*/
    $site->add_contents($cts);
    $site->end_page();
      
    exit();
  }

  if ($_REQUEST['page'] == 'commitedit')
  {
    $site->allow_only_logged_users("forum");
    
    //$site->start_page("forum",$sujet->titre);
    if ( $message->is_valid() )
    {
      if ((($message->id_utilisateur == $site->user->id)
        || ($forum->is_admin($site->user)))
        && ($GLOBALS['svalid_call'] == true))
      {
        $ret = $message->update($forum, 
      			  $sujet,
      			  $_REQUEST['title'],
      			  $_REQUEST['text'],
      			  $_REQUEST['synengine']);
        $cts = new contents("Modification d'un message", "Message modifié");
      }
      else
        $cts = new contents("Modification d'un message", 
  			  "Erreur lors de la modification du message. Assurez-vous d'avoir les privilèges suffisants.");
      
      $site->add_contents($cts);
    }
    elseif ($GLOBALS['svalid_call'] == true)
    {
      if ($sujet->id_utilisateur != $site->user->id && !$forum->is_admin($site->user))    
        $site->error_forbidden("forum","group");
        
      $message->load_initial_of_sujet($sujet->id);  
      
      $message->update($forum, 
      			  $sujet,
      			  $_REQUEST['titre'],
      			  $_REQUEST['text'],
      			  $_REQUEST['synengine']);
      
      $type=SUJET_NORMAL;
      $date_fin_annonce=null;
      
      if ( $forum->is_admin($site->user) )
      {
        $type = $_REQUEST["subj_type"];
        if ( $type == SUJET_ANNONCESITE && 
          !$site->user->is_in_group("moderateur_forum") && 
          !$site->user->is_in_group("root") )
        {
          $type = SUJET_ANNONCE;
          $date_fin_annonce=$_REQUEST["date_fin_announce_site"];
        }  
        elseif ( $type == SUJET_ANNONCE )
          $date_fin_annonce=$_REQUEST["date_fin_announce"];
          
        elseif ( $type == SUJET_ANNONCESITE )
          $date_fin_annonce=$_REQUEST["date_fin_announce_site"];
      }

      $sujet->update ($_REQUEST["titre"], $_REQUEST["soustitre"],
          $type,null,$date_fin_annonce,
          $sujet->id_nouvelle,$sujet->id_catph,$sujet->id_sondage );
      
    }
    //$site->end_page();
    //exit();
  }    

  if ( $_REQUEST["page"] == "reply" )
  {
    $site->allow_only_logged_users("forum");

    $site->start_page("forum",$sujet->titre);

    $cts = new contents($path." / <a href=\"?id_sujet=".$sujet->id."&amp;page=reply\">Répondre</a>");    
  
    /* formulaire d'invite à postage de réponse */
    $frm = new form("frmreply", "?page=commit&amp;id_sujet=".$sujet->id, true);
  
    if (intval($_REQUEST['quote']) == 1)
		{
		  $_auteur="";
		  /* l'objet message doit alors etre chargé */
			if($message->id_utilisateur>0)
		  {
				$_auteur=new utilisateur($site->db,$site->dbrw);
				$_auteur->load_by_id($message->id_utilisateur);
				if(!is_null($_auteur->id))
		      $_auteur="=".$_auteur->alias;
		  }
			
	    $rpltext = "[quote".$_auteur."]".$message->contenu . "[/quote]";
	    $rpltitle = "Re : " . $message->titre;
    }
    else 
    {
	    $rpltext = '';
	    $rpltitle = '';  
    }

    $frm->add_text_field("rpltitle", "Titre du message : ", $rpltitle,false,80);
    $frm->add_select_field('synengine',
		     'Moteur de rendu : ',
		     array('bbcode' => 'bbcode (type phpBB)','doku' => 'Doku Wiki (recommandé)'),'doku');
    $frm->add_dokuwiki_toolbar('rpltext');
    $frm->add_text_area("rpltext", "Texte du message : ",$rpltext,80,20);
    $frm->add_submit("rplsubmit", "Poster");
    $frm->allow_only_one_usage();
    $cts->add($frm);
    
    
    $npp=40;
    $nbpages = ceil($sujet->nb_messages / $npp);
    $start = ($nbpages - 1) * $npp;
    
    $cts->add(new sujetforum ($forum, 
			    $sujet, 
			    $site->user, 
			    "./", 
			    0, 
			    40, 
			    "DESC" ));
    
    $site->add_contents($cts);
    $site->end_page();
    exit();  
  }

  
  /* réponse postée */
  if ($_REQUEST['page'] == 'commit')
  {
    $site->start_page("forum",$sujet->titre);

    $cts = new contents($path.
		  " / <a href=\"?id_sujet=".
		  $sujet->id.
		  "&amp;page=reply\">Répondre</a>");          
    
    /*  sujet */
    
    /* nombre de posts par page */
    $npp=40;

    if (($GLOBALS['svalid_call'] == true) && ($_REQUEST['rpltext'] != ''))
	    $retpost = $message->create($forum,
				    $sujet,
				    $site->user->id,
				    $_REQUEST['rpltitle'],
				    $_REQUEST['rpltext'],
				    $_REQUEST['synengine']);
    else
      $retpost = false;
				  
    /* nombre de pages */
    $nbpages = ceil($sujet->nb_messages / $npp);
    /* on va à la derniere */
    $start = ($nbpages - 1) * $npp;
    
    $cts->add(new sujetforum ($forum, 
			$sujet, 
			$site->user, 
			"./", 
			$start, 
			$npp));
      
    if ($retpost == true)
	    $answ = new contents("Poster une réponse",
			     "<b>Réponse postée avec succès.</b>");      
    else
	    $answ = new contents("Poster une réponse", 
			     "<b>Echec lors de la tentative de postage de la réponse.</b>");

    if ($GLOBALS['svalid_call'] == false)
	    $answ->add_paragraph('Votre réponse a déjà été postée.');

    $site->add_contents($answ);

    for($n=0 ; $n<$nbpages ; $n++)
      $entries[]=array($n,"forum2/?id_sujet=".$sujet->id."&spage=".$n,$n+1);
      
    $cts->add(new tabshead($entries, floor($start/$npp), "_bottom"));

    $site->add_contents($cts);

    $site->end_page();
    exit();
  }
  

  $site->start_page("forum",$sujet->titre);
  
  $cts = new contents($path);

  $npp=40;
  $start=0;
  $delta=0;
  $nbpages = ceil($sujet->nb_messages/$npp);

  if ( isset($_REQUEST["spage"]) && $_REQUEST["spage"] == "firstunread" && $site->user->is_valid() )
  { 
    $last_read = $sujet->get_last_read_message ( $site->user->id );
    if ( !is_null($last_read) )
    {
      $message->load_by_id($last_read);
      $delta=1;
    }
    elseif( !is_null($site->user->tout_lu_avant) )
    {
      $req = new requete($site->db,"SELECT id_message FROM frm_message ".
        "WHERE id_sujet='".mysql_real_escape_string($sujet->id)."' ".
        "AND date_message > '".date("Y-m-d H:i:s",$site->user->tout_lu_avant)."' ".
        "ORDER BY date_message LIMIT 1");
      if ( $req->lines == 1 )
      {
        list($last_read) = $req->get_row();
        $message->load_by_id($last_read);
        $delta=1;
      }
    }
    unset($_REQUEST["spage"]);
  }

  if ( $message->is_valid() )
  {
    $req = new requete($site->db,"SELECT id_message FROM frm_message WHERE id_sujet='".mysql_real_escape_string($sujet->id)."' ORDER BY date_message");
    
    $ids = array();
    while ( list($id) = $req->get_row() )
      $ids[] = $id;
  
    list($start) = array_keys($ids, $message->id);
    $start += $delta;
    $start -= $start%$npp;
  }
  elseif ( isset($_REQUEST["spage"]) )
  {
    $start = intval($_REQUEST["spage"])*$npp;
    if ( $start > $sujet->nb_messages )
    {
      $start = $sujet->nb_messages;
      $start -= $start%$npp;
    }
  }
  
  /**@todo:bouttons+infos*/

  $cts->add_paragraph("<a href=\"?id_sujet=".$sujet->id."&amp;page=reply\"><img src=\"".$wwwtopdir."images/icons/16/message.png\" class=\"icon\" alt=\"\" />Répondre</a>","frmtools");

  if ( $start == 0 )
  {
    if ( !is_null($sujet->id_sondage) )
    {
      $sdn = new sondage($site->db);  
      $sdn->load_by_id($sujet->id_sondage);
      
      $cts->puts("<div class=\"sujetcontext\">");
      
    	$cts->add_title(2,"Sondage : resultats");
	
    	$cts->add_paragraph($sdn->question);
    	
    	$cts->puts("<p>");
    
    	$res = $sdn->get_results();
    	
    	foreach ( $res as $re )
    	{
    		$cumul+=$re[1];
    		$pc = $re[1]*100/$sdn->total;
    		
    		$cts->puts($re[0]."<br/>");
    		
    		$wpx = floor($pc);
    		if ( $wpx != 0 )
    			$cts->puts("<div class=\"activebar\" style=\"width: ".$wpx."px\"></div>");
    		if ( $wpx != 100 )
    			$cts->puts("<div class=\"inactivebar\" style=\"width: ".(100-$wpx)."px\"></div>");
    		
    		$cts->puts("<div class=\"percentbar\">".round($pc,1)."%</div>");
    		$cts->puts("<div class=\"clearboth\"></div>\n");
    		
    	}
    	
    	if ( $cumul < $sdn->total )
    	{
    		$pc = ( $sdn->total-$cumul)*100/$sdn->total;
    		$cts->puts("<br/>Blanc ou nul : ".round($pc,1)."%");
    	}
    	$cts->puts("</p>");
    	$cts->puts("</div>");
    }
    if ( !is_null($sujet->id_nouvelle) )
    {
      $news = new nouvelle ($site->db);
      $news->load_by_id($sujet->id_nouvelle);
      $cts->add($news->get_contents(),true,true,"newsboxed","sujetcontext");
    }
    if ( !is_null($sujet->id_catph) )
    {
      $cat = new catphoto($site->db);
      $catpr = new catphoto($site->db);
      $cat->load_by_id($sujet->id_catph);
      
      $path = classlink($cat);
      $catpr->load_by_id($cat->id_catph_parent);
      while ( $catpr->is_valid() )
      {
        $path = classlink($catpr)." / ".$path;
        $catpr->load_by_id($catpr->id_catph_parent);
      }
      
      if ( !$cat->is_right($site->user,DROIT_LECTURE) )
      {
        $cts->add(new contents($path),true,true,"sasboxed","sujetcontext");
      }
      else
      {
        require_once($topdir."include/cts/gallery.inc.php");
        $site->add_css("css/sas.css");

        $sqlph = $cat->get_photos ( $cat->id, $site->user, $site->user->get_groups_csv(), "sas_photos.*", " LIMIT 5");
        
        $gal = new gallery($path,"photos","phlist","../sas2/?id_catph=".$cat->id,"id_photo",array());
        while ( $row = $sqlph->get_row() )
        {
          $img = "../sas2/images.php?/".$row['id_photo'].".vignette.jpg";
          if ( $row['type_media_ph'] == 1 )
            $gal->add_item("<a href=\"../sas2/?id_photo=".$row['id_photo']."\"><img src=\"$img\" alt=\"Photo\">".
                "<img src=\"".$wwwtopdir."images/icons/32/multimedia.png\" alt=\"Video\" class=\"ovideo\" /></a>","");
          else
            $gal->add_item("<a href=\"../sas2/?id_photo=".$row['id_photo']."\"><img src=\"$img\" alt=\"Photo\"></a>","");
        }
        
        $img = $topdir."images/misc/sas-default.png";
        
        if ( $cat->id_photo )
          $img = "../sas2/images.php?/".$cat->id_photo.".vignette.jpg";
      
        $gal->add_item("<a href=\"../sas2/?id_catph=".$cat->id."\"><img src=\"$img\" alt=\"Photo\"></a>",$cat->nom." : suite..." );
        
        $cts->add($gal,true,true,"sasboxed","sujetcontext");
      }
    }
  }
  
  
  $entries=array();
  
  for( $n=0;$n<$nbpages;$n++)
    $entries[]=array($n,"forum2/?id_sujet=".$sujet->id."&spage=".$n,$n+1);
    
	$cts->add(new tabshead($entries, floor($start/$npp), "_top"));
    
  $cts->add(new sujetforum ($forum, 
			    $sujet, 
			    $site->user, 
			    "./", 
			    $start, 
			    $npp ));
    
	$cts->add(new tabshead($entries, floor($start/$npp), "_bottom"));

  $cts->add_paragraph("<a href=\"?id_sujet=".$sujet->id."&amp;page=reply\"><img src=\"".$wwwtopdir."images/icons/16/message.png\" class=\"icon\" alt=\"\" />Répondre</a>","frmtools");
  $cts->add_paragraph($path);
  
  /**@todo:bouttons+infos*/

  if ( $site->user->is_valid() )
  {
    $num = $start+$npp-1;
    if ( $num >= $sujet->nb_messages )
      $max_id_message = null;
    else
    {
      $req = new requete($site->db,"SELECT id_message FROM frm_message WHERE id_sujet='".mysql_real_escape_string($sujet->id)."' ORDER BY date_message LIMIT $num,1"); 
      list($max_id_message) = $req->get_row();    
    }
    
    $sujet->set_user_read ( $site->user->id, $max_id_message );
  }

  $site->add_contents($cts);

  $site->end_page();
  exit();
}

if ( $_REQUEST["page"] == "post" && !$forum->categorie )
{
  $site->allow_only_logged_users("forum");
  
  $site->start_page("forum", $forum->titre);
  
  $cts = new contents($path." / Nouveau sujet");
  
  /* formulaire d'invite à postage de nouveau sujet */
  $frm = new form("newsbj","?id_forum=".$forum->id, 
		  true);
		  
	$frm->add_hidden("action","post");
		  
  $frm->allow_only_one_usage();
  
  if ( isset($Erreur) )
    $frm->error($Erreur);

  if ( $forum->is_admin($site->user) )
  {
    $type=SUJET_NORMAL;
  
    $sfrm = new form("subj_type",null,null,null,"Sujet normal");
    $frm->add($sfrm,false,true, $type==SUJET_NORMAL ,SUJET_NORMAL ,false,true);
    
    $sfrm = new form("subj_type",null,null,null,"Sujet épinglé, il sera toujours affiché en haut");
    $frm->add($sfrm,false,true, $type==SUJET_STICK ,SUJET_STICK ,false,true);
    
    $sfrm = new form("subj_type",null,null,null,"Annonce, le message sera affiché en haut dans un cadre séparé");
    $sfrm->add_datetime_field('date_fin_announce', 
  			   'Date de fin de l\'annonce',
  			   time()+(7*24*60*60));
    $frm->add($sfrm,false,true, $type==SUJET_ANNONCE ,SUJET_ANNONCE ,false,true);
    
    if ( $site->user->is_in_group("moderateur_forum") || $site->user->is_in_group("root") )
    {
      $sfrm = new form("subj_type",null,null,null,"Annonce du site, le message sera affiché en haut sur la première page du forum");
      $sfrm->add_datetime_field('date_fin_announce_site', 
    			   'Date de fin de l\'annonce',
    			   time()+(7*24*60*60));
      $frm->add($sfrm,false,true, $type==SUJET_ANNONCESITE ,SUJET_ANNONCESITE ,false,true);
    }
  }

  /* on part du principe qu'un sujet est nécessairement initié par
   * un message */

  /* choix d'une icone ? */
  /* TODO : à définir */

  /* choix d'une news de référence
   * id sondage concerné
   * => A ne supporter que si les IDs passés en paramètre
   */

  if ( isset($_REQUEST["id_nouvelle"]) )
  {
    $news = new nouvelle($site->db);
    $news->load_by_id($_REQUEST["id_nouvelle"]);
    if ( $news->is_valid() )
    {
      $frm->add_hidden("id_nouvelle",$news->id);
      $frm->add_info("<b>En reaction de la nouvelle</b> : ".$news->get_html_link());
    }
  }
  elseif ( isset($_REQUEST["id_catph"]) )
  {
    $catph = new catphoto($site->db);
    $catph->load_by_id($_REQUEST["id_catph"]);
    if ( $catph->is_valid() )
    {
      $frm->add_hidden("id_catph",$catph->id);
      $frm->add_info("<b>En reaction de la catégorie du SAS</b> : ".$catph->get_html_link());
    }    
  }
  elseif ( isset($_REQUEST["id_sondage"]) )
  {
    $sdn = new sondage($site->db);
    $sdn->load_by_id($_REQUEST["id_sondage"]);
    if ( $sdn->is_valid() )
    {
      $frm->add_hidden("id_sondage",$sdn->id);
      $frm->add_info("<b>En reaction du sondage</b> : ".$sdn->get_html_link());
    }
  }

  /* titre du sujet */
  $frm->add_text_field("titre_sujet", 
		       "Titre du message : ",$_REQUEST["titre_sujet"],true,80);
  /* sous-titre du sujet */
  $frm->add_text_field("soustitre_sujet", 
		       "Sous-titre du message (optionel) : ","",false,80);
  /* moteur de rendu */
  $frm->add_select_field('synengine',
			 'Moteur de rendu : ',
			 array('bbcode' => 'bbcode (type phpBB)',
			       'doku' => 'Doku Wiki (recommandé)'),'doku');
  
  /* texte du message initiateur */
  $frm->add_text_area("subjtext", "Texte du message : ","",80,20);
  /* et hop ! */
  $frm->add_submit("subjsubmit", "Poster");

  $cts->add($frm);
  
  $site->add_contents($cts);
  
  $site->end_page();  
  
  exit();
}



$site->start_page("forum",$forum->titre);

$cts = new contents($path);



if ( $forum->categorie )
{
// Liste des sous-forums 

  if ( $forum->id == 1 && $site->user->is_valid() )
  {
   /*$cts->add_paragraph("<a href=\"./search.php?page=unread\">Voir tous les messages non lu</a>","frmgeneral");
   $cts->add_paragraph("<a href=\"./?action=setallread\">Marquer tous les messages comme lu</a>","frmgeneral");*/
   

   
    $query = "SELECT COUNT(*) " .
        "FROM frm_sujet " .
        "INNER JOIN frm_forum USING(id_forum) ".
        "LEFT JOIN frm_message ON ( frm_message.id_message = frm_sujet.id_message_dernier ) " .
        "LEFT JOIN frm_sujet_utilisateur ".
          "ON ( frm_sujet_utilisateur.id_sujet=frm_sujet.id_sujet ".
          "AND frm_sujet_utilisateur.id_utilisateur='".$site->user->id."' ) ".
        "WHERE ";
              
    if( is_null($site->user->tout_lu_avant))
      $query .= "(frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ";    
    else
      $query .= "((frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ".
                "AND frm_message.date_message > '".date("Y-m-d H:i:s",$site->user->tout_lu_avant)."') ";  
    
    if ( !$forum->is_admin( $site->user ) )
    {
      $grps = $site->user->get_groups_csv();
      $query .= "AND ((droits_acces_forum & 0x1) OR " .
        "((droits_acces_forum & 0x10) AND id_groupe IN ($grps)) OR " .
        "(id_groupe_admin IN ($grps)) OR " .
        "((droits_acces_forum & 0x100) AND frm_forum.id_utilisateur='".$site->user->id."')) ";
    }
      
    $req = new requete($site->db,$query);  
      
    list($nb)=$req->get_row();
      
    if ( $nb > 0 )
      $cts->add_paragraph(
      "<a href=\"search.php?page=unread\">".
        "<img src=\"".$wwwtopdir."images/icons/16/unread.png\" class=\"icon\" alt=\"\" />Messages non lu ($nb sujet(s))".
      "</a> ".
      "<a href=\"./?action=setallread\">".
        "<img src=\"".$wwwtopdir."images/icons/16/valid.png\" class=\"icon\" alt=\"\" />Marquer tout comme lu".
      "</a> ".
      "<a href=\"search.php\">".
        "<img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher".
      "</a>"
      ,"frmtools");
    else
      $cts->add_paragraph("<a href=\"search.php\"><img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher</a>","frmtools");
      
  }
  else
    $cts->add_paragraph("<a href=\"search.php\"><img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher</a>","frmtools");

  $cts->add(new forumslist($forum, $site->user, "./"));

}
else
{
// Liste des sujets 
  $npp=40;
  $start=0;
  $nbpages = ceil($forum->nb_sujets/$npp);
  
  if ( isset($_REQUEST["fpage"]) )
  {
    $start = intval($_REQUEST["fpage"])*$npp;
    if ( $start > $forum->nb_sujets )
    {
      $start = $forum->nb_sujets;
      $start -= $start%$npp;
    }
  }
  
  /**@todo:bouttons+infos*/
  
  $cts->add_paragraph("<a href=\"search.php\"><img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher</a> <a href=\"?id_forum=".$forum->id."&amp;page=post\"><img src=\"".$wwwtopdir."images/icons/16/sujet.png\" class=\"icon\" alt=\"\" />Nouveau sujet</a>","frmtools");
  
  $cts->add(new sujetslist($forum, $site->user, "./", $start, $npp));
  
  $entries=array();
  
  for( $n=0;$n<$nbpages;$n++)
    $entries[]=array($n,"forum2/?id_forum=".$forum->id."&fpage=".$n,$n+1);
  
	$cts->add(new tabshead($entries, floor($start/$npp), "_bottom"));
	
  $cts->add_paragraph("<a href=\"search.php\"><img src=\"".$wwwtopdir."images/icons/16/search.png\" class=\"icon\" alt=\"\" />Rechercher</a> <a href=\"?id_forum=".$forum->id."&amp;page=post\"><img src=\"".$wwwtopdir."images/icons/16/sujet.png\" class=\"icon\" alt=\"\" />Nouveau sujet</a>","frmtools");

  /**@todo:bouttons+infos*/
}

$site->add_contents($cts);

$site->end_page();

?>
