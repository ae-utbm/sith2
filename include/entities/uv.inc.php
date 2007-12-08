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

/* "Mis en quarantaine" par l'équipe de modération */
define('UVCOMMENT_QUARANTINE', 2);

/* accepté définitivement(après mise en quarantaine)
 * stade apres lequel un utilisateur normal ne peut
 * plus le rapporter comme abusif
 *
 * (l'équipe de modération reste toutefois maitre du statut)
 */
define('UVCOMMENT_ACCEPTED', 3);



$uvcomm_utilite = array(
			'-1' => 'Non renseigné',
			'0' => 'Inutile',
			'1' => 'Pas très utile',
			'2' => 'Utile',
			'3' => 'Très utile',
			'4' => 'Indispensable');

$uvcomm_interet = array('-1' => 'Non renseigné',
			'0'  => 'Aucun',
			'1' => 'Faible',
			'2' => 'Bof',
			'3' => 'Intéressant',
			'4' =>'Tres intéressant');

$uvcomm_travail = array ('-1' =>'Non renseigné',
			 '0'=>'Symbolique',
			 '1'=>'Faible',
			 '2'=>'Moyenne',
			 '3'=>'Importante',
			 '4'=>'Très importante');

$uvcomm_note = array ('-1' => 'Sans avis',
		      '0'=>'Nul',
		      '1'=>'Pas terrible',
		      '2'=>'Neutre',
		      '3'=>'Pas mal',
		      '4'=>'Génial');

$uvcomm_qualite = array ('-1' => 'Sans avis',
			 '0'=>'Inexistante',
			 '1'=>'Mauvaise',
			 '2'=>'Moyenne',
			 '3'=>'Bonne',
			 '4'=>'Excellente');


$departements = array('Humanites', 'TC', 'GESC', 'GI', 'IMAP', 'GMC', 'EDIM');
 
class uv extends stdentity
{
  
  var $code;
  var $intitule;
  var $objectifs;
  var $programme;
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
	
	$this->id        = $row['id_uv'];
	$this->code      = $row['code_uv'];
	$this->intitule  = $row['intitule_uv'];
	$this->objectifs = $row['objectifs_uv'];
	$this->programme = $row['programme_uv'];
	$this->ects      = $row['ects_uv'];
	$this->cours     = $row['cours_uv'];
	$this->td        = $row['td_uv'];
	$this->tp        = $row['tp_uv'];
	
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
	$this->objectifs = $row['objectifs_uv'];
	$this->programme = $row['programme_uv'];
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

  function modify($code_uv, $intitule, $obj, $prog, $c, $td, $tp, $ects, $depts)
  {
    if ($this->id <= 0)
      return false;

    $this->code      = $code_uv;
    $this->intitule  = $intitule;
    $this->programme = $prog;
    $this->objectifs = $obj;
    $this->ects      = $ects;
    $this->cours     = $c;
    $this->td        = $td;
    $this->tp        = $tp;
    
    $req = new update ($this->dbrw,
		       'edu_uv',
		       array('code_uv' => $this->code,
			     'intitule_uv' => $this->intitule,
			     'objectifs_uv' => $this->objectifs,
			     'programme_uv' => $this->programme,
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
  

  function load_comments($admin = false)
  {
    if (!$this->id)
      return false;

    $this->comments = array();

    $sql = 'SELECT 
                                `id_comment` 
                       FROM
                                `edu_uv_comments`
                       WHERE
                                `id_uv` = '.$this->id;
    if ($all == false)
      $sql .= " AND state_comment IN (0, 1, 3)";

    $rq = new requete($this->db,
		      $sql);


    $i = 0;
    while ($rs = $rq->get_row())
      {
	$this->comments[$i] = new uvcomment($this->db);
	$this->comments[$i]->load_by_id($rs['id_comment']);
	$i++;
      }
    return;
  }
  
  function prefer_list()
  {
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
  /* note sur la qualité de l'enseignement */
  var $qualite_ens;
  /* note générale que donne l'étudiant sur l'UV */
  var $note;

  /* commentaire dokuwiki */
  var $comment;

  /* date du commentaire */
  var $date;

  /* etat du commentaire */
  var $etat;

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
	$this->qualite_ens     = $row['qualite_uv'];
	$this->note            = $row['note_uv'];
	
	$this->comment         = $row['comment_uv'];
	
	$this->date            = $row['date_commentaire'];

	$this->etat            = $row['state_comment'];
	
	return true;
      }  
  }

  function modify($commentaire,
		  $note_obtention = null,
		  $interet = 3,
		  $utilite = 3,
		  $note    = 3,
		  $travail = 3,
		  $qualite = 3)
  {
    $sql = new update($this->dbrw,
		      'edu_uv_comments',
		      array('note_obtention_uv' => $note_obtention,
			    'comment_uv' => $commentaire,
			    'interet_uv' => $interet,
			    'utilite_uv' => $utilite,
			    'note_uv'    => $note,
			    'travail_uv' => $travail,
			    'qualite_uv' => $qualite,
			    'date_commentaire' => date("Y-m-d H:i:s")),
		      array ("id_comment" => $this->id));
    
    return ($sql->lines == 1);
  }

  function create($id_uv,
		  $id_commentateur,
		  $commentaire,
		  $note_obtention = null,
		  $interet = 3,
		  $utilite = 3,
		  $note    = 3,
		  $travail = 3,
		  $qualite = 3)
  {
    $sql = new insert($this->dbrw,
		      'edu_uv_comments',
		      array ('id_uv' => $id_uv,
			     'id_utilisateur' => $id_commentateur,
			     'note_obtention_uv' => $note_obtention,
			     'comment_uv' => $commentaire,
			     'interet_uv' => $interet,
			     'utilite_uv' => $utilite,
			     'note_uv'    => $note,
			     'travail_uv' => $travail,
			     'qualite_uv' => $qualite,
			     'date_commentaire' => date("Y-m-d H:i:s"),
			     'state_comment' => 0));

    if ($sql->lines <= 0)
      return false;
    else
      $this->load_by_id($sql->get_id());
    return true;
  }
  function delete()
  {
    if (!$this->id)
      return false;

    $req = new delete($this->dbrw,
		      'edu_uv_comments',
		      array('id_comment' => $this->id));

    return ($req->lines == 1);
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

/** Fonctions "globales" sur les UVs */

function get_creds_cts($id_etu, $db)
{
  global $topdir;
  require_once($topdir . "include/cts/sqltable.inc.php");

  $req = new requete($db, "SELECT
                                  `edu_uv`.`id_uv`
                                , `edu_uv`.`code_uv`
                                , `edu_uv`.`intitule_uv`
                                , `edu_uv_groupe`.`semestre_grp`
                                , `edu_uv`.`ects_uv`
                           FROM
                                `edu_uv_comments`

                           INNER JOIN
                                 `edu_uv`
                           USING (`id_uv`)

                           INNER JOIN
                                 `edu_uv_groupe`
                           ON `edu_uv_groupe`.`id_uv` = `edu_uv`.`id_uv` 

                           INNER JOIN
                                 `edu_uv_groupe_etudiant`
                           ON `edu_uv_groupe`.`id_uv` = `edu_uv_comments`.`id_uv`

                           WHERE
                                 `edu_uv_comments`.`note_obtention_uv` IN ('A', 'B', 'C', 'D', 'E')
                           AND
                                 `edu_uv_comments`.`id_utilisateur` = ".intval($id_etu) . 
		         " GROUP BY
                                 `code_uv`");

  
  $cts = new contents("Détails des crédits obtenus");
  $cts->add_paragraph("Ces statistiques sont déterminées en fonction des commentaires ".
		      "que vous avez laissés sur les UVs (qui permettent de donner ".
		      "une information sur les obtentions d'UV)"); 

  $cts->add(new sqltable('details_uv', "", $req, "./index.php", "id_uv",
			 array("code_uv" => "Code de l'UV", 
			       "intitule_uv" => "Intitulé de l'UV", 
			       "semestre_grp"=> "Semestre d'obtention", 
			       "ects_uv"     => "Crédits ECTS obtenus"), array (), array()));
  

  if ($sql->lines > 0)
    {
      $totects = 0;
      while ($line = $req->get_row)
	{
	  $totects += $line['ects_uv'];
	}

      $cts->add_paragraph("Total des crédits ECTS obtenus : ".
			  "<b>$totects</b> crédits");

    }

  return $cts;
}


?>