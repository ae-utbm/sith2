<?php
/* Copyright 2011
 * - Antoine Tenart < antoine dot tenart at gmail dot com >
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

/**
    Parse le mail du SME contenant l'affectation aux groupes et UVs.
**/

/* TODO : question des doubles horaires (ET)
          gestion de l'hors edt
          renommer la classe ??
*/

require_once($topdir . 'include/mysql.inc.php');
require_once($topdir . 'pedagogie/include/pedagogie.inc.php');

class UVParser
{
  // --- Protected vars
  protected $uv;
  protected $semester;
  protected $type;
  protected $group;
  protected $begin_hour;
  protected $end_hour;
  protected $day;
  protected $room;
  protected $frequency;

  protected $db;
  protected $_target = array();
  protected $_results = array();

  // Rules
  protected $_phrase;
  protected $_title;
  protected $_info;
  protected $_schedule;
  protected $_uv = '([A-Z]{2}[0-9]{2})';
  protected $_type = '(?:(C|TD|TP)([0-9]))';
  protected $_day = '(L|MA|ME|J|V|S)';
  protected $_frequency = '(\(1SEMAINE\/2\))';
  protected $_hour = '([0-2]?[0-9]H[0-5][0-9])';
  protected $_room = '(?:en([A-Z][0-9]{1,3}[A-Z]?))';


  // --- public functions
  // constructor
  function UVParser(&$db, $semester = SEMESTRE_NOW) {
    $this->db = &$db;
    $this->semester = $semester;

    $this->_schedule = "$this->_hour$this->_hour";

    $this->_title = "$this->_uv$this->_type?";
    $this->_info = "(?:(?:ET)?$this->_day$this->_schedule$this->_frequency?$this->_room)|(HORSEMPLOIduTEMPS)";

    $this->_phrase = "^$this->_title$this->_info$";
  }

  // load text & parse it
  public function load_by_text($txt) {
    //$txt = preg_replace('/(.+):(.+)ET(.+)/', "$1$2\n$1$3", $txt); // life is easy
    $txt = str_replace(array(' ', ':', '-'), '', $txt);
    $this->_target = explode("\n",$txt);

    $this->parse();
  }

  // load next parsed UV, if any (usefull in a loop)
  public function load_next() {
    $foo = current($this->_results);
    next($this->_results);

    if(!$foo)
      return false;

    $this->uv = $foo[1];
    $this->type = $foo[2];
    $this->group = $foo[3];
    $this->day = $foo[4];
    $this->begin_hour = $foo[5];
    $this->end_hour = $foo[6];
    $this->room = $foo[8];

    if($foo[7] != '')
      $this->frequency = true;
    else
      $this->frequency = false;

    $this->id = $this->get_id_uv();

    return true;
  }

  public function get_id_group() {
    $sql = "SELECT id_groupe FROM pedag_groupe";
    $sql .= " WHERE `id_uv` = ".$this->id." AND `type` = '".$this->type."'";
    $sql .= " AND `num_groupe` = ".$this->group." AND `semestre` = '".$this->semester."' LIMIT 1";

    $req = new requete($this->db, $sql);

    if($req->is_success()) {
      $res = $req->get_row();
      return $res['id_groupe'];
    }
    else
      return null;
  }

  public function get_uv() {
    if( !empty($this->uv) )
      return $this->uv;
    else
      return null;
  }

  public function get_nice_print() {
    $plop = array( 'C' => 'Cours', 'TD' => 'Travaux dirigés', 'TP' => 'Travaux pratiques');
    $jours = array( 'L' => 'lundi', 'MA' => 'mardi', 'ME' => 'mercredi', 'J' => 'jeudi', 'V' => 'vendredi', 'S' => 'samedi');

    $ret = $plop[$this->type] . (preg_match('/^[A|E|U|I|O]$/', $this->uv[0]) ? ' d\'' : ' de ') . $this->uv;
    $ret .= ' le ' . $jours[$this->day] .' de ' . $this->begin_hour . ' à ' . $this->end_hour . ' en ' . $this->room . '.';

    return $ret;
  }

  public function is_weekly() {
    return $this->frequency;
  }


  // --- protected functions
  // parse text loaded
  protected function parse() {

    while( $foo = current($this->_target) ) {
      preg_match('/'.$this->_phrase.'/', $foo, $matches);

      if($matches) {
        unset($matches[0]);
        $this->_results[] = $matches;
      }

      next($this->_target);
    }
  }

  protected function get_id_uv() {
    $sql = "SELECT id_uv FROM pedag_uv WHERE code='".$this->uv."' LIMIT 1";
    $req = new requete($this->db, $sql);

    if( $req->is_success() ) {
      $res = $req->get_row();
      return $res['id_uv'];
    }
    else
      return null;
  }

}

