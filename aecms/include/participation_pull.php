<?php
/* Copyright 2009
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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
 */

class participation extends basedb
{
  public $id;

  // Infos personelles
  public $nom;
  public $prenom;
  public $date_de_naissance;
  public $email;
  public $telephone;

  public $adresse_rue;
  public $adresse_additional;
  public $adresse_ville;
  public $adresse_codepostal;

  public $contribution_nom;
  public $contribution_parent;
  public $contribution_siteweb;
  public $contribution_depot;
  public $contribution_description;

  public function add_participation ()
  {
    $req = new insert($this->dbrw, 'pull_participations',
                      array('nom' => $nom,
                            'prenom' => $prenom,
                            'date_de_naissance' => $date_de_naissance,
                            'email' => $email,
                            'telephone' => $telephone,
                            'adresse_rue' => $adresse_rue,
                            'adresse_additional' => $adresse_additional,
                            'adresse_ville' => $adresse_ville,
                            'adresse_codepostal' => $adresse_codepostal,
                            'contribution_nom' => $contribution_nom,
                            'contribution_parent' => $contribution_parent,
                            'contribution_siteweb' => $contribution_siteweb,
                            'contribution_depot' => $contribution_depot,
                            'contribution_description' => $contribution_description));

    if (!$req->is_success())
      return false;

    $id = $req->get_id ();

    return true;
  }

  public function load_by_id ($id)
  {
    $req = new requete ($this->db,
                        "SELECT * FROM `pull_participations` WHERE `id_participation`='".intval($id)."'");

    if ($req->lines != 1)
      return false;

    $this->_load($req->get_row());

    return true;
  }

  public function _load ($row)
  {
    $id = $row['id_participation'];
    $nom = $row['nom'];
    $prenom = $row['prenom'];
    $date_de_naissance = $row['date_de_naissance'];
    $email = $row['email'];
    $telephone = $row['telephone'];
    $adresse_rue = $row['adresse_rue'];
    $adresse_additional = $row['adresse_additional'];
    $adresse_ville = $row['adresse_ville'];
    $adresse_codepostal = $row['adresse_codepostal'];
    $contribution_nom = $row['contribution_nom'];
    $contribution_parent = $row['contribution_parent'];
    $contribution_siteweb = $row['contribution_siteweb'];
    $contribution_depot = $row['contribution_depot'];
    $contribution_description = $row['contribution_description'];
  }

}

?>