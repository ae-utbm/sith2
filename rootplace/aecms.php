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

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/asso.inc.php");
$site = new site ();

if ( !$site->user->is_in_group("root") )
	error_403();
	
if( !preg_match('/^\/var\/www\/ae\/www\/ae2\//', $_SERVER['SCRIPT_FILENAME']) )
  $aecms_home = "/var/www/ae/www/taiste/aecms";
else
  $aecms_home = "/var/www/ae/www/ae2/aecms";
  
define("AECMS_ACCOUNTS","/var/www/ae/accounts/");
  
function list_aecms ()
{
  $list = array();

  if ($dh = opendir(AECMS_ACCOUNTS))
  {    while (($file = readdir($dh)) !== false)
    {
      if ( is_dir(AECMS_ACCOUNTS.$file) && file_exists(AECMS_ACCOUNTS.$file."/specific/aecms.conf.php") )
      {
        $data = file_get_contents(AECMS_ACCOUNTS.$file."/specific/aecms.conf.php");
        if ( ereg("define\\(\"CMS_ID_ASSO\",([0-9]+)\\)",$data,$regs) )
        {
          $list[] = array("unixname"=>$file,"id_asso"=>$regs[1], "aecms"=>readlink(AECMS_ACCOUNTS.$file."/aecms") );
        }
      }
      
    }    closedir($dh);  }

  return $list;
}

function list_noaecms ()
{
  $list = array();

  if ($dh = opendir(AECMS_ACCOUNTS))
  {    while (($file = readdir($dh)) !== false)
    {
      if ( $file != "." && $file != ".." && is_dir(AECMS_ACCOUNTS.$file) && !file_exists(AECMS_ACCOUNTS.$file."/specific/aecms.conf.php") )
        $list[$file] = $file;
    }    closedir($dh);  }

  return $list;
}

function install_aecms ( $unixname, $id_asso )
{
  if ( !ereg("^([a-z0-9])$", $unixname) )
    return false;
  
  return _install_aecms(AECMS_ACCOUNTS.$unixname."/",$id_asso);
}
  
function _install_aecms ( $target, $id_asso )
{
  global $aecms_home;

  if ( !is_dir($target) )
    if ( !mkdir($target) )
      return false;

  if ( substr($target,-1) != "/" )
    $target = $target."/";

  if ( !is_dir($target."specific") )
    if ( !mkdir($target."specific") )
      return false;
    
  $aecmsConfPhp='<?php
define("CMS_ID_ASSO",'.$id_asso.');
define("CMS_PREFIX","cms:".CMS_ID_ASSO.":");
?>';

  if ( !file_put_contents($target."specific/aecms.conf.php",$aecmsConfPhp) )
    return false;
  
  if ( !file_put_contents($target."specific/custom.css","/* a personaliser */") )
    return false;
  
  if ( is_link($target."aecms") )
    if ( !unlink($target."aecms") )
      return false;
  
  if ( !symlink($aecms_home,$target."aecms") )
    return false;

  $apacheRules='RewriteEngine On
RewriteRule ^([a-z]*)\.php(.*)$  aecms/$1.php$2 [L] 
RewriteRule ^$  aecms/index.php [L] 
RewriteRule ^images/(.*)$  aecms/images/$1 [L]
RewriteRule ^css/(.*)$  aecms/css/$1 [L]
';  
  if ( !file_put_contents($target.".htaccess",$apacheRules) )
    return false;
    
  return true;
}
	
function raz_aecms ( $id_asso )
{
  $file = $topdir."var/aecms/cms".$id_asso.".conf.php";
  return unlink($file);
}
	
if ( $_REQUEST["action"] == "install" )
{
  if ( !install_aecms ( $_REQUEST["unixname"], $_REQUEST["id_asso"] ) )
	{
	  $Message="Erreur lors de l'installation.";
	  $_REQUEST["page"] = "install";
	}
	else
	  $Message="AECMS installé.";
}

if ( $_REQUEST["page"] == "install" )
{
  $asso = new asso($site->db);
  
  
  $site->start_page("none","Administration");
  $cts = new contents("<a href=\"./\">Administration</a> / <a href=\"aecms.php\">AECMS</a> / Installer");
   
  if ( isset($Message) )
    $cts->add_paragraph($Message,"error");
     
  $cts->add_paragraph("<b>Attention</b>: Ceci va installer la version \"$aecms_home\"");

  $places=list_noaecms();

  asort($places); 
   
  $frm = new form("installexists","aecms.php",false,"post","Installer AECMS sur un site existant");
  $frm->add_hidden("action","install");
  $frm->add_select_field("unixname","Emplacement",$places);
  $frm->add_entity_smartselect ( "id_asso", "Association/Activitée", $asso);
  $frm->add_submit("valid","Installer");
  $cts->add($frm,true);

  $frm = new form("installexists","aecms.php",false,"post","Installer AECMS sur un nouveau site");
  $frm->add_hidden("action","install");
  $frm->add_text_field("unixname","Emplacement","",true);
  $frm->add_entity_smartselect ( "id_asso", "Association/Activitée", $asso);
  $frm->add_submit("valid","Installer");
  $cts->add($frm,true);
  
  $places=array();
  $list = list_aecms();
  foreach($list as $row )
    $places[$row["unixname"]]=$row["unixname"];
  
  asort($places);
  
  $frm = new form("installexists","aecms.php",false,"post","Re-Installer AECMS");
  $frm->add_hidden("action","install");
  $frm->add_select_field("unixname","Emplacement",$places);
  $frm->add_entity_smartselect ( "id_asso", "Association/Activitée", $asso);
  $frm->add_submit("valid","Installer");
  $cts->add($frm,true);
  
  $site->add_contents($cts);
  $site->end_page();
  exit();
}
	
	
$site->start_page("none","Administration");

$list = list_aecms();

$cts = new contents("<a href=\"./\">Administration</a> / AECMS");

if ( isset($Message) )
  $cts->add_paragraph($Message,"error");
    
$cts->add(new sqltable(
  "aecms", 
  "", $list, "aecms.php", 
  "type", 
  array("unixname"=>"Nom","aecms"=>"AECMS installé"), 
  array(), 
  array(),
  array()
  ));
  
$lst = new itemlist();
$lst->add("<a href=\"aecms.php?page=raz\">RAZ d'un AECMS</a> (remet les paramètres aux valeurs par défaut)");
$lst->add("<a href=\"aecms.php?page=install\">Installation d'un AECMS</a> (ou re-installation)");
$cts->add($lst);

$site->add_contents($cts);
 
//TODO

$site->end_page();

?>