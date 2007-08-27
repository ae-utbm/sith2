<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

require_once($topdir."include/entities/basedb.inc.php");

/**
 * @file
 */

/**
 * Page wiki
 */
class wiki extends basedb
{
  
  var $id_wiki_parent;  
  var $id_asso;
  var $id_rev_last;
  
  var $name;
  var $fullpath;
  var $namespace_behaviour;
  
  var $rev_id;
  var $rev_id_utilisateur;
  var $rev_date;
  var $rev_contents;
  var $rev_title;
  var $rev_comment;
  
	function load_by_id ( $id )
	{
	 
		$req = new requete($this->db, "SELECT * 
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki`.`id_rev_last`=`wiki_rev`.`id_rev` )
				WHERE `wiki`.`id_wiki` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
  
	function load_by_id_and_rev ( $id, $id_rev )
	{
	 
		$req = new requete($this->db, "SELECT * 
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki_rev`.`id_rev`='" . mysql_real_escape_string($id) . "')
				WHERE `wiki`.`id_wiki` = '" . mysql_real_escape_string($id_rev) . "'
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
	
	function load_by_name ( $parent, $name )
	{
	 
		$req = new requete($this->db, "SELECT * 
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki`.`id_rev_last`=`wiki_rev`.`id_rev` )
				WHERE `name_wiki` = '" . mysql_real_escape_string($name) . "'
				AND `id_wiki_parent`= '" . mysql_real_escape_string($parent->id) . "'
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
	
	function load_by_fullpath ( $fullpath )
	{
	 
		$req = new requete($this->db, "SELECT * 
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki`.`id_rev_last`=`wiki_rev`.`id_rev` )
				WHERE `fullpath_wiki` = '" . mysql_real_escape_string($fullpath) . "'
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
  
	function load_by_fullpath_and_rev ( $fullpath, $id_rev )
	{
	 
		$req = new requete($this->db, "SELECT * 
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki_rev`.`id_rev`='" . mysql_real_escape_string($id_rev) . "')
				WHERE `fullpath_wiki` = '" . mysql_real_escape_string($fullpath) . "'
				LIMIT 1");
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}  
	
	function _load( $row )
	{
	  $this->id = $row["id_wiki"];
	  
		$this->id_utilisateur = $row['id_utilisateur'];
		$this->id_groupe = $row['id_groupe'];
		$this->id_groupe_admin = $row['id_groupe_admin'];
		$this->droits_acces = $row['droits_acces_wiki'];
		$this->modere = true;
		
    $this->id_wiki_parent = $row['id_wiki_parent']; 
    $this->id_asso = $row['id_asso'];
    $this->id_rev_last = $row['id_rev_last'];
  
    $this->name = $row['name_wiki'];
    $this->fullpath = $row['fullpath_wiki'];
    $this->namespace_behaviour = $row['namespace_behaviour'];
  
    $this->rev_id = $row['id_rev'];
    $this->rev_id_utilisateur = $row['id_utilisateur_rev'];
    $this->rev_date = strtotime($row['date_rev']);
    $this->rev_contents = $row['contents_rev'];
    $this->rev_title = $row['title_rev'];
    $this->rev_comment = $row['comment_rev'];
	}
	
  function create ( $parent, $id_asso, $name, $namespace, $title, $contents, $comment="créée!")
  {
		if ( strlen($name) > 64 )
		  return false;
		
		$this->id_wiki_parent = $parent->id;
    $this->id_asso = $id_asso;
    
    $this->name = $name;
    $this->namespace_behaviour = $namespace;
    
    if ( !empty($parent->fullpath) )
      $this->fullpath = $parent->fullpath.":".$this->name;
    else
      $this->fullpath =$this->name;
      
		if ( strlen($this->fullpath) > 512 )
		  return false;
		  
    $req = new insert($this->dbrw,"wiki", array (
      "id_utilisateur" => $this->id_utilisateur,
      "id_groupe" => $this->id_groupe,
      "id_groupe_admin" => $this->id_groupe_admin,
      "droits_acces_wiki" => $this->droits_acces,
      "id_wiki_parent" => $this->id_wiki_parent,
      "id_asso" => $this->id_asso,
      "id_rev_last" => null,
      "name_wiki" => $this->name,
      "fullpath_wiki" => $this->fullpath,
      "namespace_behaviour" => $this->namespace_behaviour));

		if ( $req )
			$this->id = $req->get_id();
		else
		{
			$this->id = null;
			return false;
		} 
    
		$req = new requete($this->db, "SELECT id_wiki 
		    FROM `wiki_ref_missingwiki`
				WHERE `fullname_wiki_rel` = '" . mysql_real_escape_string($this->fullpath) . "'");    
    
    while ( $row = $req->get_row() )
    {
      unset($row[0]);
      $row["id_wiki_rel"] = $this->id;
      new insert($this->dbrw,"wiki_ref_wiki",$row);
    }  
      
    new delete($this->dbrw,"wiki_ref_missingwiki",array("fullname_wiki_rel"=>$this->fullpath));
      
    return $this->revision($this->id_utilisateur,$title, $contents, $comment);
  }
	
	function update_last_rev()
	{
	  new update($this->dbrw,"wiki",array("id_rev_last"=>$this->id_rev_last),array("id_wiki"=>$this->id));
	}
	
	function revision ( $id_utilisateur, $title, $contents, $comment="" )
	{
    $this->rev_id_utilisateur = $id_utilisateur;
    $this->rev_date = time();
    $this->rev_contents = $contents;
    $this->rev_title = $title;
    $this->rev_comment = $comment;
    
    $req = new insert($this->dbrw,"wiki_rev", array (
      "id_wiki" => $this->id,
      "id_utilisateur_rev" => $this->rev_id_utilisateur,
      "date_rev" => date("Y-m-d H:i:s",$this->rev_date),
      "contents_rev" => $this->rev_contents,
      "title_rev" => $this->rev_title,
      "comment_rev" => $this->rev_comment));   
       
		if ( $req )
			$this->rev_id = $req->get_id();
		else
		{
			$this->rev_id = null;
			return false;
		} 
		
    $this->id_rev_last = $this->rev_id;
    
    $this->update_last_rev();
    
    $this->update_references($this->rev_contents);
    
    return true;
	}
	
	function update()
	{
    new update($this->dbrw,"wiki", array (
      "id_utilisateur" => $this->id_utilisateur,
      "id_groupe" => $this->id_groupe,
      "id_groupe_admin" => $this->id_groupe_admin,
      "droits_acces_wiki" => $this->droits_acces,
      "id_asso" => $this->id_asso,
      "namespace_behaviour" => $this->namespace_behaviour),array("id_wiki"=>$this->id));	 
	}
	
	/**
	 * Met à jours les réfences de la page sur
	 * la base du contenu fourni.
	 * @param $contents Contenu à analyser
	 */
  function update_references($contents)
  {
    new requete($this->dbrw,
      "DELETE FROM wiki_ref_file ".
      "WHERE `id_wiki` = '" . mysql_real_escape_string($this->id) . "'");
    
    new requete($this->dbrw,
      "DELETE FROM wiki_ref_wiki ".
      "WHERE `id_wiki` = '" . mysql_real_escape_string($this->id) . "'");    

    new requete($this->dbrw,
      "DELETE FROM wiki_ref_missingwiki ".
      "WHERE `id_wiki` = '" . mysql_real_escape_string($this->id) . "'");   

    $this->_ref_cache=array("f"=>array(),"w"=>array(),"mw"=>array());

    $this->_update_references($contents,"#\[\[([^\]]+?)\]\]#i");
    $this->_update_references($contents,"#\{\{([^\}]+?)\}\}#i",true);
    
  }
  
  function add_rel_wiki ( $fullname )
  {
    $id_wiki = $this->get_id_fullpath($fullname);
    if ( !is_null($id_wiki))
    {
      if ( !isset($this->_ref_cache["w"][$id_wiki]) )
      {
        new insert($this->dbrw,"wiki_ref_wiki",array("id_wiki"=>$this->id,"id_wiki_rel"=>$id_wiki));
        $this->_ref_cache["w"][$id_wiki]=1;
      }
    }
    else
    {
      if ( !isset($this->_ref_cache["mw"][$fullname]) )
      {
        new insert($this->dbrw,"wiki_ref_missingwiki",array("id_wiki"=>$this->id,"fullname_wiki_rel"=>$fullname));
        $this->_ref_cache["mw"][$fullname]=1;
      }         
    }
  } 
  
  function add_rel_file ( $id_file )
  {
    if ( !isset($this->_ref_cache["f"][$id_file]) ) 
    {
      new insert($this->dbrw,"wiki_ref_file",array("id_wiki"=>$this->id,"id_file"=>$id_file));
      $this->_ref_cache["f"][$id_file]=1;
    }
  }
  
  function _update_references( $contents, $regexp, $media=false )
  {
    if ( !preg_match_all ( $regexp, $contents, $matches ) ) return;
    
    foreach( $matches[1] as $link )
    {
      list($link,$dummy) = explode("|",$link,2);
      
      if ( $media )
        list($link,$dummy) = explode("?",$link,2);
        
      if( preg_match('/^([a-zA-Z]+):\/\//',$link) )
      {
        if ( preg_match("#^(dfile:\/\/|.*d\.php\?id_file=)([0-9]*)(.*)$#i",$link,$match) )
          $this->add_rel_file($match[2]);
        
        elseif ( !$media && preg_match("#^wiki:\/\/(.*)$#i",$link,$match) )
          $id_wiki = $this->get_id_fullpath($match[1]);
      }
      elseif ( !preg_match("#(\.|/)#",$link) )
      {
        $wiki = preg_replace("/[^a-z0-9\-_:#]/","_",strtolower(utf8_enleve_accents($link)));
        
        if ( $wiki{0} == ':' )
          $wiki = substr($wiki,1);
        else
          $wiki = $this->get_scope().$wiki;
            
        $this->add_rel_wiki($wiki);
      }
    }
  }
  
  function get_id_fullpath($fullpath)
  {
		$req = new requete($this->db, "SELECT id_wiki 
		    FROM `wiki`
				WHERE `fullpath_wiki` = '" . mysql_real_escape_string($fullpath) . "'
				LIMIT 1");    
				
		if ( $req->lines != 1 )
		  return null;
	
		list($id) = $req->get_row();
		
		return $id;
  }
  
  /**
   * 
   */
  function get_scope ()
  {
    if ( empty($this->fullpath) )
      return "";
    elseif ( $this->namespace_behaviour ) // Pour éviter de polluer la racine
      return $this->fullpath.":";
    else
      return substr($this->fullpath,0,-strlen($this->name));
  }
  
  function __map_childs($id_wiki)
  {
		$req = new requete($this->db, "SELECT wiki.id_wiki, name_wiki, title_rev
		    FROM `wiki`
		    INNER JOIN `wiki_rev` 
		      ON ( `wiki`.`id_wiki`=`wiki_rev`.`id_wiki` 
		           AND `wiki`.`id_rev_last`=`wiki_rev`.`id_rev` )
				WHERE `wiki`.`id_wiki_parent` = '" . mysql_real_escape_string($id_wiki) . "'");    
				
		if ( $req->lines == 0 )
		  return "";	
				
    $buffer = "<ul>\n";
		while ( $row = $req->get_row() )
		{
      $buffer .= "<li>".
        "<a class=\"wpage\" href=\"?name=".$row['name_wiki']."\">".
        ($row['name_wiki']?$row['name_wiki']:"(sans nom)")."</a> ".
        " : <span class=\"wtitle\">".
        htmlentities($row['title_rev'],ENT_NOQUOTES,"UTF-8").
        "</span> ".
        $this->__map_childs($row['id_wiki'])."</li>\n";		  
		}
				
    $buffer .= "</ul>\n";
    return $buffer;
  }

  
  function get_stdcontents()
  {
    global $conf;
    $conf["linkscontext"] = "wiki";
    $conf["linksscope"] = $this->get_scope();
    
    $cts = new wikicontents($this->rev_title,$this->rev_contents);
    
    if ( preg_match("#@@([^a-z0-9\-_:]*):pagesmap@@#",$cts->buffer,$match) )
    {
      $wiki = $match[1];
      
      if ( $wiki{0} == ':' )
        $wiki = substr($wiki,1);
      else
        $wiki = $this->get_scope().$wiki;      
      
      $id = $this->get_id_fullpath($wiki);
      
      if ( !is_null($id) )
      {
        $buffer = "<ul>\n";
        $buffer .= $this->__map_childs($id);
        $buffer .= "</ul>\n";
        $cts->buffer = str_replace("@@".$match[1].":pagesmap@@", $buffer, $cts->buffer);
      }
    }
    if ( preg_match("#@@([^a-z0-9\-_:]*):missingpages@@#",$cts->buffer,$match) )
    {
      $wiki = $match[1];
      
      if ( $wiki{0} == ':' )
        $wiki = substr($wiki,1);
      else
        $wiki = $this->get_scope().$wiki;      
      
      $req = new requete($site->db,"SELECT fullname_wiki_rel AS fullpath_wiki ".
        "FROM wiki_ref_missingwiki ".
        "WHERE fullname_wiki_rel LIKE '".mysql_real_escape_string($wiki)."%' ".
        "GROUP BY fullname_wiki_rel ".
    		"ORDER BY fullname_wiki_rel");      
      
      if ( $req->lines== 0 )
        $buffer ="(aucune page manquante)";
      else
      {
        $buffer = "<ul>\n";
  		  while ( $row = $req->get_row() )
          $buffer .= "<li><a class=\"wpage\" href=\"?name=".$row['fullpath_wiki']."\">".
            $row['fullpath_wiki']."</a></li>\n"   
        $buffer .= "</ul>\n";
      }
      
      $cts->buffer = str_replace("@@".$match[1].":missingpages@@", $buffer, $cts->buffer);
    }
    $conf["linksscope"]="";
    $conf["linkscontext"]="";
    return $cts;
  }
  
	function is_admin ( &$user )
	{
		if ( $user->is_in_group("wiki_admin") )
		  return true;	

		return parent::is_admin($user);
	}
	
	function herit ( $basedb )
	{
		$this->id_utilisateur = null;
		$this->id_groupe = $basedb->id_groupe;	
		$this->id_groupe_admin = $basedb->id_groupe_admin;
		$this->modere=true;
	  $this->droits_acces = $basedb->droits_acces;
	}
	
	function set_rights ( $user,  $rights, $id_group, $id_group_admin )
	{
		if ( $this->is_admin($user) && $id_group_admin )
			$this->id_groupe_admin = $id_group_admin;
		if ( !$this->id_utilisateur )
			$this->id_utilisateur = $user->id;
		$this->id_groupe = $id_group;	
	  $this->droits_acces = $rights;
	}
	
	function is_locked(&$user)
	{
	  $req = new requete($this->dbrw,
      "SELECT id_utilisateur FROM wiki_lock ".
      "WHERE `id_wiki` = '" . mysql_real_escape_string($this->id) . "' ".
      "AND time_lock >= '".date("Y-m-d H:i:s",time()-900)."' ".
      "LIMIT 1");
    
    if ( $req->lines == 0 )
    {
      new delete($this->dbrw,"wiki_lock",array("id_wiki"=>$this->id)); // Nettoyage      
      return false;
    }
    
    list($uid) = $req->get_row();
    
    if ( $uid == $user->id_utilisateur )
      return false;
    
	  return $uid; 
	}
	
	function lock(&$user)
	{
	  $this->unlock($user); // Supprime d'eventuels vieux verrous...
    new insert($this->dbrw,"wiki_lock",
      array(
        "id_wiki"=>$this->id,
        "id_utilisateur"=>$user->id,
        "time_lock"=>date("Y-m-d H:i:s")));
	}
	
	function unlock(&$user)
	{
    new delete($this->dbrw,"wiki_lock",
      array(
        "id_wiki"=>$this->id,
        "id_utilisateur"=>$user->id
        ));
	}
	
	function force_unlock()
	{
    new delete($this->dbrw,"wiki_lock",array("id_wiki"=>$this->id));
	}
	
	
}





?>