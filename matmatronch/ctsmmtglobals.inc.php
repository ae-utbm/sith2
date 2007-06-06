<?php

class form_mmt extends form
{

	/** Ajoute un champ à cocher au formulaire pour la promo

	 * @param $name		Nom du champ
	 * @param $title		Libéllé
	 * @param $values	Valeurs possibles (key=>Titre)
	 * @param $checked	Nom de la radio box activee par defaut
	 * @param $disabled  Nom de la radio box desactivee non active
	 * @param $required	Précise si le champ est obligatoire
	 * @param $imgs	Tableau associatif des items et des images
	 */		
	function add_radiobox_field_promo ( $name, $title=false, $values, $value=false , $disabled=false, $required = false, $imgs, $size_imgs = "100%")
	{
		global $topdir;

		if ( $this->autorefill && $_REQUEST[$name] ) $value = $_REQUEST[$name];	
		$this->buffer .= "<div class=\"formrow\">\n";
		$this->_render_name($name,$title,$required);
		$this->buffer .= "<div class=\"formfield\">";
		foreach ( $values as $key => $item )
		{
			$this->buffer .= "<input type=\"radio\" name=\"$name\" class=\"radiobox\" value=\"$key\"";

			if ( $key == $value )
				$this->buffer .= " checked=\"checked\"";
			if ( $key == $disabled )
				$this->buffer .= " disabled=\"disabled\"";
			$this->buffer .= " /> ";
			if ( $imgs[$key] && $size_imgs )
				$this->buffer .= "<img src=\"".$topdir."images/".$imgs[$key]."\" width=\"".$size_imgs."\" height=\"".$size_imgs."\" alt=\"".$item."\" title=\"".$item."\">";
			else
				$this->buffer .= " $item";
			if ( $key == "4" )
				$this->buffer .= "<br/>";
		}
		$this->buffer .= "</div>\n";
		$this->buffer .= "</div>\n";
	}
	
	function html_render ()
	{
		$html = "";
		
		if ( $this->error_contents )
			$html .= "<p class=\"formerror\">Erreur : ".$this->error_contents."</p>\n";
			
		$html .= "<form action=\"$this->action\" method=\"$this->method\"";
		if ( $this->name ) 
			$html .= " name=\"".$this->name."\" id=\"".$this->name."\"";
		if ( $this->enctype ) 
			$html .= " enctype=\"".$this->enctype."\"";
		$html .= ">\n";
		foreach ( $this->hiddens as $key => $value )
			$html .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
		$html .= "<div class=\"form\">\n";
		$html .= $this->buffer;
		$html .= "</div>\n";
		$html .= "</form>\n";
		return $html;
	}
	
	/** @private
	*/
	function _render_name ( $name, $title, $required )
	{
		if ( !$title )
		{
			$this->buffer .= "<div class=\"formlabel\"></div>";	
			return;	
		}
		
		if ( $required && $this->autorefill && $_REQUEST[$name]=="" )
			$this->buffer .= "<div class=\"formlabelmissing\">";
		else
			$this->buffer .= "<div class=\"formlabel\">";
		
		$this->buffer .= $title;
		if ( $required ) 
			$this->buffer .= " *";
		$this->buffer .= "</div>";	
	}	
}


?>
