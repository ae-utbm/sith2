<?php
/** @file trajet.inc.php : Definition et gestion des entités trajet,
 *  dans le cadre du module de covoiturage.
 *
 */
/* Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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

define('STEP_WAITING',  0);
define('STEP_ACCEPTED', 1);
define('STEP_REFUSED',  2);

/* trajet ponctuel avec dates (table cv_trajet_date) */
define('TRJ_PCT', 0);
/* trajet lié à un événement du calendrier AE */
define('TRJ_EVT', 1);
/* trajet lié à une séance d'UV */
define('TRJ_EDU', 2);


require_once($topdir . "include/entities/edt.inc.php");

class trajet extends stdentity
{
  var $id_utilisateur;

  /* identifiants de départ / arrivée (loc_villes)  */
  var $ville_depart;
  var $ville_arrivee;

  var $etapes;

  var $date_proposition;

  var $dates;

  var $commentaires;

  /* un type de trajet */
  var $type;
  /* un identifiant d'entité liée (nouvelle ou séance d'emploi du temps) */
  var $id_ent;

  /** Charge une nouvelle en fonction de son id
   * $this->id est égal à null en cas d'erreur
   * @param $id id de la fonction
   */
  function load_by_id ($id)
  {
    $req = new requete($this->db, "SELECT 
                                            * 
                                   FROM 
                                            `cv_trajet`
				   WHERE 
                                            `id_trajet` = '" .
		       mysql_real_escape_string($id) . "'
				   LIMIT 1");

    if ( $req->lines == 1 )
      {
	$this->_load($req->get_row());
	$this->load_dates();
	return true;
      }
		
    $this->id = null;	
    return false;
  }
  /*
   * fonction de chargement des dates du trajet
   *
   */
  function load_dates()
  {
    $this->dates = array();

    if ($this->id <= 0)
      return false;
    
    switch ($this->type)
      {
	/* trajet ponctuel avec dates */
      case TRJ_PCT:
	$sql = new requete($this->db, "SELECT 
                                            `trajet_date` 
                                       FROM 
                                            `cv_trajet_date` 
                                       WHERE 
                                             `id_trajet` = $this->id");
	break;
	
	/* événement lié du calendrier */
      case TRJ_EVT:
	$sql = new requete($this->db, "SELECT 
                                            `date_debut_eve` 
                                       FROM 
                                            `nvl_dates` 
                                       WHERE 
                                             `id_nouvelle` = $this->id_ent");

	break;
	/* séance de cours du calendrier */
      case TRJ_EDU:
	$sql = new requete($this->db, "SELECT 
                                            `jour_grp`, `heure_debut_grp` 
                                       FROM 
                                            `edu_uv_groupe` 
                                       WHERE 
                                             `id_uv_groupe` = $this->id_ent");
	break;
	
      }
    if ($sql->lines <= 0)
      {
	return;
      }


    while ($res = $sql->get_row())
      {
	switch ($this->type)
	  {
	  case TRJ_PCT:
	    $this->dates[] = $res['trajet_date'];
	    break;

	  case TRJ_EVT:
	    $this->dates[] = $res['date_debut_eve'];
	    break;

	  case TRJ_EDU:
	    global $jour;
	    $this->dates[] = $jour[$res['jour_grp']] . ", " . $res['heure_debut_grp'] ;
	    break;
	  }
      }
    return;
  }
  
/*
   * fonction de chargement (privee)
   *
   * @param row tableau associatif
   * contenant les informations sur le trajet.
   *
   */
  function _load ($row)
  {
    $this->id			= $row['id_trajet'];
    $this->id_utilisateur	= $row['id_utilisateur'];
    
    $this->date_proposition     = $row['date_prop_trajet'];
    $this->commentaires         = $row['comments_trajet'];

    $this->ville_depart = new ville($this->db, 
				    $this->dbrw);

    $this->ville_arrivee = new ville($this->db, 
				     $this->dbrw);

    $this->ville_depart->load_by_id($row['id_ville_dep_trajet']);  
    $this->ville_arrivee->load_by_id($row['id_ville_arrivee_trajet']);
    
    $this->type = $row['type_trajet'];
    $this->id_ent = $row['id_ent'];
  }
  
  function create ($user, $villedepart, $villearrivee, $comments, $type, $id_ent = NULL)
  {
    $user = intval($user);

    if (($villedepart < 0) || ($villearrivee < 0))
      {
	return false;
      }
    if (($type > TRJ_PCT) && ($id_ent < 0))
      {
	return false;
      }
    $sql = new insert($this->dbrw,
		      'cv_trajet',
		      array('id_utilisateur'            => $user,
			    'type_trajet'               => $type,
			    'id_ville_dep_trajet'       => $villedepart,
			    'id_ville_arrivee_trajet'   => $villearrivee,
			    'date_prop_trajet'          => date('Y-m-d H:i:s'),
			    'comments_trajet'           => $comments,
			    'id_ent'                    => $id_ent));
    

    $this->load_by_id($sql->get_id());

    return ($this->id > 0);
    
  }
  
  /*
   * Ajoute une date à un trajet
   * @param date un timestamp
   * 
   */
  function add_date($date)
  {
    if ($this->id <= 0)
      return false;

    /* non approprié */
    if ($this->type != TRJ_PCT)
      return false;

    $date = intval($date);

    $sql = new insert($this->dbrw,
		      'cv_trajet_date',
		      array('id_trajet' => $this->id,
			    'trajet_date' => date("Y-m-d H:i:s",$date)));
    
    return ($sql->lines == 1);
  }

  /*
   * Fonction retournant si un trajet
   * est toujours d'actualité
   *
   */
  function has_expired()
  {
    /* un trajet pour séance dans l'emploi du temps 
     * est toujours valable
     */
    if ($this->type == TRJ_EDU)
      {
	return false;
      }
    
    if (count($this->dates) == 0)
      return true;
    
    foreach ($this->dates as $date)
      {
	/* il existe des dates pour ce trajet dans le futur */
	if (strtotime($date) > time())
	  return false;
      }
    return true;
  }
  function get_steps_by_date($date)
  {

    /* inutile dans le cas d'un trajet TRJ_EDU / TRJ_EVT */

    /* TODO : reflechir la dessus ; il semblerait que ce code soit 
     * un hack foireux dû à une mauvaise conception du système
     * (pas prévu que les étapes seraient liées à des dates de trajet
     * précises)
     *
     * Obsolétiser tout ca lui ferait le plus grand bien
     */

    if (($this->type == TRJ_EDU) || ($this->type == TRJ_EVT))
      {
	$this->load_steps();
	return false;
      }

    if (! in_array($date, $this->dates))
      return false;
	
    /* tentative de chargement des étapes */
    if (! count($this->etapes))
      {
	$this->load_steps();
      }

    /* pas d'étapes */
    if (! count($this->etapes))
      {
	return false;
      }

    foreach ($this->etapes as $etape)
      {
	if ($etape['date_etape'] == $date)
	  $ret[] = $etape;
      }

    return $ret;
  }

  /* chargement des étapes */

  function load_steps()
  {
    $this->etapes = array();

    $req = new requete($this->db, "SELECT * 
                                   FROM 
                                          `cv_trajet_etape` 
                                   WHERE 
                                          `id_trajet` = ".$this->id.
			         " ORDER BY 
                                          `date_prop_etape` 
                                   ASC");

    if ($req->lines <= 0)
      {
	return false;
      }
    
    while ($res = $req->get_row())
      {
	$step = array();
	$step['ville'] = $res['id_ville_etape'];
	$step['date_etape'] = $res['trajet_date'];
	$step['id'] = $res['id_etape'];
	$step['id_utilisateur'] = $res['id_utilisateur'];
	$step['date_proposition'] = $res['date_prop_etape'];
	$step['comments']   = $res['comments_etape'];
	$step['etat'] = $res['accepted_etape'];     
	$this->etapes[] = $step;
      }

    return true;
  }

  /* retourne si pour une date donnée,
   * l'utilisateur a déjà proposé une étape
   */
  function already_proposed_step($user, $date = NULL)
  {

    if (! count ($this->etapes))
      $this->load_steps();

    if (! count($this->etapes))
      return false;


    foreach($this->etapes as $etape)
      {
	if ($etape['date_etape'] != $date)
	  continue;

	if ($etape['id_utilisateur'] == $user)
	  return true;
      }
    return false;
  }
  /*
   * Fonction permettant d'ajouter une étape
   *
   * Note : une étape avec une ville signifie que l'utilisateur
   * veut participer au trajet, mais qu'il n'a pas d'obligation 
   * précise en terme de modification du trajet. (explications 
   * plus poussée en commentaires, ...)
   *
   */
  function add_step($user, $comments, $date = NULL, $ville = NULL)
  {
    if ($this->id <= 0)
      {      
	return false;
      }


    if (($this->type == TRJ_EVT) || ($this->type == TRJ_EDU))
      {
	if (! already_proposed_step($user))
	  {
	    $req = new insert($this->dbrw,
			      'cv_trajet_etape',
			      array('id_trajet'        => $this->id,
				    'trajet_date'      => $date,
				    'id_utilisateur'   => $user,
				    'id_ville_etape'   => $ville,
				    'date_prop_etape'  => date('Y-m-d H:i:s'),
				    'comments_etape'   => $comments), true);
	  }
	else
	  return false;
      }

    else if ($this->type == TRJ_PCT)
      {
	
	if (! in_array($date, $this->dates))
	  {
	    print_r($date);
	    print_r($this->dates);

	    return false;
	  }
	
	/* pour une date donnée, un utilisateur ne 
	 * peut pas proposer 2 étapes différentes
	 */
	if ($this->already_proposed_step($user, $date))
	  {
	    echo "deja propose ...";
	    return false;
	  }
	
	$req = new insert($this->dbrw,
			  'cv_trajet_etape',
			  array('id_trajet'        => $this->id,
				'trajet_date'      => $date,
				'id_utilisateur'   => $user,
				'id_ville_etape' => $ville,
				'date_prop_etape'  => date('Y-m-d H:i:s'),
				'comments_etape'   => $comments), true);
      }
    return ($req->lines > 0);
  }
  /*
   * obtention des informations pour une étape spécifique
   *
   */
  function get_step_by_id($id)
  {
    if (! count($this->etapes))
      return false;

    foreach ($this->etapes as $etape)
      {
	if ($etape['id'] == $id)
	  return $etape;
      }
    return false;
  }
  /*
   * obtention des utilisateurs motivés par un trajet pour une 
   * date donnée.
   */
  function get_users_by_date($date)
  {
    if ($this->type == TRJ_PCT)
      { 
	$req = new requete($this->db,
			   "SELECT DISTINCT 
                                         `id_utilisateur`
                            FROM
                                         `cv_trajet_etape`
                            WHERE
                                         `id_trajet` = $this->id
                            AND
                                         `trajet_date` = '".$date."'");

    if ($req->lines <= 0)
      {
	return false;
      }
    else
      {
	while ($res = $req->get_row())
	  {
	    $ret[] = $res['id_utilisateur'];
	  }
      }
    return $ret;
      }
  }
  /* acceptation / refus d'étapes */
  function accept_step($id, $date)
  {
    $sql = new update($this->dbrw,
		      'cv_trajet_etape',
		      array('accepted_etape' => 1),
		      array('id_trajet' => $this->id,
			    'trajet_date' => mysql_real_escape_string($date),
			    'id_etape' => intval($id)));
    
    return ($sql->lines > 0);
  }

  function refuse_step($id, $date)
  {
    $sql = new update($this->dbrw,
		      'cv_trajet_etape',
		      array('accepted_etape' => 2),
		      array('id_trajet' => $this->id,
			    'trajet_date' => mysql_real_escape_string($date),
			    'id_etape' => intval($id)));
    
    return ($sql->lines > 0);
  }
  
}


?>
