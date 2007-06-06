<?php
/* 
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *        
 * Copyright 2004-2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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
 
require_once("include/site.inc.php");
require_once($topdir."sas2/include/cat.inc.php");
require_once($topdir."sas2/include/photo.inc.php");
require_once($topdir."include/cts/sascategory.inc.php");

require_once($topdir."include/cts/gallery.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");

$photo = new photo($site->db,$site->dbrw);
$cat = new catphoto($site->db,$site->dbrw);
$rootcat = new catphoto($site->db);
$metacat = new catphoto($site->db);
$catpr = new catphoto($site->db);

$rootcat->load_by_asso_summary($site->asso->id);

if ( !$rootcat->is_valid() )
{
  $site->start_page ( CMS_PREFIX."sas", "Erreur" );
  $cts = new contents("Gallerie non configurée");
  $cts->add_paragraph("Veuillez contacter l'équipe informatique de l'AE pour configurer votre gallerie de photos.","error");
  $site->add_contents($cts);  
  $site->end_page();
  exit();
}

if ( isset($_REQUEST["id_photo"]))
{
  $photo->load_by_id($_REQUEST["id_photo"]);
  if ( !$photo->is_valid() )
  {
    header("Location: index.php?name=404");
    exit();
  }
  $cat->load_by_id($photo->id_catph);
}
elseif ( isset($_REQUEST["id_catph"]))
{
  $cat->load_by_id($_REQUEST["id_catph"]);
}  
elseif ( !$cat->is_valid() )
{
  $cat->load_by_id($rootcat->id);
}


if ( isset($_REQUEST["meta_id_catph"]))
  $metacat->load_by_id($_REQUEST["meta_id_catph"]);

if ( !$cat->is_valid() )
{
  header("Location: index.php?name=404");
  exit();
}

if ( !$cat->is_right($site->user,DROIT_LECTURE) )
{
  header("Location: index.php?name=403");
  exit();
}

if ( $metacat->is_valid() && !$metacat->is_right($site->user,DROIT_LECTURE) )
  $metacat->id=null;

if ( $photo->is_valid() && !$photo->is_right($site->user,DROIT_LECTURE) )
{
  header("Location: index.php?name=403");
  exit();
}

$site->add_css("css/sas.css");

$valid = false;

$grps = $site->user->get_groups_csv();

if ( $cat->id == $rootcat->id )
{
  $valid = true;
  $cat->nom = "Photos";
}

if ($photo->is_valid() )
  $path =   classlink($cat)." / ".classlink($photo);
else
  $path =   classlink($cat);

if ( $metacat->is_valid() )
{
  $catpr->load_by_id($metacat->id);
  $cat->set_meta($metacat);
  $self="photos.php?meta_id_catph=".$metacat->id."&";
  $selfhtml="photos.php?meta_id_catph=".$metacat->id."&amp;";
  $path = str_replace("photos.php?","photos.php?meta_id_catph=".$metacat->id."&amp;",$path);
}
else
{
  $catpr->load_by_id($cat->id_catph_parent);
  $self="photos.php?";
  $selfhtml="photos.php?";
}

while ( $catpr->is_valid() && $catpr->id != $rootcat->id_catph_parent )
{
  if ( $catpr->id == $rootcat->id )
  {
    $valid = true;
    $catpr->nom = "Photos";
  }
  
  $path =   classlink($catpr)." / ".$path;
  $catpr->load_by_id($catpr->id_catph_parent);
}

if ( !$valid ) // On vérifie que la catégorie a bien comme parent $rootcat, sinon on n'est plus dans la gallerie de l'activité
{
  echo $path;
  echo "wrong place, ".$rootcat->id." was excepted";
  //header("Location: index.php?name=403");
  exit();
}



/** @todo vérifier après cette ligne */

/*
 * Photos
 */

if ( $_REQUEST["action"] == "rertraitphoto" && $photo->is_valid() && $photo->is_on_photo($site->user->id) )
{
  if ( $_REQUEST["mesure"] == "retrait" )
  {
    $photo->remove_photo();
    $photo->id=null;
  }
  elseif ( $_REQUEST["mesure"] == "notonphoto" )
  {
    $photo->remove_personne($site->user->id);
  }
  else
  {
    $photo->donne_accord($user->id);
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

if ( $photo->is_valid() )
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
    if ( $utl->is_valid() )
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
    if ( $utl->is_valid() )
    {
      $photo->add_personne($utl,false);
      $Message="Personne ajout&eacute;e comme suggestion : ".classlink($utl);;
    }
    else
      $ErrorSuggest="Personne inconnue...";

  }

  if ( ($_REQUEST["page"] == "edit" || $_REQUEST["action"] == "edit") && $can_write )
  {
    $site->start_page(CMS_PREFIX."sas","Photos");

    $cts = new contents($path." / Editer");

    $frm = new form("updatephoto","photos.php?id_photo=".$photo->id);
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
    $site->start_page(CMS_PREFIX."sas","Photos");

    $cts = new contents($path." / Demande de retrait");

    if ( $photo->is_on_photo($site->user->id) )
    {

      $frm = new form("droitphoto","photos.php?id_photo=".$photo->id."&id_catph=".$cat->id,false,"POST","Votre souhait");
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

  if ( $metacat->is_valid() )
    $exdata="meta_id_catph=".$metacat->id."&";
  else
    $exdata="";

  if ( $_REQUEST["fetch"] == "script" )
  {
    echo "openInContents( 'cts1', 'photos.php', '".$exdata."id_photo=".$photo->id."&fetch=cts1');";
    if ( $_REQUEST["diaporama"] > 0 && ( $idx != $count-1 ) )
    {
      echo "cache5.src=\"images.php?/".$photos[$idx+1].".diapo.jpg\";\n";
      echo "setTimeout(\"evalCommand('photos.php', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=script&diaporama=".intval($_REQUEST["diaporama"])."')\", ".intval($_REQUEST["diaporama"]).");";
    }
    exit();
  }

  if ( $_REQUEST["diaporama"] > 0 && ( $idx != $count-1 ) )
  {
    $cts->puts("<script>" .
        "cache1= new Image(); cache1.src=\"".$wwwtopdir."images/to_prev.png\";".
        "cache2= new Image(); cache2.src=\"".$wwwtopdir."images/to_next.png\";".
        "cache3= new Image(); cache3.src=\"".$wwwtopdir."images/icons/16/catph.png\";".
        "cache4= new Image(); cache4.src=\"".$wwwtopdir."images/icons/16/photo.png\";".
        "cache5= new Image(); cache5.src=\"images.php?/".$photos[$idx+1].".diapo.jpg\";".
        "cache6= new Image(); cache6.src=\"".$wwwtopdir."images/user.png\";".
        "cache7= new Image(); cache7.src=\"".$wwwtopdir."images/actions/delete.png\";");
    $cts->puts("setTimeout(\"evalCommand('photos.php', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=script&diaporama=".intval($_REQUEST["diaporama"])."')\", ".intval($_REQUEST["diaporama"]).");");
    $cts->puts("</script>");
  }

  $subcts = new contents();

  $subcts->puts("<div id=\"sasnav\">");

  if ( $idx != 0 )
  {
    $subcts->puts("<div id=\"back\">");
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx-1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx-1]."&fetch=cts1'); return false;\">");
    $subcts->puts("<img src=\"images.php?/".$photos[$idx-1].".vignette.jpg\" alt=\"Precedent\" class=\"mininav\" />");
    $subcts->puts("<img src=\"".$wwwtopdir."images/to_prev.png\" alt=\"Precedent\" class=\"mininavbtn\" />");
    $subcts->puts("</a>");
    $subcts->puts("</div>");
  }

  if ( $idx != $count-1 )
  {
    $subcts->puts("<div id=\"next\">");
    $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx+1]."\" onclick=\"openInContents( 'cts1', './', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=cts1'); return false;\">");
    $subcts->puts("<img src=\"images.php?/".$photos[$idx+1].".vignette.jpg\" alt=\"Suivant\" class=\"mininav\" />");
    $subcts->puts("<img src=\"".$wwwtopdir."images/to_next.png\" alt=\"Suivant\" class=\"mininavbtn\" />");
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

  if ( $phasso->is_valid() )
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
        $frm = new form("setfull","photos.php?id_photo=".$photo->id,false,"POST","Liste complète");
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
  $subcts->add_paragraph("<a href=\"".$self."id_photo=".$photo->id."&amp;page=askdelete\">Demander le retrait</a>");



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

  $site->start_page(CMS_PREFIX."sas","Photos",true);
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

    $cat->set_rights($site->user,$_REQUEST['rights'],$_REQUEST['rights_id_group'],$_REQUEST['rights_id_group_admin'],true);
    $cat->update_catphoto($site->user,$cat->id_catph_parent,$_REQUEST["nom"],$_REQUEST["debut"],$_REQUEST["fin"],$_REQUEST["id_asso"],$_REQUEST["mode"]);

    $cat->set_photo($photo->id);
    $path =   classlink($cat);
    $catpr->load_by_id($cat->id_catph_parent);
    while ( $catpr->is_valid() )
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
  $site->start_page(CMS_PREFIX."sas","Photos");
  $cts = new contents($path." / Editer");


  $frm = new form("editcat","photos.php?id_catph=".$cat->id);
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
  $site->start_page(CMS_PREFIX."sas","Photos");
  $cts = new contents($path." / Nouvelle sous-catégorie");

  $cts->add_paragraph("Remarque: la nouvelle catégorie sera visible des autres utilisateurs dès qu'elle sera modérée.");

  $frm = new form("addsubcat","photos.php?id_catph=".$cat->id);
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
$site->start_page(CMS_PREFIX."sas","Photos");

$cts = new contents($path);

if ( $cat->is_right($site->user,DROIT_ECRITURE) )
  $cts->set_toolbox(new toolbox(array("photos.php?id_catph=".$cat->id."&page=edit"=>"Editer")));

// Sous-catégories
if ( $cat->is_right($site->user,DROIT_AJOUTCAT) )
  $cts->add_paragraph("<a href=\"photos.php?id_catph=".$cat->id."&amp;page=subcat\">Ajouter une catégorie dans ".$cat->nom."</a>");
  
$cts->add(new sascategory ( "photos.php", $cat, $site->user ));
// --> voir include/cts/sascategory.inc.php


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

  $tabs = array(array("",$self."id_catph=".$cat->id, "photos - $nb"),
          array("diaporama",$self."view=diaporama&id_catph=".$cat->id,"diaporama"),
          array("tools",$self."view=tools&id_catph=".$cat->id,($nbtcad>0||$nbtcus>0)?"<b>outils !!</b>":"outils"),
          array("stats",$self."view=stats&id_catph=".$cat->id,"statistiques"));

  if ($cat->is_right($site->user,DROIT_AJOUTITEM) )
    $tabs[] =array("add",$self."view=add&id_catph=".$cat->id,"Ajouter");

  $cts->add(new tabshead($tabs,$_REQUEST["view"]));
}

if ( $_REQUEST["view"] == "tools" )
{


  if ( $nbtcus > 0 )
    $cts->add_paragraph("<a href=\"/sas2/complete.php?mode=userphoto&id_catph=".$cat->id."\">Identification des personnes sur mes photos ($nbtcus)</a>");

  if ( $cat->is_admin($site->user) )
  {

    $cts->add_paragraph("<a href=\"/sas2/complete.php?mode=adminzone&id_catph=".$cat->id."\">Identification des personnes sur les photos ($nbtcad)</a>");

  }
}
elseif ( $_REQUEST["view"] == "stats" )
{



}
elseif ( $_REQUEST["view"] == "add" && $cat->is_right($site->user,DROIT_AJOUTITEM) )
{

  if ( $metacat->is_valid() )
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
      $tabs[]=array($n,$self."id_catph=".$cat->id."&page=".$n,$n+1 );
      $i+=$npp;
    }
    $cts->add(new tabshead($tabs, $page, "_bottom"));
  }
}

$site->add_contents($cts);

$site->end_page ();




?>