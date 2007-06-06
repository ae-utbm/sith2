<?php


/**
 *
 */
class flvideo extends stdcontents
{

	var $src;
	var $class;
	
	/**
	 * @param $title 
	 * @param $src 
	 */
	function flvideo ( $title, $src)
	{
		$this->title = $title;
		$this->src = $src;
	}

	function html_render ()
	{
	  global $wwwtopdir;
	  
		return
"<object type=\"application/x-shockwave-flash\" data=\"".$wwwtopdir."images/flash/flvplayer.swf\" width=\"400\" height=\"300\">".
"<param name=\"movie\" value=\"".$wwwtopdir."images/flash/flvplayer.swf\" />"."<param name=\"FlashVars\" value=\"flv=../../".$this->src."\" />"."<param name=\"wmode\" value=\"transparent\" />"."</object>";
	}	

}



?>