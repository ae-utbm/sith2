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
 * @file Gestion des uvs et partie pédagogie du site
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

/*
 * identifiant du répertoire contenant les fichiers
 * relatifs aux UVs
 *
 */
define('UVFOLDER', 784);


/* tableaux globaux sur les commentaires UV */

/* Note : ces critères sont inspirés du projet de David 
 * Anderson (Dave`), 
 * http://code.google.com/p/critic
 */
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


/* tableaux sur la catégorisation des UVs à l'intérieur des départements */

$humas_cat = array(''=> null, 'EC' => 'EC', 'CG' => 'CG', 'EX' => 'EX');
$tc_cat    = array('' => null, 'CS' => 'CS', 'TM' => 'TM', 'EX' => 'EX');
/* note : à l'heure actuelle, il n'existe pas d'UV de TM en EDIM */
$edim_cat  = array(''=>null, 
       'CS' => 'CS', 
       'TM' => 'TM', 
       'RN' => 'RN', 
       'EX' => 'EX');

$gesc_cat  = array(''=> null,
       'CS' => 'CS', 
       'TM' => 'TM', 
       'RN' => 'RN', 
       'EX' => 'EX');
$gi_cat    = array('' => null, 
       'CS' => 'CS', 
       'TM' => 'TM', 
       'RN' => 'RN', 
       'EX' => 'EX');

$gmc_cat   = array('' => null, 
       'CS' => 'CS', 
       'TM' => 'TM', 
       'RN' => 'RN', 
       'EX' => 'EX');

$imap_cat  = array('' => null, 
       'CS' => 'CS', 
       'TM' => 'TM', 
       'RN' => 'RN', 
       'EX' => 'EX');

$uv_descr_cat = array('NA' => 'Inconnu',
                      'CS' => 'Connaissances scientifiques',
          'TM' => 'Techniques et méthodes',
          'EX' => 'Extérieur',
          'EC' => 'Expression / Communication',
          'CG' => 'Culture Générale',
          'RN' => 'Remise à niveau');
 
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

  /* catégories par département ? */
  var $cat_by_depts;

  /* un identifiant de répertoire (partie fichiers) */
  var $idfolder;
  var $folder;


  /* un tableau d'objets uvcomments */
  var $comments;


  function load_by_code($code)
  {
    $req = new requete($this->db, "SELECT * ".
                                   "FROM ".
                                   "`edu_uv` ".
                                   "WHERE ".
                                   "`code_uv` = '".
                                   mysql_real_escape_string($code).
                                   "' ".
                                   "LIMIT 1");

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
      $this->idfolder  = $row['id_folder'];

      $this->load_depts();
  
      return true;
    }

    $this->id = null;  
    return false;
  }

  function load_by_id ($id)
  {
    $req = new requete($this->db, "SELECT * ". 
                                  "FROM ".
                                  "`edu_uv` ".
                                  "WHERE ".
                                  "`id_uv` = '".
                                  mysql_real_escape_string($id).
                                  "' ".
                                  "LIMIT 1");

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
      $this->idfolder = $row['id_folder'];

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
           "SELECT `id_dept`, `uv_cat` FROM `edu_uv_dept` WHERE `id_uv` = ".$this->id);

    while ($row = $req->get_row())
    {
      $this->depts[] = $row['id_dept'];
      $this->cat_by_depts[$row['id_dept']] = $row['uv_cat'];
    }
  }

  function reload_depts ()
  {
    $this->load_depts ();
  }

  /*
   * Modification d'UV.
   *
   * note : uv_cat doit etre un tableau indexé par le nom du département
   * (on part du principe qu'à un département d'enseignement peut correspondre
   *  une catégorie spécifique).
   */
  function modify($code_uv, $intitule, $obj, $prog, $c, $td, $tp, $ects, $depts, $uv_cat = null)
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
                                "id_dept" => $dept,
                                "uv_cat" => $uv_cat[$dept]));
    }

    $this->reload_depts();

    return;
  }
  


  function create ($code_uv, $intitule, $c, $td, $tp, $ects, $depts, $uv_cat)
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
              "id_dept" => $dept,
              "uv_cat" => $uv_cat[$dept]));
    }
    
    return true;
  }
  

  function load_comments($admin = false)
  {
    if (!$this->id)
      return false;

    $this->comments = array();

    $sql = 'SELECT '.
           '`id_comment` '.
           'FROM '.
           '`edu_uv_comments` '.
           'WHERE '.
           '`id_uv` = '.$this->id;
    if ($all == false)
      $sql .= " AND state_comment IN (0, 1, 3)";

    $sql .= " ORDER BY date_commentaire ASC";
      
    $rq = new requete($this->db,$sql);


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
  
  function load_folder()
  {
    if ($this->idfolder == null)
      return false;

    global $topdir;
    require_once($topdir. "include/entities/folder.inc.php");
    
    $this->folder = new dfolder($this->db, $this->dbrw);

    $this->folder->load_by_id($this->idfolder);

    return true;
  }

  /* fonction vérifiant qu'un répertoire est bien
   * dans l'arborescence relative aux UVs.
   */
  function check_folder($id_folder)
  {
    $id_folder = intval($id_folder);

    while (true)
    {
      $req = new requete($this->db, "SELECT ".
                                    "`id_folder_parent` ".
                                    "FROM ".
                                    "`d_folder` ".
                                    "WHERE ".
                                    "`id_folder` = $id_folder ".
                                    "LIMIT 1");
      if ($req->lines < 0)
        return false;

      $row = $req->get_row();

      /* arrivé en haut de l'arbo */
      if ($row['id_folder_parent'] == null)
        return false;

      /* c'est bon. */
      if ($row['id_folder_parent'] == $this->idfolder)
        return true;
      /* sinon on boucle */
      $id_folder = intval($row['id_folder_parent']);
    }
  }

  function create_folder()
  {
    global $topdir;
    require_once($topdir. "include/entities/folder.inc.php");
    
    // non chargé
    if (!$this->is_valid())
      return false;
    
    // le répertoire existe deja
    if (!is_null($this->idfolder))
      return false;

    $parent = new dfolder($this->db, $this->dbrw);
    $parent->create_or_load("pédagogie");
    
    $newfold = new dfolder($this->db, $this->dbrw);
    $newfold->id_groupe_admin = 7; 
    $newfold->id_groupe = 7; 
    $newfold->droits_acces = 0xDDD;
    $newfold->id_utilisateur = null;
    $newfold->add_folder ( $this->code, $parent->id, "Fichiers relatifs à l'UV ".$this->code, null );

    $newfold->set_modere(true);

    new update($this->dbrw, 
             'edu_uv',
             array('id_folder' => $newfold->id),
             array('id_uv' => $this->id));
  
    $this->idfolder = $newfold->id;

    /* chargement du dossier */
    $this->folder = $newfold;

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
  var $semestre_obtention;
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
    $req = new requete($this->db, "SELECT * ".
                                  "FROM ".
                                  "`edu_uv_comments` ".
                                  "WHERE ". 
                                  "`id_comment` = '".
                                  mysql_real_escape_string($id).
                                  "' ".
                                  "LIMIT 1");
    
    if ($req->lines == 1)
    {
      $row = $req->get_row();

      $this->id              = $row['id_comment'];
 
      $this->id_uv           = $row['id_uv'];
      $this->id_commentateur = $row['id_utilisateur'];
  
      $req2 = new requete($this->db, "SELECT * FROM `edu_uv_obtention` ".
                                     "WHERE `id_utilisateur` = ".
                                     intval($this->id_commentateur).
                                     " AND `id_uv` = ".
                                     intval($this->id_uv).
                                     " LIMIT 1");
      /* Note : TODO pour les gens qui ont redoublé,
       * on fait quoi ? l'utilisation des semestres
       * n'est pas optimal pour "trier" en SQL
       */
      if ($req2->lines == 1)
      {
        $row2 = $req2->get_row();
        $this->note_obtention      = $row2['note_obtention'];
        $this->semestre_obtention  = $row2['semestre_obtention'];
      }

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
    return false;  
  }

  function modify($commentaire,
                  $note_obtention = null,
                  $semestre_obtention,
                  $interet = 3,
                  $utilite = 3,
                  $note    = 3,
                  $travail = 3,
                  $qualite = 3)
  {

    /* Champ `edu_uv_comment`.`note_obtention_uv` DEPRECIE ! */
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
    

    $sql2 = new update($this->dbrw,
           'edu_uv_obtention',
           array('note_obtention' => $note_obtention,
           'semestre_obtention' => $semestre_obtention),
           array('id_uv' => $this->id_uv,
           'id_utilisateur' => $this->id_commentateur));


    return ($sql->lines == 1);
  }

  function create($id_uv,
                  $id_commentateur,
                  $commentaire,
                  $note_obtention = null,
                  $semestre_obtention,
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

    $sql2 = new insert($this->dbrw,
           'edu_uv_obtention',
           array ('id_uv' => $id_uv,
            'id_utilisateur' => $id_commentateur,
            'note_obtention' => $note_obtention,
            'semestre_obtention' => $semestre_obtention));
           
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

function add_result_uv($id_etu, $id_uv, $note, $semestre, $dbrw)
{
  if (strlen($semestre) != 3)
    return false;

  if (($semestre[0] != 'A') && ($semestre[0] != 'P'))
    return false;

  $req = new insert($dbrw, "edu_uv_obtention",
        array("id_uv" => $id_uv,
        "id_utilisateur" => $id_etu,
        "note_obtention" => $note,
        "semestre_obtention" => strtoupper($semestre)));
  return ($req->lines == 1);
}

function delete_result_uv($id_etu, $id_uv, $semestre, $dbrw)
{
  $req = new delete($dbrw, "edu_uv_obtention",
        array("id_utilisateur" => $id_etu,
        "id_uv" => $id_uv,
        "semestre_obtention" => $semestre));

  return ($req->lines == 1);

  
}

function get_creds_cts(&$etu, $db, $camembert = false)
{
  global $topdir;
  require_once($topdir . "include/cts/sqltable.inc.php");

  $req = new requete($db, "SELECT ".
                          "`edu_uv`.`id_uv`".
                          ", `edu_uv`.`code_uv`".
                          ", `edu_uv`.`intitule_uv`".
                          ", `edu_uv`.`ects_uv`".
                          ", `edu_uv_dept`.`id_dept`".
                          ", `edu_uv_dept`.`uv_cat`".
                          ", `edu_uv_obtention`.`note_obtention`".
                          ", `edu_uv_obtention`.`semestre_obtention`".
                          ", `edu_uv_comments`.`id_comment`".
                          " FROM".
                          " `edu_uv`".
                          " INNER JOIN".
                          " `edu_uv_obtention`".
                          " USING (`id_uv`)".
                          " INNER JOIN".
                          " `edu_uv_dept`".
                          " USING (`id_uv`)".
                          " LEFT JOIN `edu_uv_comments`".
                          " USING ( `id_uv`, `id_utilisateur` )".
                          " WHERE".
                          " `edu_uv_obtention`.`id_utilisateur` = ".
                          $etu->id .
                          " GROUP BY".
                          " `id_uv`, `semestre_obtention`".
                          " ORDER BY".
                          " `semestre_obtention`");

  
  $cts = new contents("Détails des crédits obtenus");


  if ($req->lines > 0)
  {
    $totcreds = 0;
    $statsobs = array();
    $totuvs = 0;
    /* on découpe par semestre */
    while ($rs = $req->get_row())
    {
      $totsuvs++;
    
      if ($rs['uv_cat'] != null)
        $stats_by_cat[$rs['uv_cat']][] = $rs;
      else
        $stats_by_cat['NA'][] = $rs;

      $stats_by_sem[$rs['semestre_obtention']][] = $rs;

      $statsobs[$rs['note_obtention']] ++;
      ksort($statsobs, SORT_STRING);
    
      if (($rs['note_obtention'] != 'F') && ($rs['note_obtention'] != 'Fx'))
        $totcreds += $rs['ects_uv'];
    }

    /* on trie */
    if (count($stats_by_sem) > 0)
    {
      foreach ($stats_by_sem as $key => $uvsemestre)
      {
        // On récupère l'info sur le semestre (printemps ou automne, A ou P)
        $ap = substr($key, 0, 1);
        // On récupère l'année sur 2 chiffres
        $annee = substr($key, 1, 2);
        
        /* semestre d'automne, on regarde s'il n'y a pas un semestre de printemps avant de dispo */
        if ($ap == 'A')
        {
          // il existe un semestre de printemps pour la meme année
          if (isset($stats_by_sem['P' . $annee]))
          {
            // on le place dans la liste
            $stats_by_sem_sorted['P' . $annee] = $stats_by_sem['P' . $annee];
          }
          // dans tous les cas, on ajoute le semestre d'automne
          $stats_by_sem_sorted["A" . $annee] = $uvsemestre;
        }
        /* Si c'est un semestre de printemps, on l'ajoute à la liste, et au suivant ! */
        else
        $stats_by_sem_sorted['P' . $annee] = $uvsemestre;
      }
    }

    // affichage anti-chronologique mais fleme d'essayer de comprendre la fontion de tri
    if (count($stats_by_sem_sorted) > 0)
      $stats_by_sem_sorted = array_reverse($stats_by_sem_sorted); 
  
    $first = 0;
    if (count($stats_by_sem_sorted) > 0)
    {
      foreach ($stats_by_sem_sorted as $key => $semestre)
      {
        $ap = substr($key, 0, 1);
        $annee = substr($key, 1, 2);
        if ($ap == "A")
          $sm = "d'Automne ";
        else
          $sm = "de Printemps ";
        
        $sm .= $annee;
        
        //$cts->add_title(3, "Semestre " . $sm);
        $table = new sqltable('details_uv', "Semestre " . $sm, $semestre, "./index.php?semestre=$key",
                              "id_uv",
                              array("code_uv" => "Code de l'UV", 
                                    "intitule_uv" => "Intitulé de l'UV",
                                    "uv_cat"      => "Catégorie de l'UV",
                                    "note_obtention"=> "Note d'obtention",
                                    "ects_uv"     => "Crédits ECTS"),
                              array ("delete" => "Enlever"),
                              array());
             
       $cts->add($table, true, false, "res_".$ap.$annee, false, true, !($first++), true);
      }
    }
    if ($totcreds > 0)
    {
      $cts->add_title(2, "Récapitulatif");
      $cts->add_paragraph("<b>".$totcreds . 
            " crédits ECTS</b> obtenus au long de votre scolarité.");
      if ($etu->a_fait_tc())
      {
        $a_fait_tc = true;
        $rem = 240 - $totcreds;

        $cts->add_paragraph("Ayant fait le TC, il vous faut <b>240 crédits</b> (art. V-3 du réglement ".
          "des études) pour achever votre cursus.");
        if ($rem > 0)
          $cts->add_paragraph("Il vous manque <b>". $rem . " crédits</b>");
        else
          $cts->add_paragraph("Vous disposez d'un surplus de <b>". abs($rem) . " crédits</b>");

      }
      else if ($etu->departement != 'tc')
      {
        $cts->add_paragraph("Etant entré en branche, il faut <b>120 crédits</b> (art. V-3 du réglement ".
          "des études) pour achever votre cursus.");

        $rem = 120 - $totcreds;
        if ($rem > 0)
          $cts->add_paragraph("Il vous manque <b>". $rem . " crédits</b>");
        else
          $cts->add_paragraph("Vous avez un surplus de <b>". abs($rem) . " crédits</b>");
      }

      /* étudiant de TC */
      else
      {
        $cts->add_paragraph("Vous êtes en TC. Il vous faut par conséquent <b>102 crédits</b> en 3 ou 4 ".
                            "semestres, ou bien <b>120 crédits</b> en cas de semestre(s) supplémentaire(s).");
        
        if ($etu->semestre > 4)
        {
          $rem = 120 - $totcreds;
          if ($rem > 0)
            $cts->add_paragraph("Il vous manque <b>" . $rem . " crédits</b> pour pouvoir entrer en branche.");
          else
            $cts->add_paragraph("Vous avez un surplus de <b>" . abs($rem) . " crédits</b>.");
        }
        else
        {
          $rem = 102 - $totcreds;
          if ($rem > 0)
            $cts->add_paragraph("Il vous manque <b>" . $rem . " crédits</b> pour pouvoir entrer en branche.");
          else
            $cts->add_paragraph("Vous êtes en surplus de <b>" . $rem . " crédits</b>.");
        } 
      }
      /* statistiques par catégories */
      if (count($stats_by_cat) > 0)
      {
        $cts->add_title(2, "Statistiques par catégories d'UVs");

        global $uv_descr_cat;

        foreach ($stats_by_cat as $key => $array)
        {
          $cts->add_title(3, "Catégorie " . $uv_descr_cat[$key]);
          $cts->add(new sqltable('details_uv', "", $array, "./index.php?semestre=$key",
                                 "id_uv",
                                 array("code_uv" => "Code de l'UV", 
                                       "intitule_uv" => "Intitulé de l'UV",
                                       "uv_cat"      => "Catégorie de l'UV",
                                       "note_obtention"=> "Note d'obtention",
                                       "ects_uv"     => "Crédits ECTS"),
                                       array ("delete" => "Enlever"),
                                       array()));
          $totcreds_by_cat = 0;
          $totcreds_by_cat_tc = 0;

          foreach ($array as $uv)
          {
            if (($uv['note_obtention'] == 'F') || ($uv['note_obtention'] == 'Fx'))
              continue;

            $totcreds_by_cat += $uv['ects_uv'];
    
            if ($uv['id_dept'] == 'TC')
              $totcreds_by_cat_tc += $uv['ects_uv']; 
          }
          $cts->add_paragraph("Soit un total de <b>".$totcreds_by_cat." crédits ECTS</b> dans cette catégorie.");
          if ($a_fait_tc)
          {
            if (($key == 'CS') || ($key == 'TM'))
            {
              $cts->add_paragraph("Vous avez fait le TC. Il vous faut au moins <b>30 crédits</b> dans cette ".
                                  "catégorie, obtenus via des UVs de branche");
              $cts->add_paragraph("<b>" . ($totcreds_by_cat - $totcreds_by_cat_tc) . " crédits</b> obtenus.");
            }
            else if ($key == 'CG')
            {
              $cts->add_paragraph("Vous avez fait le TC. Il vous faut au moins <b>32 crédits</b> dans cette ".
                                  "catégorie.");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
            else if ($key == 'EC')
            {
              $cts->add_paragraph("Vous avez fait le TC. Il vous faut au moins <b>20 crédits</b> dans cette ".
                                 "catégorie.");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
          } // fin étudiant ayant fait TC
          else if ($etu->departement != 'tc') // etudiant de branche sans TC
          {
            if (($key == 'CS') || ($key == 'TM'))
            {
              $cts->add_paragraph("Il vous faut au moins <b>30 crédits</b> dans cette ".
                                  "catégorie");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
            else if ($key == 'CG')
            {
              $cts->add_paragraph("Il vous faut au moins <b>16 crédits</b> dans cette ".
                                  "catégorie.");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
            else if ($key == 'EC')
            {
              $cts->add_paragraph("Il vous faut au moins <b>12 crédits</b> dans cette ".
                                  "catégorie.");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }

          } // fin étudiants branche sans TC
          else // etudiant TC
          {
            if ($key == 'CS')
            {
              $cts->add_paragraph("Il vous faut au moins <b>48 crédits</b> dans cette ".
                                  "catégorie");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
            else if ($key == 'TM')
            {
              $cts->add_paragraph("Il vous faut au moins <b>24 crédits</b> dans cette ".
                                  "catégorie");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus.");
            }
            else if (($key == 'CG') || ($key == 'EC'))
            {
              $cts->add_paragraph("Il vous faut au moins <b>24 crédits</b> dans les deux ".
                                  "catégories de culture générale (CG et EC).");
              $cts->add_paragraph("<b>" . $totcreds_by_cat . " crédits</b> obtenus dans cette catégorie.");
            }
          }
        }
      }
    } // todcreds > 0

    if ( $camembert == true) 
    {
      global $topdir;
      require_once($topdir . "include/graph.inc.php");
      
      if (count($statsobs) > 0)
      {
        $cam = new camembert(600,400,array(),2,0,0,0,0,0,0,10,150);
        foreach ($statsobs as $key => $nbuvobt)
        {
          $cam->data($nbuvobt, $key);
        }
      }
      else
        $cam = new camembert(10,10,array(),2,0,0,0,0,0,0,0,0);
      return $cam;
    }
  }
  elseif( $camembert == true )
    return new camembert(10,10,array(),2,0,0,0,0,0,0,0,0);

  $cts->add_paragraph("<br/>");
  
  return $cts;
}

function get_uvsmenu_box()
{
  global $departements;
  global $site;
  
  $cts = new contents("Pédagogie");
  $dpt = new itemlist("<a href=\"uvs.php\" title=\"Toutes les UV\">Accéder aux UV</a>");

  foreach ($departements as $dpt_key)
    $dpt->add("<a href=\"uvs.php?iddept=".$dpt_key."\">".$dpt_key."</a>");
  
  
  $cts->add($dpt, true);
  
  $outils = new itemlist("Outils", false, array("<a href=\"edt.php\" title=\"Gérer vos emploi du temps\">Emploi du temps</a>",
                                                "<a href=\"profils.php\" title=\"Toutes les UV\">Profils</a>"));
  $cts->add($outils, true);
  
  if( $site->user->is_in_group("etudiants-utbm-actuels") )
  {
    $sql = new requete($site->db, "SELECT id_uv, id_comment, code_uv, surnom_utbm
                                    FROM edu_uv_comments
                                    NATURAL JOIN edu_uv
                                    NATURAL JOIN utl_etu_utbm
                                    ORDER BY date_commentaire DESC
                                    LIMIT 5 
                                    ");

    $avis = new itemlist("Les derniers commentaires");
    
    while( $row = $sql->get_row() )
      $avis->add("<a href=\"uvs.php?view=commentaires&id_uv=".$row['id_uv']."#cmt_".$row['id_comment']."\">".$row['code_uv']."  par ".$row['surnom_utbm']."</a>");
    
    $cts->add($avis, true);
  }
  
  return $cts;
}


?>
