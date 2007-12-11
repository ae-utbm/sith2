<?php
/* Copyright 2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des Etudiants de
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
 
$topdir="../";
require_once("include/site.inc.php");
require_once($topdir."include/entities/bus.inc.php");
require_once($topdir."include/entities/ville.inc.php");

$site = new pgsite();

$reseaubus = new reseaubus($site->db,$site->dbrw);
$lignebus = new lignebus($site->db,$site->dbrw);
$arretbus = new arretbus($site->db,$site->dbrw);
$ville = new ville($site->db);

if ( isset($_REQUEST["id_arretbus"]) )
  $arretbus->load_by_id($_REQUEST["id_arretbus"]);
elseif ( isset($_REQUEST["id_geopoint"]) )
  $arretbus->load_by_id($_REQUEST["id_geopoint"]);

if ( isset($_REQUEST["id_lignebus"]) )
{
  if ( $lignebus->load_by_id($_REQUEST["id_lignebus"]) )
    $reseaubus->load_by_id($lignebus->id_reseaubus);
}
elseif ( isset($_REQUEST["id_reseaubus"]) )
  $reseaubus->load_by_id($_REQUEST["id_reseaubus"]);
  
if ( $site->is_admin() && isset($_REQUEST["action"]) )
{
  if ( $_REQUEST["action"] == "createreseaubus" ) 
  {
    $reseaubusparent = new reseaubus($site->db);
    $reseaubusparent->load_by_id($_REQUEST["id_reseaubus_parent"]);
    $reseaubus->create ( $_REQUEST["nom"], $_REQUEST["siteweb"], $reseaubusparent->id );
  }
  elseif ( $_REQUEST["action"] == "createarretbus" ) 
  {
    $ville->load_by_id($_REQUEST["id_ville"]);
    $arretbus->create ( $ville->id,  $_REQUEST["nom"], $_REQUEST["lat"], $_REQUEST["long"], $_REQUEST["eloi"] )
  }  
  
  
}

// Gènère le chemin affiché
if ( $reseaubus->is_valid() )
{
  $path = $reseaubus->get_html_link();

  $reseaubusparent = new reseaubus($site->db);
  $reseaubusparent->id_reseaubus_parent = $reseaubus->id_reseaubus_parent;
  
  while ( !is_null($reseaubusparent->id_reseaubus_parent) 
    && $reseaubusparent->load_by_id($reseaubusparent->id_reseaubus_parent) )
    $path = $reseaubusparent->get_html_link()." / ".$path;

  $path = "<a href=\"bus.php\">Reseaux de bus</a> / ".$path;
}
else
  $path = "<a href=\"bus.php\">Reseaux de bus</a>";

// Affichage
if ( $arretbus->is_valid() )
{
  $path .= " / ".$arretbus->get_html_link();
  
  $site->start_page("pgbus","Reseaux de bus");
  $cts = new contents($path);
  
  
  
  $site->add_contents($cts);
  $site->end_page(); 
  exit();  
} 
elseif ( $lignebus->is_valid() ) 
{
  $path .= " / ".$lignebus->get_html_link();
  $site->start_page("pgbus","Reseaux de bus");
  $cts = new contents($path);
  
  
  
  $site->add_contents($cts);
  $site->end_page(); 
  exit();  
}
elseif ( $reseaubus->is_valid() ) 
{
  $site->start_page("pgbus","Reseaux de bus");
  $cts = new contents($path);
  
  
  
  $site->add_contents($cts);
  $site->end_page(); 
  exit();  
}

$site->start_page("pgbus","Reseaux de bus");
  
$cts = new contents($path);

$req = new requete($site->db,"SELECT * FROM pg_reseaubus WHERE id_reseaubus_parent IS NULL ORDER BY nom_reseaubus");
$list = new itemlist("Reseaux de bus");
while ( $row = $req->get_row() )
{
  $reseaubus->_load($row);
  $list->add($reseaubus->get_html_link());
}
$cts->add($list,true);

if ( $site->is_admin() )
{
  $reseaubus->id=null;
  $frm = new form("createreseaubus","bus.php",false,"POST","Ajouter un reseau de bus");
  $frm->add_hidden("action","createreseaubus");
  $frm->add_text_field("nom","Nom du réseau");
  $frm->add_text_field("siteweb","Site web","http://");
  $frm->add_entity_smartselect("id_reseaubus_parent","Reseau parent",$reseaubus,true);
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);
  
  $frm = new form("createarretbus","bus.php",false,"POST","Ajouter un arret de bus");
  $frm->add_hidden("action","createarretbus");
  $frm->add_text_field("nom","Nom");
  $frm->add_entity_smartselect("id_ville","Ville",$ville);
  $frm->add_geo_field("lat","Latitude","lat");
  $frm->add_geo_field("long","Longitude","long");
  $frm->add_text_field("eloi","Eloignement");
  $frm->add_submit("valid","Ajouter");
  $cts->add($frm,true);  
  
}

$site->add_contents($cts);
$site->end_page();

?>