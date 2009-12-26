<?php
/*
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *
 * Copyright 2010
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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

require_once($topdir."include/entities/basedb.inc.php");

// Type de champ possible, chacun a ses particularités
define(TYPE_TEXT, 1);
define(TYPE_DATE, 2);
define(TYPE_EMAIL, 3);
define(TYPE_SELECT, 4);
define(TYPE_TEXT_AREA, 5);
define(TYPE_SUBMIT, 6);
define(TYPE_INFO, 7);
define(TYPE_RADIO, 8);
define(TYPE_CHECK, 9);

/*

  Type de JSON à parser

  Structure globale :

  { nom_du_champ : [type, estNecessaire, args], ... }

  nom_du_champ : un string qui définit la propriété name du champ
  type : une valeur numérique correspondant aux defines ci-dessus
  estNecessaire : un boolean, définit si le champ est facultatif ou pas
  args : un dico avec les clés et les valeurs correspondant aux paramètres de nos fonctions

 */

function array_get ($array, $key, $default)
{
  return array_key_exists ($key, $array) ? $array[$key] : $default;
}

class formulaire extends basedb
{
  var $id;
  var $id_asso;
  var $name;
  var $prev_text;
  var $next_text;
  var $success_text;
  var $json;

  function load_by_id ($id)
  {
    $id = intval ($id);
    $req = new requete ($this->db,
                        "SELECT * FROM aecms_forms WHERE id_form = $id LIMIT 1");

    if ($req->lines != 1)
      return false;

    $row = $req->get_row ();

    return $this->_load ($row);
  }

  function load_by_asso ($id)
  {
    $id = intval ($id);
    $req = new requete ($this->db,
                        "SELECT * FROM aecms_forms WHERE id_asso = $id LIMIT 1");

    if ($req->lines != 1)
      return false;

    $row = $req->get_row ();

    return $this->_load ($row);
  }

  function _load ($row)
  {
    $this->id = $row['id_form'];
    $this->id_asso = $row['id_asso'];
    $this->name = $row['name'];
    $this->prev_text = $row['prev_text'];
    $this->next_text = $row['next_text'];
    $this->success_text = $row['success_text'];
    $this->json = $row['json'];

    return true;
  }

  function is_valid ($id_asso)
  {
    return $this->id_asso == $id_asso;
  }

  function validate_and_post ()
  {
    $obj = json_decode ($this->json, TRUE);
    if ($obj == NULL)
      return 'JSON decode error';

    $result_array = array();

    foreach ($obj as $name=>$args) {
      if (count($args) != 3 || empty($name))
        return 'JSON malformed';

      if (!isset($_REQUEST[$name]))
        return 'Erreur du champ '.$name;

      if ($args[2] == TRUE) {
        if (empty($_REQUEST[$name]))
          return 'Le champ '.$name.' n\'est pas renseigné';

        if ($args[1] == TYPE_EMAIL && CheckEmail($_REQUEST[$name], 3))
          return 'L\'email donné dans le champ '.$name.' n\'est pas valide';
      }

      $result_array[$name] = $_REQUEST[$name];
    }

    $req = new insert ($this->dbrw, 'aecms_forms_results', array('id_form' => $this->id_form,
                                                                 'json_answer' => json_encode($result_array)));

    return false;
  }

  function get_form ($action, $page, $erreur = false)
  {
    $form = new form ($action, $page, false, 'POST', $this->name);
    $form->allow_only_one_usage ();
    if ($erreur)
      $form->error ($erreur);

    $frm->add_hidden('action', $action);

    return $this->build ($frm);
  }

  function build ($frm)
  {
    $obj = json_decode ($this->json, TRUE);
    if ($obj == NULL)
      return false;

    foreach ($obj as $name=>$args) {
      if (count($args) != 3 || empty($name))
        return false;

      // Get the type of field
      switch ($args[0]) {
      case TYPE_TEXT:
        _build_text ($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_DATE:
        _build_date ($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_EMAIL:
        _build_email ($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_SELECT:
        _build_select($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_TEXT_AREA:
        _build_text_area ($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_SUBMIT:
        _build_submit ($frm, $name, $args[2]);
        break;
      case TYPE_INFO:
        _build_info ($frm, $args[2]);
        break;
      case TYPE_RADIO:
        _build_radio ($frm, $name, $args[1], $args[2]);
        break;
      case TYPE_CHECK:
        _build_check ($frm, $name, $args[2]);
        break;
      }

    }

    return $frm;
  }

  function _build_text ($frm, $name, $necessaire, $args)
  {
    $frm->add_text_field ($name, array_get($args, 'title', $name),
                          array_get($args, 'value', ''),
                          $necessaire,
                          array_get($args, 'size', false));
  }

  function _build_date ($frm, $name, $necessaire, $args)
  {
    $frm->add_date_field ($name, array_get($args, 'title', $name),
                          array_get($args, 'value', -1),
                          $necessaire);
  }

  function _build_email ($frm, $name, $necessaire, $args)
  {
    // Same as text, it's only different upon verification
    $this->_build_text ($frm, $name, $necessaire, $args);
  }

  function _build_select ($frm, $name, $necessaire, $args)
  {
    $frm->add_select_field ($name, array_get($args, 'title', $name),
                            array_get($args, 'values', array()),
                            array_get($args, 'value', false), '', $necessaire);
  }

  function _build_text_area ($frm, $name, $necessaire, $args)
  {
    $frm->add_text_area ($name, array_get($args, 'title', $name),
                         array_get($args, 'value', ''),
                         array_get($args, 'width', 40),
                         array_get($args, 'height', 3),
                         $necessaire);
  }

  function _build_submit ($frm, $name, $args)
  {
    $frm->add_submit($name, array_get($args, 'title', $name));
  }

  function _build_info ($frm, $args)
  {
    $frm->add_info (array_get($args, 'infos', ''));
  }

  function _build_radio ($frm, $name, $necessaire, $args)
  {
    $frm->add_radiobox_field ($name, array_get($args, 'title', $name),
                              array_get($args, 'values', array()),
                              array_get($args, 'value', false),
                              false,
                              $necessaire);
  }

  function _build_check ($frm, $name, $args)
  {
    $frm->add_checkbox ($name, array_get($args, 'title', $name),
                        array_get($args, 'checked', false));
  }
}

?>