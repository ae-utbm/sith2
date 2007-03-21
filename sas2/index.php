<?php
/* Copyright 2004-2006
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
require_once($topdir. "include/page.inc.php");
require_once($topdir. "include/assoclub.inc.php");
$site = new sas();

if ( $site->user->id < 1 )
  error_403("connect");

$photo = new photo($site->db,$site->dbrw);
$cat = new catphoto($site->db,$site->dbrw);
$metacat = new catphoto($site->db);
$catpr = new catphoto($site->db);
$asso = new asso($site->db);

if ( isset($_REQUEST["id_photo"]))
{
  $photo->load_by_id($_REQUEST["id_photo"]);
  if ( $photo->id < 1 )
  {
    header("Location: ../404.php");
    exit();
  }
  $cat->load_by_id($photo->id_catph);
}
if ( isset($_REQUEST["id_catph"]))
  $cat->load_by_id($_REQUEST["id_catph"]);
elseif ( $cat->id <= 0 )
  $cat->load_by_id(1);

if ( isset($_REQUEST["meta_id_catph"]))
  $metacat->load_by_id($_REQUEST["meta_id_catph"]);

if ( $cat->id < 1 )
{
  header("Location: ../404.php");
  exit();
}

if ( !$cat->is_right($site->user,DROIT_LECTURE) )
  error_403();

if ( $metacat->id > 0 && !$metacat->is_right($site->user,DROIT_LECTURE) )
  unset($metacat->id);

if ( $photo->id > 0 && !$photo->is_right($site->user,DROIT_LECTURE) )
  error_403();

$site->add_css("css/sas.css");



$grps = $site->user->get_groups_csv();


if ( $_REQUEST["action"] == "addphoto" && $GLOBALS["svalid_call"] )
{
  $_REQUEST["view"] = "add";

  if ( !is_uploaded_file($_FILES['file']['tmp_name']) ||
    ($_FILES['file']['error'] != UPLOAD_ERR_OK) )
    $ErreurUpload = "Erreur lors du transfert du fichier.";
  elseif ( !$cat->is_right($site->user,DROIT_AJOUTITEM) )
    $ErreurUpload = "Vous n'avez pas les droits requis.";
  else
  {
    $asso->load_by_id($_REQUEST["id_asso"]);
    if ( $asso->id < 1 )
      $asso->id = null;
            
    $photo->herit($cat,false);
    $photo->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin'],false);
    $photo->add_photo ( $_FILES['file']['tmp_name'], $cat->id, $_REQUEST['comment'], null, $_REQUEST['personne'],$asso->id);

  }
}
elseif ( $_REQUEST["action"] == "delete" && $photo->id > 0 && !$_REQUEST["id_utilisateur"] )
{
  if ( $photo->is_right($site->user,DROIT_ECRITURE) )
  {
    $photo->remove_photo();
    $photo->id=-1;
  }
}
elseif ( $_REQUEST["action"] == "sethome" && $photo->id > 0 && $cat->id > 0 )
{
  if ( $cat->is_right($site->user,DROIT_ECRITURE) )
    if ( $photo->droits_acquis && ($photo->droits_acces & 1))
    {
      $cat->set_photo($photo->id);
      $photo->id=-1;
    }

}
elseif ( $_REQUEST["action"] == "delete" && $cat->id > 0 && !$_REQUEST["id_photo"] )
{
  if ( $cat->is_right($site->user,DROIT_ECRITURE) )
  {
    if ( $site->is_sure ( "","Suppression de la catégorie ".$cat->nom,"ctph".$cat->id, 1 ) )
    {
      $cat->remove_cat();
      $cat->load_by_id($cat->id_catph_parent);
    }
  }
}
if ($photo->id > 0)
  $path =   classlink($cat)." / ".classlink($photo);
else
  $path =   classlink($cat);

if ( $metacat->id > 0 )
{
  $catpr->load_by_id($metacat->id);
  $cat->set_meta($metacat);
  $self="./?meta_id_catph=".$metacat->id."&";
  $selfhtml="./?meta_id_catph=".$metacat->id."&amp;";
  $path = str_replace("/?","/?meta_id_catph=".$metacat->id."&amp;",$path);
}
else
{
  $catpr->load_by_id($cat->id_catph_parent);
  $self="./?";
  $selfhtml="./?";
}

$root_asso_id = null;

while ( $catpr->id > 0 )
{
  if ( is_null($root_asso_id) && $catpr->meta_mode == CATPH_MODE_META_ASSO )
    $root_asso_id = $catpr->meta_id_asso;
    
  $path =   classlink($catpr)." / ".$path;
  $catpr->load_by_id($catpr->id_catph_parent);
}


/*
 * Photos
 */

if ( $_REQUEST["action"] == "rertraitphoto" && $photo->id > 0 && $photo->is_on_photo($site->user->id) )
{
  if ( $_REQUEST["mesure"] == "retrait" )
  {
    $photo->remove_photo();
    $photo->id=-1;
  }
  elseif ( $_REQUEST["mesure"] == "notonphoto" )
  {
    $photo->remove_personne($site->user->id);
  }
  else
  {
    $photo->donne_accord($user->id);
    require_once($topdir."include/group.inc.php");
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

if ( $photo->id > 0 )
{
  $sqlph = $cat->get_photos ( $cat->id, $site->user, $grps, "sas_photos.id_photo");
  $count=0;
  while ( list($id) = $sqlph->get_row() )
  {
    if ( $id == $photo->id ) $idx = $count;
    $photos[] = $id;
    $count++;
  }

  $can_write = $photo->is_right($site->user,DROIT_ECRITURE);
  $can_comment = $can_write; //|| $photo->is_on_photo($site->user->id);


  if ( ($_REQUEST["action"] == "addpersonne") && $can_write)
  {
    $utl = new utilisateur($site->db);
    $utl->load_by_id($_REQUEST["id_utilisateur"]);
    if ( $utl->id > 0 )
    {
      $photo->add_personne($utl,true);
      $Message="Personne ajout&eacute;e : ".classlink($utl);
    }
    else
      $ErrorPersonne="Personne inconnue...";

  }
  elseif ( ($_REQUEST["action"] == "setfull") && $can_write)
  {
    $photo->set_incomplet(false);
    $Message="La liste des personnes a été marquée comme complète.";
  }
  elseif ( ($_REQUEST["action"] == "delete") && $can_write)
  {
    $photo->remove_personne($_REQUEST["id_utilisateur"]);
    $Message="Personne supprimée.";
  }
  elseif ( ($_REQUEST["action"] == "updatephoto") && $can_write)
  {
    $photo->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin'],false);
    $photo->update_photo(
      $_REQUEST["date"],
      $_REQUEST["comment"],
      NULL,
      $_REQUEST["id_asso"]);

    $photo->set_incomplet(isset($_REQUEST["incomplet"]));
  }
  elseif ( ($_REQUEST["action"] == "setcomment") && $can_comment)
  {
    $photo->set_comment ( $_REQUEST["commentaire"], $can_write);
  }
  elseif ( $_REQUEST["action"] == "suggestpersonne" )
  {
    $utl = new utilisateur($site->db);
    $utl->load_by_id($_REQUEST["id_utilisateur"]);
    if ( $utl->id > 0 )
    {
      $photo->add_personne($utl,false);
      $Message="Personne ajout&eacute;e comme suggestion : ".classlink($utl);;
    }
    else
      $ErrorSuggest="Personne inconnue...";

  }

  if ( ($_REQUEST["page"] == "edit" || $_REQUEST["action"] == "edit") && $can_write )
  {
    $site->start_page("sas","Stock à Souvenirs");

    $cts = new contents($path." / Editer");

    $frm = new form("updatephoto","./?id_photo=".$photo->id);
    $frm->add_hidden("action","updatephoto");
    $frm->add_datetime_field("date","Date et heure de prise de vue",$photo->date_prise_vue);
    $frm->add_text_area("comment","Commentaire",$photo->commentaire);
    $frm->add_checkbox("incomplet","Liste des personnes incomplète",$photo->incomplet);
    $frm->add_entity_select ( "id_asso", "Association/Club lié", $site->db, "asso",$photo->meta_id_asso,true);
    $frm->add_rights_field($photo,false,$photo->is_admin($site->user));
    $frm->add_submit("valid","Enregistrer");

    $cts->add($frm);

    $site->add_contents($cts);

    $site->end_page ();
    exit();
  }
  elseif ( $_REQUEST["page"] == "askdelete" )
  {
    $site->start_page("sas","Stock à Souvenirs");

    $cts = new contents($path." / Demande de retrait");

    if ( $photo->is_on_photo($site->user->id) )
    {

      $frm = new form("droitphoto","./?id_photo=".$photo->id."&id_catph=".$cat->id,false,"POST","Votre souhait");
      $frm->add_hidden("action","rertraitphoto");
      $frm->add_hidden("id_photo",$photo->id);

      $frm->add_radiobox_field (  "mesure","",array("utbm"=>"Limiter l'accés aux personnes de l'UTBM."),"ok");

      if ( $site->user->promo_utbm )
        $frm->add_radiobox_field (  "mesure","",array(sprintf("promo%02d",$site->user->promo_utbm)=>"Limiter l'accés à la promo ".$site->user->promo_utbm).".","ok");

      $frm->add_radiobox_field (  "mesure","",array("retrait"=>"Retrait du SAS de la photo. (Attention, ceci est irreversible)."),"ok");
      $frm->add_radiobox_field (  "mesure","",array("notonphoto"=>"Je ne suis pas sur cette photo."),"ok");


      $frm->add_submit("set","Valider");

      $cts->add($frm,true);
    }
    else
      $cts->add_paragraph("Vous n'avez pas été identifitié sur la photo, pour en demander le retrait contactez l'AE");

    $site->add_contents($cts);

    $site->end_page ();
    exit();
  }

  $phasso = new asso($site->db);
  if ( $photo->meta_id_asso )
    $phasso->load_by_id($photo->meta_id_asso);


  $cts = new contents($path);

  $imgcts = new contents();

  $imgcts->add(new image($photo->id,"images.php?/".$photo->id.".diapo.jpg"));

  $cts->add($imgcts,false,true,"sasimg");

  if ( $metacat->id > 0 )
    $exdata="meta_id_catph=".$metacat->id."&";
  else
    $exdata="";

  if ( $_REQUEST["fetch"] == "script" )
  {
    echo "openInContents( 'cts1', './', '".$exdata."id_photo=".$photo->id."&fetch=cts1');";
    if ( $_REQUEST["diaporama"] > 0 && ( $idx != $count-1 ) )
    {
      echo "cache5.src=\"images.php?/".$photos[$idx+1].".diapo.jpg\";\n";
      echo "setTimeout(\"evalCommand('./', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=script&diaporama=".intval($_REQUEST["diaporama"])."')\", ".intval($_REQUEST["diaporama"]).");";
    }
    exit();
  }

  if ( $_REQUEST["diaporama"] > 0 && ( $idx != $count-1 ) )
  {
    $cts->puts("<script>" .
        "cache1= new Image(); cache1.src=\"".$topdir."images/to_prev.png\";".
        "cache2= new Image(); cache2.src=\"".$topdir."images/to_next.png\";".
        "cache3= new Image(); cache3.src=\"".$topdir."images/icons16/catph.png\";".
        "cache4= new Image(); cache4.src=\"".$topdir."images/icons16/photo.png\";".
        "cache5= new Image(); cache5.src=\"images.php?/".$photos[$idx+1].".diapo.jpg\";".
        "cache6= new Image(); cache6.src=\"".$topdir."images/user.png\";".
        "cache7= new Image(); cache7.src=\"".$topdir."images/actions/delete.png\";");
    $cts->puts("setTimeout(\"evalCommand('./', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=script&diaporama=".intval($_REQUEST["diaporama"])."')\", ".intval($_REQUEST["diaporama"]).");");
    $cts->puts("</script>");
  }

  $subcts = new contents();
/*  
  $subcts->puts("<p>");
  if ( $idx != 0 )
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx-1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx-1]."&fetch=cts1'); return false;\"><img src=\"".$topdir."images/to_prev.png\" border=\"0\" alt=\"Precedent\" /></a> ");
  if ( $idx != $count-1 )
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx+1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=cts1'); return false;\"><img src=\"".$topdir."images/to_next.png\" border=\"0\" alt=\"Suivant\" /></a> ");
  $subcts->puts("</p>\n");
*/

  $subcts->puts("<div id=\"sasnav\">");

  if ( $idx != 0 )
  {
    $subcts->puts("<div id=\"back\">");
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx-1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx-1]."&fetch=cts1'); return false;\">");
    $subcts->puts("<img src=\"http://ae.utbm.fr/sas2/images.php?/".$photos[$idx-1].".vignette.jpg\" alt=\"Precedent\" class=\"mininav\" />");
    $subcts->puts("<img src=\"../images/to_prev.png\" alt=\"Precedent\" class=\"mininavbtn\" />");
    $subcts->puts("</a>");
    $subcts->puts("</div>");
  }

  if ( $idx != $count-1 )
  {
    $subcts->puts("<div id=\"next\">");
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx+1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=cts1'); return false;\">");
    $subcts->puts("<img src=\"http://ae.utbm.fr/sas2/images.php?/".$photos[$idx+1].".vignette.jpg\" alt=\"Suivant\" class=\"mininav\" />");
    $subcts->puts("<img src=\"../images/to_next.png\" alt=\"Suivant\" class=\"mininavbtn\" />");
    $subcts->puts("</a>");
    $subcts->puts("</div>");
  }

  $subcts->puts("</div>");

  if ( $Message )
  {
    $subcts->add_title(2,"Opération réussie");
    $subcts->add_paragraph($Message);
  }

  $subcts->add_title(2,"Informations");

  if ( !is_null($photo->date_prise_vue) && $photo->date_prise_vue > 3600 )
    $subcts->add_paragraph(date("d/m/Y H:i:s",$photo->date_prise_vue));

  if ( $phasso->id > 0 )
    $subcts->add_paragraph(classlink($phasso));

  if ( $photo->is_admin($site->user) && $photo->id_utilisateur_moderateur )
  {
    $modo = new utilisateur($site->db);
    $modo->load_by_id($photo->id_utilisateur_moderateur);
    $subcts->add_paragraph("Modéré par: ".classlink($modo));  
  }
  if ( $photo->is_admin($site->user) && $photo->id_utilisateur )
  {
    $contrib = new utilisateur($site->db);
    $contrib->load_by_id($photo->id_utilisateur);
    $subcts->add_paragraph("Proposé par: ".classlink($contrib));  
  }
  
  $req = new requete($site->db,
    "SELECT `utilisateurs`.`id_utilisateur`, " .
    "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
    "FROM `sas_personnes_photos` " .
    "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`sas_personnes_photos`.`id_utilisateur` " .
    "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
    "WHERE `sas_personnes_photos`.`id_photo`='".$photo->id."' " .
    "ORDER BY `nom_utilisateur`");


  if ( $can_comment )
  {
    $frm = new form("setcomment",$self."id_photo=".$photo->id,false,"POST","Commentaires");
    $frm->add_hidden("action","setcomment");
    $frm->add_text_area("commentaire","",$photo->commentaire,25,4);
    $frm->add_submit("valid","Enregistrer");
    $subcts->add($frm,true);
  }
  elseif ( $photo->commentaire != "")
  {
    $subcts->add_title(2,"Commentaires");
    $subcts->add_paragraph(htmlentities($photo->commentaire,ENT_NOQUOTES,"UTF-8"));
  }

  $subcts->add(new sqltable(
      "listper",
      "Personnes", $req, $self."id_photo=".$photo->id,
      "id_utilisateur",
      array("nom_utilisateur"=>"Utilisateur"),
      $can_write?array("delete"=>"Supprimer"):array(),
      array(),
      array( )
      ),true);

  if ( $can_write )
  {
    $frm = new form("addpersonne",$self."id_photo=".$photo->id,false,"POST","Ajouter une personne");
    if ( $ErrorPersonne )
      $frm->error($ErrorPersonne);
    $frm->add_hidden("action","addpersonne");
    $frm->add_user_fieldv2("id_utilisateur","");
    $frm->add_submit("valid","Ajouter");
    $subcts->add($frm,true);

    if ( $photo->incomplet )
    {
        $frm = new form("setfull","./?id_photo=".$photo->id,false,"POST","Liste complète");
        $frm->add_hidden("action","setfull");
        $frm->add_info("Toutes les personnes étant sur la photo (au premier plan) sont dans la liste.");
        if ( $req->lines==0 )
          $frm->add_info("ou il n'y a personnes sur cette photo (de reconaissable).");
        $frm->add_submit("valid","Oui");
        $subcts->add($frm,true);
    }

    $subcts->add_title(2,"Outils");
    $subcts->add_paragraph("<a href=\"".$self."id_photo=".$photo->id."&amp;page=edit\">Editer</a>");
    $subcts->add_paragraph("<a href=\"".$self."id_photo=".$photo->id."&amp;action=delete\">Supprimer</a>");
  }
  else
  {
    if ( $photo->incomplet )
    {
      $frm = new form("suggestpersonne",$self."id_photo=".$photo->id,false,"POST","Ajouter une personne");
      $frm->add_hidden("action","suggestpersonne");
      if ( $ErrorSuggest )
        $frm->error($ErrorSuggest);
      $frm->add_info("Vous pouvez ajouter une personne se trouvant sur cette photo. Votre propositon sera cependant soumise à modération.");
      $frm->add_user_fieldv2("id_utilisateur","");
      $frm->add_submit("valid","Ajouter");
      $subcts->add($frm,true);
    }
    $subcts->add_title(2,"Outils");
  }

  $subcts->add_paragraph("<a href=\"images.php?/".$photo->id.".jpg\">Version HD</a>");
  $subcts->add_paragraph("<a href=\"./?id_photo=".$photo->id."&amp;page=askdelete\">Demander le retrait</a>");



  $cts->add($subcts,false,true,"photoinfo");
  $cts->puts("<div class=\"clearboth\"></div>");


  if ( $_REQUEST["fetch"] == "cts1" )
  {
    echo "<h1>".$cts->title."</h1>\n";
    if ( $cts->toolbox )
    {
      echo "<div class=\"toolbox\">\n";
      echo $cts->toolbox->html_render()."\n";
      echo "</div>\n";
    }
    echo $cts->html_render();
    exit();
  }

  $site->start_page("sas","Stock à Souvenirs",true);
  $site->add_contents($cts);
  $site->end_page ();

  exit();
}


if ( $_REQUEST["action"] == "addsubcat" && $cat->is_right($site->user,DROIT_AJOUTCAT) && $GLOBALS["svalid_call"] )
{
  $ErreurAjout=null;

  if ( !$_REQUEST["nom"] )
    $ErreurAjout = "Veuillez précisez un nom";
  elseif ( ($_REQUEST["__rights_add"] & DROIT_AJOUTITEM ) && ($_REQUEST["debut"] > $_REQUEST["fin"] || $_REQUEST["debut"] <= 0) )
    $ErreurAjout = "Dates non valides";
  else
  {
    if ( !$_REQUEST["debut"] )
    {
      $_REQUEST["debut"] = null;
      $_REQUEST["fin"] = null;
    }

    $ncat = new catphoto($site->db,$site->dbrw);
    $ncat->herit($cat,true);
    $ncat->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin'],true);
    $ncat->add_catphoto($cat->id,$_REQUEST["nom"],$_REQUEST["debut"],$_REQUEST["fin"],$_REQUEST["id_asso"],$_REQUEST["mode"]);
    $path .= " / ".classlink($ncat);
    $cat = $ncat;
  }

  if ( $ErreurAjout )
    $_REQUEST["page"] = "subcat";
}
elseif ( $_REQUEST["action"] == "editcat" && $cat->is_right($site->user,DROIT_ECRITURE) && $GLOBALS["svalid_call"] )
{
  if ( !$_REQUEST["nom"] )
    $ErreurEdition = "Veuillez précisez un nom";
  elseif ( ($_REQUEST["__rights_add"] & DROIT_AJOUTITEM ) && ($_REQUEST["debut"] > $_REQUEST["fin"] || $_REQUEST["debut"] <= 0) )
    $ErreurEdition = "Dates non valides";
  else
  {
    if ( !$_REQUEST["debut"] )
    {
      $_REQUEST["debut"] = null;
      $_REQUEST["fin"] = null;
    }

    $photo->load_by_id($_REQUEST["id_photo_index"]);
    if ($photo->id < 1 )
      $photo->id=null;

    $cat->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin'],true);
    $cat->update_catphoto($site->user,$cat->id_catph_parent,$_REQUEST["nom"],$_REQUEST["debut"],$_REQUEST["fin"],$_REQUEST["id_asso"],$_REQUEST["mode"]);

    $cat->set_photo($photo->id);
    $path =   classlink($cat);
    $catpr->load_by_id($cat->id_catph_parent);
    while ( $catpr->id > 0 )
    {
      $path =   classlink($catpr)." / ".$path;
      $catpr->load_by_id($catpr->id_catph_parent);
    }
  }
  if ( $ErreurEdition )
    $_REQUEST["page"] = "edit";
}

if ( ( $_REQUEST["page"] == "edit" || $_REQUEST["action"] == "edit") && $cat->is_right($site->user,DROIT_ECRITURE) )
{
  $site->start_page("sas","Stock à Souvenirs");
  $cts = new contents($path." / Editer");


  $frm = new form("editcat","./?id_catph=".$cat->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","editcat");
  if ( $ErreurEdition )
    $frm->error($ErreurEdition);
  $frm->add_text_field("nom","Nom",$cat->nom,true);
  $frm->add_text_field("id_photo_index","N° de la photo de la miniature",$cat->id_photo,true);
  $frm->add_datetime_field("debut","Date et heure de début",$cat->date_debut,true);
  $frm->add_datetime_field("fin","Date et heure de fin",$cat->date_fin,true);
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",$cat->meta_id_asso,true);
  $frm->add_select_field("mode","Mode",$GLOBALS['catph_modes'],$cat->meta_mode);
  $frm->add_rights_field($cat,true,$cat->is_admin($site->user));
  $frm->add_submit("valid","Enregistrer");

  $cts->add($frm);

  $site->add_contents($cts);
  $site->end_page ();
  exit();
}

if ( $_REQUEST["page"] == "subcat" && $cat->is_right($site->user,DROIT_AJOUTCAT) )
{
  $site->start_page("sas","Stock à Souvenirs");
  $cts = new contents($path." / Nouvelle sous-catégorie");

  $cts->add_paragraph("Remarque: la nouvelle catégorie sera visible des autres utilisateurs dès qu'elle sera modérée.");

  $frm = new form("addsubcat","./?id_catph=".$cat->id);
  $frm->allow_only_one_usage();
  $frm->add_hidden("action","addsubcat");
  if ( $ErreurAjout )
    $frm->error($ErreurAjout);
  $frm->add_text_field("nom","Nom","",true);
  $frm->add_datetime_field("debut","Date et heure de début",-1,true);
  $frm->add_datetime_field("fin","Date et heure de fin",-1,true);
  $frm->add_entity_select("id_asso", "Association/Club lié", $site->db, "asso",false,true);
  $frm->add_select_field("mode","Mode",$GLOBALS['catph_modes'],CATPH_MODE_NORMAL);
  $frm->add_rights_field($cat,true,$cat->is_admin($site->user));
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);

  $site->add_contents($cts);
  $site->end_page ();
  exit();
}

/*
 * Listing catégories
 */
$site->start_page("sas","Stock à Souvenirs");





function cats_produde_gallery ( $sqlct)
{
  global $topdir,$site;
  $scat=new catphoto(null);
  $gal = new gallery(false,"cats",false,false,"id_catph",array("edit"=>"Editer","delete"=>"Supprimer"));
  while ( $row = $sqlct->get_row() )
  {
    $img = $topdir."images/misc/sas-default.png";
    if ( $row['id_photo'] )
      $img = "images.php?/".$row['id_photo'].".vignette.jpg";

    $scat->_load($row);
    $acts=false;
    if ( $scat->is_right($site->user,DROIT_ECRITURE) )
      $acts = array("delete","edit");

    $gal->add_item(
        "<a href=\"./?id_catph=".$row['id_catph']."\"><img src=\"$img\" alt=\"".$row['nom_catph']."\" /></a>",
        "<a href=\"./?id_catph=".$row['id_catph']."\">".$row['nom_catph']."</a>",
        $row['id_catph'],
        $acts);
  }
  return $gal;
}



    
if ( $cat->id == 1 )
{
  $page = new page ($site->db);
  $page->load_by_name("sas-indexinfo");

  if ( $page->id == -1 )
    $site->add_contents(new contents("Bienvenue dans le Stock à Souvenirs (SAS)"));
  else
    $site->add_contents($page->get_contents());



  $cts = new contents("Ajouts r&eacute;cents");

  $cts->add(cats_produde_gallery($cat->get_recent_photos_categories($site->user,$grps)));




  $site->add_contents($cts);

}


if ( $metacat->id > 0 || $cat->meta_mode == CATPH_MODE_META_ASSO || !is_null($root_asso_id) )
{

  if ( $cat->meta_mode == CATPH_MODE_META_ASSO )
    $asso->load_by_id($cat->meta_id_asso); 
  else if ( !is_null($root_asso_id) )
    $asso->load_by_id($root_asso_id); 
  else
    $asso->load_by_id($metacat->meta_id_asso); 
    
  $cts = new contents($asso->nom);

  $cts->add(new tabshead($asso->get_tabs($site->user),"photos"));
  $cts->add_title(1,$path);
}
else
  $cts = new contents($path);

$sqlct = $cat->get_categories($cat->id,$site->user,$grps,"*");

$mode = "simple";

if ( $cat->id != 1 )
{
  $n = 0;
  while ( $row = $sqlct->get_row() )
  {
    $sqlsubct = $cat->get_categories($row['id_catph'],$site->user,$grps,"COUNT(*)");
    list($nb) = $sqlsubct->get_row();
    if ( $nb==0 ) $n++;
  }
  if ( $n == 0 ) $mode = "expand";
  $sqlct->go_first();
}

if ( $cat->is_right($site->user,DROIT_AJOUTCAT) )
  $cts->add_paragraph("<a href=\"./?id_catph=".$cat->id."&amp;page=subcat\">Ajouter une catégorie dans ".$cat->nom."</a>");

if ( $cat->is_right($site->user,DROIT_ECRITURE) )
  $cts->set_toolbox(new toolbox(array("./?id_catph=".$cat->id."&page=edit"=>"Editer")));

if ( $cat->meta_mode == CATPH_MODE_META_ASSO )
{
  $cats=array();

  $req = new requete($site->db,
    "SELECT DISTINCT(sas_photos.id_catph),sas_cat_photos.*, parent.nom_catph as parent_nom_catph " .
    "FROM `sas_photos` ".
    "INNER JOIN sas_cat_photos ON ( sas_photos.id_catph = sas_cat_photos.id_catph ) " .
    "LEFT JOIN sas_cat_photos as parent ON ( parent.id_catph = sas_cat_photos.id_catph_parent ) ".
    "WHERE  sas_photos.meta_id_asso_ph='".$cat->meta_id_asso."' " .
    "AND sas_cat_photos.id_catph!='".$cat->id."' ".
    "AND sas_cat_photos.id_catph_parent!='".$cat->id."' ".    
    "AND parent.id_catph_parent!='".$cat->id."' ".      
    "AND (sas_cat_photos.meta_id_asso_catph!='".$cat->meta_id_asso."' OR sas_cat_photos.meta_id_asso_catph IS NULL)");

  while ( $row = $req->get_row() )
  {

    $req2 = new requete($site->db,
      "SELECT id_photo FROM `sas_photos` ".
      "WHERE meta_id_asso_ph='".$cat->meta_id_asso."' " .
      "AND id_catph='".$row['id_catph']."' " .
      "AND droits_acquis =1 " .
      "AND (droits_acces_ph & 1) = 1 " .
      "ORDER BY date_prise_vue");

    if ( $req2->lines > 0 )
      list($row['id_photo']) = $req2->get_row();

    $cats[] = $row;
  }

  $req = new requete($site->db,
    "SELECT sas_cat_photos.*, parent.nom_catph as parent_nom_catph " .
    "FROM sas_cat_photos ".
    "LEFT JOIN sas_cat_photos as parent ON ( parent.id_catph = sas_cat_photos.id_catph_parent ) ".
    "WHERE sas_cat_photos.meta_id_asso_catph='".$cat->meta_id_asso."' ".
    "AND sas_cat_photos.id_catph!='".$cat->id."' ".
    "AND sas_cat_photos.id_catph_parent!='".$cat->id."' ".    
    "AND parent.id_catph_parent!='".$cat->id."' ".    
    "AND sas_cat_photos.meta_mode_catph!='".CATPH_MODE_META_ASSO."'");

  while ( $row = $req->get_row() ) $cats[] = $row;

  if ( count($cats) > 0 )
  {
    $cts->add_title(2,"Catégories trouvées");
  
    $gal = new gallery(false,"cats",false,false,"id_catph",array("edit"=>"Editer","delete"=>"Supprimer"));
    foreach ( $cats as $row )
    {
      if ( $row['parent_nom_catph'] )
        $row['nom_catph'] .= " (".$row['parent_nom_catph'].")";
  
      $img = $topdir."images/misc/sas-default.png";
      if ( $row['id_photo'] )
        $img = "images.php?/".$row['id_photo'].".vignette.jpg";
  
      $gal->add_item(
          "<a href=\"./?meta_id_catph=".$cat->id."&amp;id_catph=".$row['id_catph']."\"><img src=\"$img\" alt=\"".$row['nom_catph']."\" /></a>",
          "<a href=\"./?meta_id_catph=".$cat->id."&amp;id_catph=".$row['id_catph']."\">".$row['nom_catph']."</a>",
          $row['id_catph'],
          false);
    }
  
    $cts->add($gal);
  }
  
  if ( $mode != "expand" )
    $cts->add_title(2,"Sous catégories");
}

if ( $mode == "expand" )
{
  $scat = new catphoto(null);

  while ( $row = $sqlct->get_row() )
  {
    $scat->_load($row);

    $cts->add_title(2,"<a href=\"./?id_catph=".$row['id_catph']."\">".$row['nom_catph']."</a>");

    $sqlsubct = $cat->get_categories($row['id_catph'],$site->user,$grps,"*");

    $cts->add(cats_produde_gallery($sqlsubct));

    if ( $scat->is_right($site->user,DROIT_AJOUTCAT) )
      $cts->add_paragraph("<a href=\"./?id_catph=".$row['id_catph']."&amp;page=subcat\">Ajouter une catégorie dans ".$row['nom_catph']."</a>");

  }
}
else
  $cts->add(cats_produde_gallery($sqlct));


$sqlcntph = $cat->get_photos ( $cat->id, $site->user, $grps, "COUNT(*)");

list($nb) = $sqlcntph->get_row();

if ( $nb>0 || $cat->is_right($site->user,DROIT_AJOUTITEM) )
{

  if ( $nb>0)
  {
    $req = new requete($site->db, "SELECT COUNT(*) FROM `sas_photos` ".
      "WHERE `incomplet`='1' AND `id_catph`='".intval($cat->id)."' AND (`droits_acces_ph` & 0x100) AND `id_utilisateur` ='".$site->user->id."'");
    list($nbtcus)=$req->get_row();

    if ( $cat->is_admin($site->user) )
    {
      $req = new requete($site->db, "SELECT COUNT(*) FROM `sas_photos` ".
        "WHERE `incomplet`='1' AND `id_catph`='".intval($cat->id)."' AND `id_groupe_admin` ='".$cat->id_groupe_admin."'");
      list($nbtcad)=$req->get_row();
    }
    else
      $nbtcad=0;
  }
  else
  {
    $nbtcad=0;
    $nbtcus=0;
  }

  $tabs = array(array("","sas2/".$self."id_catph=".$cat->id, "photos - $nb"),
          array("diaporama","sas2/".$self."view=diaporama&id_catph=".$cat->id,"diaporama"),
          array("tools","sas2/".$self."view=tools&id_catph=".$cat->id,($nbtcad>0||$nbtcus>0)?"<b>outils !!</b>":"outils"),
          array("stats","sas2/".$self."view=stats&id_catph=".$cat->id,"statistiques"));

  if ($cat->is_right($site->user,DROIT_AJOUTITEM) )
    $tabs[] =array("add","sas2/".$self."view=add&id_catph=".$cat->id,"Ajouter");

  $cts->add(new tabshead($tabs,$_REQUEST["view"]));
}

if ( $_REQUEST["view"] == "tools" )
{


  if ( $nbtcus > 0 )
    $cts->add_paragraph("<a href=\"complete.php?mode=userphoto&id_catph=".$cat->id."\">Identification des personnes sur mes photos ($nbtcus)</a>");

  if ( $cat->is_admin($site->user) )
  {

    $cts->add_paragraph("<a href=\"complete.php?mode=adminzone&id_catph=".$cat->id."\">Identification des personnes sur les photos ($nbtcad)</a>");

  }
}
elseif ( $_REQUEST["view"] == "stats" )
{



}
elseif ( $_REQUEST["view"] == "add" && $cat->is_right($site->user,DROIT_AJOUTITEM) )
{
  $asso->id=-1;

  if ( $metacat->id > 0 )
    $asso->load_by_id($metacat->meta_id_asso);

  $cts->add_paragraph("<br/>Si vous voulez ajouter de nombreuses photos, " .
      "nous vous conseillons d'utiliser le logiciel UBPT Transfert qui " .
      "vous permet d'envoyer plusieurs photos en même temps de façon automatisée.<br/> " .
      "<a href=\"../article.php?name=sas-transfert\">Télécharger UBPT Transfert</a> (Disponible pour Windows, Mac OS X et Linux)");

  $cts->add_paragraph("Après avoir ajout&eactute; vos photos, il faut <b>IMPERATIVEMENT renseigner les noms des personnes</b> " .
      "se trouvant sur les photos pour que ces dernières puissent être visibles de tous.<br/>");

  $frm = new form("setfull",$self."id_catph=".$cat->id);
  $frm->add_hidden("action","addphoto");
  $frm->allow_only_one_usage();
  if ( $ErreurUpload )
    $frm->error($ErreurUpload);
  $frm->add_file_field("file","Fichier",true);
  $frm->add_text_area("comment","Commentaire");
  $frm->add_checkbox("personne","Il n'y a personne sur la photo (reconaissable)");
  $frm->add_entity_select ( "id_asso", "Association/Club lié", $site->db, "asso",$asso->id,true);
  $frm->add_rights_field($cat,false,$cat->is_admin($site->user));
  $frm->add_submit("valid","Ajouter");

  $cts->add($frm);
}
elseif ( $_REQUEST["view"] == "diaporama" && $nb > 0 )
{
  $sqlph = $cat->get_photos ( $cat->id, $site->user, $grps, "sas_photos.id_photo", " LIMIT 1");
  list($id_photo)=$sqlph->get_row();


  $cts->add_paragraph("<br/>Selectionnez l'interval entre deux photos, et lancez le diaporama.<br/>");

  $frm = new form("diaporama",$self."id_photo=".$id_photo);
  $frm->add_select_field("diaporama","Interval",array(1000=>"1 seconde",3000=>"3 secondes",5000=>"5 secondes"),3000);
  $frm->add_submit("valid","Lancer");

  $cts->add($frm);
}
elseif ( $nb )
{
  $can_sethome = $cat->is_right($site->user,DROIT_ECRITURE);

  $page = intval($_REQUEST["page"]);
  $npp=60;
  $st=$page*$npp;

  $sqlph = $cat->get_photos ( $cat->id, $site->user, $grps, "sas_photos.*", " LIMIT $st,$npp");

  $gal = new gallery(false,"photos","phlist",$self."id_catph=".$cat->id,"id_photo",array("delete"=>"Supprimer","edit"=>"Editer","sethome"=>"Definir comme photo de présentation"));
  while ( $row = $sqlph->get_row() )
  {
    $photo->_load($row);
    $img = "images.php?/".$photo->id.".vignette.jpg";
    $acts=array();
    if ( $photo->is_right($site->user,DROIT_ECRITURE) )
      $acts = array("delete","edit");
    if ( $can_sethome && $photo->droits_acquis && ($photo->droits_acces & 1))
      $acts[]="sethome";


    $gal->add_item("<a href=\"".$selfhtml."id_photo=".$photo->id."\"><img src=\"$img\" alt=\"Photo\"></a>","",$photo->id, $acts );
  }
  $cts->add($gal);

  if ( $nb > $npp )
  {
    $tabs = array();
    $i=0;
    while ( $i < $nb )
    {
      $n = $i/$npp;
      $tabs[]=array($n,"sas2/".$self."id_catph=".$cat->id."&page=".$n,$n+1 );
      $i+=$npp;
    }
    $cts->add(new tabshead($tabs, $page, "_bottom"));
  }
}

$site->add_contents($cts);

$site->end_page ();


?>
