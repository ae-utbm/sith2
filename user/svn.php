<?php

/* Copyright 2007
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
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/svn.inc.php");


$site = new site ();

$site->allow_only_logged_users("");

$site->start_page("none","Utilisateur / SVN");
$cts = new contents("<a href=\"./\">Utilisateur</a> / SVN");

if(empty($site->user->alias))
{
  if( isset($_REQUEST["alias"]) )
  {
    if ( !preg_match("#^([a-z0-9][a-z0-9\-\._]+)$#i",$_REQUEST["alias"]) )
    {
      $ErreurMAJ = "Alias invalide, utilisez seulement des lettres, des chiffres, des tirets, des points, et des underscore.";
    }
    elseif ( $_REQUEST["alias"] && !$user->is_alias_avaible($_REQUEST["alias"]) )
    {
      $ErreurMAJ = "Alias d&eacute;j&agrave;  utilis&eacute;";
    }
    else
      $iste->user->saveinfos();
  }

  if( !isset($_REQUEST["alias"]) || isset($ErreurMAJ) )
  {
    $cts->add_paragraph("<b>Vous n'avez pas d'alias, il vous ets donc impossible d'utiliser les dépots" . 
                        " subversions.</b>");
    $frm = new form("setalias","svn.php",false,"post","Créer un alias :");
    if ( isset($ErreurMAJ) )
      $frm->error($ErreurMAJ);
    $frm->add_text_field("alias","Alias",$user->alias);
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);

    $site->add_contents($cts);
    $site->end_page();
    exit();
  }
}

if( isset($_REQUEST["action"]) && $_REQUEST["action"]=="pass" )
{
  if(empty($_REQUEST["pass"]))
    $cts->add_paragraph("<b>Veuillez spécifier un mot de passe.</b>");
  else
    @exec("/usr/bin/htpasswd -sb ".SVN_PATH.PASSWORDFILE." ".$site->user->alias." ".$_REQUEST["pass"]);
}

$find = @exec("grep \"^".$site->user->alias.":\"" .SVN_PATH.PASSWORDFILE);
if( empty($find) )
{
  $cts->add_paragraph("<b>Vous n'avez pas de mot de passe, il vous est donc impossible d'utiliser les dépots" . 
                      " subversions.</b>");
  $frm = new form("setmdp","svn.php",false,"post","Créer un mot de passe :");
  $frm->add_text_field("pass","Mot de passe");
  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);

  $site->add_contents($cts);
  $site->end_page();
  exit();
}

$frm = new form("changemdp","svn.php",false,"post","Changer le mot de passe :");
$frm->add_text_field("pass","Mot de passe");
$frm->add_submit("valid","Valider"); $cts->add($frm,true);

/* ici faire la liste des dépots privés, publiques et aeinfo */

$site->add_contents($cts);
$site->end_page();

?>
