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
 * @file Gestion des fichier des repertoires virtuels (partie téléchargement).
 */
require_once($topdir."include/entities/basedb.inc.php");
require_once($topdir."include/entities/folder.inc.php");

/**
 * Classe de gestion des fichiers des repertoires virtuels
 */
class dfile extends basedb
{

	/** Nom du fichier avec l'extention, c'est ce qui sera communiqué au navigateur lors du téléchargement */
	var $nom_fichier;
	/** Titre du fichier, c'est ce qui sera affiché */
	var $titre;
	/** Id du dossier dans le quel le fichier se trouve */
	var $id_folder;
	/** Description du fichier */
	var $description;
	/** date de l'ajout du fichier */
	var $date_ajout;
	/** Id de l'association lié (méta-donnée) (n'a pas de rapport avec l'id_asso du dossier racine, @see dfolder)*/
	var $id_asso;
	/** Nombre de téléchargements */
	var $nb_telechargement;
	/** Mime type du fichier */
	var $mime_type;
	/** Taille en octets du fichier */
	var $taille;

	/** Charge un fichier par son ID
	 * @param $id ID du fichier
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `d_file`
				WHERE `id_file` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
	}

	/**
	 * Charge un fichier d'après une ligne de resultat SQL
	 */
	function _load ( $row )
	{
		$this->id = $row['id_file'];
		$this->nom_fichier = $row['nom_fichier_file'];
		$this->titre = $row['titre_file'];
		$this->id_folder = $row['id_folder'];
		$this->description = $row['description_file'];
		$this->date_ajout = strtotime($row['date_ajout_file']);
		$this->id_asso = $row['id_asso'];
		$this->nb_telechargement = $row['nb_telechargement_file'];
		$this->mime_type = $row['mime_type_file'];
		$this->taille = $row['taille_file'];

		$this->id_utilisateur = $row['id_utilisateur'];
		$this->id_groupe = $row['id_groupe'];
		$this->id_groupe_admin = $row['id_groupe_admin'];
		$this->droits_acces = $row['droits_acces_file'];
		$this->modere = $row['modere_file'];

	}

	/**
	 * Ajoute un fichier.
	 * Vous DEVEZ avoir fait appel à herit et set_rights avant !
	 * @param $file Un élément de $_FILES
	 * @param $titre Titre du dossier
	 * @param $id_folder Id du dossier parent (NULL si aucun)
	 * @param $description Description (NULL si aucune)
	 * @param $id_asso Association lié (NULL si aucune)
	 */
	function add_file ( $file, $titre, $id_folder, $description, $id_asso )
	{
		if ( !is_uploaded_file($file['tmp_name']) )
			return;

		$this->titre = $titre;
		$this->id_folder = $id_folder;
		$this->description = $description;
		$this->id_asso = $id_asso;
		$this->date_ajout = time();
		$this->modere=false;

		$this->nom_fichier= $file['name'];
		$this->taille=$file['size'];
		$this->mime_type=$file['type']; // ou mime_content_type($file['tmp_name']);

		$this->nb_telechargement=0;

		$sql = new insert ($this->dbrw,
			"d_file",
			array(
				"titre_file"=>$this->titre,
				"id_folder"=>$this->id_folder,
				"description_file"=>$this->description,
				"date_ajout_file"=>date("Y-m-d H:i:s",$this->date_ajout),
				"id_asso"=>$this->id_asso,

				"nom_fichier_file"=>$this->nom_fichier,
				"taille_file"=>$this->taille,
				"mime_type_file"=>$this->mime_type,
				"nb_telechargement_file"=>$this->nb_telechargement,

				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe"=>$this->id_groupe,
				"id_groupe_admin"=>$this->id_groupe_admin,
				"droits_acces_file"=>$this->droits_acces,
				"modere_file"=>$this->modere
				)
			);

		if ( $sql )
			$this->id = $sql->get_id();
		else
		{
			$this->id = null;
			return;
		}

		move_uploaded_file ( $file['tmp_name'], $this->get_real_filename() );

		if ( ereg("image/(.*)",$this->mime_type) )
		{
			exec("/usr/share/php5/exec/convert ".$this->get_real_filename()." -thumbnail 128x128 -quality 95 ".$this->get_thumb_filename());
			exec("/usr/share/php5/exec/convert ".$this->get_real_filename()." -thumbnail 500x1000 -quality 95 ".$this->get_screensize_filename());
		}
	}

	/**
	 * Ajoute un fichier.
	 * Vous DEVEZ avoir fait appel à herit et set_rights avant !
	 * @param $localfile Un fichier local
	 * @param $filename Nom de fichier
	 * @param $filesize Taille en octets
	 * @param $mime_type Type MIME du contenu
	 * @param $dateajout Date d'ajout
	 * @param $modere Modéré
	 * @param $nbdownlds Nombre de téléchargements
	 * @param $titre Titre du dossier
	 * @param $id_folder Id du dossier parent (NULL si aucun)
	 * @param $description Description (NULL si aucune)
	 * @param $id_asso Association lié (NULL si aucune)
	 */
	function import_file ( $localfile, $filename, $filesize, $mime_type, $dateajout, $modere, $nbdownlds, $titre, $id_folder, $description, $id_asso )
	{


		$this->titre = $titre;
		$this->id_folder = $id_folder;
		$this->description = $description;
		$this->id_asso = $id_asso;
		$this->date_ajout = $dateajout;
		$this->modere=$modere;

		$this->nom_fichier= $filename;
		$this->taille=$filesize;
		$this->mime_type=$mime_type; // ou mime_content_type($file['tmp_name']);

		$this->nb_telechargement=$nbdownlds;

		$sql = new insert ($this->dbrw,
			"d_file",
			array(
				"titre_file"=>$this->titre,
				"id_folder"=>$this->id_folder,
				"description_file"=>$this->description,
				"date_ajout_file"=>date("Y-m-d H:i:s",$this->date_ajout),
				"id_asso"=>$this->id_asso,

				"nom_fichier_file"=>$this->nom_fichier,
				"taille_file"=>$this->taille,
				"mime_type_file"=>$this->mime_type,
				"nb_telechargement_file"=>$this->nb_telechargement,

				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe"=>$this->id_groupe,
				"id_groupe_admin"=>$this->id_groupe_admin,
				"droits_acces_file"=>$this->droits_acces,
				"modere_file"=>$this->modere
				)
			);

		if ( $sql )
			$this->id = $sql->get_id();
		else
		{
			$this->id = null;
			return;
		}

		copy ( $localfile, $this->get_real_filename() );

		if ( ereg("image/(.*)",$this->mime_type) )
		{
			exec("/usr/share/php5/exec/convert ".$this->get_real_filename()." -thumbnail 128x128 -quality 95 ".$this->get_thumb_filename());
			exec("/usr/share/php5/exec/convert ".$this->get_real_filename()." -thumbnail 500x1000 -quality 95 ".$this->get_screensize_filename());
		}
	}

	/**
	 * Met à jour les informations d'un fichier.
	 * @param $titre Titre du dossier
	 * @param $description Description (NULL si aucune)
	 * @param $id_asso Association lié (NULL si aucune)
	 */
	function update_file ( $titre, $description, $id_asso )
	{

		$this->titre = $titre;
		$this->description = $description;
		$this->id_asso = $id_asso;

		$sql = new update ($this->dbrw,
			"d_file",
			array(
				"titre_file"=>$this->titre,
				"description_file"=>$this->description,
				"id_asso"=>$this->id_asso,
				"id_utilisateur"=>$this->id_utilisateur,
				"id_groupe"=>$this->id_groupe,
				"id_groupe_admin"=>$this->id_groupe_admin,
				"droits_acces_file"=>$this->droits_acces
				),
			array("id_file"=>$this->id)
			);

	}

	/**
	 * Deplace le fichier dans un autre dossier
	 * @param $id_folder Titre du dossier
	 */
	function move_to ( $id_folder )
	{
		$this->id_folder = $id_folder;
		$sql = new update ($this->dbrw,
			"d_file",
			array("id_folder"=>$this->id_folder),
			array("id_file"=>$this->id)
			);
	}

	/**
	 * Donne le nom du fichier sur le serveur.
	 * Les fichiers ne doivent pas être accessibles depuis l'exterieur.
	 */
	function get_real_filename()
	{
		global $topdir;
		return $topdir."var/files/".$this->id;
	}

	/**
	 * Donne le nom de l'aperçu sur le serveur.
	 * Les fichiers ne doivent pas être accessibles depuis l'exterieur.
	 */
	function get_thumb_filename()
	{
		global $topdir;
		return $topdir."var/files/thumb/".$this->id.".jpg";
	}

	/**
	 * Donne le nom de la version écran sur le serveur.
	 * Les fichiers ne doivent pas être accessibles depuis l'exterieur.
	 */
	function get_screensize_filename()
	{
		global $topdir;
		return $topdir."var/files/preview/".$this->id.".jpg";
	}

	function get_icon_name()
	{
		if ( ereg("image/(.*)",$this->mime_type) ) return "image.png";

		if ( ereg("video/(.*)",$this->mime_type) ) return "video.png";

		if ( ereg("audio/(.*)",$this->mime_type) ) return "sound.png";

		if ( $this->mime_type == "application/pdf" || $this->mime_type == "application/x-pdf" )
			return "pdf.png";

		if ( $this->mime_type == "text/richtext" || $this->mime_type == "application/msword"
				|| $this->mime_type == "application/vnd.oasis.opendocument.text" )
			return "doc.png";

		if ( ereg("text/(.*)",$this->mime_type) ) return "txt.png";

		return "file.png";
	}

	/** Extention de basedb, informe que le fichier n'est pas une catégorie (comportement par défaut).
	 * @return false
	 */
	function is_category()
	{
		return false;
	}

	/**
	 * Définit le status de modération du fichier
	 * @param $modere true=modéré, false=non modéré
	 */
	function set_modere($modere=true)
	{
		$this->modere=$modere;
		$sql = new update($this->dbrw,"d_file",array("modere_file"=>$this->modere),array("id_file"=>$this->id));
	}

	/**
	 * Supprime le fichier
	 */
	function delete_file()
	{
		$f = $this->get_real_filename();
		if ( file_exists($f)) unlink($f);
		$f = $this->get_thumb_filename();
		if ( file_exists($f)) unlink($f);
		$f = $this->get_screensize_filename();
		if ( file_exists($f)) unlink($f);

		$sql = new delete($this->dbrw,"d_file",array("id_file"=>$this->id));
	}

	/**
	 * Incremente le compteur de téléchargements
	 */
	function increment_download()
	{
		$sql = new requete($this->dbrw,"UPDATE `d_file` SET nb_telechargement_file=nb_telechargement_file+1 WHERE id_file='".$this->id."'");
		$this->nb_telechargement++;
	}
	
	function get_root_element()
  {
    $folder = new dfolder($this->db);
    $folder->load_root_by_asso(null);
    return $folder;  
  }
  
  function get_parent()
  {
    $folder = new dfolder($this->db);
    $folder->load_by_id($this->id_folder);
    return $folder;  
  }
  
  function can_explore()
  {
    return true;  
  }
  
}

?>
