<?php

/**
 * RSS Feed qui ne contient qu'un channel
 *
 *
 */
class rssfeed 
{
  
  var $title;
  var $link;
  var $description;
  var $generator;
  var $pubDate;
  
  function rssfeed()
  {
    $this->pubDate = time();  
    $this->generator = "http://ae.utbm.fr/";
  }  
  
  function output_items()
  {
    
  }
  
  function output ()
  {
    header("Content-Type: text/xml; charset=utf-8");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    echo "<rss version=\"2.0\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n";
    echo "<channel>\n";
    
    if ( !empty($this->title) )
      echo "<title>".htmlspecialchars($this->title,ENT_NOQUOTES,"UTF-8")."</title>\n";
      
    if ( !empty($this->link) )
      echo "<link>".htmlspecialchars($this->link,ENT_NOQUOTES,"UTF-8")."</link>\n";      
      
    if ( !empty($this->description) )
      echo "<description>".htmlspecialchars($this->description,ENT_NOQUOTES,"UTF-8")."</description>\n";
      
    if ( !empty($this->generator) )
      echo "<generator>".htmlspecialchars($this->generator,ENT_NOQUOTES,"UTF-8")."</generator>\n";    
    
    echo "<pubDate>".gmdate("D, j M Y G:i:s T",$this->pubDate)."</pubDate>\n";
    
    $this->output_items();
        
    echo "</channel>\n";
    echo "</rss>\n";
    exit();
  }  
}




?>