<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
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
 
 require_once($topdir."include/entities/objet.inc.php");

class jeu extends objet
{
	/** Id de la série */
	var $id_serie;
	/** Id de l'éditeur */
	var $id_editeur;
	/** Numéro dans la série */
	var $num_livre;

	var $isbn;
  
  /** Charge un livre en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT `inv_objet`.*, `bk_book`.* FROM `inv_objet`
				INNER JOIN `bk_book` ON `bk_book`.`id_objet`=`inv_objet`.`id_objet`
				WHERE `inv_objet`.`id_objet` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
		
	/** Charge un livre en fonction de son code barre
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_cbar ( $cbar )
	{
		$req = new requete($this->db, "SELECT `inv_objet`.*, `bk_book`.* FROM `inv_objet`
				INNER JOIN `bk_book` ON `bk_book`.`id_objet`=`inv_objet`.`id_objet`
				WHERE `inv_objet`.`cbar_objet` = '" . mysql_real_escape_string($cbar) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}
		
	
	
	function _load ( $row )
	{
		$this->id_serie = $row['id_serie'];	
		$this->id_editeur = $row['id_editeur'];	
		$this->num_livre = $row['num_livre'];
		$this->isbn = $row['isbn_livre'];
		parent::_load($row);
	}
	
	
	function add_book ( $id_asso, $id_asso_prop, $id_salle, $id_objtype, $id_op, $nom,
				$code_objtype, $num_serie, $prix, $caution, $prix_emprunt, $empruntable,
				$en_etat, $date_achat, $notes,
				$id_serie, $id_editeur,$num_livre, $isbn="" )
	{
	
		parent::add($id_asso, $id_asso_prop, $id_salle, $id_objtype, $id_op, $nom,
				$code_objtype, $num_serie, $prix, $caution, $prix_emprunt, $empruntable,
				$en_etat, $date_achat, $notes );
	
		$this->id_serie = $id_serie;	
		$this->id_editeur = $id_editeur;	
		$this->num_livre = $num_livre;	
		$this->isbn = $isbn;
		
		if ( $this->is_valid() )
		{
			$sql = new insert ($this->dbrw,
				"bk_jeu",
				array(
					"id_objet" => $this->id,
					"id_serie" => $this->id_serie,
					"id_editeur" => $this->id_editeur,
					"num_livre" => $this->num_livre,
					"isbn_livre" => $this->isbn
					)
				);
		}
	}
	
	function save_book ( $id_asso, $id_asso_prop, $id_salle, $id_objtype, $id_op, $nom,
				$num_serie, $prix, $caution, $prix_emprunt, $empruntable,
				$en_etat, $date_achat, $notes,$cbar,
				$id_serie, $id_editeur,$num_livre, $isbn=""  )
	{
		
		$this->save_objet ( $id_asso, $id_asso_prop, $id_salle, $id_objtype, $id_op, $nom,
				$num_serie, $prix, $caution, $prix_emprunt, $empruntable,
				$en_etat, $date_achat, $notes,$cbar );
	
		$this->id_serie = $id_serie;	
		
		$this->id_editeur = $id_editeur;	
		$this->num_livre = $num_livre;	
	
		$sql = new update ($this->dbrw,
			"bk_jeu",
			array(
				"id_serie" => $this->id_serie,
				"id_editeur" => $this->id_editeur,
				"num_livre" => $this->num_livre,
				"isbn_livre" => $this->isbn
				),
			array("id_objet" => $this->id)
			);
	
	}
	

	
}

?>