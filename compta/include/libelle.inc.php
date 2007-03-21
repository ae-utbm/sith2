<?php
/* Copyright 2006
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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
 * @file
 */
class compta_libelle /* table: cpta_libelle */
{
  var $id;
  var $id_asso;
  var $nom;

  var $db;
  var $dbrw;
  
  function compta_libelle ( $db, $dbrw = null)
  {
    $this->db = $db;
    $this->dbrw = $dbrw;
  }
  
	/** Charge un libellé en fonction de son id
	 * @param $id Id du compte bancaire
	 */
	function load_by_id ( $id )
	{
		$req = new requete ($this->db, "SELECT * FROM `cpta_libelle`
							WHERE id_libelle='".intval($id)."'");
		if ( $req->lines < 1 )
			$this->id_cptbc = -1;
		else
		{
			$row = $req->get_row();
			$this->id = $row['id_libelle'];
			$this->id_asso = $row['id_asso'];
			$this->nom = $row['nom_libelle'];
		}
	}
  
  function add_libelle ( $id_asso, $nom )
  {
    $this->nom = $nom;
    $this->id_asso = $id_asso;

    $req = new insert ($this->dbrw,
		       "cpta_libelle",
		       array(
		        "id_asso" => $this->id_asso,
		        "nom_libelle" => $this->nom
		        ));

    if ( $sql )
      $this->id = $sql->get_id();
    else
    {
      $this->id = -1;
      return;
    }
    
  }
  
  function update_libelle ( $nom )
  {
    $this->nom = $nom;

    $req = new update ($this->dbrw,
		       "cpta_libelle",
		       array("nom_libelle" => $nom),
		       array("id_libelle" => $this->id));

    if ( !$req )
      return false;

    return true;
  }
  
  function remove_libelle ()
  {
    $sql = new delete ($this->dbrw,"cpta_libelle",array("id_libelle" => $this->id));  
  }
  
  
}




?>