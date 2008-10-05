<?php

/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/user.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","Administration");

if(isset($_POST['action'])
   && $_POST['action']=='bloubiboulga'
   && is_uploaded_file($_FILES['xmleuh']['tmp_name']) )
{
  $i=0;
  $j=0;
  $utl = new utilisateur($site->db,$site->dbrw);
  $reader = new XMLReader();
  $reader->open($_FILES['xmleuh']['tmp_name']);
  while ($reader->read())
  {
    if($reader->nodeType == XMLReader::ELEMENT && $reader->name=='Etudiant')
    {
      $node = $reader->expand();
      $nom=$node->getElementsByTagName('Nom');
      $nom=$nom->item(0)->textContent;
      $prenom=$node->getElementsByTagName('Prenom');
      $prenom=$prenom->item(0)->textContent;
      $dob=$node->getElementsByTagName('DateNaissance');
      $dob=$dob->item(0)->textContent;
      $email=$node->getElementsByTagName('email');
      $email=$email->item(0)->textContent;
      $last=$email;
      $dep=$node->getElementsByTagName('CodeDepartement');
      $dep=$dep->item(0)->textContent;
      $sem=$node->getElementsByTagName('Semestre');
      $sem=$sem->item(0)->textContent;
      $filiere=$node->getElementsByTagName('CodeFiliere');
      $filiere=$filiere->item(0)->textContent;
      $ae=$node->getElementsByTagName('CotisantAE');
      $ae=$ae->item(0)->textContent;
      if($utl->load_by_email($email))
      {
        $user->nom=$nom;
        $user->prenom=$prenom;
        $user->date_naissance=$dob;
        $user->departement=strtolower($dep);
        $user->filiere=$filiere;
        if($ae=='O')
        {
        }
        if($user->saveinfos())
        {
          if ( $site->user->id != $user->id )
        $site->log("Édition d'une fiche matmatronch par un tierce","Fiche matmatronch de ".$user->nom." ".$user->prenom." (id : ".$user->id.") modifiée","Fiche MMT",$site->user->id);
        }
        $j++;
      }
      $i++;
      $reader->moveToElement();
    }
  }
  $cts = new contents("Administration/Mise à jour massive : résultat");
  $cts->add_paragraph("$j personnes peuvent êtres mises à jours sur un total de $i personnes");
  $site->add_contents($cts);
}

$cts = new contents("Administration/Mise à jour massive ");
$frm = new form("photos","?",true,"POST","Et paf les photos");
$frm->add_hidden("action","bloubiboulga");
$frm->add_file_field ( "xmleuh", "xmleuh" );
$frm->add_submit("paff","Et paf!");
$cts->add($frm,true);

$site->add_contents($cts);

$site->end_page();

?>
