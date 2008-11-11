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

$VAL_GENERALE = array (
     '-1' => 'Sans avis',
      '0' => 'Nul',
      '1' => 'Pas terrible',
      '2' => 'Neutre',
      '3' => 'Pas mal',
      '4' => 'Génial'
      );

$VAL_UTILITE = array(
     '-1' => 'Non renseigné',
      '0' => 'Inutile',
      '1' => 'Pas très utile',
      '2' => 'Utile',
      '3' => 'Très utile',
      '4' => 'Indispensable'
      );


$VAL_INTERET = array(
     '-1' => 'Non renseigné',
      '0' => 'Aucun',
      '1' => 'Faible',
      '2' => 'Bof',
      '3' => 'Intéressant',
      '4' => 'Passionnant'
      );

$VAL_ENSEIGNEMENT = array (
     '-1' => 'Sans avis',
      '0' => 'Inexistante',
      '1' => 'Mauvaise',
      '2' => 'Moyenne',
      '3' => 'Bonne',
      '4' => 'Excellente'
      );

$VAL_TRAVAIL = array (
     '-1' => 'Non renseigné',
      '0' => 'Symbolique',
      '1' => 'Faible',
      '2' => 'Moyenne',
      '3' => 'Importante',
      '4' => 'Très importante'
      );


/**
 * Représentation d'un commentaire à une UV
 * @ingroup stdentity
 * @author Manuel Vonthron
 * @author Pierre Mauduit
 */
class uv_comment extends stdentity
{
  var $id;
  var $id_uv; /* en general, uv_comment appele depuis une UV, donc a n'utiliser que dans les autres cas */
  var $id_utilisateur;

  /* notes entre 0 et 5 */
  var $note_generale;
  var $note_utilite;
  var $note_interet;
  var $note_enseignement;
  var $note_travail;

  var $content;
  var $date;

  public function load_by_id($id){
  }

  private function _load($row){
  }

  public function add($id_uv, $id_utilisateur,
                      $note_generale, $note_utilite, $note_interet, $note_enseignement, $note_travail,
                      $content){
  }

  public function update(){
  }

  public function remove(){
  }

  public function set_valid($value=1){
  }
}

?>
