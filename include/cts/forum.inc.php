<?php

include($topdir."include/lib/bbcode.inc.php");
//include($topdir."include/lib/dokusyntax.inc.php");

class forumslist extends stdcontents
{
  
  
  function forumslist ( &$forum, &$user, $page )
  {
    
    $rows = $forum->get_sub_forums($user);
    
    $sections=true;
    
    foreach ( $rows as $row )
    {
      if ( !$row['categorie_forum'] )
        $sections = false;
    }    
    $this->buffer .= "<div class=\"forumlist\">\n";
    if ( $sections )
    {
      $sforum = new forum ( $forum->db );
      
      foreach ( $rows as $row )
      {
        $sforum->_load($row);
        $srows = $sforum->get_sub_forums($user);
        $this->_render_section ( $sforum, $srows, $page );      
      }
    }
    else
      $this->_render_section ( $forum, $rows, $page );      
    
    $this->buffer .= "</div>\n";
    
    
  }
  
  function _render_section ( &$forum, &$rows, $page )
  {
    $this->buffer .= "<div class=\"forumsection\">\n";
    
    $this->buffer .= "<h2>".htmlentities($forum->titre,ENT_NOQUOTES,"UTF-8")."</h2>\n";
    
    foreach ( $rows as $row )
    {
      $this->buffer .= "<div class=\"forumitem\">\n";
      $this->buffer .= "<h3><a href=\"".$page."?id_forum=".$row['id_forum']."\">".
                       htmlentities($row['titre_forum'], ENT_NOQUOTES, "UTF-8")."</a></h3>\n";

      $this->buffer .= "<p class=\"description\">".htmlentities($row['description_forum'],ENT_NOQUOTES,"UTF-8")."</p>\n";
      
      $this->buffer .= "<p class=\"nbsujets\">".$row['nb_sujets_forum']."</p>\n";
      
      if ( !is_null($row['id_message']) )
        $this->buffer .= "<p class=\"dernier\"><a href=\"".$page."?id_message=".$row['id_message']."#msg".$row['id_message']."\">".htmlentities($row['nom_utilisateur_dernier_auteur'],ENT_NOQUOTES,"UTF-8")." ".date("d/m/Y H:i",strtotime($row['date_message']))."</a></p>\n";
      
      $this->buffer .= "</div>\n";
    }
    
    $this->buffer .= "</div>\n";

  }
  
  
}

class sujetslist extends stdcontents
{
  
  
  function sujetslist ( &$forum, &$user, $page, $start, $npp )
  {
    global $wwwtopdir;
    
    if ( is_array($forum) )
      $rows = $forum;
    else
      $rows = $forum->get_sujets($user, $start, $npp);
    
    $this->buffer .= "<div class=\"forumsujetsliste\">\n";
    
    foreach ( $rows as $row )
    {
      
      if ( $row['nonlu'] )
        $this->buffer .= "<div class=\"forumsujet nonlu\">\n";
      else
        $this->buffer .= "<div class=\"forumsujet\">\n";
      
      $this->buffer .= "<h2><a href=\"".$page."?id_sujet=".$row['id_sujet']."\">".
                       htmlentities($row['titre_sujet'], ENT_NOQUOTES, "UTF-8")."</a></h2>\n";
                       
      if ( !$row['soustitre_sujet'] )
        $this->buffer .= "<p class=\"soustitre\">&nbsp;</p>\n";
      else
        $this->buffer .= "<p class=\"soustitre\">".htmlentities($row['soustitre_sujet'],ENT_NOQUOTES,"UTF-8")."</p>\n";
      
      $this->buffer .= "<p class=\"sujeticon\"><img src=\"".$wwwtopdir."images/icons/16/sujet.png\" /></p>\n";


      /* actions */
      if ( !is_array($forum) )
      if (($user->id == $row['id_utilisateur']) ||($forum->is_admin($user)))
	    {
        $this->buffer .= "<p class=\"actions\">";
        
        $this->buffer .= "<a href=\"?id_sujet=".$row['id_sujet']."&amp;page=delete\">Supprimer</a>";
        $this->buffer .= " | <a href=\"?id_sujet=".$row['id_sujet']."&amp;page=edit\">Editer</a>";
        $this->buffer .= "</p>\n";
	    }

      if ( !$row['nom_utilisateur_premier_auteur'] )
        $this->buffer .= "<p class=\"auteur\">&nbsp;</p>\n";
      else
        $this->buffer .= "<p class=\"auteur\">".htmlentities($row['nom_utilisateur_premier_auteur'],ENT_NOQUOTES,"UTF-8")."</p>\n";
      
      $this->buffer .= "<p class=\"nbmessages\">".$row['nb_messages_sujet']."</p>\n";
      
      if ( !is_null($row['id_message']) )
        $this->buffer .= "<p class=\"dernier\"><a href=\"".$page."?id_message=".$row['id_message']."#msg".$row['id_message']."\">".htmlentities($row['nom_utilisateur_dernier_auteur'],ENT_NOQUOTES,"UTF-8")." ".date("d/m/Y H:i",strtotime($row['date_message']))."</a></p>\n";
      
      $this->buffer .= "</div>\n";
    }
    $this->buffer .= "</div>\n";
  }
  
  
  
  
}

class sujetforum extends stdcontents
{
  
  function sujetforum (&$forum, &$sujet, &$user, $page, $start, $npp, $order = "ASC" )
  {
    global $topdir, $wwwtopdir;
    
    if ( $user->is_valid() )
      $last_read = $sujet->get_last_read_message ( $user->id );
    else
      $last_read = null;
    
    $rows = $sujet->get_messages ( $user, $start, $npp, $order );
    
    $this->buffer .= "<div class=\"forummessagesliste\">\n";
    
    $firstunread=true;
    
    $initial = ($start==0 && $order=="ASC");
    
    foreach ( $rows as $row )
    {
      $t = strtotime($row['date_message']);
      
      if ( $user->is_valid() && 
      ( is_null($last_read) || $last_read < $row['id_message'] ) && 
      ( is_null($user->tout_lu_avant) || $t > $user->tout_lu_avant ) )
      {
        $this->buffer .= "<div class=\"forummessageentry nonlu\" id=\"msg".$row['id_message']."\">\n";
        if ( $firstunread )
        {
          $firstunread=false;  
          $this->buffer .= "<div id=\"firstunread\"></div>";
        }
      }
      else
        $this->buffer .= "<div class=\"forummessageentry\" id=\"msg".$row['id_message']."\">\n";
      
      $this->buffer .= "<h2>".htmlentities($row['titre_sujet'], ENT_NOQUOTES, "UTF-8")."</h2>\n";      
       $this->buffer .= "<p class=\"date\">".date("d/m/Y H:i",$t)."</p>\n";

       /* actions sur un message */
       $this->buffer .= "<p class=\"actions\">";
       
       /* utilisateur authentifié */
       if ($user->is_valid())
    	 {
    	   $this->buffer .= "<a href=\"?page=reply&amp;id_forum=".
    	     $forum->id.
    	     "&amp;id_sujet=".
    	     $sujet->id.
    	     "&amp;id_message=".
    	     $row['id_message'].
    	     "&amp;quote=1\">Répondre en citant</a>";
    	 }

       if (($user->id == $row['id_utilisateur']) ||($forum->is_admin($user)))
    	 {
    	   if ( $initial ) // Pour le message initial, renvoie vers le sujet
    	   {
      	   $spage = ceil($start/$npp);
      	   $this->buffer .= " | <a href=\"?page=edit&amp;id_sujet=".$sujet->id."\">Modifier</a> | ".
      	     "<a href=\"?page=delete&amp;id_sujet=".$sujet->id."&amp;spage=$spage\">Supprimer</a>";    	     
    	   }
    	   else
    	   {
      	   $spage = ceil($start/$npp);
      	   $this->buffer .= " | <a href=\"?page=edit&amp;id_forum=".
      	     $forum->id.
      	     "&amp;id_sujet=".
      	     $sujet->id.
      	     "&amp;id_message=".
      	     $row['id_message']."\">Modifier</a> | ".
      	     "<a href=\"?page=delete&amp;id_message=".$row['id_message']."&amp;spage=$spage\">Supprimer</a>";
    	   }
    	 }

       $this->buffer .= "</p>\n";   
          
      $this->buffer .= "<div class=\"auteur\">\n";
      
      $this->buffer .= "<p class=\"funame\"><a href=\"".$wwwtopdir."user.php?id_utilisateur=".$row['id_utilisateur']."\">".htmlentities($row['alias_utl'],ENT_NOQUOTES,"UTF-8")."</a></p>\n";
      
      $img=null;
      if (file_exists($topdir."var/img/matmatronch/".$row['id_utilisateur'].".jpg"))
        $img = $wwwtopdir."var/img/matmatronch/".$row['id_utilisateur'].".jpg";
      elseif (file_exists($topdir."var/img/matmatronch/".$row['id_utilisateur'].".identity.jpg"))
        $img = $wwwtopdir."var/img/matmatronch/".$row['id_utilisateur'].".identity.jpg";
        
      if ( !is_null($img) )
        $this->buffer .= "<p class=\"fuimg\"><img src=\"".htmlentities($img,ENT_NOQUOTES,"UTF-8")."\" /></p>\n";
      
      
      $this->buffer .= "</div>\n";
      $this->buffer .= "<div class=\"forummessage\">\n";
      
      if ( $row['syntaxengine_message'] == "bbcode" )
        $this->buffer .= bbcode($row['contenu_message']);
        
      elseif ( $row['syntaxengine_message'] == "doku" )
        $this->buffer .= doku2xhtml($row['contenu_message']);
        
      elseif ( $row['syntaxengine_message'] == "plain" )
        $this->buffer .= "<pre>".htmlentities($row['contenu_message'],ENT_NOQUOTES,"UTF-8")."</pre>";
      
      else // text
        $this->buffer .= nl2br(htmlentities($row['contenu_message'],ENT_NOQUOTES,"UTF-8"));
        
      if ( !is_null($row['signature_utl']) )  
      {
        $this->buffer .= "<div class=\"signature\">\n";      
        $this->buffer .= doku2xhtml($row['signature_utl']);
        $this->buffer .= "</div>\n";      
      }
      
      $this->buffer .= "</div>\n";      
      
      $this->buffer .= "</div>\n";
      $initial=false;
    }
    
    $this->buffer .= "</div>\n";
  }

}





?>