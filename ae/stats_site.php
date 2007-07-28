<?php
/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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
require_once($topdir . "include/cts/sqltable.inc.php");
require_once($topdir . "include/graph.inc.php");
$site = new site ();

if (!$site->user->is_in_group ("gestion_ae"))
  error_403();

$color=array(0 => "FFF200",
//             1 => "FFE700",
//             2 => "FFCD00",
//             3 => "FFC600",
             4 => "FFB000",
//             5 => "FFA500",
//             6 => "FF9A00",
//             7 => "FF8F00",
             8 => "FF8400",
//             9 => "FF7900",
//             10=> "FF7200",
//             11=> "FF6500",
             12=> "FF5A00",
//             13=> "FF5000",
//             14=> "FF4400",
//             15=> "FF3A00",
             16=> "FF2F00",
//             17=> "FF2400",
//             18=> "FF1A00",
//             19=> "FF1000",
             20=> "FF0000");

function dec2hex($val)
{
  $hex="";
  for($i=0; $i<3; $i++)
  {
    $temp = dechex($val[$i]);
    if(strlen($temp) < 2)
      $hex .= "0". $temp;
    else
      $hex .= $temp;
  }
  return $hex;
}

if ( $_REQUEST["action"] == "os" )
{
  $color=array(0=>255,1=>255,2=>255);
  $_color="#ffffff";
  $inc=50;
  $req = new requete($site->db,"SELECT * FROM `stats_os`  ORDER BY `visites` DESC");
  $cam=new camembert(600,500,array(),2,20,0,10,0.25,10,10,10,150);
  while($row=$req->get_row())
  {
    $cam->data($row['visites'], $_color, $row['os']);
    if($i==1)
    {
      if($color[0]!=0)
      {
        $color[0]=$color[0]-$inc;
        if($color[0]<0)
          $color[0]=0;
      }
      elseif($color[1]!=0)
      {
        $color[1]=$color[1]-$inc;
        if($color[1]<0)
          $color[1]=0;
      }
      elseif($color[2]!=0)
      {
        $color[2]=$color[2]-$inc;
        if($color[2]<0)
          $color[2]=0;
      }
      else
        $i=0;
      $_color=dec2hex($color);
    }
    if($i==0)
    {
      if($color[2]!=255)
      {
        $color[2]=$color[2]+$inc;
        if($color[2]>255)
          $color[2]=255;
      }
      elseif($color[0]!=255)
      {
        $color[0]=$color[0]+$inc;
        if($color[0]>255)
          $color[0]=255;
      }
      elseif($color[1]!=255)
      {
        $color[1]=$color[1]+$inc;
        if($color[1]>255)
          $color[1]=255;
      }
      else
      {
        $color[0]=$color[0]-$inc;
        $i=1;
      }
      $_color=dec2hex($color);
    }
  }
  $cam->png_render();
  exit();
}
if ( $_REQUEST["action"] == "browser" )
{
  $color=array(0=>255,1=>255,2=>255);
  $_color="#ffffff";
  $inc=50;
  $req = new requete($site->db,"SELECT * FROM `stats_browser`  ORDER BY `visites` DESC");
  $cam=new camembert(600,500,array(),2,20,0,10,0.25,10,10,10,150);
  $i=20;
  while($row=$req->get_row())
  {
    $cam->data($row['visites'], $_color, $row['os']);
    if($i==1)
    {
      if($color[0]!=0)
      {
        $color[0]=$color[0]-$inc;
        if($color[0]<0)
          $color[0]=0;
      }
      elseif($color[1]!=0)
      {
        $color[1]=$color[1]-$inc;
        if($color[1]<0)
          $color[1]=0;
      }
      elseif($color[2]!=0)
      {
        $color[2]=$color[2]-$inc;
        if($color[2]<0)
          $color[2]=0;
      }
      else
        $i=0;
      $_color=dec2hex($color);
    }
    if($i==0)
    {
      if($color[2]!=255)
      {
        $color[2]=$color[2]+$inc;
        if($color[2]>255)
          $color[2]=255;
      }
      elseif($color[0]!=255)
      {
        $color[0]=$color[0]+$inc;
        if($color[0]>255)
          $color[0]=255;
      }
      elseif($color[1]!=255)
      {
        $color[1]=$color[1]+$inc;
        if($color[1]>255)
          $color[1]=255;
      }
      else
      {
        $color[0]=$color[0]-$inc;
        $i=1;
      }
      $_color=dec2hex($color);
    }
  }
  $cam->png_render();
  exit();
}

$site->start_page ("none", "statistiques du site");

$cts = new contents("Classement");

if ( $_REQUEST["action"] == "reset" )
{
  $req = new requete($site->dbrw, "DELETE FROM `stats_page` WHERE `page`!=''");
  $req = new requete($site->dbrw, "DELETE FROM `stats_os` WHERE `os`!=''");
  $req = new requete($site->dbrw, "DELETE FROM `stats_browser` WHERE `browser`!=''");
  $cts->add_title(2, "Reset");
  $cts->add_paragraph("Le reset des stats a &eacute;t&eacute; effectu&eacute; avec succ&egrave;s");
}

$cts->add_title(2, "Administration");
$cts->add_paragraph("Remettre &agrave; z&eacute;ro les stats du site ae.".
                    "<br /><img src=\"".$topdir."images/actions/delete.png\"><b>ATTENTION CECI EST IRREVERSIBLE</b> : <a href=\"stats_site.php?action=reset\">Reset !</a>");

$req = new requete($site->db,"SELECT * FROM `stats_page`  ORDER BY `visites` DESC");


$cts->add(new sqltable("top_full",
                       "Pages visit&eacute;es visit&eacute;s", $req, "stats.php",
                       "page",
                       array("=num" => "N°",
                             "page"=>"page",
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
                      ),true);
$site->add_contents($cts);
/*
$req = new requete($site->db,"SELECT * FROM `stats_browser`  ORDER BY `visites` DESC");
$cts->add(new sqltable("top_full",
                       "Navigateurs utilis&eacute;s", $req, "stats.php",
                       "browser",
                       array("=num" => "N°",
                             "browser"=>"Navigateur",
                             "visites"=>"Total"),
                       array(),
                       array(),
                       array()
         ),true);
 */
$cts = new contents("Navigateurs utilis&eacute;s");
$cts->add_paragraph("<center><img src=\"stats_site.php?action=browser\" alt=\"navigateurs utilis&eacute;s\" /></center>\n");
$site->add_contents($cts);
/*
$req = new requete($site->db,"SELECT * FROM `stats_os`  ORDER BY `visites` DESC");
$cts->add(new sqltable("top_full",
                       "Syst&egrave;mes d'exploitation utilis&eacute;s", $req, "stats.php",
                       "id_utilisateur",
                       array("=num" => "N°",
                             "os"=>"Syst&egrave;me d'exploitation",
                             "visites"=>"Total"),
                       array(),
                       array(),
                       array()
         ),true);
 */
$cts = new contents("Syst&egrave;mes d'exploitation utilis&eacute;s");
$cts->add_paragraph("<center><img src=\"stats_site.php?action=os\" alt=\"syst&egrave;mes d'exploitation utilis&eacute;s\" /></center>\n");
$site->add_contents($cts);

$site->end_page ();

?>
