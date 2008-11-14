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
  var $dept;
  var $state;
  var $tc_available;
  var $semestre;
  var $credits;
  /* extra infos */
  var $extra_loaded = false;
  var $guide = array("objectifs" => null,
                     "programme" => null,
                     "c" => null,
                     "td" => null,
                     "tp" => null,
                     "the" => null);
  var $responsable = null;
  var $antecedent = array();
  var $nb_comments = null;
  var $alias_of = null;
  var $cursus = array();

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

  /* chargement effectif infos basiques */
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
    $sql = new requete($this->db, "SELECT `responsable`,
                                    `guide_objectifs`, `guide_programme`,
                                    `guide_c`, `guide_td`, `guide_tp`, `guide_the`,
                                    FROM `pedag_uv`
                                    WHERE `id_uv` = ".$this->id." LIMIT 1");
    if($sql->is_success()){
      $sql->get_row();
      
      $this->reponsable = $row['responsable'];
      $this->guide['objectifs'] = $row['objectifs'];
      $this->guide['programme'] = $row['programme'];
      $this->guide['c'] = $row['guide_c'];
      $this->guide['td'] = $row['guide_td'];
      $this->guide['tp'] = $row['guide_tp'];
      $this->guide['the'] = $row['guide_the'];
    }
    /* chargement des antecedents */
    $sql = new requete($site->db, "SELECT * FROM `pedag_uv_antecedent`
                                    WHERE `id_uv_source` = ".$this->id);
    if($sql->is_success())
      while($row = $sql->get_row())
        $this->antecedent[] = array("id_cible" => $row['id_uv_cible'],
                                    "obligatoire" => $row['obligatoire'],
                                    "commentaire" => $row['commentaire']);

    /* chargement alias */
    $sql = new requete($site->db, "SELECT * FROM `pedag_uv_alias`
                                    WHERE `id_uv_source` = ".$this->id);
    if($sql->is_success()){
      $row = $sql->get_row()
      $this->alias_of = array("id" => $row['id_uv_cible'],
                              "commentaire" => $row['commentaire']);
    }
    
    /* chargement nb commentaires */
    $sql = new requete($site->db, "SELECT COUNT(*) as `nb_comments`
                                    FROM `pedag_uv_commentaire`
                                    WHERE `id_uv` = ".$this->id);
    if($sql->is_success()){
      $row = $sql->get_row()
      $this->nb_comments = $row['nb_comments'];
    }
    
    /* chargement cursus */
    $sql = new requete($site->db, "SELECT `id_cursus` 
                                    FROM `pedag_uv_cursus`
                                    WHERE `id_uv` = ".$this->id);
    if($sql->is_success())
      while($row = $sql->get_row())
        $this->cursus[] = $row['id_cursus'];
        
    $this->extra_loaded = true;
  }
  
  /**
   * Ajout d'une UV
   */
  public function add($code, $intitule, $type, $responsable=null, $semestre, $tc_available=true){
    $code = strtoupper($code);
    if(!check_uv_format($code))
      throw new Exception("Wrong format code ".$code);
    /* verification qu elle n existe pas deja, avec le code */
    $sql = new requete($this-db, "SELECT 1 FROM `pedag_uv` WHERE `code` = '".$code."'");
    if($sql->lines != 0)
      throw new Exception("UV code already used in database");
      
    $sql = new insert($site->dbrw, "pedag_uv", 
                      array("code" => $code,
                            "intitule" => mysql_real_escape_string($intitule),
                            "type" => $type,
                            "semestre" => $semestre,
                            "responsable" => mysql_real_escape_string($responsable),
                            "state" => STATE_PENDING,
                            "tc_available" => (bool) $tc_available));
    if($sql->is_success())
      return $this->load_by_id($sql->get_id());
    else
      return false;      
  }
  
  /* mise a jour des infos */
  public function update($code=null, $intitule=null, $type=null, $responsable=null, $semestre=null, $tc_available=null){
    $data = array();
    if($code)     $data['code'] = $code;
    if($intitule) $data['intitule'] = $intitule;
    if($type)     $data['type'] = $type;    
    if($responsable)  $data['responsable'] =  $responsable;
    if($semestre)     $data['semestre'] = $semestre;
    if($tc_available) $data['tc_available'] = $tc_available;
    $data['state'] = STATE_MODIFIED;

    $sql = new update($site->dbrw, "pedag_uv", $data, array("id_uv"=>$this->id));
    return $sql->is_success(); 
  }
  
  /* separation des infos du guide pour ne pas alourdir la fonction de creation */
  public function update_guide_infos($objectifs=null, $programme=null, $c=null, $td=null, $tp=null, $the=null, $credits=null){
    $data = array();
    if($objectifs) $data['guide_objectifs'] = $objectifs;
    if($programme) $data['guide_programme'] = $programme;
    if($credits)   $data['guide_credits'] = $credits;    
    if($c)  $data['guide_c'] =  $c;
    if($td) $data['guide_td'] = $td;
    if($tp) $data['guide_tp'] = $tp;
    if($the)$data['guide_the'] = $the;
    $data['state'] = STATE_MODIFIED;

    $sql = new update($site->dbrw, "pedag_uv", $data, array("id_uv"=>$this->id));
    return $sql->is_success();
  }

  public function set_open($value){
    $sql = new update($site->dbrw, "pedag_uv", array("semestre"=>$value), array("id_uv"=>$this->id));
    return $sql->is_success();
  }

  public function set_valid($value=STATE_VALID){
    $sql = new update($site->dbrw, "pedag_uv", array("state"=>$value), array("id_uv"=>$this->id));
    return $sql->is_success();
  }

  /**
   * L'UV est-elle un alias d'une autre UV ? ex XE03 => LE03
   * @todo voir en fonction des besoins d utilisation si prop a detacher de extra
   * @return id de l'UV cible si c'est un alias, false sinon
   */
  public function is_alias(){
    if(!$this->extra_loaded)
      $this->load_extra();
    
    if(is_null($this->alias_of))
      return false;
    else
      return $this->alias_of['id'];
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
    if(!$this->extra_loaded)
      $this->load_extra();
      
    return !empty($this->antecedent);
  }

  public function add_antecedent($id_uv, $comment=null, $obligatoire=true){
    $sql = new insert($this->dbrw, 'pedag_uv_antecedent',
                      array('id_uv_source' => $this->id,
                            'id_uv_cible' => $id_uv,
                            'commentaire' => $comment,
                            'obligatoire' => $obligatoire),
                      false);
    return $sql->is_success();
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
    $sql = "SELECT `id_groupe`
            FROM `pedag_groupe`
            WHERE `id_uv` = ".$this->id."
              AND `semestre` = '".$semestre."'";
    if($type)
      $sql .= "  AND `type` = ".$type;
    $req = new requete($this->db, $sql);
    
    if(!$req->is_success)
      return false;
    else
      $t = array();
      
    while($row = $req->get_row())
      $t[] = $row['id_groupe'];
      
    return $t;
  }
  
  public function get_nb_students_group($id_group){
    $sql = new requete($this->db, "SELECT COUNT(*) as `nb` 
                                    FROM `pedag_groupe_utl`
                                    WHERE `id_groupe` = ".$id_group);
    $row = $sql->get_row();
    return $row['nb'];
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

