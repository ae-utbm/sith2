<?php
/* Copyright 2010
 * - Julien Etelain < julien at pmad dot net >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Mathieu Briand < briandmathieu at hyprua dot org >
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

/** @file
 * Gestion des affiches
 *
 */

/**
 * Nouvelle du site
 */
class affiche extends stdentity
{
  /** Auteur de l'affiche */
  var $id_utilisateur;

  /** Association/club concerné */
  var $id_asso;

  /** Titre */
  var $titre;

  /** Le fichier lié */
  var $id_file;

  /** Etat de modération: true modéré, false non modéré */
  var $modere;

  /** Utilisateur ayant modéré l'affiche */
  var $id_utilisateur_moderateur;

  /** Charge une affiche en fonction de son id
   * $this->id est égal à null en cas d'erreur
   * @param $id id de la fonction
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `aff_affiches`
        WHERE `id_affiche` = '" .
           mysql_real_escape_string($id) . "'
        LIMIT 1");

    if ( $req->lines == 1 )
    {
      $this->_load($req->get_row());
      return true;
    }

    $this->id = null;
    return false;
  }

  /*
   * fonction de chargement (privee)
   *
   * @param row tableau associatif
   * contenant les informations sur l'affiche.
   *
   */
  function _load ( $row )
  {
    $this->id      = $row['id_affiche'];
    $this->id_utilisateur  = $row['id_utilisateur'];
    $this->id_asso    = $row['id_asso'];
    $this->titre      = $row['titre_aff'];
    $this->id_file    = $row['id_file'];
    $this->date_deb   = strtotime($row['date_deb']);
    $this->date_fin    = strtotime($row['date_fin']);
    $this->date        = strtotime($row['date_aff']);
    $this->modere      = $row['modere_aff'];
    $this->id_utilisateur_moderateur  = $row['id_utilisateur_moderateur'];
  }

  /** Construit un stdcontents avec l'affiche
   */
  function get_contents ($displaymap=true)
  {
    global $wwwtopdir,$topdir;

    $file = new dfile($site->db, $site->dbrw);
    $file->load_by_id($this->id_file);
    $image = new image($this->titre, $file->get_html_link());

    $cts = new contents("Affiche : ".$this->titre);
    $cts->add(image);
    $cts->add_paragraph("Affichée du ".textual_plage_horraire($this->date_deb)."au ".textual_plage_horraire($this->date_fin));

    return $cts;
  }

  /** Supprime l'affiche
   */
  function delete ()
  {
    if ( !$this->dbrw ) return;

    $this->set_tags_array(array());

    new delete($this->dbrw,"aff_affiches",array("id_affiche"=>$this->id));
    $this->id = null;
  }

  /** Valide l'affiche
   */
  function validate($id_utilisateur_moderateur)
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"aff_affiches",array("modere_aff"=>1,"id_utilisateur_moderateur"=>$id_utilisateur_moderateur),array("id_affiche"=>$this->id));
    $this->modere_aff = 1;
    $this->id_utilisateur_moderateur = $id_utilisateur_moderateur;
  }

  /** Invalide l'affiche
   */
  function unvalidate()
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"aff_affiches",array("modere_aff"=>0),array("id_affiche"=>$this->id));
    $this->modere_aff = 0;
  }


  /** @brief Ajoute une affiche
   *
   * @param id_utilisateur l'identifiant de l'utilisateur
   * @param id_asso (facultatif) l'identifiant de l'association
   * @param titre titre de l'affiche
   * @param id_file id du fichier de l'affiche
   * @param date_deb début de la campagne d'affichage
   * @param date_fin fin de la campagne d'affichage
   *
   * @return true ou false en fonction du resultat
   */
  function add_affiche($id_utilisateur,
        $id_asso = null,
        $titre,
        $id_file,
        $date_deb,
        $date_fin)

  {
    if (!$this->dbrw)
      return false;

    $this->id_utilisateur = $id_utilisateur;
    $this->id_asso = $id_asso;
    $this->titre = $titre;
    $this->id_file = $id_file;
    $this->date_deb = $date_deb;
    $this->date_fin = $date_fin;

    $req = new insert ($this->dbrw,
           "aff_affiches",
           array ("id_utilisateur" => $id_utilisateur,
            "id_asso" => $id_asso,
            "titre_aff" => $titre,
            "id_file" => $id_file,
            "date_modifie" => date("Y-m-d H:i:s"),
            "date_deb" => date("Y-m-d H:i:s", $date_deb),
            "date_fin" => date("Y-m-d H:i:s", $date_fin),
            "modere_aff" =>  false,
            "id_utilisateur_moderateur"=>null
            ));

    if ( $req )
      $this->id = $req->get_id();
    else
      $this->id = null;

    return ($req != false);
  }


  /**
   * Modifie l'affiche
   *
   */
  function save_affiche(
        $id_asso = null,
        $titre,
        $date_deb,
        $date_fin,
        $modere=false,
        $id_utilisateur_moderateur=null)
  {
    if (!$this->dbrw)
      return false;

    $this->id_asso = $id_asso;
    $this->titre = $titre;
    $this->id_file = $id_file;
    $this->date_deb = $date_deb;
    $this->date_fin = $date_fin;
    $this->modere = $modere;
    $this->id_utilisateur_moderateur = $id_utilisateur_moderateur;

    $req = new update ($this->dbrw,
           "aff_affiches",
           array ("id_utilisateur" => $id_utilisateur,
            "id_asso" => $id_asso,
            "titre_aff" => $titre,
            "id_file" => $id_file,
            "date_modifie" => date("Y-m-d H:i:s"),
            "date_deb" => date("Y-m-d H:i:s", $date_deb),
            "date_fin" => date("Y-m-d H:i:s", $date_fin),
            "modere_aff" =>  $modere,
            "id_utilisateur_moderateur"=>$id_utilisateur_moderateur
            ),
         array(
           "id_affiche"=>$this->id
           ));
  }

  /* Renvoie un sqltable des affiches que l'utilisateur peut modifier
  */
  function get_html_list($user){
    $where = "";
    if (! $user->is_in_group("moderateur_site"))
      $where = "AND (`id_utilisateur` = '".$user->id."'
              OR `id_asso` IN (".$user->get_assos_csv(ROLEASSO_MEMBREBUREAU)."))";

    $req = new requete($this->db, "SELECT aff_affiches.*,
         CONCAT(`utilisateurs`.`prenom_utl`,
            ' ',
            `utilisateurs`.`nom_utl`) AS `nom_utilisateur`
        FROM `aff_affiches`
        INNER JOIN `utilisateurs` USING (id_utilisateur)
        WHERE `date_fin` > NOW()" .
        $where);

    $tbl = new sqltable(
      "listaff",
      "Campagnes d'affichage en cours ou à venir",
      $req,
      "news.php",
      "id_affiche",
      array("titre"=>"Titre", "nom_utilisateur"=>"Auteur", "date_deb"=>"Début", "date_fin"=>"Fin"),
      array("edit" => "Modifier", "delete"=>"Enlever"), array(), array( )
      );

    return $tbl;
  }

}


?>
