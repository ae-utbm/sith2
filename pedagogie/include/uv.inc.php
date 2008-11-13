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
  /* basic infos */
  var $id;
  var $code;
  var $intitule;
  var $state;
  var $tc_available;
  var $semestre;
  var $credits;
  /* extra infos */
  var $guide = array("objectifs" => null,
                     "programme" => null,
                     "c" => null,
                     "td" => null,
                     "tp" => null,
                     "the" => null);
  var $prerequis = null;
  var $nb_comments = null;

  /**
   * chargement d'une UV par son id dans la BDD
   * n'initialise que les principaux attributs (code, intitulé, ...)
   * @param $id Id de l'UV
   * @return id/false selon le résultat
   * @see load_extra()
   */
  public function load_by_id($id){
    $sql = new requete($this->db, "SELECT `id_uv`, `code`, `intitule`,
                                    `semestre`, `state`, `tc_available`,
                                    `guide_credits`
                                    FROM `pedag_uv`
                                    WHERE `id_uv` = ".$id." LIMIT 1");
    if($sql->is_success()){
      $this->_load($sql->get_row());
      return $this->id;
    }else{
      $this->id = -1;
      return false;
    }
  }

  /**
   * chargement d'une UV a partir de son code UTBM (ex RE41)
   * @param $code code de l'UV
   * @return id/false selon le résultat
   * @see load_extra()
   */
  public function load_by_code($code){
    if(!check_semester_format($code))
      return false;

    $sql = new requete($this->db, "SELECT `id_uv`, `code`, `intitule`,
                                    `semestre`, `state`, `tc_available`,
                                    `guide_credits`
                                    FROM `pedag_uv`
                                    WHERE `code` = ".$code." LIMIT 1");
    if($sql->is_success()){
      $this->_load($sql->get_row());
      return $this->id;
    }else{
      $this->id = -1;
      return false;
    }
  }

  private function _load($row){
    $this->id = $row['id_uv'];
    $this->code = $row['code'];
    $this->intitule = $row['intitule'];
    $this->semestre = $row['semestre'];
    $this->state = $row['state'];
    $this->tc_available = $row['tc_available'];
  }

  /**
   * charge les informations complementaires, susceptibles d'etre utiles
   * dans une presentation "guide" plutot que liste succinte
   * ex: departements, credits, tags, ...
   */
  public function load_extra(){
  }
    
  private function _load_extra(){
  }
  /**
   * Ajout d'une UV
   */
  public function add($code, $intitule){
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
   * N'oublions pas les methodes d'acces aux tags heritees de stdentity
   * @see stdentity::set_tags_array
   * @see stdentity::set_tags
   * @see stdentity::get_tags_list
   * @see stdentity::get_tags
   */
  


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
  public function get_nb_students($semestre=SEMESTER_NOW){
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
  
  /**
   * Recuperation des id de groupes
   * @param $type type des groupes recherches du style GROUP_TD ou null si tout
   * @param $semestre semestre visé
   * @return tableau des ids
   */
  public function get_groups($type=null, $semestre=SEMESTER_NOW){
  }
  
  public function get_nb_students_group($id_group){
  }

  /**
   * Departements
   */
  public function add_to_dpt($dpt){
  }

  public function remove_from_dpt($dpt){
  }
  
  /**
   * Admin des commentaires
   * dans leur globalité
   */
  public function reset_eval_comments(){
  }
  
  public function get_nb_comments(){
  }
}

?>

