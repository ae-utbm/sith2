<?php

class comment_contents extends stdcontents
{
  
  function comment_contents (&$comment, $user_id, $is_user_moderator)
  {
    global $topdir, $wwwtopdir;
    
    if ( !$is_user_moderator && $comment["modere_commentaire"] )
      return false;
    
    $this->buffer .= "<div class=\"commentaire\">\n";
    $this->buffer .= $this->comment_header( $comment, ( $comment["id_commentateur"] == $user_id ), $is_user_moderator );
    
    $this->buffer .= "\t<div><a href=\"?id_utilisateur=".$comment["id_commentateur"]."\">" . (
        (isset($comment["alias_utl"]) && $comment["alias_utl"] != "") ?
        $comment["alias_utl"] :
        $comment["prenom_utl"]." ".$comment["nom_utl"]
      )."</a></div>\n";
      
    $this->buffer .= "\t<div class=\"author_avatar\">";
    if (file_exists($topdir."var/img/matmatronch/".$comment['id_commentateur'].".jpg"))
      $img = $wwwtopdir."var/img/matmatronch/".$comment['id_commentateur'].".jpg";
    $this->buffer .= "<img src=\"".htmlentities($img,ENT_NOQUOTES,"UTF-8")."\" />";
    $this->buffer .= "</div>\n";
    
    $this->buffer .= "\t<div class=\"comment_content\">\n";
    $this->buffer .= doku2xhtml($comment['commentaire']);
    $this->buffer .= "\t</div>\n";
    
    $this->buffer .= "\t<div class=\"clearboth\"></div>\n";
    $this->buffer .= "</div>\n";
  }
  
  function comment_header ($comment, $is_user_comment, $is_user_moderator)
  {
    $header = "\t<div class=\"comment_header\">";
    $header .= "<span>".$comment["date_commentaire"]."</span>";
    
    $separator = false;
    
    if ( $is_user_comment || $is_user_moderator )
      $header .= "<a href=\"?page=edit&amp;id_commentaire=".$comment["id_commentaire"]."\">Editer</a>";
      $separator = true;
      
    if ( $is_user_moderator )
      $header .= ($separator ? " | " : "") . "<a href=\"?action=moderate&amp;id_commentaire=".$comment["id_commentaire"]."\">".($comment["modere_commentaire"] ? "Restaurer" : "Modérer")."</a>";
      
    $header .= "</div>\n";
    return $header;
  }

}

?>