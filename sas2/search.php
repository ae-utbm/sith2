<?php
/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
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
require_once("include/sas.inc.php");
require_once($topdir."include/cts/gallery.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/cts/sas.inc.php");
require_once($topdir."include/cts/video.inc.php");
require_once($topdir."include/cts/react.inc.php");
require_once($topdir. "include/entities/page.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$site = new sas();
$site->add_css("css/doku.css");
$site->allow_only_logged_users("sas");

$asso = new asso($site->db);
$assoph = new asso($site->db);
$user = new utilisateur($site->db);
$userph = new utilisateur($site->db);
$userad = new utilisateur($site->db);

if ( isset($_REQUEST["id_asso"]) )
  $asso->load_by_id($_REQUEST["id_asso"]);
  
if ( isset($_REQUEST["id_asso_photographe"]) )
  $assoph->load_by_id($_REQUEST["id_asso_photographe"]);
  
if ( isset($_REQUEST["id_utilisateur_present"]) )
  $user->load_by_id($_REQUEST["id_utilisateur_present"]);
  
if ( isset($_REQUEST["id_utilisateur_photographe"]) )
  $userph->load_by_id($_REQUEST["id_utilisateur_photographe"]);
  
if ( isset($_REQUEST["id_utilisateur_contributeur"]) )
  $userad->load_by_id($_REQUEST["id_utilisateur_contributeur"]);  

$site->start_page("sas","Recherche - Stock à Souvenirs");

$cts = new contents("Recherche");

$frm = new form("search","search.php",false,"POST","Paramètres de recherche");
$frm->add_hidden("action","search");
$frm->add_date_field("date_debut","Photos prisent après le");
$frm->add_date_field("date_fin","Photos prisent avant le");
$frm->add_text_field("tags","Tags");
$frm->add_entity_smartselect ( "id_asso", "Association/Club", $asso, true );
$frm->add_entity_smartselect ( "id_asso_photographe", "Club photographe", $assoph, true );
$frm->add_entity_smartselect ( "id_utilisateur_present", "Personne sur la photo", $user, true );
$frm->add_entity_smartselect ( "id_utilisateur_photographe", "Photographe", $userph, true );
$frm->add_entity_smartselect ( "id_utilisateur_contributeur", "Contributeur", $userad, true );
$frm->add_select_field("type","Type de média",array(0=>"Tous",MEDIA_PHOTO+1=>"Photo",MEDIA_VIDEOFLV+1=>"Video"));
$frm->add_submit("go","Rechercher");

$cts->add($frm,true);

if ( $_REQUEST["action"] == "search" )
{
  $joins=array();
  $conds=array();
  $params="";
  
  if ( $asso->is_valid() )
  {
    $conds[] = "sas_photos.meta_id_asso_ph='".$asso->id."'";
    $params.="&id_asso=".$asso->id;
  }
  
  if ( $assoph->is_valid() )
  {
    $conds[] = "sas_photos.id_asso_photographe='".$assoph->id."'";
    $params.="&id_asso_photographe=".$assoph->id;
  }
  
  if ( $user->is_valid() )
  {
    $joins[] = "INNER JOIN sas_personnes_photos AS `p2` ON ( sas_photos.id_photo=p2.id_photo AND p2.id_utilisateur='".$user->id."') ";
    $params.="&id_utilisateur_present=".$user->id;
  }
  
  if ( $userph->is_valid() )
  {
    $conds[] = "sas_photos.id_utilisateur_photographe='".$userph->id."'";
    $params.="&id_utilisateur_photographe=".$userph->id;
  }
  
  if ( $userad->is_valid() )
  {
    $conds[] = "sas_photos.id_utilisateur='".$userad->id."'";
    $params.="&id_utilisateur_contributeur=".$userad->id;
  }
  
  if ( $_REQUEST["date_debut"] )
  {
    $conds[] = "sas_photos.date_prise_vue>='".date("Y-m-d H:i",$_REQUEST["date_debut"])."'";
    $params.="&date_debut=".date("Y-m-d H:i",$_REQUEST["date_debut"]);
  }
  
  if ( $_REQUEST["date_fin"] )
  {
    $conds[] = "sas_photos.date_prise_vue<='".date("Y-m-d H:i",$_REQUEST["date_fin"])."'";
    $params.="&date_debut=".date("Y-m-d H:i",$_REQUEST["date_fin"]);
  }
  
  if ( $_REQUEST["type"] )
  {
    $conds[] = "sas_photos.type_media_ph='".$_REQUEST["type"]."'";
    $params.="&type=".$_REQUEST["type"];
  }
  
  if ( $_REQUEST["tags"] )
  {
    $tags=trim(strtolower($_REQUEST["tags"]));
    if ( !empty($tags) )
    {
      $tags = explode(",",$tags);
      $tconds=array();
      $missing=array();
      foreach ( $tags as $tag )
      {
        $tag = trim($tag);  
        $tconds[] = "nom_tag='".mysql_escape_string($tag)."'";
        $missing[$tag]=$tag;
      }
      
      $tags=array();
      $req = new requete($site->db, "SELECT id_tag, nom_tag FROM tag WHERE ".implode(" OR ",$tconds));
      while ( list($id,$tag) = $req->get_row() )
      {
        $tags[$id]=$tag;
        unset($missing[$tag]);
      }
      if ( count($missing) == 0 )
      {
        foreach ( $tags as $id => $tag )
        {
          $joins[] = "INNER JOIN sas_photos_tag AS tag$id ON ".
                     "( tag".$id.".id_photo=sas_photos.id_photo ".
                       "AND tag".$id.".id_tag='".$id."' )";
        }
      }
    }
  }
  
  if ( count($conds) == 0 )
    $conds[]="1";
    
  print_r($conds);
  print_r($joins);
    
  $cat = new catphoto($site->db);
  $cat->load_by_id(1);
  $req = $cat->get_photos_search ( $site->user, implode(" AND ",$conds), implode(" ",$joins), "COUNT(*)");

  list($count) = $req->get_row();
  
  $cts->add_paragraph("$count photos");


}

$site->add_contents($cts);

$site->end_page ();

?>