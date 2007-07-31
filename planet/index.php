<?php

/* Copyright 2007
 *
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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
define('MAGPIE_CACHE_DIR', '/var/www/ae/www/var/cache/planet/');
define('MAGPIE_CACHE_ON', true);
define('MAGPIE_CACHE_AGE', 60*60); //une heure
define('MAGPIE_OUTPUT_ENCODING', "UTF-8");
define('MAX_NUM',2);

include($topdir. "include/site.inc.php");
require_once($topdir. "include/lib/magpierss/rss_fetch.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir . "include/graph.inc.php");

$site = new site();

if (!$site->user->id)
  error_403();

$site->start_page ("none", "Planet AE ");

$tabs = array(array("","planet/index.php", "Planet"),
              array("perso","trombi/index.php?view=perso", "Personnaliser"),
              array("add","planet/index.php?view=add", "Proposer")
             );
$cts = new contents("Planet AE ");
$cts->add(new tabshead($tabs,$_REQUEST["view"]));



if($_REQUEST["view"]=="add")
{
    $cts->add_paragraph("Gestion du contenu");
    $site->add_contents($cts);
    $cts = new contents("Proposer un nouveau flux");
    $site->add_contents($cts);
    $cts = new contents("Mes propositions");
    $cts->add_paragraph("Liste des flux déjà proposés\n");
}
else
{
  $cts->add_title(2, "Mon planet à moi");
  $_tags = new requete($site->db,
                       "SELECT `planet_tags`.`tag`, `planet_flux_tags`.`id_flux`, `planet_flux`.`nom`, `planet_flux`.`url` ".
                       "FROM `planet_user_tags` ".
                       "INNER JOIN `planet_flux_tags` USING(`id_tag`) ".
                       "INNER JOIN `planet_tags` ON `planet_user_tags`.`id_tag` = `planet_tags`.`id_tag` ".
                       "INNER JOIN `planet_flux` ON `planet_flux_tags`.`id_flux`=`planet_flux`.`id_flux` ".
                       "LEFT JOIN `planet_user_flux` ON `planet_flux_tags`.`id_flux`=`planet_user_flux`.`id_flux` ".
                       "WHERE `planet_user_tags`.`id_utilisateur`='".$site->user->id."' ".
                       "AND (`planet_user_flux`.`view` IS NULL OR `planet_user_flux`.`view`!='0') ".
                       "AND `planet_flux`.`modere`='1' ".
                       "GROUP BY `planet_flux_tags`.`id_flux`");
  $totflux=$_tags->lines;
  $tags=array();
  $flux=array();
  $j=0;
  while(list($tag,$id_flux,$nom_flux,$url_flux)=$_tags->get_row())
  {
    if(!isset($tags[$tag]))
      $tags[$tag]=array();
    $tags[$tag][]=$id_flux;
    $flux[$id_flux]['nom_flux']=$nom_flux;
    $flux[$id_flux]['url_flux']=$url_flux;
  }
  $_flux = new requete($site->db,
                       "SELECT `planet_flux`.`id_flux`, `planet_flux`.`nom`, `planet_flux`.`url` ".
                       "FROM `planet_user_flux` ".
                       "INNER JOIN `planet_flux` ON `planet_user_flux`.`id_flux`=`planet_flux`.`id_flux` ".
                       "WHERE `planet_user_flux`.`view`='1' AND `planet_flux`.`modere`='1'");
  $totflux=$totflux+$_flux->lines;
  while(list($id_flux,$nom_flux,$url_flux)=$_flux->get_row())
  {
    if(!isset($flux[$id_flux]))
    {
      if(!isset($tags['Reste']))
        $tags['Reste']=array();
      $tags['Reste'][]=$id_flux;
      $flux[$id_flux]['nom_flux']=$nom_flux;
      $flux[$id_flux]['url_flux']=$url_flux;
    }
  }
  if(count($totflux)==0)
  {
    $cts->add_paragraph("Vous n'êtes inscrits à aucun flux (actifs) pour le moment.");
  }
  else
  {
    $cts->add_paragraph("le planet est à la galaxy ce qu'est le la tour effel à la patate ... cherhez pas y'a pas de logique ...");
    foreach($tags AS $tag => $_flux)
    {
      $content=array();
      $num=0;
      foreach($_flux AS $id_flux)
      {
        if($num==MAX_NUM)
          break;
        if($rs=fetch_rss($flux[$id_flux]['url_flux']))
        {
          if(count($rs->items)>0)
          {
            foreach($rs->items as $item)
            {
              if($num==MAX_NUM)
                break;
              if(!isset($content[$item['date_timestamp']]))
                $content[$item['date_timestamp']]=array();
              $content[$item['date_timestamp']][]=array('title'=>$item['title'],'content'=>$item['content']['encoded']);
              $num++;
            }
          }
        }
      }
      if(count($content)>0)
      {
        $site->add_contents($cts);
        $cts = new contents("Tag : ".$tag);
        foreach($content AS $date => $items)
        {
          $published = $date;
          foreach($items AS $item)
          {
						$cts->add_title(3, $item['title']." (le "./*date("d/m/Y h:i:s", */$published/*)*/.")");
            $cts->puts($item['content']);
          }
        }
      }
    }
  }
}

$site->add_contents($cts);

$site->end_page ();

?>

