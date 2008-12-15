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

/**
 * @file
 * Administration de AECMS
 * @ingroup wiki2
 * @author Simon Lopez
 */

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir."include/entities/asso.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

define("AE_ACCOUNTS","/var/www/ae/accounts/");

function process_namespace($path,$namespace)
{
  echo '<h1>namespace : '.$namespace.'</h1>';;
  $subs=array();
  $pages=array();
  if ($dh = opendir($path))
  {
    while (($file = readdir($dh)) !== false)
    {
      if($file=='.' || $file=='..' || $file=='playground')
        continue;
      if(is_dir($path.$file))
        $subs[]=$file;
      else
      {
        $_file=explode('.',$file,3);
        if(!isset($pages[$_file[0]]))
          $pages[$_file[0]]=array();
        $pages[$_file[0]][]=$_file[1];
      }
    }
    if(!empty($pages))
    {
      foreach($pages as $page => $revisions)
      {
        echo '<h2>page : '.$namespace.':'.$page.'</h2>';
        sort($revisions);
        foreach($revisions as $revision)
        {
          echo '&eacute;dit&eacute;e le : '.date('Y-m-d', $revision).' &agrave; '.date('H:i:s', $revision).'<br/>';
          $lines = gzfile($path.$page.'.'.$revision.'.gz');
          echo $path.$page.'.'.$revision.'.gz';
          $txt=implode('\n',$lines);
          echo $txt;
          exit();
        }
      }
    }
    closedir($dh);
    if(!empty($subs))
      foreach($subs as $sub)
        process_namespace($path.$sub.'/',$namespace.':'.$sub);
  }
}


if($_REQUEST["action"]=="process")
{
  if(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]))
  {
    if(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/attic/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/attic/";
    elseif(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/data/attic/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/data/attic/";
    else
      $path=null;
    if(!is_null($path))
    {
       $asso = new asso($site->db);
       $passo = new asso($site->db);
       $asso->load_by_unix_name($_REQUEST["unixname"]);
       $passo->load_by_id($asso->id_parent);
       $wiki_path=$passo->nom_unix.":".$asso->nom_unix;
       process_namespace($path,$wiki_path);
       exit();
    }
  }
}


function list_wikis ()
{
  $list = array();

  if ($dh = opendir(AE_ACCOUNTS))
  {
    while (($file = readdir($dh)) !== false)
    {
      if($file=='equipecom')
        continue;
      if ( is_dir(AE_ACCOUNTS.$file) && is_dir(AE_ACCOUNTS.$file."/wiki/data/attic/") )
        $list[] = array("unixname"=>$file );
      elseif( is_dir(AE_ACCOUNTS.$file) && is_dir(AE_ACCOUNTS.$file."/data/attic/") )
        $list[] = array("unixname"=>$file );
    }
    closedir($dh);
  }
  return $list;
}


$site->start_page("none","Administration");

$baselist = list_wikis();
$list=array();
$asso = new asso($site->db);
$passo = new asso($site->db);
foreach($baselist as $row)
{
  $asso->load_by_unix_name($row["unixname"]);
  $row["nom_asso"]=$asso->nom;
  $passo->load_by_id($asso->id_parent);
  $row["path"]=$passo->nom_unix.":".$row["unixname"];
  $list[]=$row;
}

$cts = new contents("<a href=\"./\">Administration</a> / IMPORTS WIKI");
$cts->add(new sqltable(
  "wikis",
  "Liste des wikis installés", $list, "import_wiki.php",
  "unixname",
  array("path"=>"Path","unixname"=>"Nom","nom_asso"=>"Association/Activité"),
  array("process"=>"Goooo"),
  array(),
  array()
  ),true);

$site->add_contents($cts);
$site->end_page();

?>
