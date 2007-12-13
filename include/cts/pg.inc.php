<?php

function pgicon ( $color )
{
  global $topdir;
  
  if ( !preg_match('^([0-9A-Fa-f]{6})$',$color) )
    return $topdir."images/icons/16/misc.png";
  
  $file = $topdir."/var/cache/icon".$color.".png";
  
  if ( file_exists($file) )
    return $file;
  
  $img = imagecreatetruecolor(16,16);
  $back = imagecolorallocate($img,255,255,255);
  imagefilledellipse($img,8,8,7,7,hexdec($color));
  imagepng($img,$file);
  return $file;
}

class pgcatminilist extends stdcontents
{
  var $id;
  var $nom;
  var $couleur;
  var $data;
  
  function pgcatminilist ( $id, $nom, $couleur )
  {
    $this->id = $id;
    $this->nom = $nom;
    $this->couleur = $couleur;
    $this->data=array();
  }
  
  function add ( $id, $nom )
  {
    $this->data[] = "<a href=\"index.php?id_pgcategory=$id\">$nom</a>";
  }
  
  function html_render ()
  {
    return 
      "<div class=\"pgcatminilist\" style=\"background: #".$this->couleur.";\">\n".
      "<h3><a href=\"index.php?id_pgcategory=".$this->id."\">".$this->nom."</a></h3>\n".
      "<div class=\"pgcatminilistdata\">\n".implode(", ",$this->data)."</div>\n</div>\n";
  }
}

class pgcatlist extends stdcontents
{
  var $couleur;
  var $data;
  
  function pgcatlist ( $couleur )
  {
    $this->couleur = $couleur;
    $this->data=array();
  }
  
  function add ( $id, $nom )
  {
    $this->data[] = "<li><a href=\"index.php?id_pgcategory=$id\"><img src=\"".pgicon($this->couleur)."\" class=\"icon\" alt=\"\" /> $nom</a></li>";
  }
  
  function html_render ()
  {
    return 
      "<div class=\"pgcatlist\"><ul>\n".implode("\n",$this->data)."\n</ul>\n</div>\n";
  }
}



class colortabshead extends tabshead
{
  function html_render()
  {
    global $wwwtopdir;
    
    $this->buffer .= "<div class=\"".$this->tclass."\">\n";
    
    foreach ($this->entries as $entry)
    {
      $this->buffer .= "<span";
      if ($this->sel == $entry[0])
        $this->buffer .= " class=\"selected\"";
        
      $this->buffer .= "><a href=\"" . htmlentities($wwwtopdir . $entry[1],ENT_NOQUOTES,"UTF-8") . "\"";
      
      if ($this->sel == $entry[0])
        $this->buffer .= " class=\"selected\" style=\"background: #".$entry[3]."\"";
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
    $this->tclass = "tabs pgtabs";
  }
  
  
}


?>