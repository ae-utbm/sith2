<?php

/* Copyright 2007
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
 
$topdir = "./";
require_once($topdir . "include/graph.inc.php");

$values=explode(";",$_REQUEST["values"]);
if(empty($values))
  exit();

if ( $_REQUEST["action"] == "cam" )
{
  $cam=new camembert(600,400);
  foreach($values as $value)
  {
    $value=explode("|", $value, 2);
    if(count($value)==2)
      $cam->data($value[1], $value[0]);
    else
      exit();
  }
  $cam->png_render();
  exit();
}

if ( $_REQUEST["action"] == "bar" )
{
  $datas = array("Nom" => "Pourcentage");
  foreach($values as $value)
  {
    $value=explode("|", $value, 2);
    if(count($value)==2)
      $datas[$value[0]]=$value[1];
    else
      exit();
  }
  $hist = new histogram($datas,"");
  $hist->png_render();
  $hist->destroy();
  exit();
}

?>
