<?php

/* Copyright 2007 - 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

define("PASSWORDFILE","svn.passwd");
define("AUTHFILE","svn.auth");
define("SVN_PATH","/var/lib/svn/");
define("PRIVATE_SVN","private/");
define("PUBLIC_SVN","public/");
define("AEINFO_SVN","aeinfo/");


class svn_depot extends stdentity
{
  /*
   * various valid datas
   */
  var $valid_rights=array("","r","rw");
  var $type_depot=array("public","private","aeinfo");

  /*
   * class private attributes
   */
  var $id;

  /* this function load an svn repository using an id */
  function load_by_id( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `svn_depot` WHERE `id_depot`='".mysql_real_escape_string($id)."' LIMIT 1");
    if( $req->lines == 1 )
    {
      $this->_load($req->get_row());
      return true;
    }
    else
    {
      $this->id = null;
      return false;
    }
  }

  /* load the svn values */
  function _load( $row )
  {
    $this->id = $row['id_depot'];
    $this->nom = $row['nom'];
    $this->type = $row['type'];
  }

  /* create the svn repository */
  function init_depot($nom,$type)
  {
    if( !in_array($type,$this->type_depot) )
      return false;

    $req = new insert($this->dbrw,
                      "svn_depot",
                      array("nom"=>mysql_real_escape_string($nom),
                            "type"=>$type
                          ));
    if ( !$req )
    {
      $this->id=null;
      return false;
    }
    else
    {
      $this->id = $req->get_id();
      $this->nom = mysql_real_escape_string($nom);
      $this->type = $type;

      if($type == "private")
        $dest = SVN_PATH.PRIVATE_SVN;
      elseif($type == "public")
        $dest == SVN_PATH.PUBLIC_SVN;
      elseif($type == "aeinfo")
        $dest == SVN_PATH.AEINFO_SVN;

      if(!exec("svnadmin create ".$dest.$this->nom))
      {
        // il faut supprimer l'entrée dans la base de donnée
        return false;
      }

      @mkdir("/tmp/".$thie->nom,0777);
      @mkdir("/tmp/".$this->nom."/branches",0777);
      @mkdir("/tmp/".$this->nom."/tags",0777);
      @mkdir("/tmp/".$this->nom."/trunk",0777);
      if(is_dir("/tmp/".$this->nom))
        exec("svn import /tmp/".$this->nom." file://".$dest." -m 'Initial import'");
      @rmdir("/tmp/".$this->nom."/branches");
      @rmdir("/tmp/".$this->nom."/tags");
      @rmdir("/tmp/".$this->nom."/trunk");
      @rmdir("/tmp/".$this->nom);

      return true;
    }
  }

  /* change the repository type */
  function change_repo_type($type)
  {
    if( !in_array($type,$this->type_depot) )
      return false;

    if($type == $this->$type)
      return false;

    if($this->type == "private")
      $from = SVN_PATH.PRIVATE_SVN;
    elseif($this->type == "public")
      $from = SVN_PATH.PUBLIC_SVN;
    elseif($this->type == "aeinfo")
      $from = SVN_PATH.AEINFO_SVN;

    if($type == "private")
      $dest = SVN_PATH.PRIVATE_SVN;
    elseif($type == "public")
      $dest = SVN_PATH.PUBLIC_SVN;
    elseif($type == "aeinfo")
      $dest = SVN_PATH.AEINFO_SVN;

    if(file_exists($dest.$name) || is_dir($dest.$name))
      return false;

    $req = new update($this->dbrw,
                      "svn_depot",
                      array("type"=>$type),
                      array("id_depot"=>$this->id)
                    );
    if (!$req)
      return false;

    print_r(@exec("/bin/mv ".$from.$this->nom." ".$dest.$this->nom));
    if(@exec("/bin/mv ".$from.$this->nom." ".$dest.$this->nom))
    {
      $this->delete_auth_file();
      $this->type=$type;
      $this->create_auth_file();
      return true;
    }
    else
    {
      $req = new update($this->dbrw,
                        "svn_depot",
                        array("type"=>$this->type),
                        array("id_depot"=>$this->id)
                      );
      return false;
    }
  }

  /* create associated trac */
  function init_trac()
  {
    // TODO
  }

  /* delete associated trac */
  function delete_trac()
  {
    // TODO
  }

  /* add an user to the repository with specified level */
  function add_user_access($user,$level)
  {
    if( !in_array($level,$this->valid_rights) )
      return false;
    if( $user->is_valid() )
    {
      $req = new requete($this->db,
                         "SELECT `id_utilisateur` FROM `svn_member_depot` "
                         . "WHERE `id_utilisateur` = '".$user->id."' "
                         . "AND `id_depot`='".$this->id."'");
      if($req->lines!=0)
      {
        return false;
      }
      else
      {
        return new insert($this->dbrw,
                          "svn_member_depot",
                          array("id_utilisateur"=>$user->id,
                                "id_depot"=>$this->id,
                                "right"=>$level
                              ));
      }
    }
    else
      return false;
  }

  /* delete acces for specified user */
  function del_user_access($user)
  {
    if( $user->is_valid() )
    {
      return new delete ($this->dbrw,"svn_member_depot",array("id_utilisateur"=>$user->id));
    }
    else
      return false;
  }

  /* update user access level */
  function update_user_access($user,$level)
  {
    if ( !in_array($level,$this->valid_rights) )
      return false;

    if( $user->is_valid() )
    {
      $req = new requete($this->db,
                         "SELECT `right` FROM `svn_member_depot` "
                         . "WHERE `id_utilisateur` = '".$user->id."' "
                         . "AND `id_depot`='".$this->id."'");
      if($req->lines==1)
      {
        list($_level)=$req->get_row();
        if($_level==$level)
          return false;
        else
          return new update($this->dbrw,
                            "svn_member_depot",
                            array("right"=>$level),
                            array("id_utilisateur"=>$user->id,
                                  "id_depot"=>$this->id)
                           );
      }
      else
        return false;
    }
    else
      return false;
  }


  /* create auth file */
  function create_auth_file()
  {
    if($this->type == "private")
      $path == SVN_PATH.PRIVATE_SVN;
    elseif($this->type == "public")
      $path == SVN_PATH.PUBLIC_SVN;
    elseif($this->type == "aeinfo")
      $path == SVN_PATH.AEINFO_SVN;

    if(!$handle = @fopen($path.AUTHFILE, "r"))
      return false;


    $req = new requete($this->db,
                       "SELECT `id_utilisateur`, `right` ".
                       "FROM `svn_member_depot` ".
                       "WHERE `id_depot`='".$this->id."'");

    if($req->lines == 0)
      return true;

    else
    {
      $readwrite=array();
      $readonly=array();
      $user = new utilisateur($this->db,$this->dbrw);
      while(list($id,$right)=$req->get_row())
      {
        if($user->load_by_id($id))
        {
          if(!is_null($user->alias))
          {
            if($right=="rw")
              $readwrite[]=$user->alias;
            elseif($right=="r")
              $readonly[]=$user->alias;
          }
          else
          {
            //ici soit on génère un alias soit on mail bombe !
          }
        }
      }
      if(empty($read) && empty($readwrite))
        return true;
    }

    $contents = @fread($handle, @filesize($path."svn.auth"));
    @fclose($handle);

    $render="";
    if(!preg_match("#\n".$this->name."(rw|ro) \= (.*?)\n#",$contents))
    {
      $con = explode("\n", $contents);
      $i=0;
      while($i<count($con))
      {
        if($i==1)
        {
          if(!empty($readonly))
          {
            for($i=0;$i<count($readonly);$i++)
            {
              if($i==0)
                $_ro=$readonly[$i];
              else
                $_ro.=", ".$readonly[$i];
            }
            $render.=$this->name."ro = ".$_ro."\n";
          }
          else
            $render.=$this->name."ro = \n";
          if(!empty($readwrite))
          {
            for($i=0;$i<count($readwrite);$i++)
            {
              if($i==0)
                $_rw=$readwrite[$i];
              else
                $_rw.=", ".$readwrite[$i];
            }
            $render.=$this->name."rw = ".$_rw."\n";
          }
          else
            $render.=$this->name."rw = \n";
        }
        if( $i==0 || $con[$i-1]!=$con[$i] )
          $render.=$con[$i]."\n";
        $i++;
      }
      $render .="[".$this->name.":/]\n@".$this->name."rw = rw\n@".$this->name."ro = r\n* =";
    }
    else
    {
      $con = explode("\n", $contents);
      for($i=0;$i<count($con);$i++)
      {
        if(preg_match("#^".$this->name."rw \= (.*?)$#",$con[$i]))
        {
          if(!empty($readwrite))
          {
            for($i=0;$i<count($readwrite);$i++)
            {
              if($i==0)
                $_rw=$readwrite[$i];
              else
                $_rw.=", ".$readwrite[$i];
            }
            $render.=$this->name."rw = ".$_rw."\n";
          }
          else
            $render.=$this->name."rw = \n";
        }
        elseif(preg_match("#^".$this->name."ro \= (.*?)$#",$con[$i]))
        {
          if(!empty($readonly))
          {
            for($i=0;$i<count($readonly);$i++)
            {
              if($i==0)
                $_ro=$readonly[$i];
              else
                $_ro.=", ".$readonly[$i];
            }
            $render.=$this->name."ro = ".$_ro."\n";
          }
          else
            $render.=$this->name."ro = \n";
        }
        else
          $render.=$con[$i]."\n";
      }
    }
    if(!$handle = @fopen($path.AUTHFILE, "w"))
      return false;
    @fwrite($handle,$render);
    @fclose ($handle);
    return true;
  }

  /* delete auth file */
  function delete_auth_file()
  {
    if($this->type == "private")
      $path == SVN_PATH.PRIVATE_SVN;
    elseif($this->type == "public")
      $path == SVN_PATH.PUBLIC_SVN;
    elseif($this->type == "aeinfo")
      $path == SVN_PATH.AEINFO_SVN;

    $handle = @fopen($path."svn.auth", "r");
    $contents = @fread($handle, @filesize($path."svn.auth"));
    @fclose($handle);
    $con = explode("\n", $contents);
    $find=false;
    $render="";
    for ( $i=0; $i<count($con);$i++)
    {
      if($find)
      {
        if(preg_match("#\* =$#",$con[$i]))
        {
          $find=false;
          continue;
        }
        else
          continue;
      }
      elseif(preg_match("#^".$this->name."(rw|ro) \= (.*?)$#",$con[$i]))
        continue;
      elseif(preg_match("#^\[".$this->name.":/\]$#",$con[$i]))
      {
        $find=true;
        continue;
      }
      else
        $render .= $con[$i]."\n";
    }

    $handle = @fopen($path."svn.auth", "w");
    @fwrite($handle,$render);
    @fclose ($handle);

    return true;
  }

  /* update auth file */
  function update_auth_file()
  {
    return $this->create_auth_file();
  }

}

?>
