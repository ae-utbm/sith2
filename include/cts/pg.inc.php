<?php


class pgcatlist extends stdcontents
{
  var $id;
  var $nom;
  var $couleur;
  var $data;
  
  function pgcatlist ( $id, $nom, $couleur )
  {
    $this->id = $id;
    $this->nom = $nom;
    $this->couleur = $couleur;
    $this->data=array();
  }
  
  function add ( $id, $nom )
  {
    $this->data[] = "<li><a href=\"index.php?id_pgcategory=$id\">$nom</a></li>";
  }
  
  function html_render ()
  {
    return 
    "<div class=\"pgcatlist\" style=\"background: #".$this->couleur.";\">\n".
    "<h2><a href=\"index.php?id_pgcategory=".$this->id."\">".$this->nom."</a></h2>\n".
    "<div class=\"pgcatlistdata\">\n<ul>\n".implode("\n",$this->data)."\n</ul>\n</div>\n</div>\n";
  }
}




?>