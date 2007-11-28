<?php

/**
 *
 */
class mp3player extends stdcontents
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
"<object type=\"application/x-shockwave-flash\" data=\"".$wwwtopdir."images/flash/dewplayer.swf?showtime=1&amp;mp3=".rawurlencode($this->src)."\" width=\"200\" height=\"20\">".
"<param name=\"movie\" value=\"".$wwwtopdir."images/flash/dewplayer.swf?showtime=1&amp;mp3=".rawurlencode($this->src)."\" />"."<param name=\"wmode\" value=\"transparent\" />"."</object>";
	}	

}



?>