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
    if(!check_semester_format($semestre))
      throw new Exception("Wrong format \$semestre ".$semestre);
    $sql = new insert($this->dbrw, "pedag_resultat", array("id_utilisateur" => $this->id, 
                                                           "id_uv" => $id_uv,
                                                           "semestre" => $semestre,
                                                           "note" => $result));
    if($sql->is_success())
      return $sql->get_id();
    else 
      return false;
  }

  /**
   * Permet d'annuler un resultat d'ÚV sans le supprimer
   * afin de pouvoir conserver les UV passées par un étudiant parti puis reviendu
   * sans pour autant les prendre en compte dans le comptage des crédits
   */
  public function set_cancelled_result($id_result, $val=true){
    if($val != 0 && $val != 1)
      return false;
    $sql = new update($this->dbrw, "pedag_resultat", 
                      array("id_utilisateur" => $this->id, "id_resultat" => $id_result),
                      array("cancelled" => $val));
    return $sql->is_success();
  }
  
  public function remove_uv_result($id_result){
    $sql = new delete($this->dbrw, "pedag_resultat", array("id_utilisateur"=>$this->id, "id_resultat"=>$id_result));
    return $sql->is_success();
  }

  public function update_uv_result($id_result=null, $id_uv=null, $semestre=null, $result=null){
    if(!check_semester_format($semestre))
      throw new Exception("Wrong format \$semestre ".$semestre);
    $data = array();
    if($id_uv)  $data['id_uv'] = $id_uv;
    if($semestre)  $data['semestre'] = $semestre;
    if($result)  $data['note'] = $result;
    
    $sql = new update($this->dbrw, "pedag_resultat", array("id_resultat" => $id_result), $data);
    if($sql->is_success())
      return $sql->get_id();
    else 
      return false;
  }

  public function join_uv_group($id_group, $semaine=null){
    $sql = new insert($this->dbrw, "pedag_groupe_utl", array("id_utilisateur"=>$this->id, "id_groupe"=>$id_group, "semaine"=>$semaine));
    return $sql->is_success();
  }

  public function leave_uv_group($id_group){
    $sql = new delete($this->dbrw, "pedag_groupe_utl", array("id_utilisateur"=>$this->id, "id_groupe"=>$id_group));
    return $sql->is_success();
  }

  /* desincription d'une UV entiere, donc desinscrition de tous les groupes */
  public function get_out_from_uv($id_uv){
  }
  
  
  /**
   * Affiliation a un cursus (filiere, mineur, ...)
   */
  public function join_cursus($id_cursus){
    $sql = new insert($this->dbrw, "pedag_cursus_utl", array("id_utilisateur"=>$this->id, "id_cursus"=>$id_cursus), true);
    return $sql->is_success();
  }
  
  public function leave_cursus($id_cursus){
    $sql = new delete($this->dbrw, "pedag_cursus_utl", array("id_utilisateur"=>$this->id, "id_cursus"=>$id_cursus));
    return $sql->is_success();
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
  
  public function get_nb_uv_result(){
    return;
  }
  
  
  /**
   * Emplois du temps
   */
  public function get_edt_list(){
    $sql = new requete($this->db, "SELECT DISTINCT `semestre` 
                                    FROM `pedag_groupe`
                                    NATURAL JOIN `pedag_groupe_utl`
                                    WHERE `pedag_groupe_utl`.`id_utilisateur` = ".$this->id);
    if(!$sql->is_success())
      return array();
    else{
      $t=null;
      while($row = $sql->get_row())
        $t[] = $row['semestre'];
        
      return $t;
    }
  }

  public function get_edt_detail($semestre=SEMESTER_NOW){
    $sql = new requete($this->db, "SELECT *
                                    FROM `pedag_uv`
                                    WHERE `id_uv`
                                    IN (
                                      SELECT DISTINCT `pedag_groupe`.`id_uv`
                                      FROM `pedag_groupe`
                                      LEFT JOIN `pedag_groupe_utl` 
                                        ON `pedag_groupe`.`id_groupe` = `pedag_groupe_utl`.`id_groupe`
                                      WHERE `pedag_groupe`.`semestre` = '$semestre'
                                        AND `pedag_groupe_utl`.`id_utilisateur` = $this->id
                                    )");
    if(!$sql->is_success())
      return false;
    else{
      $t=null;
      while($row = $sql->get_row())
        $t[] = $row;
        
      return $t;
    }
  }
  
  public function delete_edt($semestre){
    $sql = new requete($this->db, "SELECT `pedag_groupe`.`id_groupe`
                                    FROM `pedag_groupe`
                                    LEFT JOIN `pedag_groupe_utl` 
                                      ON `pedag_groupe`.`id_groupe` = `pedag_groupe_utl`.`id_groupe`
                                    WHERE `pedag_groupe`.`semestre` = '$semestre'
                                      AND `pedag_groupe_utl`.`id_utilisateur` = "$this->id);
    if($sql->is_success())
      return false;
    else
      while($row = $sql->get_row())
        $this->leave_uv_group($row['id_groupe']);
        
    return true;
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
