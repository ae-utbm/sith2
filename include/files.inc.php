<?php
/** @file
 *
 * @brief Classe de gestion des fichiers. Surtout utilis� sur la page
 * fichiers.php, mais pourra a terme etre utilis� pour chaque
 * formulaire necessitant l'envoi d'un fichier sur le serveur.
 *
 */

/* Copyright 2006
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Repris des sources de la version 1 du site.
 *
 * Ce fichier fait partie du site de l'Association des �tudiants de
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

/* etat des fichiers */
define ("F_UNMODERATED", 0);
define ("F_MODERATED",   1);
define ("F_ALL",         2);


/** Classe de gestion des fichiers */
class file
{
  var $name, $type, $ext;

  /* table fichiers */
  var $id;
  var $auteur;
  var $id_cat;
  var $nom;
  var $mime;
  var $date;
  var $comment;
  var $modere;
  var $public;
  var $nb_dl;
  var $titre;


  var $errmsg;

  var $db, $dbrw;
  function file ($db, $dbrw = false)
  {
    $this->db = $db;
    $this->dbrw = $dbrw;
  }

  /** Upload
   *
   * Cette fonction permet d'uploader un fichier; le nom est ambigu,
   * puisque ce n'est pas rellement php qui va se charger d'uploader
   * le fichier. Ce code ne rentrera en jeu qu'apres le formulaire
   * (contenant le fichier) post�, et traitera le fichier fraichement
   * upload�.
   *
   * @param tmp_file   Le nom du fichier temporaire dans lequel les
   *                   donn�es ont �t� upload�es.
   * @param mime_types Un tableau des types mimes autoris�s.  Etant
   * donne la conception de la partie fichier du site, son utilisation
   * n'a pas d'importance dans cette partie, mais en a une concernant
   * l'envoi d'images pour le matmatronch par exemple ...
   * @param type       Type MIME du fichier.
   * @param filename   Le fichier de destination
   *
   * @return 0 si succ�s, -1 si erreur
   */
  function upload ($tmp_file,
		   $mime_types,
		   $type,
		   $filename)
  {
    global $topdir;

    $this->type = $type;

    /* Est-ce que l'upload a correctement fonctionn� ? */
    if( !is_uploaded_file($tmp_file))
      {
	$this->errmsg = "Erreur dans l'envoi du fichier : " . $error;
	return -1;
      }

    /* verification mimetype */
    if ($mime_types)
      {
	if (!array_key_exists($this->type, $mime_types) &&
	    !in_array($this->type, $mime_types))
	  {
	    $this->errmsg = "Type MIME (" .
	      $this->type .
	      ") interdit ou incorrect !";
	    return -1;
	  }
	$this->ext = $mime_types[$this->type];
      }

    /* destination */
    $this->name = $filename;

    /* On d�place le fichier */
    if(!move_uploaded_file($tmp_file, $this->name))
      {
	$this->errmsg = "Impossible de copier le fichier dans " . $this->name;
	return -1;
      }
    return 0;
  }

  /** Enregistre le fichier, suite � un envoi depuis fichiers.php
   *
   * @param tmp_file  chemin d'acces temporaire ($_FILES)
   * @param author    l'id de l'etudiant
   * @param cat       categorie affectee
   * @param nom       nom du fichier (chemin d'acces)
   * @param comment   commentaire associ�
   * @param public    public ou non
   * @param modere    modere ou non
   * @param titre     titre generique
   */
  function  add_file($tmp_file,
		     $author,
		     $cat,
		     $nom,
		     $comment,
		     $public,
		     $modere,
		     $titre)
  {
  	global $topdir;
    /* verification acces base */
    if (!$this->dbrw)
      return false;

    /* les fichiers a destination de fichiers.php ne
     * necessitent pas de verification de type */
    $mime_types = NULL;
    /* le mimetype */
    $type = mime_content_type($tmp_file);
    $filename = $topdir . "var/files/" . $nom;


    /* on deplace le fichier (fonction membre upload()) */
    $this->upload ($tmp_file,
		   $mime_types,
		   $type,
		   $filename);

    /* on ajoute le fichier dans la base de donn�es */
    $req = new insert ($this->dbrw,
		       "fichiers",
		       array ("id_utilisateur" => $author,
			      "id_catfch" => $cat,
			      "nom_fichier" => "./var/files/" . $nom,
			      "mime_fichier" => $type,
			      "date_fichier" => date("Y-m-d h:i:s"),
			      "commentaire_fichier" => $comment,
			      "modere_fichier" => $modere,
			      "public_fichier" => $public,
			      "titre_fichier" => $titre));
    if ($req == false)
      return false;

    return true;
  }

  /** Affiche le d�tail des fichiers.
   *
   *
   * @param public indique si on r�cup�re seulement les fichiers
   * publics
   * @return infos un tableau associatif ([0] => (id => 0, etc...)
   * ...)
   *
   *
   */
  function get_files_info ($public = false)
  {
    global $topdir;

    $sql = "SELECT
         `fichiers`.`id_fichier`
         ,`fichiers`.`id_utilisateur`
         ,`fichiers`.`nom_fichier`
         ,`fichiers`.`mime_fichier`
         ,`fichiers`.`date_fichier`
         ,`fichiers`.`commentaire_fichier`
         ,`fichiers`.`nb_telecharge_fichier`
         ,`fichiers`.`titre_fichier`
         ,`fichiers_cat`.`nom_catfch`
         , CONCAT(`utilisateurs`.`prenom_utl` , ' ', `utilisateurs`.`nom_utl`)
                 AS `nom_prenom`

       FROM `fichiers`
       INNER JOIN `fichiers_cat` ON
           `fichiers`.`id_catfch` = `fichiers_cat`.`id_catfch`
       INNER JOIN `utilisateurs` ON
           `utilisateurs`.`id_utilisateur` = `fichiers`.`id_utilisateur`

       WHERE 1";
    $sql .= " AND `fichiers`.`modere_fichier` = 1";
    
    /* public = true (visiteur) ? ou non */
    if ($public == true)
      $sql .= " AND `fichiers`.`public_fichier` = 1";
    /* on tri par categories */
    $sql .= " ORDER BY `fichiers`.`date_fichier` DESC";

    $req = new requete ($this->db, $sql);


    while ($file = $req->get_row())
      $file_cat[$file['nom_catfch']][] = $file;

    return $file_cat;
  }

  /** Recupere les fichiers recents
   * @param public selectionne uniquement les fichiers publics
   * @param nb nombre de fichiers a r�cuperer
   *
   * @return files un tableau associatif contenant les infos
   */
  function get_recent_files ($public = true, $nb = 5)
  {
    $sql = "SELECT `fichiers`.*
                   , CONCAT(`utilisateurs`.`prenom_utl`,
                            ' ',
                            `utilisateurs`.`nom_utl`) AS `nom_prenom`

            FROM `fichiers`
            INNER JOIN `utilisateurs` ON
                       `utilisateurs`.`id_utilisateur` = `fichiers`.`id_utilisateur`

            WHERE `fichiers`.`modere_fichier` = 1";
    /* on veut juste les fichiers publics */
    if ($public == true)
      $sql .= " AND `fichiers`.`public_fichier` = 1";
    /* dans l'autre cas, on veut tout */

    /* terminaison requete */
    $sql .= "    ORDER BY `date_fichier` DESC LIMIT ".  intval($nb);

    $query = new requete($this->db,
			 $sql);

    $files = array();


    for ($i = 0; $i < $query->lines; $i++)
      $files[] = $query->get_row ();

    return $files;
  }

  /** Recupere le nombre de fichiers non moderes
   * @return nombre de fichiers non moderes
   */
  function get_unmoderated_files_number ()
  {
    $query = new requete($this->db,
			 "SELECT Count(`id_fichier`) FROM `fichiers` ".
			 "WHERE `modere_fichier` = 0");
    $res = $query->get_row();
    return $res[0];
  }
  /** Charge les infos contenues dans la table files d'un fichier
   * en fonction du num�ro
   */
  function load_by_id ($filenum)
  {

    $filenum = intval($filenum);

    $sql = new requete ($this->db,
			"SELECT * FROM `fichiers` WHERE ".
			"`id_fichier` = " . $filenum . " LIMIT 1");
    $rs = $sql->get_row();
    if ($sql->lines>0) {
      //    $this->file_infos = $rs;

      $this->id = $rs["id_fichier"];
      $this->auteur = $rs["id_utilisateur"];
      $this->id_cat  = $rs["id_catfch"];
      $this->nom  = $rs["nom_fichier"];
      $this->mime  = $rs["mime_fichier"];
      $this->date  = $rs["date_fichier"];
      $this->comment  = $rs["commentaire_fichier"];
      $this->modere  = $rs["modere_fichier"];
      $this->public  = $rs["public_fichier"];
      $this->nb_dl  = $rs["nb_telecharge_fichier"];
      $this->titre  = $rs["titre_fichier"];
      return 0;
    }
    else return -1;
  }
  /** supprime un fichier
   * en fonction du num�ro
   */
  function delete_file ()
  {
    global $topdir;
    if (!$this->dbrw)
      return false;

    $id = $this->id;

    /* suppression dans la base */
    $sql = new delete($this->dbrw,
		      "fichiers",
		      array ("id_fichier" => intval($id)));


    /* unlinkage */
    unlink ($topdir . $this->nom);

    return $sql->lines == 1 ? true : false;
  }

  /** modere un fichier
   */
  function modere ()
  {
    if ((!$this->id) || (!$this->dbrw))
      return false;
    
    $sql = new update ($this->dbrw,
		       "fichiers",
		       array('modere_fichier' => '1'),
		       array('id_fichier' => $this->id));

    return $sql->lines == 1 ? true : false;
  }

  /** modifie un fichier
   */
  function modify ($id,
		   $titre,
		   $cat,
		   $comment,
		   $public)
  {
    if (!$this->dbrw)
      return false;

    $req = new update ($this->dbrw,
		       "fichiers",
		       array ("titre_fichier" => $titre,
			      "id_catfch" => $cat,
			      "commentaire_fichier" => $comment,
			      "public_fichier" => $public),
		       array ("id_fichier" => intval($id)));

    return $req->lines == 1 ? true : false;
  }
}

?>
