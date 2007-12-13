<?php
/* Copyright 2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des 0tudiants de
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
 * @file
 */
 

require_once($topdir."include/entities/geopoint.inc.php");

class pgcategory extends stdentity
{
  var $id_pgcategory_parent; 
  var $nom;
  var $description;
  var $ordre;
  
  var $couleur_bordure_web;
  var $couleur_titre_web;
  var $couleur_contraste_web;
  
  var $couleur_bordure_print;
  var $couleur_titre_print;
  var $couleur_contraste_print;
  
  
  
  /**
   * Charge un élément par son id
   * @param $id Id de l'élément
   * @return false si non trouvé, true si chargé
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * 
        FROM `pg_category`
				WHERE `id_pgcategory` = '".mysql_real_escape_string($id)."'
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
    $this->id = $row['id_pgcategory'];
    
    $this->id_pgcategory_parent = $row['id_pgcategory_parent'];
    $this->nom = $row['nom_pgcategory'];
    $this->description = $row['description_pgcategory'];
    $this->ordre = $row['ordre_pgcategory'];
  
    $this->couleur_bordure_web = $row['couleur_bordure_web_pgcategory'];
    $this->couleur_titre_web = $row['couleur_titre_web_pgcategory'];
    $this->couleur_contraste_web = $row['couleur_contraste_web_pgcategory'];
  
    $this->couleur_bordure_print = $row['couleur_bordure_print_pgcategory'];
    $this->couleur_titre_print = $row['couleur_titre_print_pgcategory'];
    $this->couleur_contraste_print = $row['couleur_contraste_print_pgcategory'];   
  }
  
  function create ( $id_pgcategory_parent, $nom, $description, $ordre, $couleur_bordure_web, $couleur_titre_web,$couleur_contraste_web, $couleur_bordure_print, $couleur_titre_print, $couleur_contraste_print )
  {
    $this->id_pgcategory_parent = $id_pgcategory_parent;
    $this->nom = $nom;
    $this->description = $description;
    $this->ordre = $ordre;
  
    $this->couleur_bordure_web = $couleur_bordure_web;
    $this->couleur_titre_web = $couleur_titre_web;
    $this->couleur_contraste_web = $couleur_contraste_web;
  
    $this->couleur_bordure_print = $couleur_bordure_print;
    $this->couleur_titre_print = $couleur_titre_print;
    $this->couleur_contraste_print = $couleur_contraste_print;       
    
    $req = new insert ( $this->dbrw, "pg_category", 
      array( 
      "id_pgcategory_parent" => $this->id_pgcategory_parent,
      "nom_pgcategory" => $this->nom,
      "description_pgcategory" => $this->description,
      "ordre_pgcategory" => $this->ordre,

      "couleur_bordure_web_pgcategory" => $this->couleur_bordure_web,
      "couleur_titre_web_pgcategory" => $this->couleur_titre_web,
      "couleur_contraste_web_pgcategory" => $this->couleur_contraste_web,

      "couleur_bordure_print_pgcategory" => $this->couleur_bordure_print,
      "couleur_titre_print_pgcategory" => $this->couleur_titre_print,
      "couleur_contraste_print_pgcategory" => $this->couleur_contraste_print
      ) );
    
    if ( !$req->is_success() )
    {
      $this->id = null;
      return false;
    }
    
	  $this->id = $req->get_id();
    return true;
  }
  
  function update ( $id_pgcategory_parent, $nom, $description, $ordre, $couleur_bordure_web, $couleur_titre_web,$couleur_contraste_web, $couleur_bordure_print, $couleur_titre_print, $couleur_contraste_print )
  {
    $this->id_pgcategory_parent = $id_pgcategory_parent;
    $this->nom = $nom;
    $this->description = $description;
    $this->ordre = $ordre;
  
    $this->couleur_bordure_web = $couleur_bordure_web;
    $this->couleur_titre_web = $couleur_titre_web;
    $this->couleur_contraste_web = $couleur_contraste_web;
  
    $this->couleur_bordure_print = $couleur_bordure_print;
    $this->couleur_titre_print = $couleur_titre_print;
    $this->couleur_contraste_print = $couleur_contraste_print; 

    new update ( $this->dbrw, "pg_category", 
      array( 
      "id_pgcategory_parent" => $this->id_pgcategory_parent,
      "nom_pgcategory" => $this->nom,
      "description_pgcategory" => $this->description,
      "ordre_pgcategory" => $this->ordre,

      "couleur_bordure_web_pgcategory" => $this->couleur_bordure_web,
      "couleur_titre_web_pgcategory" => $this->couleur_titre_web,
      "couleur_contraste_web_pgcategory" => $this->couleur_contraste_web,

      "couleur_bordure_print_pgcategory" => $this->couleur_bordure_print,
      "couleur_titre_print_pgcategory" => $this->couleur_titre_print,
      "couleur_contraste_print_pgcategory" => $this->couleur_contraste_print
      ),
      array("id_pgcategory"=>$this->id) );
  }
  
  function delete ()
  {

  } 
  
  function get_html_link()
  {
    global $topdir,$wwwtopdir;

    require_once($topdir."include/cts/pg.inc.php");

    return "<a href=\"".$wwwtopdir."pg2/?id_pgcategory=".$this->id."\"><img src=\"".pgicon($this->couleur_bordure_web)."\" class=\"icon\" alt=\"\" /> ". htmlentities($this->get_display_name(),ENT_COMPAT,"UTF-8")."</a>;      
  }
  
  
  
}

if ( !defined("HR_DIMANCHE") )
{
  define("HR_DIMANCHE",   0x01);
  define("HR_LUNDI",      0x02);
  define("HR_MARDI",      0x04);
  define("HR_MERCREDI",   0x08);
  define("HR_JEUDI",      0x10);
  define("HR_VENDREDI",   0x20);
  define("HR_SAMEDI",     0x40);
}

define("HR_OUVERTURE",      0);
define("HR_EXCP_OUVERTURE", 1);
define("HR_EXCP_FERMETURE", 2);

class pgfiche extends geopoint
{
  var $id_pgcategory;
  var $id_rue;
  var $id_entreprise;
  
  var $description;
  var $tel;
  var $fax;
  var $email;
  var $website;
  var $numrue;
  var $adressepostal;
  
  var $placesurcarte;
  var $contraste;
  var $appreciation;
  var $commentaire;
  
  var $date_maj;
  var $date_validite;
  
  function add_arretbus ( $id_arretbus )
  {
    
  }
  
  function add_extra_pgcategory ( $id_pgcategory )
  {
    
  }
  
  function add_tarif ( $id_typetarif, $min_tarif, $max_tarif, $commentaire, $date_maj=null, $date_validite=null )
  {
    
  }
  
  function add_reduction ( $id_typereduction, $valeur, $unite, $commentaire, $date_maj=null, $date_validite=null )
  {
    
  }
  
  function add_service ( $id_service, $commentaire, $date_maj=null, $date_validite=null )
  {
    
  }
  
  function add_horraire ( $datevaldebut, $datevalfin, $type, $jours, $heuredebut, $heurefin )
  {
    
    $ouvert = 1;
    if ( $type == HR_EXCP_FERMETURE )
      $ouvert = -1;
    
    
  } 
  
  function integrate_update ( &$pgfichemaj )
  {
    
  }
  
  
} 

class pgfichemaj extends stdentity
{
  var $id_pgfiche;
  
  var $nom;
  var $id_ville;
  var $lat;
  var $long;
  var $eloi;
  
  var $id_pgcategory;
  var $id_rue;
  var $id_entreprise;
  
  var $description;
  var $tel;
  var $fax;
  var $email;
  var $website;
  var $numrue;
  var $adressepostal;
  
  var $date_validite;

  var $arretsbus; // (données sérialisés)
  var $tarifs; // (données sérialisés)
  var $reductions; // (données sérialisés)
  var $services; // (données sérialisés)
  var $tags; // (données sérialisés)

  var $date_soumission;
  var $id_utilisateur;
  var $commentaire;

}
 
 
 
 
?>