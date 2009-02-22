<?php

/* Copyright 2009
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

/**
 * @file
 * Administration de AECMS
 * @ingroup wiki2
 * @author Simon Lopez
 */
$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."sas2/include/licence.inc.php");
$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","Administration");

if(isset($_REQUEST['id_licence']))
{
  $licence = new licence($site->db,$site->dbrw);
  if($licence->load_by_id($_REQUEST['id_licence']))
  {
    if($_REQUEST['action']=='edit')
    {
      $cts = new contents("<a href=\"./\">Administration</a> / <a href=\"saslicences.php\">Licences sas</a>");
      $frm = new form("updatelicence","?id_licence=".$licence->id);
      $frm->add_hidden("action","updatelicence");
      $frm->add_text_field("titre","Titre",$licence->titre);
      $frm->add_text_field("desc","Description",$licence->desc);
      $frm->add_text_field("url","URL",$licence->url);
      $frm->add_text_field("icone","icone",$licence->icone);
      $cts->add($frm);
      $site->add_contents($cts);
      $site->end_page();
      exit();
    }
  }
}

$cts = new contents("<a href=\"./\">Administration</a> / Licences sas");
$req = new requete($site->db,'select * from licences');
$cts->add(new sqltable(
  "wikis",
  "Liste des licences",
  $req,
  "saslicences.php",
  "id_licence",
  array("titre"=>"Titre"),
  array("edit"=>"Modifier"),
  array(),
  array()
  ),true);

$site->add_contents($cts);
$site->end_page();

?>
