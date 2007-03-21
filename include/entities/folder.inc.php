<?php
/* Copyright 2006
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
/**
 * @file Gestion des repertoires virtuels (partie téléchargement).
 */
 
require_once($topdir."include/entities/basedb.inc.php");

/**
 * Classe de gestion des repertoires virtuels.
 * 
 * La partie "fichier" est décrite par le dossier qui a id_asso=null et id_folder_parent=null.
 * Les repertoire pour chaque asso est décrit par le dossier ayant l'id de lasso et id_folder_parent=null.
 */
class dfolder extends basedb
{
	/** Id du dossier */
	var $id;
	/** Titre du dossier */
	var $titre;
	/** Id du dossier parent, NULL si dossier racine */
	var $id_folder_parent;
	/** Description du dossier */
	var $description;
	/** Date d'ajout du dossier */
	var $date_ajout;
	/** Dans le cas du dossier parent, donne l'association à qui est rattaché ce dossier parent, (NULL si section "fichiers").
	 * Dans le cas général c'est une méta-donnée informant si l'association liée.
	 */
	var $id_asso;
	
	/** Charge un dossier par son ID
	 * @param $id ID du dossier
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `d_folder`
				WHERE `id_folder` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	} 

	/** Charge un dossier par son ID
	 * @param $id_asso Id de l'asso
	 */
	function load_root_by_asso ( $id_asso )
	{
		if ( is_null($id_asso) )
			$req = new requete($this->db, "SELECT * FROM `d_folder`
				WHERE `id_asso` IS NULL AND id_folder_parent IS NULL
				LIMIT 1");
		else
			$req = new requete($this->db, "SELECT * FROM `d_folder`
				WHERE `id_asso` = '" . mysql_real_escape_string($id_asso) . "' AND id_folder_parent IS NULL
				LIMIT 1");	
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	} 
	
	/** Charge un dossier par son titre et son dossier parent
	 * @param $id_parent Id du dossier parent
	 * @param $titre Titre du dossier
	 */
	function load_by_titre ( $id_parent, $titre )
	{
		$req = new requete($this->db, "SELECT * FROM `d_folder`
				WHERE `titre_folder` = '" . mysql_real_escape_string($titre) . "' AND id_folder_parent ='".mysql_real_escape_string($id_parent)."'
				LIMIT 1");	
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	/**
	 * Charge un dossier d'après une ligne de resultat SQL.
	 * @param $row Ligne SQL
	 */
	function _load ( $row )
	{
		$this->id = $row['id_folder'];
		$this->titre = $row['titre_folder'];
		$this->id_folder_parent = $row['id_folder_parent'];
		$this->description = $row['description_folder'];
		$this->date_ajout = strtotime($row['date_ajout_folder']);
		$this->id_asso = $row['id_asso'];
		
		$this->id_utilisateur = $row['id_utilisateur'];	
		$this->id_groupe = $row['id_groupe'];	
		$this->id_groupe_admin = $row['id_groupe_admin'];	
		$this->droits_acces = $row['droits_acces_folder'];
		$this->modere = $row['modere_folder'];	
	}
	
	/**
	 * Ajoute un dossier.
	 * Vous DEVEZ avoir fait appel à herit et set_rights avant !
	 * @param $titre Titre du dossier
	 * @param $id_folder_parent Id du dossier parent (NULL si aucun)
	 * @param $description Description (NULL si aucune)
	 * @param $id_asso Association lié ou racine (NULL si aucune)
	 */
	function add_folder ( $titre, $id_folder_parent, $description, $id_asso )
	{
		$this->titre = $titre;
		$this->id_folder_parent = $id_folder_parent;
		$this->description = $description;
		$this->id_asso = $id_asso;
		$this->date_ajout = time();
		$this->modere=(is_null($id_folder_parent) && !is_null($id_asso))?true:false;
		
		$sql = new insert ($this->dbrw,
			"d_folder",
			array(
				"titre_folder"=>$this->titre,
				"id_folder_parent"=>$this->id_folder_parent,	
				"description_folder"=>$this->description,
				"date_ajout_folder"=>date("Y-m-d H:i:s",$this->date_ajout),
				"id_asso"=>$this->id_asso,
				
				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe"=>$this->id_groupe,
				"id_groupe_admin"=>$this->id_groupe_admin,
				"droits_acces_folder"=>$this->droits_acces,
				"modere_folder"=>$this->modere
				)
			);
		if ( $sql )
			$this->id = $sql->get_id();
		else
		{
			$this->id = -1;
			return;	
		}		
	}
	
	/**
	 * met à jour les informations d'un dossier.
	 * @param $titre Titre du dossier
	 * @param $description Description (NULL si aucune)
	 * @param $id_asso Association lié ou racine (NULL si aucune)
	 */
	function update_folder ( $titre, $description, $id_asso )
	{
		$this->titre = $titre;
		$this->description = $description;
		$this->id_asso = $id_asso;
		
		$sql = new update ($this->dbrw,
			"d_folder",
			array(
				"titre_folder"=>$this->titre,
				"description_folder"=>$this->description,
				"id_asso"=>$this->id_asso,
				
				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe"=>$this->id_groupe,
				"id_groupe_admin"=>$this->id_groupe_admin,
				"droits_acces_folder"=>$this->droits_acces,
				),
			array("id_folder"=>$this->id)
			);
	
	}
	
	/**
	 * Deplace le fichier dans un autre dossier
	 * @param $id_folder Titre du dossier
	 */
	function move_to ( $id_folder )
	{
		
		$pfolder = new dfolder($this->db);
		$pfolder->load_by_id($id_folder);
		
		while ( $pfolder->id > 0 )
		{
		  if ( $pfolder->id == $this->id ) return; // On ne peut deplacer un dossier dans un dossier fils ou dans lui même
		  $pfolder->load_by_id($pfolder->id_folder_parent);
		}
		
		
		$this->id_folder_parent = $id_folder;
		$sql = new update ($this->dbrw,
			"d_folder",
			array("id_folder_parent"=>$this->id_folder_parent),
			array("id_folder"=>$this->id)
			);
	}
	
	
	/** Liste les sous-dossiers que l'utilisateur peut voir
	 * @param $user Instance de utilisateur
	 * @param $select Champs SQL à récupéré
	 * @return Une instance de requete avec les resultats
	 */
	function get_folders ( $user, $select="*")
	{
		if ( $this->is_admin( $user ) )
			return new requete($this->db,"SELECT $select " .
				"FROM d_folder " .
				"WHERE " .
				"id_folder_parent='".$this->id."' " .
				"ORDER BY `titre_folder`");	
				
		elseif ( $user->id < 1 )
			return new requete($this->db,"SELECT $select " .
				"FROM d_folder " .
				"WHERE " .
				"id_folder_parent='".$this->id."' AND " .
				"(droits_acces_folder & 0x1) " .
				"AND modere_folder='1' " .
				"ORDER BY `titre_folder`");
				
		else		
			return new requete($this->db,"SELECT $select " .
				"FROM d_folder " .
				"WHERE " .
				"id_folder_parent='".$this->id."' AND " .
				"((" .
					"(" .
						"(droits_acces_folder & 0x1) OR " .
						"((droits_acces_folder & 0x10) AND id_groupe IN (".$user->get_groups_csv()."))" .
					") " .
					"AND modere_folder='1'" .
				") OR " .
				"(id_groupe_admin IN (".$user->get_groups_csv().")) OR " .
				"((droits_acces_folder & 0x100) AND id_utilisateur='".$user->id."')) " .
				"ORDER BY `titre_folder`");		

	}	
	
	/** Liste les fichiers contenus dans le dossier que l'utilisateur peut voir
	 * @param $user Instance de utilisateur
	 * @param $select Champs SQL à récupéré
	 * @return Une instance de requete avec les resultats
	 */
	function get_files ( $user, $select="*")
	{
		if ( $this->is_admin( $user ) )
			return new requete($this->db,"SELECT $select " .
				"FROM d_file " .
				"WHERE " .
				"id_folder='".$this->id."' " .
				"ORDER BY `titre_file`");	
				
		elseif ( $user->id < 1 )		
			return new requete($this->db,"SELECT $select " .
				"FROM d_file " .
				"WHERE " .
				"id_folder='".$this->id."' AND " .
				"(droits_acces_file & 0x1) " .
				"AND modere_file='1' " .
				"ORDER BY `titre_file`");	
				
		else		
			return new requete($this->db,"SELECT $select " .
				"FROM d_file " .
				"WHERE " .
				"id_folder='".$this->id."' AND " .
				"((" .
					"(" .
						"((droits_acces_file & 0x1) OR " .
						"((droits_acces_file & 0x10) AND id_groupe IN (".$user->get_groups_csv().")))" .
					") " .
					"AND modere_file='1'" .
				") OR " .
				"(id_groupe_admin IN (".$user->get_groups_csv().")) OR " .
				"((droits_acces_file & 0x100) AND id_utilisateur='".$user->id."')) " .
				"ORDER BY `titre_file`");		

	}	
	
	/**
	 * Définit le status de modération du dossier
	 * @param $modere true=modéré, false=non modéré
	 */
	function set_modere($modere=true)
	{
		$this->modere=$modere;
		$sql = new update($this->dbrw,"d_folder",array("modere_folder"=>$this->modere),array("id_folder"=>$this->id));	
	}
	
	/**
	 * Supprime le dossier, ses sous-dossiers et ses fichiers
	 */
	function delete_folder()
	{
		$fd = new dfolder($this->db,$this->dbrw);
		$req = new requete($this->db,"SELECT * " .
				"FROM d_folder " .
				"WHERE " .
				"id_folder_parent='".$this->id."'");
				
		if ( $req->lines > 0 )	
			while($row = $req->get_row())
			{
				$fd->_load($row);
				$fd->delete_folder();
			}
		
		$fl = new dfile($this->db,$this->dbrw);
		$req = new requete($this->db,"SELECT * " .
				"FROM d_file " .
				"WHERE " .
				"id_folder='".$this->id."'");	
		if ( $req->lines > 0 )
			while($row = $req->get_row())
			{
				$fl->_load($row);
				$fl->delete_file();
			}
		$sql = new delete($this->dbrw,"d_folder",array("id_folder"=>$this->id));
	}
}

?>
