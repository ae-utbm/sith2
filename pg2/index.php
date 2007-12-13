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
require_once($topdir."include/entities/pgfiche.inc.php");
require_once($topdir."include/cts/board.inc.php");
require_once($topdir."include/cts/pg.inc.php");

$site = new pgsite();

$fiche = new pgfiche($site->db,$site->dbrw);
$category = new pgcategory($site->db,$site->dbrw);

if ( isset($_REQUEST["id_pgfiche"]) )
{
  if ( $fiche->load_by_id($_REQUEST["id_pgfiche"]) )
    $category->load_by_id($fiche->id_pgcategory);
}
elseif ( isset($_REQUEST["id_pgcategory"]) )
  $category->load_by_id($_REQUEST["id_pgcategory"]);


if ( $category->is_valid() )
{
  $title_path = $category->nom;
  
  if ( $category->id_pgcategory_parent == 1 )
  {
    $id_pgcategory1 = $category->id;
    $path = "&nbsp;";   
  }
  else
  {
    $path = $category->get_html_link();
    $parent = new pgcategory($site->db);
    $parent->id_pgcategory_parent = $category->id_pgcategory_parent;
    
    while ( !is_null($parent->id_pgcategory_parent)
            && $parent->id_pgcategory_parent != 1
            && $parent->load_by_id($parent->id_pgcategory_parent) )
    {
      if ( $parent->id_pgcategory_parent == 1 )
        $id_pgcategory1 = $parent->id;
      else
        $path = $parent->get_html_link()." / ".$path;
        
      $title_path = $parent->nom." / ".$title_path;        
    }
  }
  $title_path = "Petit géni / ".$title_path;
}

if ( $fiche->is_valid() )
{
  $site->add_alternate_geopoint($fiche);
  $site->set_meta_information($fiche->get_tags(),$fiche->description);
  
  $path .= " / ".$fiche->get_html_link();
  $title_path .= " / ".$fiche->nom;
  $site->start_page("pg",$title_path);
  $cts = new contents("<a href=\"index.php\">Le Guide</a>");
  $cts->add(new pgtabshead($site->db,$id_pgcategory1));
  $cts->add_paragraph($path);
  
  $site->add_contents($cts);
  $site->end_page();
  exit(); 
}
elseif ( $category->is_valid() && $category->id != 1 )
{
  $site->set_meta_information($category->get_tags(),$category->description);
  $site->start_page("pg",$title_path);
  $cts = new contents("<a href=\"index.php\">Le Guide</a>");
  
  $cts->add(new pgtabshead($site->db,$id_pgcategory1));
  $cts->add_paragraph($path);
  
  $req = new requete($site->db,
    "SELECT id_pgcategory, nom_pgcategory ".
    "FROM pg_category ".
    "WHERE id_pgcategory_parent='".mysql_real_escape_string($category->id)."' ".
    "ORDER BY ordre_pgcategory, nom_pgcategory");
    
  if ( $req->lines > 0 )
  {
    $sscts = new pgcatlist($category->couleur_bordure_web);
    while ( $row = $req->get_row() )
      $sscts->add($row["id_pgcategory"],$row["nom_pgcategory"]);
    $cts->add($sscts);
  }
  
  $site->add_contents($cts);
  $site->end_page();
  exit(); 
}

$site->start_page("pg","Petit Géni 2.0");
$cts = new board("Bienvenue");

$scts = new contents("Le Guide");
$req = new requete($site->db,
  "SELECT cat1.id_pgcategory AS id, cat1.nom_pgcategory AS nom, cat1.couleur_bordure_web_pgcategory AS couleur, ".
  "cat2.id_pgcategory AS id2, cat2.nom_pgcategory AS nom2 ".
  "FROM pg_category AS cat1 ".
  "LEFT JOIN pg_category AS cat2 ON (cat1.id_pgcategory=cat2.id_pgcategory_parent) ".
  "WHERE cat1.id_pgcategory_parent='1' ".
  "ORDER BY cat1.ordre_pgcategory, cat2.ordre_pgcategory, cat2.nom_pgcategory");

$prev_cat=null;
$sscts=null;

while ( $row = $req->get_row() )
{
  if ( $prev_cat != $row["id"] )
  {
    if ( !is_null($sscts) )
      $scts->add($sscts);
    $sscts = new pgcatminilist($row["id"],$row["nom"],$row["couleur"]);
    $prev_cat = $row["id"];
  }
  $sscts->add($row["id2"],$row["nom2"]);
}

if ( !is_null($sscts) )
  $scts->add($sscts);

$cts->add($scts,true);

$scts = new contents("Rechercher");
$cts->add($scts,true);

$scts = new contents("Agenda");
$cts->add($scts,true);

$scts = new contents("Bons plans");
$cts->add($scts,true);

$scts = new contents("Le Petit Géni");
$cts->add($scts,true);

$site->add_contents($cts);
$site->end_page();

?>