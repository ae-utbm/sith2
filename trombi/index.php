<?php

/* Copyright 2007
 *
 * - Sebastien WATTIEZ < webast2 at gmail dot com >
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


require_once($topdir."comptoir/include/defines.inc.php");

include($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/special.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/cts/gallery.inc.php");
require_once($topdir. "include/cts/special.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir . "include/entities/ville.inc.php");
require_once($topdir . "include/entities/pays.inc.php");
require_once($topdir . "include/graph.inc.php");

$site = new site();

$site->add_css("css/userfullinfo.css");

if (!$site->user->id)
  error_403();

$site->start_page ("none", "Trombi AE ");

$tabs = array(array("","trombi/index.php", "Informations"),
              //array("board","trombi/index.php?view=board", "Messages"),
              array("listing","trombi/index.php?view=listing", "La promo"),
              array("stats","trombi/index.php?view=stats", "Des chiffres")
             );
$cts = new contents("Trombinoscope, promo ".$site->user->promo_utbm);
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if (isset($_REQUEST['id_utilisateur']))
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  
  if (!$user->is_valid())
    $site->error_not_found("matmatronch");
    
  $can_edit = ($user->id==$site->user->id || $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));
  
  if ($user->id != $site->user->id && !$site->user->utbm && !$site->user->ae)
    $site->error_forbidden("matmatronch","group",10001);
 
  if (!$user->publique && !$can_edit)
    $site->error_forbidden("matmatronch","private");
  if(!$user->promo_utbm != $site->user->promo_utbm && $site->user->is_in_group("gestion_ae"))
  {
    $user = &$site->user;
    $can_edit = true;
  }
}
else
{
  $user = &$site->user;
  $can_edit = true;
}

if(isset($_REQUEST["stats"]))
{
  if($_REQUEST["stats"]=="sexe")
  {
    $req = new requete($site->db,
                       "SELECT `utilisateurs`.`sexe_utl`, COUNT(`utilisateurs`.`sexe_utl`) ".
                       "FROM `utl_etu_utbm` ".
                       "LEFT JOIN `utilisateurs` USING (`id_utilisateur`) ".
                       "WHERE `promo_utbm`='" . $site->user->promo_utbm . "' ".
                       "GROUP BY `utilisateurs`.`sexe_utl`");
    $cam=new camembert(600,400,array(),2,0,0,0,0,0,0,10,150);
    while(list($sexe,$nb)=$req->get_row())
    {
      if($sexe==1)
        $cam->data($nb, "Homme");
      elseif($sexe=="2")
        $cam->data($nb, "Femme");
    }
    $cam->png_render();
    exit();
  }
  elseif($_REQUEST["stats"]=="departements")
	{
    $cam=new camembert(600,400,array(),2,0,0,0,0,0,0,10,150);
    $req = new requete($site->db,
                       "SELECT `branche_utbm` , COUNT( `branche_utbm` ) ".
                       "FROM `utl_etu_utbm` ".
                       "WHERE `promo_utbm` = '6'".
                       "GROUP BY `branche_utbm`");
    while(list($branche,$nb)=$req->get_row())
      $cam->data($nb, $branche);
    $cam->png_render();
    exit();
  }
}


if($_REQUEST["view"] == "listing")
{
  $site->add_css("css/mmt.css");
  $npp=18;
  $page = intval($_REQUEST["page"]);

  if ( $page)
    $st=$page*$npp;
  else
    $st=0;
  $reqnb = new requete($site->db,
                       "SELECT COUNT(`utilisateurs`.`id_utilisateur`) "
                       ."FROM `utl_etu_utbm` "
                       ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                       ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                       ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                       ."AND `publique_utl`='1'");
  list($nb) = $reqnb->get_row();

  $req = new requete($site->db,
                   "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.* "
                   ."FROM `utl_etu_utbm` "
                   ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                   ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                   ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                   ."AND `publique_utl`='1' "
                   ."ORDER BY `nom_utl`, `prenom_utl` ASC "
                   ."LIMIT ".$st." , ".$npp."");
  if ($req->lines == 0)
    $tbl = new error("Aucun resultat","");
  else
  {
    $gal = new gallery();
    $tmpuser = new utilisateur($site->db);
    while ( $row = $req->get_row() )
    {
      $tmpuser->_load_all($row);
      $gal->add_item(new userinfov2($tmpuser, "small", false, "trombi/index.php"));
    }
    $cts->add($gal);
    if ( $nb > $npp )
    {
      $tabs = array();
      $i=0;
      while ( $i < $nb )
      {
        $n = $i/$npp;
        $url = "";
        $ar = array_merge($_GET,$_POST);
        $ar["page"] = $n;
        foreach ( $ar as $key => $value )
        {
          if( $key != "magicform" && $value && $key != "mmtsubmit" )
          {
            if ( $url )
              $url .= "&";
            else
              $url = "trombi/index.php?";
            if ( !is_array($value) )
              $url .= $key."=".rawurlencode($value);
          }
        }
        $tabs[]=array($n,$url,$n+1 );
        $i+=$npp;
      }
      $cts->add(new tabshead($tabs, $page, "_bottom"));
    }
  }
}
elseif($_REQUEST["view"]=="stats")
{
  $cts->add_paragraph("Des stats, des stats, oui mais des panzanni !");
  $site->add_contents($cts);
  $cts = new contents("Répartition Homme/Femme dans la promo");
  $cts->add_paragraph("<center><img src=\"index.php?stats=sexe\" alt=\"répartition Homme/Femme\" /></center>\n");
  $site->add_contents($cts);
  $cts = new contents("Répartition par départements");
  $cts->add_paragraph("<center><img src=\"index.php?stats=departements\" alt=\"répartition par départements\" /></center>\n");
}
else
{
  $cts->add_title(2, "Informations personnelles");
  $info = new userinfov2($user,"full",$site->user->is_in_group("gestion_ae"), "trombi/index.php");
  $cts->add($info);
}

$site->add_contents($cts);


$site->end_page ();

?>

