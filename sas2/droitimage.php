<?php
/* Copyright 2006
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
require_once("include/sas.inc.php");
require_once($topdir."include/cts/gallery.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/page.inc.php");
$site = new sas();
$site->add_css("css/sas.css");

$site->allow_only_logged_users("sas");

$photo = new photo($site->db,$site->dbrw);

if ( $_REQUEST["action"] == "setaccordphoto" )
{
	$photo->load_by_id($_REQUEST["id_photo"]);
  if ( $photo->id > 0 && $photo->is_on_photo($site->user->id))
	{
    if ( $_REQUEST["mesure"] == "ok" )
		{
      $photo->donne_accord($site->user->id);
    }
    elseif ( $_REQUEST["mesure"] == "retrait" )
    {
      $photo->remove_photo();
    }
    elseif ( $_REQUEST["mesure"] == "notonphoto" )
    {
      $photo->remove_personne($site->user->id);
    }
    else
    {
      $photo->donne_accord($site->user->id);
      require_once($topdir."include/entities/group.inc.php");
      $groups = enumerates_groups($site->db);
      $groupss = array_keys ( $groups, $_REQUEST["mesure"]);
      if ( count($groupss) > 0 )
      {
        $photo->id_groupe = $groupss[0];
        $photo->droits_acces=0x310;
        $photo->save_rights();
      }
    }
  }
}


if ( $_REQUEST["page"] == "process" )
{
  $sql = new requete($site->db,
  "SELECT sas_photos.* " .
  "FROM sas_personnes_photos " .
  "INNER JOIN sas_photos ON (sas_photos.id_photo=sas_personnes_photos.id_photo) " .
  "WHERE sas_personnes_photos.id_utilisateur=".$site->user->id." " .
  "AND sas_personnes_photos.accord_phutl=0 " .
  "AND (droits_acces_ph & 0x100) " .
  "ORDER BY sas_photos.id_photo " .
  "LIMIT 1");

  $site->start_page("sas","Droit à l'image",true);
  $cts = new contents("Droit à l'image");



  if ( $sql->lines == 1)
  {
    $photo->_load($sql->get_row());

    $cat = new catphoto($site->db);
    $catpr = new catphoto($site->db);
    $cat->load_by_id($photo->id_catph);
    $path = classlink($cat)." / ".classlink($photo);
    $catpr->load_by_id($cat->id_catph_parent);
    while ( $catpr->id > 0 )
    {
      $path = classlink($catpr)." / ".$path;
      $catpr->load_by_id($catpr->id_catph_parent);
    }
    $cts->add_title(2,$path);

    $site->user->load_all_extra();
    $imgcts = new contents();
    $imgcts->add(new image($photo->id,"images.php?/".$photo->id.".diapo.jpg"));
    $cts->add($imgcts,false,true,"sasimg");

    $subcts = new contents();

    if ( ($photo->droits_acces & 1) == 0 )
    {
      require_once($topdir."include/entities/group.inc.php");
      $groups = enumerates_groups($site->db);
      $subcts->add_paragraph("L'accés à cette photo est limité à ".$groups[$photo->id_groupe]);
    }


    $frm = new form("droitphoto","droitimage.php?page=process",false,"POST","Votre souhait");
    $frm->add_hidden("action","setaccordphoto");
    $frm->add_hidden("id_photo",$photo->id);

    $frm->add_radiobox_field (  "mesure","",array("ok"=>"Accord en gardant les droits d'accés tel quel."),"ok");
    $frm->add_radiobox_field (  "mesure","",array("utbm"=>"Accord mais limiter l'accés aux personnes de l'UTBM."),"ok");

    if ( $site->user->promo_utbm )
      $frm->add_radiobox_field (  "mesure","",array(sprintf("promo%02d",$site->user->promo_utbm)=>"Accord mais limiter l'accés à la promo ".$site->user->promo_utbm).".","ok");

    $frm->add_radiobox_field (  "mesure","",array("retrait"=>"Retrait du SAS de la photo. (Attention, ceci est irreversible)."),"ok");
    $frm->add_radiobox_field (  "mesure","",array("notonphoto"=>"Je ne suis pas sur cette photo."),"ok");


    $frm->add_submit("set","Valider");
    $subcts->add($frm,true);

    $cts->add($subcts,false,true,"photoinfo");
    $cts->puts("<div class=\"clearboth\"></div>");




  }
  else
  {
    $cts->add_paragraph("Merci, toutes les photos ont été passés en revue.");
    $cts->add_paragraph("<a href=\"./\">Retour au SAS</a>");
  }

  $site->add_contents($cts);
  $site->end_page ();


  exit();
}


if ( isset($_REQUEST["setdroit"]) )
{
  $site->user->set_droit_image ( $_REQUEST["droitauto"]=="oui" );
}


$site->start_page("sas","Droit à l'image");
$cts = new contents("Droit à l'image");

$frm = new form("auto","droitimage.php",false,"POST","Accord systèmatique");

$frm->add_radiobox_field (  "droitauto",
    "",
    array("non"=>"Ne pas accorder automatiquement", "oui"=>"Accorder systèmatiquement mon droit à l'image"),
    $site->user->droit_image?"oui":"non",
    false,
    true);

$frm->add_submit("setdroit","Enregistrer");
$cts->add($frm,true);


$cts->add_title(2,"Photos en attente");
$sql = new requete($site->db,
  "SELECT COUNT(*) " .
  "FROM sas_personnes_photos " .
  "INNER JOIN sas_photos ON (sas_photos.id_photo=sas_personnes_photos.id_photo) " .
  "WHERE sas_personnes_photos.id_utilisateur=".$site->user->id." " .
  "AND sas_personnes_photos.accord_phutl=0 " .
  "AND (droits_acces_ph & 0x100) " .
  "ORDER BY sas_photos.id_photo");
list($count) = $sql->get_row();
$cts->add_paragraph("<a href=\"droitimage.php?page=process\">$count photo(s) en attente</a>");

$site->add_contents($cts);
$site->end_page ();

?>
