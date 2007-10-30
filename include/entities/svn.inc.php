<?php

/* Copyright 2007
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
define("SVN_PATH","/var/lib/svn/");
define("PRIVATE_SVN","");
define("PUBLIC_SVN","");


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

  function _load( $row )
  {
    $this->id = $row['id_depot'];
    $this->nom = $row['nom'];
    $this->type = $row['type'];
  }

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
      // TODO create effective svn repository with associated auth file
    }
  }

  function change_type($type)
  {
    if( !in_array($type,$this->type_depot) )
      return false;
    // TODO
  }

  function init_trac()
  {
    // TODO
  }

  function delete_trac()
  {
    // TODO
  }


  // TODO : vérifier qu'il y'ai un putain d'alias
  function add_user($user,$level)
  {
    if( !in_array($level,$this->valid_rights) )
      return false;

    if( $user->is_valid() )
    {
      $req = new requete($this->db,
                         "SELECT `id_utilisateur` FROM `svn_member_depot` "
                         . "WHERE `id_utilisateur` = '".$user->id."' "
                         . "AND `id_depot`='".$this->id."'");
      if($req->lines==0)
      {
        return false;
      }
      else
      {
        return new insert($site->dbrw,
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

  function del_user($user)
  {
    if( $user->is_valid() )
    {
      return new delete ($this->dbrw,"svn_member_depot",array("id_utilisateur"=>$user->id));
    }
    else
      return false;
  }

  function update_user($user,$level)
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

  function create_auth_file()
  {
    // TODO
  }

  function delete_auth_file()
  {
    // TODO
  }

}

?>
