<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 * - Julien Etelain < julien at pmad dot net >
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/entities/wiki.inc.php");

$site = new site();

/* temporairement, si t'es pas logué tu lis pas */
if (!$site->user->id)
  error_403();

$wiki = new wiki($site->db,$site->dbrw);

// Creation d'une page
if ( $site->user->is_valid() && $_REQUEST["action"] == "create" )
{
  $parent = new wiki($site->db,$site->dbrw);

  // Prepare les info
  $pagepath = $_REQUEST["name"];
  
  // Récupère les tokens et le nom de la page (dernier token du path)
  $tokens = explode(":",$pagepath);
  $pagename=array_pop($tokens); 
  
  // Cherche le dernier parent, crée les parents manquant si nécessaire
  // Commençons par la racine
  $parent->load_by_id(1);
  $can_create = $parent->is_right($site->user,DROIT_AJOUTCAT);  
  
  // Poursuivons par les eventuel parents
  $parentparent = clone $parent;
  foreach( $tokens as $token )
  {
    if ( $parent->load_by_name($parentparent->id,$token) )
      $can_create = $parent->is_right($site->user,DROIT_AJOUTCAT);
      
    elseif( $can_create ) // On a le droit de creer, on alors on crée le parent manquant
    {
      $parent->herit($parentparent);
      if ( $parent->is_admin($site->user) )
         $parent->set_rights($site->user,
           $_REQUEST['rights'],$_REQUEST['rights_id_group'],
           $_REQUEST['rights_id_group_admin']);
      else
        $parent->id_utilisateur=$site->user->id;
      $parent->create ( $parentparent, null, $token, $token, "Créée pour [[:$pagepath]]", $_REQUEST["comment"] );
    }
    $parentparent = clone $parent;
  }

  if ( $can_create && $parent->is_valid() )
  {
    $wiki->herit($parent);
    if ( $parent->is_admin($site->user) )
        $wiki->set_rights($site->user,
          $_REQUEST['rights'],$_REQUEST['rights_id_group'],
          $_REQUEST['rights_id_group_admin']);
    else
      $parent->id_utilisateur=$site->user->id;    
    $wiki->create ( $parent, null, $pagename, $_REQUEST["title"], $_REQUEST["contents"], $_REQUEST["comment"] );    
  }
  else
  {
    $Erreur="Impossible de créer la page.";
    $_REQUEST["view"]="create";
    $wiki->id=null;
  }
}
elseif ( isset($_REQUEST["name"]) )
  $wiki->load_by_fullpath($_REQUEST["name"]);
else
  $wiki->load_by_id(1);

if ( !$wiki->is_valid() )
{
  $pagepath = $_REQUEST["name"];
  $can_create = false;
  $is_admin = false;
  if ( $site->user->is_valid() )
  {
    // Cherche le parent le plus haut pour savoir si la création de page est authorisée
    $parent = new wiki($site->db);
    $tokens = explode(":",$pagepath);
    array_pop($tokens);
    
    // La racine
    $parent->load_by_id(1);
    $can_create = $parent->is_right($site->user,DROIT_AJOUTCAT);
    $is_admin = $parent->is_admin($site->user);
    $lastparent = clone $parent;
    // Les eventuels parents    
    foreach( $tokens as $token )
    {
      if ( $parent->load_by_name($parent->id,$token) )
      {
        $can_create = $parent->is_right($site->user,DROIT_AJOUTCAT);
        $is_admin = $parent->is_admin($site->user);
      }
      else
        break;
      $lastparent = clone $parent;
    }
  }
  
  
  $site->start_page ("none", "Page inexistante");
  
  if ( $can_create )
    $tabs = array(array("","wiki2/?name=".$pagepath, "Page"),
                  array("create","wiki2/?name=".$pagepath."&view=create", "Creer")
                 );
  else
    $tabs = array(array("","wiki2/?name=".$pagepath, "Page"));
               
  $cts = new contents();
  $cts->add(new tabshead($tabs,$_REQUEST["view"]));
  
  if ( $can_create && $_REQUEST["view"] == "create" )
  {
    $frm = new form("newwiki","./?name=$pagepath",true,"POST");
    if ( isset($Erreur) )
      $frm->error($Erreur);
    $frm->add_hidden("action","create");
    $frm->add_text_field("title","Titre","",true);
    $frm->add_text_area("contents","Contenu","",80,20,true);
    $frm->add_text_field("comment","Log","Créée");
    if ( $is_admin )
      $frm->add_rights_field($lastparent,true,true,"wiki");
    $frm->add_submit("save","Ajouter");  
    $cts->add($frm);
  }
  else
  {
    if ( $can_create )
      $cts->add_paragraph("Cette page n'existe pas. <a href=\"wiki2/?name=".$pagepath."&view=create\">La creer</a>","error");
    else
      $cts->add_paragraph("Cette page n'existe pas.","error");
  }
  $site->add_contents($cts);
  
  $site->end_page ();
  
  exit();  
}

$pagepath = $wiki->fullpath;
$can_edit = $site->user->is_valid() && $wiki->is_right($site->user,DROIT_ECRITURE);

if ( $_REQUEST["action"] == "revision" && $can_edit)
  $wiki->revision ( $site->user->id, $_REQUEST["title"], $_REQUEST["contents"], $_REQUEST["comment"] );

$site->start_page ("none", $wiki->title);

if ( $can_edit )
  $tabs = array(array("","wiki2/?name=".$pagepath, "Page"),
                array("edit","wiki2/?name=".$pagepath."&view=edit", "Editer"),
                array("refs","wiki2/?name=".$pagepath."&view=refs", "Références"),
                array("hist","wiki2/?name=".$pagepath."&view=hist", "Historique")
               );
else
  $tabs = array(array("","wiki2/?name=".$pagepath, "Page"),
                array("refs","wiki2/?name=".$pagepath."&view=refs", "Références"),
                array("hist","wiki2/?name=".$pagepath."&view=hist", "Historique")
               );
             
$cts = new contents();
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if ( $can_edit && $_REQUEST["view"] == "edit" )
{
  $frm = new form("newwiki","./?name=$pagepath",true,"POST");
  $frm->add_hidden("action","revision");
  $frm->add_text_field("title","Titre",$wiki->rev_title,true);
  $frm->add_text_area("contents","Contenu",$wiki->rev_contents,80,20,true);
  $frm->add_text_field("comment","Log","");
  $frm->add_submit("save","Enregistrer"); 
  $cts->add($frm);
}
elseif ( $_REQUEST["view"] == "refs" ) 
{
  
  
  
}
elseif ( $_REQUEST["view"] == "hist" ) 
{
  
  
  
}
else
{
  $cts->add_title(1,$wiki->title);
  
  $cts->add($wiki->get_stdcontents());
}

$site->add_contents($cts);

$site->end_page ();

?>