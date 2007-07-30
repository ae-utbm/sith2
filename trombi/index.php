<?php

/* Copyright 2007
 *
 * - Sebastien WATTIEZ < webast2 at gmail dot com >
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
require_once($topdir . "include/entities/carteae.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");
require_once($topdir . "include/entities/ville.inc.php");
require_once($topdir . "include/entities/pays.inc.php");


$site = new site();

$site->add_css("css/userfullinfo.css");

if (!$site->user->id)
  error_403();

$site->start_page ("none", "Trombi AE ");

$cts = new contents("Informations personnelles");

$tabs = array(array("","index.php", "Informations"),
              array("board","index.php?view=board", "Messages"),
              array("listing","index.php?view=listing", "Version papier"),
             );

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
}
else
{
  $user = &$site->user;
  $can_edit = true;
}

$info = new userinfov2($user,"full",$site->user->is_in_group("gestion_ae"));
$cts->add($info);
$site->add_contents($cts);

/*
$cts->puts('<br/>');
$cts->add_title(2,"TODO");
$cts->puts('-home<br/>-onglets<br/>-listing membre promo alphabetique<br/>');
 */

$cts = new contents("Liste des membres de la promo ".$site->user->promo_utbm);
$req = new requete($site->db,
                   "SELECT `id_utilisateur`, `promo_utbm`, "
                  ."CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) AS `nom_utilisateur` "
                  ."FROM `utl_etu_utbm` "
                  ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                  ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                  ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                  ."ORDER BY `nom_utl`, `prenom_utl` ASC "
                  ."LIMIT 0 , 30");
                  
if ($req->lines == 0)
{
  $tbl = new error("Aucun resultat","");
}
else
{
  $tbl = new sqltable("listresult",
                      "Liste des promo " . $site->user->promo_utbm,
                      $req,
                      "index.php",
                      "id_utilisateur", 
                      array("nom_utilisateur"=>"Nom"),
                      array(), 
                      array(), 
                      array()
                     );
}



$cts->add($tbl,true);
 
$site->add_contents($cts);

$site->end_page ();

?>

