<?php
/**
 * @file
 */
 
require_once($topdir."include/entities/basedb.inc.php");
require_once($topdir."include/entities/wiki.inc.php");

/** Classe gérant les pages "wiki" 
 */
class page extends basedb
{
  var $nom;
  var $texte;
  var $date;
  var $titre;
  var $section;

  
  /** Charge une page en fonction de son id
   * $this->id est égal à -1 en cas d'erreur
   * @param $id Id de la page
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `pages`
        WHERE `id_page` = '" . mysql_real_escape_string($id) . "'
        LIMIT 1");  
        
    if ( $req->lines == 1 )
    {
      $this->_load($req->get_row());
      return true;
    }
    
    $this->id = null;  
    return false;
  }
  
  /** Charge une page en fonction de son nom
   * $this->id est égal à -1 en cas d'erreur
   * @param $name Nom de la page
   */
  function load_by_pagename ( $name )
  {
    $req = new requete($this->db, "SELECT * FROM `pages`
        WHERE `nom_page` = '" . mysql_real_escape_string($name) . "'
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
    $this->id      = $row['id_page'];
    $this->nom      = $row['nom_page'];
    $this->texte      = $row['texte_page'];
    $this->date      = strtotime($row['date_page']);
    $this->titre      = $row['titre_page'];
    $this->section    = $row['section_page'];
    
    $this->id_utilisateur = $row['id_utilisateur'];
    $this->id_groupe = $row['id_groupe_modal'];
    $this->id_groupe_admin = $row['id_groupe'];
    $this->droits_acces = $row['droits_acces_page'];
    $this->modere = $row['modere_page'];
    
  }
  
  /** Génére un stdcontents avec le contenu de la page
   */
  function get_contents ( )
  {
    $cts = new wikicontents($this->titre,$this->texte);
    $cts->puts("<div class=\"clearboth\"></div>");
    return $cts;
  }
  
  /** Modifie la page actuelle
   * @param $id_utilisateur Id de l'utilisateur qui a modifié la page
   * @param $titre Titre de la page
   * @param $texte Contenu de la page
   */
  function save ( &$user, $titre, $texte, $section )
  {
    $this->texte = $texte;
    $this->titre = $titre;      
    $this->date = time();  
    $this->section = $section;
    $this->modere = true;
    $sql = new update ($this->dbrw,
      "pages",
      array(
        "texte_page" => $this->texte,
        "date_page" => date("Y-m-d H:i:s"),
        "titre_page" => $this->titre,
        "section_page"=>$this->section,
        
        "id_utilisateur"=>$this->id_utilisateur,
        "id_groupe_modal"=>$this->id_groupe,
        "id_groupe"=>$this->id_groupe_admin,
        "droits_acces_page"=>$this->droits_acces,
        "modere_page"=>$this->modere
        ),
      array(
        "id_page" => $this->id
        )
      );
    return true;
  }
  
  /** Ajoute une nouvelle page.
   * @param $nom Nom de la page
   * @param $titre Titre de la page
   * @param $texte Contenu de la page
   */
  function add ( &$user, $nom, $titre, $texte, $section )
  {
  
    $this->nom = $nom;
    $this->texte = $texte;
    $this->titre = $titre;    
    $this->date = time();    
    $this->section = $section;
    $this->modere = true;
    
    $sql = new insert ($this->dbrw,
      "pages",
      array(
        "nom_page" => $this->nom,
        "texte_page" => $this->texte,
        "date_page" => date("Y-m-d H:i:s"),
        "titre_page" => $this->titre,
        "section_page"=>$this->section,
        
        "id_utilisateur"=>$this->id_utilisateur,
        "id_groupe_modal"=>$this->id_groupe,
        "id_groupe"=>$this->id_groupe_admin,
        "droits_acces_page"=>$this->droits_acces,
        "modere_page"=>$this->modere
        
        )
      );
        
    if ( $sql )
      $this->id = $sql->get_id();
    else
      $this->id = null;
      
    return true;
  }

  function del ()
  {
    $req = new delete($this->dbrw, "pages", array("nom_page"=>$this->nom));
  }
  
  function is_category()
  {
    return false;  
  }
  
  function is_admin ( &$user )
  {
    if ( $user->is_in_group("moderateur_site") ) return true;
    if ( $user->is_in_group_id($this->id_groupe_admin) ) return true;
    return false;
  }
  
} 
 
/**
 * Remplacera à court terme la classe page. Ceci dans l'objectif de fusionner
 * le wiki et les pages/articles.
 */
class page_wikized extends wiki
{
  var $nom;
  var $texte;
  var $date;
  var $titre;
  var $section;

  function translate_pagename ( $name )
  {
    $name = preg_replace("/[^a-z0-9\-_:#]/","_",strtolower(utf8_enleve_accents($name)));
    return "articles:".$name;
  }

  function load_by_pagename ( $name )
  {
    return $this->load_by_fullpath($this->translate_pagename($name));
  }
  
  function _load ( $row )
  {
    parent::_load($row);
    
    $this->nom = substr($this->fullpath,9);
    $this->texte = $this->rev_contents;
    $this->date = $this->rev_date;
    $this->titre = $this->rev_title;
    //$this->section = $this->section;
  }

  function get_contents ( )
  {
    return $this->get_stdcontents();
  }

  function save ( &$user, $titre, $texte, $section )
  {
    if ( $this->is_locked($user) )
      return false;
    
    $this->section = $section;     
    $this->update();
    
    $this->revision ( $user->id, $titre, $texte, "Edité comme un article" );
    
    return true;
  }

  function add ( &$user, $nom, $titre, $texte, $section )
  {
    $path = $this->translate_pagename($nom);
    
    $parent = new wiki($this->db,$this->dbrw);
    
    $pagename = $parent->load_or_create_parent($path, $user, $this->droits_acces, $this->id_groupe, $this->id_groupe_admin);
        
    if ( is_null($pagename) || !$parent->is_valid() || $this->load_by_name($parent,$pagename) )
      return false;
      
    $this->create ( $parent, null, $pagename, 0, $titre, $texte, "Créée comme un article", $section );        
    
    return true;
  }

  function is_admin ( &$user )
  {
    if ( $user->is_in_group("moderateur_site") ) return true;
    return parent::is_admin($user);
  }
  
} 
 
?>
