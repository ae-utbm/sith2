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
require_once($topdir."include/entities/rue.inc.php");
require_once($topdir."include/entities/ville.inc.php");
require_once($topdir."include/cts/board.inc.php");
require_once($topdir."include/cts/pg.inc.php");
require_once($topdir."include/cts/gmap.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");

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

if ( $category->is_valid() && $site->is_admin() && $_REQUEST["action"] == "createfiche" )
{
  $ent = new entreprise($site->db);
  $rue = new rue($site->db);
  $typerue = new typerue($site->db);
  $ville = new ville($site->db);
  
  $ent->load_by_id($_REQUEST["id_entreprise"]);
  $typerue->load_by_id($_REQUEST["id_typerue"]);  
  $ville->load_by_id($_REQUEST["id_ville"]);  
  
  $rue->load_or_create ( $typerue->id, $ville->id, $_REQUEST["nom_rue"] );
  
  $fiche->create ( 
    $ville->id, $_REQUEST["nom"], $_REQUEST["lat"], $_REQUEST["long"], 0, 
    $category->id, $rue->id, $ent->id, $_REQUEST["description"], 
    $_REQUEST["longuedescription"], $_REQUEST["tel"], $_REQUEST["fax"], 
    $_REQUEST["email"], $_REQUEST["website"], $_REQUEST["numrue"], 
    $_REQUEST["adressepostal"], isset($_REQUEST["placesurcarte"]), 
    isset($_REQUEST["contraste"]), null, null, $_REQUEST["infointerne"], 
    time(), $_REQUEST["date_validite"], $site->user->id );
}
  
if ( $fiche->is_valid() )
{
  if ( $site->is_admin() && $_REQUEST["action"] == "save" )
  {
    $ent = new entreprise($site->db);
    $rue = new rue($site->db);
    $typerue = new typerue($site->db);
    $ville = new ville($site->db);
    
    $ent->load_by_id($_REQUEST["id_entreprise"]);
    $typerue->load_by_id($_REQUEST["id_typerue"]);  
    $ville->load_by_id($_REQUEST["id_ville"]);  
    
    $rue->load_or_create ( $typerue->id, $ville->id, $_REQUEST["nom_rue"] );
    
    $fiche->update ( 
      $ville->id, $_REQUEST["nom"], $_REQUEST["lat"], $_REQUEST["long"], 0, 
      $category->id, $rue->id, $ent->id, $_REQUEST["description"], 
      $_REQUEST["longuedescription"], $_REQUEST["tel"], $_REQUEST["fax"], 
      $_REQUEST["email"], $_REQUEST["website"], $_REQUEST["numrue"], 
      $_REQUEST["adressepostal"], isset($_REQUEST["placesurcarte"]), 
      isset($_REQUEST["contraste"]), null, null, $_REQUEST["infointerne"], 
      time(), $_REQUEST["date_validite"], $site->user->id );
  }
  
  $path .= " / ".$fiche->get_html_link();
  $title_path .= " / ".$fiche->nom;
  $site->start_page("pg",$title_path);

  $site->add_alternate_geopoint($fiche);
  $site->set_meta_information($fiche->get_tags(),$fiche->description);
  
  $cts = new contents("<a href=\"index.php\">Le Guide</a>");
  $cts->add(new pgtabshead($site->db,$id_pgcategory1));
  
  
  if ( $site->is_admin() && $_REQUEST["page"] == "edit" )
  {
    $cts->add_paragraph($path." / Editer");
  
    $ent = new entreprise($site->db);
    $rue = new rue($site->db);
    $typerue = new typerue($site->db);
    $ville = new ville($site->db);
    
    $ent->load_by_id($fiche->id_entreprise);
    $rue->load_by_id($fiche->id_rue);  
    $typerue->load_by_id($rue->id_typerue);  
    $ville->load_by_id($fiche->id_ville);  
       
    $frm = new form("editfiche","index.php?id_pgfiche=".$fiche->id,true,"POST","Edition");
    $frm->add_hidden("action","save");
    
    $sfrm = new subform("desc","Description");
    $sfrm->add_text_field("nom","Nom",$fiche->nom);
    $sfrm->add_text_area("description","Description courte",$fiche->description);
    $sfrm->add_text_area("longuedescription","Description longue",$fiche->longuedescription);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("contact","Contacts clients");
    $sfrm->add_text_field("tel","Telephone",telephone_display($fiche->tel));
    $sfrm->add_text_field("fax","Fax",telephone_display($fiche->fax));
    $sfrm->add_text_field("email","Email",$fiche->email);
    $sfrm->add_text_field("website","Site internet",$fiche->website);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("adresse","Addresse");
    $sfrm->add_text_field("numrue","Numéro dans la rue",$fiche->numrue);
	  $sfrm->add_entity_smartselect ("id_typerue","Type de la rue", $typerue);
    $sfrm->add_text_field("nom_rue","Nom de la rue",$rue->nom);
	  $sfrm->add_entity_smartselect ("id_ville","Ville", $ville);
	  $frm->addsub($sfrm);
	  
    $sfrm = new subform("pos","Positiion");
    $sfrm->add_geo_field("lat","Latidue","lat",$fiche->lat);
    $sfrm->add_geo_field("long","Longitude","long",$fiche->long);    
    $frm->addsub($sfrm);
    
    $sfrm = new subform("adm","Coordonnées administratives");
	  $sfrm->add_entity_smartselect ("id_entreprise","Entreprise", $ent);
    $sfrm->add_text_area("adressepostal","Adresse postale complète",$fiche->adressepostal);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("rendu","Options et validité");
    $sfrm->add_checkbox("placesurcarte","Placer sur la carte",$fiche->placesurcarte);
    $sfrm->add_checkbox("contraste","Mettre en constraste",$fiche->contraste);
    $sfrm->add_date_field("date_validite","Informations valablent jusqu'au",$fiche->date_validite);
    $frm->addsub($sfrm);

    $sfrm = new subform("int","Interne");
    $sfrm->add_text_area("infointerne","Commentaire interne",$fiche->infointerne);
    $frm->addsub($sfrm);
    
    $cts->add($frm,true);
  }
  else
  {
    $cts->add_paragraph($path);
    if ( $site->is_admin() )
      $cts->add_paragraph("<a href=\"index.php?page=edit&amp;id_pgfiche=".$fiche->id."\">Editer</a>");
    $cts->add(new pgfichefull($fiche),true);
  }
  
  $site->add_contents($cts);
  $site->end_page();
  exit(); 
}
elseif ( $category->is_valid() && $category->id != 1 )
{
  if ( $site->is_admin() && $_REQUEST["action"] == "save" )
  {
    $category->update ( $category->id_pgcategory_parent, $_REQUEST["nom"], $_REQUEST["description"], $_REQUEST["ordre"], $_REQUEST["couleur_bordure_web"], $_REQUEST["couleur_titre_web"],$_REQUEST["couleur_contraste_web"], $_REQUEST["couleur_bordure_print"], $_REQUEST["couleur_titre_print"], $_REQUEST["couleur_contraste_print"] );
  }
  elseif ( $site->is_admin() && $_REQUEST["action"] == "createcategory" )
  {
    $category->create ( $category->id_pgcategory_parent, $_REQUEST["nom"], $_REQUEST["description"], $_REQUEST["ordre"], $_REQUEST["couleur_bordure_web"], $_REQUEST["couleur_titre_web"],$_REQUEST["couleur_contraste_web"], $_REQUEST["couleur_bordure_print"], $_REQUEST["couleur_titre_print"], $_REQUEST["couleur_contraste_print"] );
  }
  
  $site->set_meta_information($category->get_tags(),$category->description);
  $site->start_page("pg",$title_path);
  $cts = new contents("<a href=\"index.php\">Le Guide</a>");
  
  $cts->add(new pgtabshead($site->db,$id_pgcategory1));
  
  if ( $site->is_admin() && $_REQUEST["page"] == "ajoutfiche" )
  {
    $cts->add_paragraph($path." / Ajouter une fiche");
  
    $ent = new entreprise($site->db);
    $rue = new rue($site->db);
    $typerue = new typerue($site->db);
    $ville = new ville($site->db);
    
    /*$ent->load_by_id($fiche->id_entreprise);
    $rue->load_by_id($fiche->id_rue);  
    $typerue->load_by_id($rue->id_typerue);  
    $ville->load_by_id($fiche->id_ville);*/  
       
    $frm = new form("editfiche","index.php?id_pgcategory=".$category->id,true,"POST","Ajouter");
    $frm->add_hidden("action","createfiche");
    
    $sfrm = new subform("desc","Description");
    $sfrm->add_text_field("nom","Nom",$fiche->nom);
    $sfrm->add_text_area("description","Description courte",$fiche->description);
    $sfrm->add_text_area("longuedescription","Description longue",$fiche->longuedescription);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("contact","Contacts clients");
    $sfrm->add_text_field("tel","Telephone",telephone_display($fiche->tel));
    $sfrm->add_text_field("fax","Fax",telephone_display($fiche->fax));
    $sfrm->add_text_field("email","Email",$fiche->email);
    $sfrm->add_text_field("website","Site internet",$fiche->website);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("adresse","Addresse");
    $sfrm->add_text_field("numrue","Numéro dans la rue",$fiche->numrue);
	  $sfrm->add_entity_smartselect ("id_typerue","Type de la rue", $typerue);
    $sfrm->add_text_field("nom_rue","Nom de la rue",$rue->nom);
	  $sfrm->add_entity_smartselect ("id_ville","Ville", $ville);
	  $frm->addsub($sfrm);
	  
    $sfrm = new subform("pos","Positiion");
    $sfrm->add_geo_field("lat","Latidue","lat",$fiche->lat);
    $sfrm->add_geo_field("long","Longitude","long",$fiche->long);    
    $frm->addsub($sfrm);
    
    $sfrm = new subform("adm","Coordonnées administratives");
	  $sfrm->add_entity_smartselect ("id_entreprise","Entreprise", $ent);
    $sfrm->add_text_area("adressepostal","Adresse postale complète",$fiche->adressepostal);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("rendu","Options et validité");
    $sfrm->add_checkbox("placesurcarte","Placer sur la carte",$fiche->placesurcarte);
    $sfrm->add_checkbox("contraste","Mettre en constraste",$fiche->contraste);
    $sfrm->add_date_field("date_validite","Informations valablent jusqu'au",$fiche->date_validite);
    $frm->addsub($sfrm);

    $sfrm = new subform("int","Interne");
    $sfrm->add_text_area("infointerne","Commentaire interne",$fiche->infointerne);
    $frm->addsub($sfrm);
    
    $cts->add($frm,true);
    //
  }
  elseif ( $site->is_admin() && $_REQUEST["page"] == "ajoutcat" )
  {
    $cts->add_paragraph($path." / Ajouter une catégorie"); 
    
    $frm = new form("editcategory","index.php?id_pgcategory=".$category->id,true,"POST","Ajouter");
    $frm->add_hidden("action","createcategory");
    
    $sfrm = new subform("desc","Description");
    $sfrm->add_text_field("nom","Nom","");
    $sfrm->add_text_area("description","Description courte","");
    $sfrm->add_text_field("ordre","Numéro d'ordre","0");
    $frm->addsub($sfrm);
    
    $sfrm = new subform("web","Couleurs Web");
    $sfrm->add_color_field("couleur_bordure_web","rgb","Bordure",$category->couleur_bordure_web);
    $sfrm->add_color_field("couleur_titre_web","rgb","Titre",$category->couleur_titre_web);
    $sfrm->add_color_field("couleur_contraste_web","rgb","Contraste",$category->couleur_contraste_web);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("print","Couleurs Impression");
    $sfrm->add_color_field("couleur_bordure_print","ymck","Bordure",$category->couleur_bordure_print);
    $sfrm->add_color_field("couleur_titre_print","ymck","Titre",$category->couleur_titre_print);
    $sfrm->add_color_field("couleur_contraste_print","ymck","Contraste",$category->couleur_contraste_print);
    $frm->addsub($sfrm);    
        
    $cts->add($frm,true);  
  }
  elseif ( $site->is_admin() && $_REQUEST["page"] == "edit" )
  {
    $cts->add_paragraph($path." / Editer"); 
    
    $frm = new form("editcategory","index.php?id_pgcategory=".$category->id,true,"POST","Ajouter");
    $frm->add_hidden("action","save");
    
    $sfrm = new subform("desc","Description");
    $sfrm->add_text_field("nom","Nom",$category->nom);
    $sfrm->add_text_area("description","Description courte",$category->description);
    $sfrm->add_text_field("ordre","Numéro d'ordre",$category->ordre);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("web","Couleurs Web");
    $sfrm->add_color_field("couleur_bordure_web","rgb","Bordure",$category->couleur_bordure_web);
    $sfrm->add_color_field("couleur_titre_web","rgb","Titre",$category->couleur_titre_web);
    $sfrm->add_color_field("couleur_contraste_web","rgb","Contraste",$category->couleur_contraste_web);
    $frm->addsub($sfrm);
    
    $sfrm = new subform("print","Couleurs Impression");
    $sfrm->add_color_field("couleur_bordure_print","ymck","Bordure",$category->couleur_bordure_print);
    $sfrm->add_color_field("couleur_titre_print","ymck","Titre",$category->couleur_titre_print);
    $sfrm->add_color_field("couleur_contraste_print","ymck","Contraste",$category->couleur_contraste_print);
    $frm->addsub($sfrm);    
        
    $cts->add($frm,true);  
  }
  else
  {
    $cts->add_paragraph($path);
    if ( $site->is_admin() )
    {
      $cts->add_paragraph("<a href=\"index.php?page=ajoutfiche&amp;id_pgcategory=".$category->id."\">Ajouter une fiche</a>");
      $cts->add_paragraph("<a href=\"index.php?page=ajoutcat&amp;id_pgcategory=".$category->id."\">Ajouter une catégorie</a>");
      $cts->add_paragraph("<a href=\"index.php?page=edit&amp;id_pgcategory=".$category->id."\">Editer</a>");
    }
    
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
    

      
    $cts->add(new pgfichelistcat($category));
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