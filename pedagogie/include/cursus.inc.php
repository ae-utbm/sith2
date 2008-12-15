<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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


/**
 * Représentation d'un cursus
 * @ingroup stdentity
 * @author Manuel Vonthron
 */
class cursus extends stdentity
{
  var $id;
  var $intitule;
  var $type;
  var $description;
  var $responsable;
  var $departement;
  var $nb_all_of;
  var $nb_some_of;
  var $uv_all_of=array();
  var $uv_some_of=array();
  
  public function load_by_id($id){
    $sql = new requete($this->db, "SELECT * FROM `pedag_cursus` WHERE `id_cursus` = ".$id." LIMIT 1");
    if($sql->is_success())
      return $this->_load($sql->get_row());
    else
      return false;
  }
  
  public function _load($row){
    foreach($row as $att => $val)
      if(property_exists($this, $att))
        $this->{$att} = $val;
    $this->id = $row['id_cursus'];
    return $this->id;
  }
  
  public function add($intitule, $type, $description, $responsable, $nb_some_of, $nb_all_of, $departement=null){
    $data = array("type" => $type,
                  "intitule" => mysql_real_escape_string($intitule),
                  "description" => mysql_real_escape_string($description),
                  "responsable" => mysql_real_escape_string($responsable),
                  "nb_some_of" => intval($nb_some_of),
                  "nb_all_of" => intval($nb_all_of));
    if($departement) $data["departement"] = $departement;
    
    $sql = new insert($this->dbrw, "pedag_cursus", $data);
    if($sql->is_success())
      return $sql->get_id();
    else 
      return false;
  }
  
  /**
   * finalement on authorise pas la suppression 
   * mais on passe un flag 'closed' a true
   */
  public function set_closed(boolean $var=true){
    $sql = new update($this->dbrw, "pedag_cursus", 
                      array("id_cursus" => $this->id),
                      array("closed" => $val));
    return $sql->is_success();
  }
  
  public function update($intitule=null, $type=null, $description=null, $responsable=null, $nb_some_of=null, $nb_all_of=null){
    if(func_num_args() < 1) return false;

    $data = array();
    if($type) $data["type"] = $type;
    if($intitule) $data["intitule"] = mysql_real_escape_string($intitule);
    if($description) $data["description"] = mysql_real_escape_string($description);
    if($responsable) $data["responsable"] = mysql_real_escape_string($responsable);
    if($nb_some_of) $data["nb_some_of"] = intval($nb_some_of);
    if($nb_all_of) $data["nb_all_of"] = intval($nb_all_of);
    
    $sql = new update($this->dbrw, "pedag_resultat", array("id_cursus" => $this->id), $data);
    return $sql->is_success();
  }
  
  /**
   * Ajout d'une UV au cursus
   * @param $id_uv UV a ajouter
   * @param $relation (SOME_OF|ALL_OF)
   */
  public function add_uv($id_uv, $relation){
    if(uv::exists($this->db, $id_uv))
      $sql = new insert($this->dbrw, "pedag_uv_cursus", array("id_uv" => $id_uv, "id_cursus" => $this->id, "relation" => $relation));
    return $sql->is_success();
  }
  
  public function remove_uv($id_uv){
    $sql = new delete($this->dbrw, "pedag_uv_cursus", array("id_uv" => $id_uv, "id_cursus" => $this->id));
    return $sql->is_success();
  }

  public function get_nb_students($ignore_graduated=false){
  }
}
?>
