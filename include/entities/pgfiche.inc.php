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