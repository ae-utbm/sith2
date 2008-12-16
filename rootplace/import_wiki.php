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
require_once($topdir."include/entities/wiki.inc.php");
require_once("fileswiki.inc.php");
require_once($topdir."include/entities/folder.inc.php");
$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

define("AE_ACCOUNTS","/var/www/ae/accounts/");

function process_namespace($path,$namespace,$config)
{
  global $site;
  global $newdfiles;
  echo '<h1>namespace : '.$namespace.'</h1>';;
  $subs=array();
  $pages=array();
  if ($dh = opendir($path))
  {
    while (($file = readdir($dh)) !== false)
    {
      if($file=='.' || $file=='..' || $file=='playground' || $file=='_dummy')
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
      $lion = new utilisateur($site->db);
      $lion->load_by_id(3538);
      foreach($pages as $page => $revisions)
      {
        $_page=$page;
        $wiki = new wiki($site->db,$site->dbrw);
        $parent = new wiki($site->db,$site->dbrw);
        if($page=="start")
        {
          $_page=$config["unixname"];
          echo '<h2>page : '.$namespace.'</h2>';
          $pagename = $parent->load_or_create_parent($namespace, $lion, $config['rights'], $config['rights_id_group'], $config['rights_id_group_admin']);
          if ( !is_null($pagename) && $parent->is_valid() && !$wiki->load_by_fullpath($namespace))
          {
            $wiki->herit($parent);
            $parent->id_utilisateur=$site->user->id;
            $wiki->set_rights($site->user,$config['rights'], $config['rights_id_group'], $config['rights_id_group_admin']);
            sort($revisions);
            $first=array_shift($revisions);
            $content=implode("",gzfile($path.$page.'.'.$first.'.txt.gz'));
            foreach($newdfiles as $url => $newdfile)
              $content=str_replace($url,$newdfile,$content);
            $content=preg_replace("/\[\[([A-Za-z-0-9\-_])([A-Za-z-0-9\-_.:#]+?)\]\]/", "[[".$_page.":$1$2]]",$content);
            $wiki->create ($parent, $config['id_asso'], $_page, 0,$_page,$content);
            foreach($revisions as $revision)
            {
              $content=implode("",gzfile($path.$page.'.'.$revision.'.txt.gz'));
              foreach($newdfiles as $url => $newdfile)
                $content=str_replace($url,$newdfile,$content);
              $content=preg_replace("/\[\[([A-Za-z-0-9\-_])([A-Za-z-0-9\-_.:#]+?)\]\]/", "[[".$_page.":$1$2]]",$content);
              $wiki->revision($lion->id,$_page,$content,'Édité le '.date('Y-m-d', $revision).' à '.date('H:i:s', $revision));
            }
          }
          continue;
        }
        echo '<h2>page : '.$namespace.':'.$page.'</h2>';
        $pagename = $parent->load_or_create_parent($namespace.':'.$page, $lion, $config['rights'], $config['rights_id_group'], $config['rights_id_group_admin']);
        if ( !is_null($pagename) && $parent->is_valid() && !$wiki->load_by_fullpath($namespace.':'.$page) )
        {
          $wiki->herit($parent);
          $parent->id_utilisateur=$site->user->id;
          $wiki->set_rights($site->user,$config['rights'], $config['rights_id_group'], $config['rights_id_group_admin']);
          sort($revisions);
          $first=array_shift($revisions);
          $content=implode("",gzfile($path.$page.'.'.$first.'.txt.gz'));
          foreach($newdfiles as $url => $newdfile)
            $content=str_replace($url,$newdfile,$content);
          $wiki->create ($parent, $config['id_asso'], $_page, 0,$_page,$content);
          foreach($revisions as $revision)
          {
            $content=implode("",gzfile($path.$page.'.'.$revision.'.txt.gz'));
            foreach($newdfiles as $url => $newdfile)
              $content=str_replace($url,$newdfile,$content);
            $wiki->revision($lion->id,$_page,$content,'Édité le '.date('Y-m-d', $revision).' à '.date('H:i:s', $revision));
          }
        }
      }
    }
    closedir($dh);
    if(!empty($subs))
      foreach($subs as $sub)
        process_namespace($path.$sub.'/',$namespace.':'.$sub,$config);
  }
}

function process_files($path,$wikipath,&$asso)
{
  global $newdfiles;
  global $site;
  global $idfolder;
  $lion = new utilisateur($site->db);
  $lion->load_by_id(3538);
  $_file = new newdfile($site->db, $site->dbrw);
  $folder = new dfolder($site->db, $site->dbrw);
  if(is_null($idfolder))
  {
    $folder->load_root_by_asso($asso->id);
    $fd = new dfolder($site->db);
    if ( !$folder->is_valid() ) // Le dossier racine n'existe pas... on va le creer :)
    {
      $folder->id_groupe_admin = $asso->get_bureau_group_id(); // asso-bureau
      $folder->id_groupe = $asso->get_membres_group_id(); // asso-membre
      $folder->droits_acces = 0xDDD;
      $folder->id_utilisateur = null;
      $folder->add_folder ( $section, null, null, $asso_folder->id );
    }
    $sub = $folder->get_folders ( $lion );
    while ( $row = $sub->get_row() )
    {
      $fd->_load($row);
      if($fd->titre=='wiki')
        break;
    }
    if($fd->titre!='wiki')//on crée un dossier wiki
    {
      $nfolder = new dfolder($site->db,$site->dbrw);
      $nfolder->herit($folder);
      $nfolder->set_rights($lion,0xDDD,$asso->get_membres_group_id(),$asso->get_bureau_group_id());
      $nfolder->add_folder('wiki',$folder->id,'le bordel importé du wiki',$asso->id );
      $nfolder->set_modere();
      $fd=$nfolder;
    }
    $idfolder=$fd->id;
  }
  $folder->load_by_id($idfolder);

  if ($dh = opendir($path))
  {
    $subs=array();
    while (($file = readdir($dh)) !== false)
    {
      if($file!='texit' && $file!='latex' && $file!='wiki' && $file!='outils' && !preg_match('/^dw-backup-/i',$file))
      {
        if(is_dir($path.$file))
        {
          $subs[]=$file;
          continue;
        }
        $_file->herit($folder);
        $_file->set_rights($lion,0xDDD,$asso->get_membres_group_id(),$asso->get_bureau_group_id());
        $filec=array();
        $filec['name']=$file;
        $filec['size']=filesize($path.$file);
        $filec['type']=mime_content_type($path.$file);
        $filec['tmp_name']=$path.$file;
        $_file->add_file ($_FILES["file"],$filec,$folder->id,'fichier importé du wiki',$asso->id );
        if(!empty($wikipath))
          $newdfiles[$wikipath.':'.$file]='newdfile://'.$_file->id;
        else
          $newdfiles[$file]='newdfile://'.$_file->id;
        $_file->set_modere();
      }
    }
  }
  if(!empty($subs))
  {
    foreach($subs as $sub)
    {
      if(!empty($wikipath))
        process_files($path.$sub.'/',$wikipath.':'.$sub,$asso);
      else
        process_files($path.$sub.'/',$sub,$asso);
    }
  }
}




/*if($_REQUEST["action"]=="cleanup")
{
  new requete($site->dbrw,'DELETE FROM wiki WHERE id_utilisateur=3538');
  new requete($site->dbrw,'DELETE FROM wiki_rev WHERE id_utilisateur_rev=3538');
  new requete($site->dbrw,'DELETE FROM wiki_lock WHERE id_utilisateur=3538');
  new requete($site->dbrw,'DELETE FROM wiki_ref_missingwiki WHERE `fullname_wiki_rel` LIKE \'pt:%\'');
  $req = new requete($site->db, 'SELECT wiki_ref_wiki.id_wiki FROM wiki_ref_wiki LEFT JOIN wiki ON wiki_ref_wiki.id_wiki=wiki.id_wiki WHERE wiki.id_wiki IS NULL');
  while(list($id)=$req->get_row())
    new requete($site->dbrw,'DELETE FROM wiki_ref_wiki WHERE id_wiki='.$id);
  $req = new requete($site->db, 'SELECT wiki_ref_file.id_wiki FROM wiki_ref_file LEFT JOIN wiki ON wiki_ref_file.id_wiki=wiki.id_wiki WHERE wiki.id_wiki IS NULL');
  while(list($id)=$req->get_row())
    new requete($site->dbrw,'DELETE FROM wiki_ref_file WHERE id_wiki='.$id);
}*/

if($_REQUEST["action"]=="process")
{
  if(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]))
  {
    $asso = new asso($site->db);
    $passo = new asso($site->db);
    $asso->load_by_unix_name($_REQUEST["unixname"]);
    $passo->load_by_id($asso->id_parent);
    // on traite les fichiers des wiki
    if(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/attic/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/media/";
    elseif(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/data/media/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/data/media/";
    else
      $path=null;
    $newdfiles=array();
    if(!is_null($path))
      process_files($path,'',$asso);

    // on traite le contenu des wiki
    if(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/attic/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/wiki/data/attic/";
    elseif(is_dir(AE_ACCOUNTS.$_REQUEST["unixname"]."/data/attic/"))
      $path=AE_ACCOUNTS.$_REQUEST["unixname"]."/data/attic/";
    else
      $path=null;
    if(!is_null($path))
    {
      $wiki_path=$passo->nom_unix.":".$asso->nom_unix;
      $req = new requete($site->db, 'SELECT id_wiki, fullpath_wiki FROM wiki WHERE fullpath_wiki LIKE \''.$wiki_path.'%\'');
      while(list($id,$_path)=$req->get_row())
      {
        new requete($site->dbrw,'DELETE FROM wiki_lock WHERE id_wiki='.$id);
        new requete($site->dbrw,'DELETE FROM wiki_ref_file WHERE id_wiki='.$id);
        new requete($site->dbrw,'DELETE FROM wiki_ref_missingwiki WHERE id_wiki='.$id);
        new requete($site->dbrw,'DELETE FROM wiki_ref_wiki WHERE id_wiki='.$id);
        new requete($site->dbrw,'DELETE FROM wiki_ref_wiki WHERE id_wiki_rel='.$id);
        new requete($site->dbrw,'DELETE FROM wiki_rev WHERE id_wiki='.$id);
      }
      new requete($site->dbrw,'DELETE FROM wiki WHERE fullpath_wiki LIKE \''.$wiki_path.'%\'');
      $config=array();
      $config['rights_id_group']=30000+$asso->id;
      $config['rights_id_group_admin']=20000+$asso->id;
      $config['__rights_lect']=272;
      $config['__rights_ecrt']=544;
      $config['__rights_ajout']=1088;
      $config['rights']=1904;
      $config['id_asso']=$asso->id;
      $config['unixname']=$asso->nom_unix;
      process_namespace($path,$wiki_path,$config);
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
