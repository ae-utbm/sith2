<?php


class board extends stdcontents
{
  var $boardclass;
  
  
  function board ( $title=null, $class=null )
  {
    $this->title = $title;
    $this->boardclass = $class;
  }
  
  function add ( &$cts, $title=false, $class=null )
  {
    if ( is_null($class) )
      $this->buffer .= "<div class=\"panel\">\n";
    else 
      $this->buffer .= "<div class=\"panel $class\">\n";
    
    if ( $title )
      $this->buffer .= "<h2>".$cts->title."</h2>\n";
    
    $this->buffer .= "<div class=\"panelcts\">\n";
    $this->buffer .= $cts->html_render()."\n";
		$this->buffer .= "</div>\n";
		$this->buffer .= "</div>\n";
  }
  
	function html_render ()
	{
	  if ( !is_null($this->boardclass) )
		  return "<div class=\"board ".$this->boardclass."\">".$this->buffer."<div class=\"clearboth\"></div></div>";
		else
		  return "<div class=\"board\">".$this->buffer."<div class=\"clearboth\"></div></div>";
	}
  
}



?>