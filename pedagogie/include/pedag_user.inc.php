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
 * extension de l'utilisateur site AE pour utilisation de 
 * la partie pedagogie
 */
class pedag_user extends utilisateur{
  /* UV actuelles */
  var $uv_suivies = array();
  /* UV suivies dans le passé */
  var $uv_passe = array();
  
  public function add_uv_result($id_uv, $semestre, $result){
  }
  
  public function remove_uv_result($id_result){
  }
  
  public function update_uv_result($id_result, $id_uv, $semestre, $result){
  }
  
  public function join_uv_group($id_group, $semaine){
  }
  
  public function leave_uv_group($id_group){
  }
  
  /* desincription d'une UV entiere, donc desinscrition de tous les groupes */
  public function get_out_from_uv($id_uv){
  }
}

?>
