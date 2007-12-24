<?php

require_once($topdir."include/cts/special.inc.php");

function pgicon ( $color )
{
  global $topdir;
  
  if ( !preg_match('/^([0-9A-F]{6})$/i',$color) )
    return $topdir."images/icons/16/misc.png";
  
  $file = $topdir."var/cache/icon".$color.".png";
  
  if ( file_exists($file) )
    return $file;
  
  $img1 = imagecreatetruecolor(64,64);
  imagefill($img1,0,0,imagecolorallocate($img1,255,255,255));
  imagefilledellipse($img1,31,31,50,50,hexdec($color));
  $img2 = imagecreatetruecolor(16,16);
  imagecopyresampled($img2,$img1,0,0,0,0,16,16,64,64);
  imagepng($img2,$file);
  imagedestroy($img1);
  imagedestroy($img2);
  return $file;
}

class pglegals extends itemlist
{
  
  var $conditions;
  var $num;
  function pglegals()
  {
    $this->conditions=array();
    $this->num=1;
    $this->class="minilegals";
  }
  
  function add_date_validite($date, $datemaj, $text="Information", $post="")
  {
    if ( is_string($date) )
      $date = strtotime($date);
   
    if ( $date === false ) 
      $date = null;
   
    if ( is_string($datemaj) )
      $datemaj = strtotime($datemaj);
        
    if ( substr($text,-1) == "s" )
      $pl=true;
    else
      $pl=false;
        
    if ( is_null($date)  || $date < time() )
      return $this->add_condition("$text non garantie".($pl?"s":"")."$post, date de validité expirée (information datant du ".date("d/m/Y",$datemaj).").");
    
    return $this->add_condition("$text valable".($pl?"nt":"")." jusqu'au ".date("d/m/Y",$date)."$post.");
  }
  
  function add_condition($condition)
  {
    if ( isset($this->conditions[$condition]) )
      return $this->conditions[$condition];
      
    $n = $this->num;
    $this->num++;
    $this->conditions[$condition] = $n;
    
    $this->add("<a name=\"legal$n\"></a>".$n." : ".htmlentities($condition,ENT_QUOTES,"UTF-8"));
    
    return "<a class=\"renvoi\" href=\"#legal$n\">".$n."</a>";
  }
  
  
  
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

class pgfichelist extends stdcontents
{
  
  function pgfichelist ( &$req )
  {
    if ( $req->lines == 0 )
      return;    
    
    $legals = new pglegals();

    
    $this->buffer = "<div class=\"pgfichelist\">";
    
    while ( $row = $req->get_row() )
    {
      
      $this->buffer .= "<div class=\"pgfiche\">";
      
      $this->buffer .= "<h3><a href=\"./?id_pgfiche=".$row["id_pgfiche"]."\">".htmlentities($row["nom_pgfiche"],ENT_QUOTES,"UTF-8")."</a> ".$legals->add_date_validite($row["date_validite_pgfiche"], $row["date_maj_pgfiche"],"Informations")."</h3>";
      
      $this->buffer .= "<p class=\"adresse\">".htmlentities($row["numrue_pgfiche"],ENT_QUOTES,"UTF-8")." ".htmlentities($row["nom_typerue"],ENT_QUOTES,"UTF-8")." ".htmlentities($row["nom_rue"],ENT_QUOTES,"UTF-8")." ".htmlentities($row["nom_ville"],ENT_QUOTES,"UTF-8")."</p>";       
      
      if ($row["tel_pgfiche"])
        $this->buffer .= "<p class=\"tel\">Tel: ".htmlentities(telephone_display($row["tel_pgfiche"]),ENT_QUOTES,"UTF-8")."</p>";   
        
      if ($row["fax_pgfiche"])
        $this->buffer .= "<p class=\"fax\">Fax: ".htmlentities(telephone_display($row["fax_pgfiche"]),ENT_QUOTES,"UTF-8")."</p>";   
        
      if ($row["email_pgfiche"])
        $this->buffer .= "<p class=\"email\">E-mail: ".htmlentities($row["email_pgfiche"],ENT_QUOTES,"UTF-8")."</p>";
      if ($row["website_pgfiche"])
        $this->buffer .= "<p class=\"siteweb\">Site web: <a href=\"".htmlentities($row["website_pgfiche"],ENT_QUOTES,"UTF-8")."\">".htmlentities($row["website_pgfiche"],ENT_QUOTES,"UTF-8")."</a></p>"; 
      
          
      $this->buffer .= "<p class=\"description\">".htmlentities($row["description_pgfiche"],ENT_QUOTES,"UTF-8")."</p>";      
      
      $this->buffer .= "<p class=\"more\"><a href=\"./?id_pgfiche=".$row["id_pgfiche"]."\">Plus d'information : horraires, tarifs, plan d'accès</a></p>";       
      
      $this->buffer .= "</div>";
      
    }
    
    
    $this->buffer .= "</div>";
   
    $this->buffer .= $legals->html_render();
    
  }

}

class pgfichelistcat extends pgfichelist
{
  function pgfichelistcat ( &$pgcategory )
  {
    $req = new requete($pgcategory->db,
      "SELECT COALESCE(extra.titre_extra_pgcategory,geopoint.nom_geopoint) AS nom_pgfiche, ".
      "COALESCE(extra.soustire_extra_pgcategory,pg_fiche.description_pgfiche) AS description_pgfiche, ".
      "pg_fiche.id_pgfiche, geopoint.lat_geopoint, geopoint.long_geopoint, ".
      "pg_fiche.tel_pgfiche, pg_fiche.fax_pgfiche, pg_fiche.email_pgfiche, pg_fiche.numrue_pgfiche,  ".
      "pg_fiche.website_pgfiche, pg_fiche.contraste_pgfiche, ".
      "pg_fiche.appreciation_pgfiche, pg_fiche.commentaire_pgfiche, ".
      "pg_fiche.date_maj_pgfiche, pg_fiche.date_validite_pgfiche, ".
      "pg_rue.id_rue, pg_rue.nom_rue, pg_typerue.nom_typerue, ".
      "loc_ville.nom_ville, loc_ville.id_ville, ".
      "'".$pgcategory->couleur_bordure_web."' AS couleur_bordure_web_pgcategory, ".
      "'".$pgcategory->couleur_titre_web."' AS couleur_titre_web_pgcategory, ".
      "'".$pgcategory->couleur_contraste_web."' AS couleur_contraste_web_pgcategory ".
      "FROM `pg_fiche` ".
      "INNER JOIN `geopoint` ON (pg_fiche.id_pgfiche=geopoint.id_geopoint) ".
      "LEFT JOIN `pg_rue` ON (pg_fiche.id_rue=pg_rue.id_rue) ".
      "LEFT JOIN `pg_typerue` ON (pg_rue.id_typerue=pg_typerue.id_typerue) ".
      "INNER JOIN `loc_ville` ON (loc_ville.id_ville=COALESCE(pg_rue.id_ville,geopoint.id_ville)) ".
      "LEFT JOIN pg_fiche_extra_pgcategory AS extra ON (pg_fiche.id_pgfiche=extra.id_pgfiche AND extra.id_pgcategory='".$pgcategory->id."') ".
      "WHERE (pg_fiche.id_pgcategory='".$pgcategory->id."' OR extra.id_pgcategory='".$pgcategory->id."') ".
      "ORDER BY 1");
    
    if ( $req->lines == 0 )
      return;
    
    $this->pgfichelist($req);
  }
}

class pgfichefull extends board
{
  
  function pgfichefull ( &$fiche )
  {
    $legals = new pglegals();
    
    $this->board(htmlentities($fiche->nom,ENT_QUOTES,"UTF-8").$legals->add_date_validite($fiche->date_validite, $fiche->date_maj,"Informations"," hors mention contraire") );

    $this->add(new wikicontents("Description",$fiche->longuedescription),true);
        
    $list = new itemlist("Contact");
    
    $rue = new rue($fiche->db);
    $ville = new ville($fiche->db);
    $typerue = new typerue($fiche->db);
    
    $rue->load_by_id($fiche->id_rue);
    $typerue->load_by_id($rue->id_typerue);
    if ( !$ville->load_by_id($rue->id_ville) )
      $ville->load_by_id($fiche->id_ville);
    
    $list->add("Adresse: ".htmlentities($fiche->numrue,ENT_QUOTES,"UTF-8")." ".htmlentities($typerue->nom,ENT_QUOTES,"UTF-8")." ".htmlentities($rue->nom,ENT_QUOTES,"UTF-8")." ".htmlentities($ville->nom,ENT_QUOTES,"UTF-8"));
    
    if ( $fiche->tel )
      $list->add("Téléphone: ".htmlentities(telephone_display($fiche->tel),ENT_QUOTES,"UTF-8"));
    if ( $fiche->fax )
      $list->add("Fax: ".htmlentities(telephone_display($fiche->fax),ENT_QUOTES,"UTF-8"));
    if ( $fiche->email )    
      $list->add("E-Mail: ".htmlentities($fiche->email,ENT_QUOTES,"UTF-8"));
    if ( $fiche->siteweb )    
      $list->add("Site internet: ".htmlentities($fiche->siteweb,ENT_QUOTES,"UTF-8"));
    if ( $fiche->adressepostal )    
      $list->add("Adresse postale: ".htmlentities($fiche->adressepostal,ENT_QUOTES,"UTF-8"));
      
    $this->add($list,true);  
      
    $req = new requete($fiche->db,"SELECT ".
      "valeur_reduction, unite_reduction, commentaire_reduction, date_maj_reduction, date_validite_reduction, ".
      "pg_typereduction.nom_typereduction, pg_typereduction.id_typereduction ".
      "FROM pg_fiche_reduction ".
      "INNER JOIN pg_typereduction USING(id_typereduction) ".
      "WHERE pg_fiche_reduction.id_pgfiche='".mysql_real_escape_string($fiche->id)."' ".
      "ORDER BY nom_typereduction");

    if ( $req->lines > 0 )
    {
      $list = new itemlist("Reductions ".$legals->add_condition("Reductions soumises à conditions, voir description de chaque type de réduction."));
      
      while ( $row = $req->get_row() )
      {
        $list->add("<a href=\"bplans.php?page=reductions#reduc".$row["id_typereduction"]."\">".htmlentities($row["nom_typereduction"],ENT_QUOTES,"UTF-8")."</a> : ".htmlentities($row["valeur_reduction"],ENT_QUOTES,"UTF-8")." ".htmlentities($row["unite_reduction"],ENT_QUOTES,"UTF-8")." ".htmlentities($row["commentaire_reduction"],ENT_QUOTES,"UTF-8")."".$legals->add_date_validite($row["date_validite_reduction"], $row["date_maj_reduction"], "Réduction"));
        
      }
        
      $this->add($list,true);  
    }

    /*
    $gmap = new gmap("mapfiche");
    if ( !is_null($fiche->lat) )
      $gmap->add_geopoint($fiche);
    $this->add($gmap,true);
    */
    $this->add($legals);
  }
  
  
  
  
  
  
  
}

?>