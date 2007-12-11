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
    if ( is_null($this->couleur) || is_null($this->nom) )
      return 
      "<div class=\"pgcatlist\">\n".
      "<div class=\"pgcatlistdata\">\n<ul>\n".implode("\n",$this->data)."\n</ul>\n</div>\n</div>\n";
    else
      return 
      "<div class=\"pgcatlist\" style=\"background: #".$this->couleur.";\">\n".
      "<h2><a href=\"index.php?id_pgcategory=".$this->id."\">".$this->nom."</a></h2>\n".
      "<div class=\"pgcatlistdata\">\n<ul>\n".implode("\n",$this->data)."\n</ul>\n</div>\n</div>\n";
  }
}



class colortabshead extends tabshead
{
  function html_render()
  {
    global $wwwtopdir;
    
    $this->buffer .= "<div class=\"".$this->tclass."\"";
    foreach ($this->entries as $entry)
    {
      if ($this->sel == $entry[0])
        $this->buffer .= " style=\"background: #".$entry[3]."\"";
    }
    $this->buffer .= ">\n";
    
    foreach ($this->entries as $entry)
    {
      $this->buffer .= "<span";
      if ($this->sel == $entry[0])
        $this->buffer .= " class=\"selected\"";
      $this->buffer .= "><a href=\"" . htmlentities($wwwtopdir . $entry[1],ENT_NOQUOTES,"UTF-8") . "\"";
      if ($this->sel == $entry[0])
      {
        $this->buffer .= " class=\"selected\" style=\"color: #".$entry[3]."\"";
      }
      else
        $this->buffer .= " style=\"background: #".$entry[3]."\"";
        
      $this->buffer .= " title=\"" .  htmlentities($entry[2],ENT_QUOTES,"UTF-8") . "\">" . $entry[2] . "</a></span>\n";
    }
    $this->buffer .= "<div class=\"clearboth\"></div>\n";  
    $this->buffer .= "</div>\n";  
    
    return $this->buffer;
  }
}

class pgtabshead extends colortabshead
{
  
  function pgtabshead ( &$db, $id_pgcategory )
  {
    $this->entries = array();
    
    $req = new requete($db,
      "SELECT id_pgcategory, nom_pgcategory, couleur_bordure_web_pgcategory ".
      "FROM pg_category ".
      "WHERE id_pgcategory_parent='1' ".
      "ORDER BY ordre_pgcategory");
    
    while ( list($id,$nom,$couleur) = $req->get_row() )
      $this->entries[] = array("pg$id","pg2/?id_pgcategory=$id",$nom,$couleur);
    
    $this->sel = "pg".$id_pgcategory;  
    $this->tclass = "tabs";
  }
  
  
}


?>