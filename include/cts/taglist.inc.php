<?


class taglist extends stdcontents
{
  function taglist(&$entity)
  {
    global $wwwtopdir;
    
    $this->title = "Tags";
  
  	$this->buffer="";
  	
  	$tags = $entity->get_tags_list();
  	
  	if ( is_null($tags) || count($tags) == 0 )
  	  return;
  	
  	foreach ( $tags as $id => $nom )
  	{
  	
  	  if ( !empty($this->buffer) )
  	    $this->buffer .= ", ";
  	 
  	 $this->buffer .= 
  	   "<a href=\"".$wwwtopdir."tag.php?id_tag=".$id."\">".
  	   htmlentities($nom,ENT_NOQUOTES,"UTF-8")."</a>";
  	}
  	
  	$this->buffer = "<p class=\"tagslist\">".$this->buffer."</p>";
  }
}


?>