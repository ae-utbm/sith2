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

  /**
   * Permet d'annuler un resultat d'ÚV sans le supprimer
   * afin de pouvoir conserver les UV passées par un étudiant parti puis reviendu
   * sans pour autant les prendre en compte dans le comptage des crédits
   */
  public function set_cancelled_result($id_result, $val=true){
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
  
  
  /**
   * Affiliation a un cursus (filiere, mineur, ...)
   */
  public function join_cursus($id_cursus){
  }
  
  public function leave_cursus($id_cursus){
  }
  
  /**
   * Verification de la conformité des infos déclarées dans la fiche Matmat
   * avec celles utilisées pour ici (Dpt, filiere)
   */
  public function check_validity(){
  }
  
  /**
   * Gestion/calcul des credits ECTS
   */
  /* Valeurs issues du marvelouze pdf de resultats de cursus UTBM 
  define('MIN_GLOBAL', 300);
  define('MIN_TC', );
  define('MIN_BRANCHE', 84);
  
  define('MIN_STAGE', 66);
  define('MIN_EC', 20);
  define('MIN_CG', 32);
  */
  public function is_from_tc($ignore_cancelled_result=false){
  }
  
  public function get_credits_tc(){
  }
  
  /**************************************
   * Données annexes de l'emploi du temps
   */

  /**
   * Cherche si l'utilisateur a des permanences inscrites dans un planning
   * du site de l'AE : foyer, MDE, Bureau AE ...
   * 
   * @return true si des perms ont été trouvées, false sinon
   */
  public function get_permanence(){
  }

  /**
   * Cherche si il y a des réunions/activités régulières inscrites dans
   * le planning du site AE pour les clubs auxquels l'utilisateur est
   * inscrit
   * 
   * @return true si des activités ont été trouvées, false sinon
   */
  public function get_recurrent_activity(){
  }
}

?>
