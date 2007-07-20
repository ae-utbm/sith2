<?

$topdir="./";
require_once($topdir . "include/serverwebdavae.inc.php");
require_once($topdir . "include/entities/files.inc.php");
require_once($topdir . "include/entities/folder.inc.php");
require_once($topdir . "include/entities/asso.inc.php");

class serverwebdavaedrive extends webdavserverae
{

  function get_entity_for_path ( $path )
  {
    $tokens = explode("/",$path);
    
    if ( count($tokens) > 0 && empty($tokens[0]) )
      array_shift($tokens);
    
    if ( count($tokens) > 0 && empty($tokens[count($tokens)-1]) )
      array_pop($tokens);
    
    if ( count($tokens) == 0 )
    {
      $ent = new dfolder($this->db); // la racine est en lecture seule
      $ent->displayname="WebDAV";
      $ent->date_ajout=0;
      return $ent;
    }
      
    $ent=null;
    $id_folder_parent=null;

    foreach ( $tokens as $idx => $token )
    {
      //echo "token($idx) : $token\n";
      if ( $token != "." )
      {
        if ( $token == ".." )
        {
          if ( !is_null($ent) && get_class($ent) == "dfolder" )
          {
            $prev = $ent;
            $ent = new dfolder($this->db,$this->dbrw); 
            $ent->load_by_id($prev->id_folder_parent);
          }
        }
        else
        {
          $ent = new dfolder($this->db,$this->dbrw); 
          if ( !$ent->load_by_nom_fichier($id_folder_parent,$token) )
          {
            $ent = new dfile($this->db,$this->dbrw); 
            
            //echo "not a folder, tries for a file\n";
            
            if ( !$ent->load_by_nom_fichier($id_folder_parent,$token) )
              return null;
              
            //echo "it's a file ".$ent->nom_fichier."\n";
              
            if ( $idx != count($tokens)-1 ) // ce n'est pas le dernier element, donc c'est faux (tm)
              return null;
              
            return $ent;
          }
        }
        //echo "continues (".$ent->nom_fichier.")\n";
        $id_folder_parent = $ent->id;
      }
    }
    
    return $ent;  
  }

  function entinfo($path,$ent) 
  {
    $info = array();
    
    $info["props"] = array();
    $info["props"][] = $this->mkprop("displayname",     $ent->nom_fichier);
    $info["props"][] = $this->mkprop("creationdate",    $ent->date_ajout);
    $info["props"][] = $this->mkprop("getlastmodified", $ent->date_modif);

    if (get_class($ent) == "dfolder")
    {
      $info["props"][] = $this->mkprop("resourcetype", "collection");
      $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");      
      $info["path"] = $this->_urlencode($this->_slashify($path)); 
    }
    else
    {
      $info["props"][] = $this->mkprop("resourcetype", "");
      $info["props"][] = $this->mkprop("getcontenttype", $ent->mime_type);        
      $info["props"][] = $this->mkprop("getcontentlength", $ent->taille);
      $info["path"] = $this->_urlencode($path); 
    }

    return $info;
  }
  
  function PROPFIND(&$options, &$files) 
  {
    $ent = $this->get_entity_for_path($options["path"]);

    if ( is_null($ent) )
      return false;
      
    if ( $ent->is_valid() && !$ent->is_right($this->user,DROIT_LECTURE) )
      return "403 Forbidden";
      
    $files["files"] = array();
    $files["files"][] = $this->entinfo($options["path"],$ent);

    if ( !empty($options["depth"]) && get_class($ent) == "dfolder" )
    { 
      $options["path"] = $this->_slashify($options["path"]);
            
      $sub = $ent->get_folders($this->user);
      $sent= new dfolder($this->db);
      while ( $row = $sub->get_row() )
      {
        $sent->_load($row);
        $files["files"][] = $this->entinfo($options["path"].$sent->nom_fichier,$sent);
        // TODO recursion needed if "Depth: infinite"
      }

      $sub = $ent->get_files($this->user);
      $sent= new dfile($this->db);
      while ( $row = $sub->get_row() )
      {
        $sent->_load($row);
        $files["files"][] = $this->entinfo($options["path"].$sent->nom_fichier,$sent);
      }
    }
    return true;
  } 
    
  function GET(&$options) 
  {
    $ent = $this->get_entity_for_path($options["path"]);

    if ( is_null($ent) )
      return false;

    if ( $ent->is_valid() && !$ent->is_right($this->user,DROIT_LECTURE) )
      return "403 Forbidden";

    if ( get_class($ent) == "dfolder" )
    {
      $path = $this->_slashify($options["path"]);
      
      if ($path != $options["path"])
      {
        header("Location: ".$this->base_uri.$path);
        exit;
      }
      
		  header("Content-Type: text/html; charset=utf-8");
		  
      $format = "%15s  %-19s  %-s\n";

      echo "<html><head><title>Index of ".htmlspecialchars($options['path'])."</title></head>\n";
          
      echo "<h1>Index of ".htmlspecialchars($options['path'])."</h1>\n";
          
      echo "<pre>";
      printf($format, "Size", "Last modified", "Filename");
      echo "<hr>";

      $sub = $ent->get_folders($this->user);
      while ( $row = $sub->get_row() )
      {
        $name = htmlspecialchars($row['nom_fichier_folder']);
        printf($format, 
                number_format($row['taille_folder']),
                $row['date_modif_folder'], 
                "<a href='$name/'>$name</a>");
      }

      $sub = $ent->get_files($this->user);
      while ( $row = $sub->get_row() )
      {
        $name = htmlspecialchars($row['nom_fichier_file']);
        printf($format, 
                number_format($row['taille_file']),
                $row['date_ajout_file'], 
                "<a href='$name'>$name</a>");
      }
      echo "</pre>";
      echo "</html>\n";
      return false;
    }
        
    $options['mimetype'] = $ent->mime_type; 
    $options['mtime'] = $ent->date_modif;
    $options['size'] = $ent->taille;
    $options['stream'] = fopen($ent->get_real_filename(), "r");
        
    return true;
  }
  
  function PUT(&$options) 
  {
    return false;
  }
  
}

$dav = new serverwebdavaedrive();

if ( isset($_GET["test"]) )
{
  ini_set("display_errors", 1);

  echo "<pre>";
  echo "\n/public/AE\n";
  print_r($dav->get_entity_for_path("/public/AE"));
  echo "\n/public/AE/\n";
  print_r($dav->get_entity_for_path("/public/AE/"));
  echo "\n/public/AE/Com/ChargeGraphique.pdf\n";
  print_r($dav->get_entity_for_path("/public/AE/Com/ChargeGraphique.pdf"));
  echo "\n/\n";
  print_r($dav->get_entity_for_path("/"));
  echo "\n\n";
  echo "</pre>";
  $opt["path"] = "/";
  $dav->GET($opt);
  exit();  
}

$dav->ServeRequest();

?>