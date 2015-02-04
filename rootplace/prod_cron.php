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
define("PROD_CRON", "/var/www/cron_update.sh");
define("PROD_SCRIPT", "/var/www/update-official-www.sh");
define("POST_COMMIT_SCRIPT", "/data/svn/ae2/hooks/post-commit");
define("REFRESH_TAISTE", "/var/www/refresh-taiste-database.sh");
$topdir="../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","Administration / passage en prod");
$cts = new contents("<a href=\"./\">Administration</a> / Passage en production");
$tabs = array(array("","rootplace/prod_cron.php","Passage en prod"),
              array("refresh","rootplace/prod_cron.php?view=refreshdb","Rafraichir taiste"),
              array("script","rootplace/prod_cron.php?view=script","Script de passage en prod"),
              array("commit","rootplace/prod_cron.php?view=commit","Script de post commit"),
              array("clone","rootplace/prod_cron.php?view=refreshdbscript","Script de refresh de taiste"));

if ( $_REQUEST["action"] == "passprod" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Passage en production",null, 2 ) )
    @exec(escapeshellcmd(PROD_CRON));
  $Ok=true;
}
if ( $_REQUEST["action"] == "refreshdb" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Rafraichir taiste",null, 2 ) )
    @exec(escapeshellcmd(REFRESH_TAISTE));
  $Ok=true;
}
if ( $_REQUEST["action"] == "scriptprod" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Modification du script de passage en production",null, 2 ) )
  {
    if(!$handle = @fopen(PROD_SCRIPT, "w"))
      $Ok=false;
    else
    {
      $content = preg_replace("/\r\n/","\n",htmlspecialchars_decode($_REQUEST["__script__"]));
      @fwrite($handle,$content);
      @fclose ($handle);
      $_REQUEST["view"]="script";
      $Ok=true;
    }
  }
}
if ( $_REQUEST["action"] == "scriptpostcommit" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Modification du script de post-commit",null, 2 ) )
  {
    if(!$handle = @fopen(POST_COMMIT_SCRIPT, "w"))
      $Ok=false;
    else
    {
      $content = preg_replace("/\r\n/","\n",htmlspecialchars_decode($_REQUEST["__script__"]));
      @fwrite($handle,$content);
      @fclose ($handle);
      $_REQUEST["view"]="commit";
      $Ok=true;
    }
  }
}
if ( $_REQUEST["action"] == "refreshdbscript" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Modification du script de refresh de taiste",null, 2 ) )
  {
    if(!$handle = @fopen(REFRESH_TAISTE, "w"))
      $Ok=false;
    else
    {
      $content = preg_replace("/\r\n/","\n",htmlspecialchars_decode($_REQUEST["__script__"]));
      @fwrite($handle,$content);
      @fclose ($handle);
      $_REQUEST["view"]="script";
      $Ok=true;
    }
  }
}

$cts->add(new tabshead($tabs,$_REQUEST["view"]));
$cts->add_paragraph("Révision en production : ".get_rev());

if ( $Ok )
{
  if ( $_REQUEST["action"] == "scriptprod" )
    $cts->add_paragraph("Script de passage en prod modifié");
  elseif( $_REQUEST["action"] == "commit" )
    $cts->add_paragraph("Script de post commit modifié");
  elseif( $_REQUEST["action"] == "refreshdbscript" )
    $cts->add_paragraph("Script de refresh modifié");
}

if($_REQUEST["view"]=="script")
{
  if(!$handle = @fopen(PROD_SCRIPT, "r"))
    $cts->add_paragraph("Impossible d'ouvrir le script de passage en prod !");
  else
  {
    $script = @fread($handle, @filesize(PROD_SCRIPT));
    $frm = new form("passageenprod", "prod_cron.php", false, "POST", "Editer le script de passage en production");
    $frm->allow_only_one_usage();
    $frm->add_hidden("action","scriptprod");
    $frm->add_text_area("__script__", "Script : ",$script,80,40);
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
}
else if($_REQUEST["view"]=="commit")
{
  if(!$handle = @fopen(POST_COMMIT_SCRIPT, "r"))
    $cts->add_paragraph("Impossible d'ouvrir le script de post commit !");
  else
  {
    $script = @fread($handle, @filesize(POST_COMMIT_SCRIPT));
    $frm = new form("postcommit", "prod_cron.php", false, "POST", "Editer le script de post commit");
    $frm->allow_only_one_usage();
    $frm->add_hidden("action","scriptpostcommit");
    $frm->add_text_area("__script__", "Script : ",$script,80,40);
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
}
else if($_REQUEST["view"]=="refreshdbscript")
{
  if(!$handle = @fopen(REFRESH_TAISTE, "r"))
    $cts->add_paragraph("Impossible d'ouvrir le script de rafraichissement !");
  else
  {
    $script = @fread($handle, @filesize(REFRESH_TAISTE));
    $frm = new form("refreshdbscript", "prod_cron.php", false, "POST", "Editer le script de refresh de taiste");
    $frm->allow_only_one_usage();
    $frm->add_hidden("action","refreshdbscript");
    $frm->add_text_area("__script__", "Script : ",$script,80,40);
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
}
else if ($_REQUEST["view"] == "refreshdb")
{
  if ($_REQUEST["action"] == "refreshdb" && $Ok) {
    $cts->add_paragraph("<b>Fafraichissement programmé dans les deux minutes à venir.</b>");
  } else {
    $frm = new form("refreshdb", "prod_cron.php", false, "POST", "Rafraichir la BDD de taiste");
    $frm->allow_only_one_usage();
    $frm->add_hidden("action","refreshdb");
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
}
else
{
  if ($_REQUEST["action"] == "passprod" && $Ok) {
    $cts->add_paragraph("<b>Passage en prod programmé dans les deux minutes à venir.</b>");
  } else {
    $frm = new form("passageenprod", "prod_cron.php", false, "POST", "Passer en production");
    $frm->allow_only_one_usage();
    $frm->add_hidden("action","passprod");
    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
}

$site->add_contents($cts);

$site->end_page();

?>
