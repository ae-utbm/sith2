<?php

/* Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
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
 
$topdir = "../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/entities/carteae.inc.php");
require_once($topdir. "include/entities/cotisation.inc.php");
require_once($topdir. "include/entities/files.inc.php");
require_once($topdir. "include/entities/folder.inc.php");
require_once($topdir. "include/entities/asso.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("matmatronch","group",7);



$site->start_page("matmatronch","Administration");

$cts = new contents("Photos manquantes");

$lst = new itemlist();

$req = new requete($site->db, 
		"SELECT * ".
		"FROM `utilisateurs` " .
		"INNER JOIN `utl_etu_utbm` ON `utilisateurs`.`id_utilisateur`=`utl_etu_utbm`.`id_utilisateur` ".
		"WHERE utbm_utl='1' AND ancien_etudiant_utl!='1' AND role_utbm='etu'");	
		
$count=0;
$noway=0;

$avatar_todo=array();
$sas_todo=array();

$user = new utilisateur($site->db);
while ( $row = $req->get_row() )
{
	if ( !file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".identity.jpg"))
	{
    $user->_load_all($row);
    
    $info = "";
    
	  if ( file_exists("../var/img/matmatronch/" . $row['id_utilisateur'] .".jpg"))
	  {
      $info = ", avartar disponible";
      $avatar_todo[] = $user->id;
	  }
	  
    $req2 = new requete($site->db,"SELECT COUNT(id_photo) " .
      "FROM sas_personnes_photos " .
      "WHERE id_utilisateur='". $user->id."' ");	  
           
    list($nb) = $req2->get_row();
        
	  if ( $nb > 0 )
	  {
      $info .= ", $nb photos disponibles sur le SAS";
      $sas_todo[] = $user->id;
	  }
	  
    $lst->add($user->get_html_link().$info);
    
    if ( empty($info) )
      $noway++;
    
    $count++;
	}
}
$lst->add("<b>$count</b> photos manquantes sur un total de ".$req->lines." );
$lst->add("<b>".count($avatar_todo)."</b> ont un avatar disponible" );
$lst->add("<b>".count($sas_todo)."</b> ont des photos disponibles dans le SAS" );
$lst->add("<b>$noway</b> sans solution possible" );

if ( count($avatar_todo) || count($sas_todo) )
{
  $cts->add_title(2,"Resolution");
  
  if ( count($avatar_todo) )
    $cts->add_paragraph("<a href=\"?action=avatars&id_utilisateurs=".implode(",",$avatar_todo)."\">Passer en revue les avatars pour en utiliser en photo d'identité</a>");
  
  if ( count($sas_todo) )
    $cts->add_paragraph("<a href=\"?action=avatars&id_utilisateurs=".implode(",",$sas_todo)."\">Decouper des photos dans le SAS pour les utiliser en photo d'identité</a>");
}

$cts->add_title(2,"Liste");

$cts->add($lst);


$site->add_contents($cts);
$site->end_page();
?>