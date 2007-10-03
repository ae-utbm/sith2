<?php
/*   
 * Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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
 
 /**
 * @file
 */


/* statut normal */
define('UVCOMMENT_NOTMODERATED', 0);
/* reporté comme abusif par un utilisateur */
define('UVCOMMENT_ABUSE', 1);
/* supprimé */
define('UVCOMMENT_DELETED', 2);


$departements = array('Humas', 'TC', 'GESC', 'GI', 'IMAP', 'GMC', 'EDIM');
 
class uv extends stdentity
{
  
  var $code;
  var $intitule;
  var $ects;

  /* booléens indiquant si des cours / tds / tps sont dispensés dans
   * l'UV en question
   */
  var $cours; 
  var $td; 
  var $tp;

  /* dans quel département ? */
  var $depts;


  /* un tableau d'objets uvcomments */
  var $comments;


  function load_by_code($code)
  {
    $req = new requete($this->db, "SELECT * 
                                   FROM 
                                          `edu_uv`
				   WHERE 
                                           `code_uv` = '" .
		       mysql_real_escape_string($code) . "'
				LIMIT 1");

    if ($req->lines == 1)
      {
	$row = $req->get_row();
	
	$this->id       = $row['id_uv'];
	$this->code     = $row['code_uv'];
	$this->intitule = $row['intitule_uv'];
	$this->ects     = $row['ects_uv'];
	$this->cours    = $row['cours_uv'];
	$this->td       = $row['td_uv'];
	$this->tp       = $row['tp_uv'];
	
	$this->load_depts();
	
	return true;
      }
    
    $this->id = null;	
    return false;
  }

  function load_by_id ($id)
  {
    $req = new requete($this->db, "SELECT * 
                                   FROM 
                                          `edu_uv`
				   WHERE 
                                           `id_uv` = '" .
		       mysql_real_escape_string($id) . "'
				LIMIT 1");

    if ($req->lines == 1)
      {
	$row = $req->get_row();

	$this->id       = $row['id_uv'];
	$this->code     = $row['code_uv'];
	$this->intitule = $row['intitule_uv'];
	$this->ects     = $row['ects_uv'];
	$this->cours    = $row['cours_uv'];
	$this->td       = $row['td_uv'];
	$this->tp       = $row['tp_uv'];

	$this->load_depts();

	return true;
      }
    
    $this->id = null;	
    return false;
  }

  function load_depts ()
  {
    if (!$this->id)
      return;

    $this->depts = array();

    $req = new requete($this->db,
		       "SELECT `id_dept` FROM `edu_uv_dept` WHERE `id_uv` = ".$this->id);

    while ($row = $req->get_row())
      {
	$this->depts[] = $row['id_dept'];
      }
  }

  function reload_depts ()
  {
    $this->load_depts ();
  }

  function modify($code_uv, $intitule, $c, $td, $tp, $ects, $depts)
  {
    if ($this->id <= 0)
      return false;

    $this->code     = $code_uv;
    $this->intitule = $intitule;
    $this->ects     = $ects;
    $this->cours    = $c;
    $this->td       = $td;
    $this->tp       = $tp;
    
    $req = new update ($this->dbrw,
		       'edu_uv',
		       array('code_uv' => $this->code,
			     'intitule_uv' => $this->intitule,
			     'cours_uv' => $this->cours,
			     'td_uv' => $this->td,
			     'tp_uv' => $this->tp,
			     'ects_uv' => $this->ects),
		       array('id_uv' => $this->id));
    

    /* suppression des départements */
    $req = new delete($this->dbrw,
		      'edu_uv_dept',
		      array('id_uv' => $this->id));

    global $departements;

    for ($i = 0; $i < count($depts); $i++)
      {
	$dept = mysql_real_escape_string($depts[$i]);
	if (in_array($dept, $departements))
	  $req = new insert($this->dbrw,
			    'edu_uv_dept',
			    array("id_uv" => $this->id,
				  "id_dept" => $dept));
      }

    $this->reload_depts();

    return;
  }
  


  function create ($code_uv, $intitule, $c, $td, $tp, $ects, $depts)
  {
    $this->code     = $code_uv;
    $this->intitule = $intitule;
    $this->ects     = $ects;
    $this->cours    = $c;
    $this->td       = $td;
    $this->tp       = $tp;
    

    $req = new insert ($this->dbrw,
		       'edu_uv',
		       array('code_uv' => $this->code,
			     'intitule_uv' => $this->intitule,
			     'cours_uv' => $this->cours,
			     'td_uv' => $this->td,
			     'tp_uv' => $this->tp,
			     'ects_uv' => $this->ects));
    
    if ($req)
      {
	$this->id = $req->get_id();
      }
    else
      {
	$this->id = -1;
	return false;
      }

    global $departements;

    /* ajout des départements */
    for ($i = 0; $i < count($depts); $i++)
      {
	$dept = mysql_real_escape_string($depts[$i]);
	if (in_array($dept, $departements))
	  $req = new insert($this->dbrw,
			    'edu_uv_dept',
			    array("id_uv" => $this->id,
				  "id_dept" => $dept));
      }
    
    return true;
  }
  

}


class uvcomment extends stdentity
{

  /* l'identifiant de l'UV */
  var $id_uv;
  /* l'identifiant de l'utilisateur ayant commenté */
  var $id_commentateur;
  /* note d'obtention (Format UTBM : A, B ...) */
  var $note_obtention;

  /* note sur l'intéret de l'UV */
  /* Est-ce que l'UV vaut le coup d'être suivie ?
   * (Réflexions sur la qualité de l'enseignement, 
   *  moyens mis à disposition ...) */
  var $interet;
  /* note sur l'utilité de l'UV */
  /* est-ce que l'UV est utile dans le cadre 
   * de la formation d'ingénieur ? */
  var $utilite;
  /* note sur la charge de travail */
  var $charge_travail;
  /* note générale que donne l'étudiant sur l'UV */
  var $note;

  /* commentaire dokuwiki */
  var $comment;

  /* date du commentaire */
  var $date;


  function load_by_id($id)
  {
    $req = new requete($this->db, "SELECT * 
                                   FROM 
                                          `edu_uv_comments`
				   WHERE 
                                           `id_comment` = '" .
		       mysql_real_escape_string($id) . "'
				LIMIT 1");
    
    if ($req->lines == 1)
      {
	$row = $req->get_row();

	$this->id              = $row['id_comment'];
 
	$this->id_uv           = $row['id_uv'];
	$this->id_commentateur = $row['id_utilisateur'];
	$this->note_obtention  = $row['note_obtention_uv'];

	$this->interet         = $row['interet_uv'];
	$this->utilite         = $row['utilite_uv'];
	$this->charge_travail  = $row['travail_uv'];
	$this->note            = $row['note_uv'];
	
	$this->comment         = $row['comment_uv'];
	
	$this->date            = $row['date_commentaire'];

	return true;
      }  
  }

  function create($id_uv,
		  $id_commentateur,
		  $commentaire,
		  $note_obtention = null,
		  $interet = 3,
		  $utilite = 3,
		  $note    = 3,
		  $travail = 3)
  {
    $sql = new insert($this->dbrw,
		      'edu_uv_comments',
		      array ('id_uv' => $id_uv,
			     'id_commentateur' => $id_utilisateur,
			     'note_obtention_uv' => $note_obtention,
			     'comment_uv' => $commentaire,
			     'interet_uv' => $interet,
			     'utilite_uv' => $utilite,
			     'note_uv'    => $note,
			     'travail_uv' => $travail,
			     'date_commentaire' => date(),
			     'state_comment' => 0));

    if ($sql->lines <= 0)
      return false;
    else
      $this->load_by_id($sql->get_id());
    return true;

  }

  function modere($level = UVCOMMENT_ABUSE)
  {
    if ($this->id <= 0)
      return false;
    
    $req = new update($this->dbrw,
		      'edu_uv_comments',
		      array('state_comment' => $level),
		      array('id_comment' => $this->id));

    return ($req->lines > 0);
  }
		  
}

?>