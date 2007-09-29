<?php

class comment_contents extends stdcontents
{
  
  function comment_contents (&$comment, $user_id, $can_edit)
  {
    global $topdir, $wwwtopdir;
    
    $this->buffer .= "<div class=\"commentaire\">\n";
    
    $this->buffer .= "\t<div class=\"author_avatar\">";
    if (file_exists($topdir."var/img/matmatronch/".$row['id_commentateur'].".jpg"))
      $img = $wwwtopdir."var/img/matmatronch/".$row['id_commentateur'].".jpg";
    $this->buffer .= "<img src=\"".$img."\" />";
    $this->buffer .= "\t</div>\n";
    
    $this->buffer .= "\t<div class=\"comment_content\">\n";
    $this->buffer .= doku2xhtml($comment['commentaire']);
    $this->buffer .= "\t</div>\n";
    
    $this->buffer .= "\t<div class=\"clearboth\"></div>\n";
    $this->buffer .= "</div>\n";
  }

}

?>