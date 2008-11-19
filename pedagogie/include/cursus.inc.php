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
  var $nb_all_of;
  var $nb_some_of;
  var $uv_all_of=array();
  var $uv_some_of=array();
  
  public function load_by_id($id){
  }
  
  public function _load($row){
  }
  
  public function add(){
  }
  
  public function remove(){
  }
  
  public function update(){
  }
  
  public function add_uv($id_uv, $relation){
  }
  
  public function remove_uv($id_uv){
  }
  
  /* le mot `diplomed` sera proposé a l'academie amglaise l'an prochain si vous etes sages */
  public function get_nb_students($ignore_diplomed=false){
  }
}
?>
