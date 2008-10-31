<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 * Représentation atomique d'une UV à l'UTBM
 * @ingroup stdentity
 * @author Manuel Vonthron
 * @author Pierre Mauduit
 */
class uv extends stdentity
{
  var $id;
  var $code;
  var $intitule;
  
  /**
   * chargement d'une UV par son id dans la BDD
   * n'initialise que les principaux attributs (code, intitulé, ...)
   * @param $id Id de l'UV
   * @return true/false selon le résultat
   * @see load_extra()
   */
  public function load_by_id($id){
  }
  
  /**
   * chargement d'une UV a partir de son code UTBM (ex RE41)
   * @param $code code de l'UV
   * @return true/false selon le résultat
   * @see load_extra()
   */
  public function load_by_code($code){
  }
  
  private function _load(){
  }
  
  /**
   * Ajout d'une UV
   */
  public function add($code, $intitule){
  }
  
  /**
   * charge les informations complementaires, susceptibles d'etre utiles
   * dans une presentation "guide" plutot que liste succinte
   * ex: departements, credits, tags, ...
   */
  public function load_extra(){
  }
  
  public function set_open($value){
  }
  
  public function update($set_valid=false){
  }
  
  /**
   * L'UV est-elle un alias d'une autre UV ? ex XE03 => LE03
   * @return id de l'UV cible si c'est un alias, false sinon
   */
  public function is_alias(){
  }
  
  public function set_alias_of($id_uv, $comment=null){
  }
  
  /**
   * Antecedents
   */
  public function has_antecedent(){
  }
  
  public function add_antecedent($id_uv, $comment=null, $obligatoire=true){
  }
    
  /* nombre d'eleves inscrits a l'UV pour un semestre donne
   * @param $semestre semestre visé, courant par défaut
   * @return nombre d'eleves
   */
  public function get_nb_students($semestre=null){
  }
  
  /**
   * gestion des groupes
   */
  public function add_group($type, $num, $freq, $semestre, $jour, $debut, $fin, $salle=null){
  }
  
  /* suppression de groupe
   * realisee uniquement si personne n'y est inscrit */
  public function remove_group($id_group){
  }
  
  public function update_group($id_group, $type, $num, $freq, $semestre, $jour, $debut, $fin, $salle=null){
  }
  
  public function get_nb_students_group($id_group=SEMESTER_NOW){
  }
  
  /**
   * Departements
   */
  public function add_to_dpt($dpt){
  }

  public function remove_from_dpt($dpt){
  }
}

?>

