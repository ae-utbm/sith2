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
$topdir = "../";

require_once($topdir. "include/site.inc.php");
$site = new site ();

$site->start_page("root","utilisateur");
$cts = new contents("Utilisateur dummy");

if ( $_REQUEST["action"] == "init" )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id(4228);

  $user->tovalid="";
  $user->hash="valid";

  if ( !isset($_REQUEST["valid"]) )
    $user->invalidate($_REQUEST["invalid_reason"]);
  else
    $user->validate();

  $user->utbm = false;
  new delete($user->dbrw,"utl_etu_utbm",array("id_utilisateur"=>$user->id));

  if ( isset($_REQUEST["utbm"]) )
  {
    $user->utbm = true;
    new update($user->dbrw,"utilisateurs",array("utbm_utl"=>$user->utbm),array("id_utilisateur"=>$user->id));
    new insert($user->dbrw,"utl_etu_utbm",array("id_utilisateur"=>$user->id,"email_utbm"=>$user->email));
  }
  else
    new update($user->user,"utilisateurs",array("utbm_utl"=>$user->utbm),array("id_utilisateur"=>$user->id));

  if ( isset($_REQUEST["etudiant"]) || isset($_REQUEST["ancien"]) )
    $user->became_etudiant(isset($_REQUEST["utbm"])?"UTBM":"Autre",isset($_REQUEST["ancien"]),true);
  else
    $user->became_notetudiant();


  $user->send_majprofil_email($site);

  $cts->add_paragraph("Fait");
}

$frm = new form("process","dummyuser.php");
$frm->add_hidden("action","init");

$frm->add_checkbox("valid","Validé");
$frm->add_select_field("invalid_reason","Raison (si non validé)",array("email","utbm","emailutbm"));
$frm->add_checkbox("utbm","UTBM");
$frm->add_checkbox("etudiant","Etudiant");
$frm->add_checkbox("ancien","Ancien");

$frm->add_submit("valide","Initialiser");
$cts->add($frm);


$site->add_contents($cts);
$site->end_page();


?>
