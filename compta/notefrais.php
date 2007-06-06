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
require_once("include/compta.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/notefrais.inc.php");
require_once($topdir . "include/cts/sqltable.inc.php");

$site = new sitecompta();

$site->allow_only_logged_users("none");
$notefrais = new notefrais($site->db,$site->dbrw);
$asso = new asso($site->db);

if ( isset($_REQUEST["id_notefrais"]) )
{
  $notefrais->load_by_id($_REQUEST["id_notefrais"]);
  if ( $notefrais->is_valid() )
    $asso->load_by_id($notefrais->id_asso);
}
elseif ( $_REQUEST["action"] == "create" )
{
  if ( !$_REQUEST["commentaire"] )
    $Erreur = "Commentaire requis";
  elseif ( !$_REQUEST["designation"][0] || ! $_REQUEST["prix"][0] )
    $Erreur = "Veuillez saisir au moins un frais";
  else
  {
    $asso->load_by_id($_REQUEST["id_asso"]);
    $notefrais->create ( null, $asso->id, $site->user->id, $_REQUEST["commentaire"], $_REQUEST["avance"] ); 
    foreach( $_REQUEST["designation"] as $i => $designation )
    {
      if ( $designation && $_REQUEST["prix"][$i] )
        $notefrais->create_line( $designation, $_REQUEST["prix"][$i] );
    }
  }
}

if ( $notefrais->is_valid() )
{
  $site->start_page ("none", "Note de frais" );
  
  $cts = new contents("Note de frais");
  
  
  $site->add_contents($cts);
  
  $site->end_page ();
  exit();
}

if ( isset($_REQUEST["id_asso"]) )
  $asso->load_by_id($_REQUEST["id_asso"]);

$site->start_page ("none", "Note de frais" );

$cts = new contents("Note de frais");

$cts->add_title(2,"Informations sur les notes de frais");

$cts->add_paragraph("Les notes de frais vous permettent d'obtenir le remboursement des frais que vous avez engagés pour l'AE. Il peut s'agir de matériel que vous avez acheté pour une activité de l'AE.");

$cts->add_paragraph("La note de frais devra être accompagné de tous les justificatifs nécessaires (factures), qui devrons être remis au trésorier de l'activité. Les factures devront être à votre nom, justifiant ainsi que vous avez bien engagé les frais.");

$frm = new form ("saisienotefrais","notefrais.php",true,"POST","Saisie de la note de frais");
$frm->add_hidden("action","create");
if ( isset($Erreur) )
  $frm->error($Erreur);
$frm->add_entity_smartselect ("id_asso","Activité concernée", $asso, false, true);
$frm->add_text_area("commentaire","Commentaire","",40,3,true);
$frm->add_price_field("avance","Avance (qui vous a déjà été versée)");

for($i=0;$i<5;$i++)
{
  $sfrm = new form(null,null,null,null);  
  $sfrm->add_text_field("designation[$i]","Designation");
  $sfrm->add_price_field("prix[$i]","Prix");
  $frm->add($sfrm, false, false, false, false, true);
}

$cts->add($frm,true);

$site->add_contents($cts);

$site->end_page ();

?>