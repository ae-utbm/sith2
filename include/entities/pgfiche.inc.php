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
  
  
}


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
  
  var $date_maj;
  var $date_validite;
  
  function add_arretbus ( $id_arretbus )
  {
    
  }
  
  function add_tarif ( $id_typetarif, $min_tarif, $max_tarif, $date_maj=null, $date_validite=null )
  {
    
  }
  
  function add_reduction ( $id_typereduction, $valeur, $unite, $date_maj=null, $date_validite=null )
  {
    
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
  var $tags; // (données sérialisés)

  var $date_soumission;
  var $id_utilisateur;
  var $commentaire;

}
 
 
 
 
?>