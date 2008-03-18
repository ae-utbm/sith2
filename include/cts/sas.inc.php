<?php

/**
 * @defgroup display_cts_sas Contents SAS
 * Contents pour le rendu des pages du SAS
 * @ingroup display_cts
 */ 

require_once($topdir."include/cts/video.inc.php");
require_once($topdir."include/cts/taglist.inc.php");

/**
 * Index des sous-catégories d'une catégorie du SAS
 *
 * Permet de remplacer le découpage manuel par semestre, par un découpe automatisé si celui-ci est possible.
 * @ingroup display_cts_sas
 */
class sascategory extends contents
{
  /**
   * Génére l'index des sous-catégories d'une catégorie du SAS
   * @param $page Nom de la page du SAS (./ dans sas2/ et photos.php dans AECMS)
   * @param $dat Catégorie à traiter
   * @param $user Utilisateur qui consulte la page
   */
  function sascategory ( $page, &$cat, &$user )
  {
    global $wwwtopdir;
    
    $cats = $cat->get_all_categories($user);   
    
    $semestre_mode=true;
    $expand_mode=true;
    
    if ( $cat->id == 1 )
      $expand_mode=false;
    
    if ( !count($cats) )
      return;
    
    // Détermine si l'affichage par semestre est possible
    foreach ( $cats as $row )
    {
      if ( is_null($row['date_debut_catph']) )
        $semestre_mode=false;
        
      if ( ($row['droits_acces_catph'] & (0x888)) != 0 )
        $expand_mode=false;
        
      if ( $cat->id != $row['id_catph_parent'] )
        $expand_mode=false;
        
      if ( $row['meta_mode_catph'] == 1 )
        $expand_mode=false;
    }
    
    $scat = new catphoto($cat->db);
    
    if ( !$semestre_mode )
    {
      if ( $expand_mode )
      {
        $sscat = new catphoto($cat->db);
        foreach ( $cats as $row )
        {
          $scat->_load($row);
          $this->write_simple_gallery( "<a href=\"".$page."?id_catph=".$scat->id."\">".$scat->nom."</a>", $scat->get_all_categories($user), $page, $scat, $user, $sscat );
        }
        return; 
      }
      
      // Affichage sans les semestres
      $this->write_simple_gallery ("Sous-catégories", $cats,$page,$cat,$user,$scat);
      return;
    }
    
    // Affichage découpé par semestre
    
    $prev_semestre = null;
    $gal = null;
    
    foreach ( $cats as $row )
    {
      $semestre = $this->get_semestre(strtotime($row['date_debut_catph']));
      
      if ( $semestre != $prev_semestre || is_null($gal) )
      {
        if ( !is_null($gal) )
          $this->add($gal,true);
        
        $prev_semestre = $semestre;
        
        $gal = new gallery($semestre,"cats",false,$page,"id_catph",array("edit"=>"Editer","delete"=>"Supprimer","cut"=>"Couper"));
      }
      
      $img = $wwwtopdir."images/misc/sas-default.png";
      
      if ( !is_null($row['id_photo']) && $row['id_photo'] > 0 )
        $img = "images.php?/".$row['id_photo'].".vignette.jpg";
      
      $acts=false;
      
      if ( $cat->id != $row['id_catph_parent'] )
        $link = $page."?meta_id_catph=".$cat->id."&amp;id_catph=".$row['id_catph'];
      else
      {
        $link = $page."?id_catph=".$row['id_catph'];
        
        $scat->_load($row);
        if ( $scat->is_right($user,DROIT_ECRITURE) )
          $acts = array("delete","edit","cut");
      }  
      

      $gal->add_item(
          "<a href=\"".$link."\"><img src=\"$img\" alt=\"".$row['nom_catph']."\" /></a>",
          "<a href=\"".$link."\">".$row['nom_catph']."</a>",
          $row['id_catph'],
          $acts);   
          
    }
    
    if ( !is_null($gal) )
      $this->add($gal,true);       
  }
  
  function write_simple_gallery ($title, &$cats,&$page,&$cat,&$user,&$scat)
  {
    global $wwwtopdir;
    
    $gal = new gallery($title,"cats",false,$page,"id_catph",array("edit"=>"Editer","delete"=>"Supprimer","cut"=>"Couper"));
       
    foreach ( $cats as $row )
    {

      $img = $wwwtopdir."images/misc/sas-default.png";
        
      if ( !is_null($row['id_photo']) && $row['id_photo'] > 0 )
        $img = "images.php?/".$row['id_photo'].".vignette.jpg";
        
      $acts=false;
        
      if ( $cat->id != $row['id_catph_parent'] )
        $link = $page."?meta_id_catph=".$cat->id."&amp;id_catph=".$row['id_catph'];
      else
      {
        $link = $page."?id_catph=".$row['id_catph'];
          
        $scat->_load($row);
        if ( $scat->is_right($user,DROIT_ECRITURE) )
          $acts = array("delete","edit","cut");
      }  
        
      $gal->add_item(
          "<a href=\"".$link."\"><img src=\"$img\" alt=\"".$row['nom_catph']."\" /></a>",
          "<a href=\"".$link."\">".$row['nom_catph']."</a>",
          $row['id_catph'],
          $acts);      
    }       
       
    $this->add($gal,true);     
  }
  
  function get_semestre ( $date )
  {
    $y = date("Y",$date);
    $m = date("m",$date);
    //$d = date("d",$date);
    
    if ( $m >= 2 && $m < 9)
      return "Printemps ".$y;
    else if ( $m >= 9 )
      return "Automne ".$y;
    else
      return "Automne ".($y-1);
  }
  
  
  
}

/**
 * Affichage d'une photo du SAS vu dans une catégorie
 * @ingroup display_cts_sas
 */
class sasphoto extends contents
{
  
  function sasphoto ( $title, $page, &$cat, &$photo, &$user, $Message="", &$metacat=null )
  {
    global $wwwtopdir, $topdir;
    
    $sqlph = $cat->get_photos ( $cat->id, $user, $user->get_groups_csv(), "sas_photos.id_photo");
    $count=0;
    while ( list($id) = $sqlph->get_row() )
    {
      if ( $id == $photo->id ) $idx = $count;
      $photos[] = $id;
      $count++;
    }
  
    $can_write = $photo->is_right($user,DROIT_ECRITURE);
    
    if ( $metacat && $metacat->is_valid() )
    {
      $self=$page."?meta_id_catph=".$metacat->id."&";
      $selfhtml=$page."?meta_id_catph=".$metacat->id."&amp;";
      $exdata="meta_id_catph=".$metacat->id."&";
    }
    else
    {
      $self=$page."?";
      $selfhtml=$page."?";
      $exdata="";
    }    
    
    $this->title = $title;
    $this->divid = "cts1";
    
    $imgcts = new contents();
    $subcts = new contents();

    $exif="";

    if ( $photo->type_media == MEDIA_VIDEOFLV )
    {
      $flvpath = "images.php?/".$photo->id.".flv";
      if ( $wwwtopdir == "../" )
        $flvpath = "sas2/images.php?/".$photo->id.".flv";
      $imgcts->add(new flvideo($photo->id,$flvpath));
    }
    else
    {
      $imgcts->add(new image($photo->id,"images.php?/".$photo->id.".diapo.jpg"));
      $_exif="<div id=\"exif\">\n";
      if(!empty($photo->manufacturer) || !empty($photo->manufacturer))
      {
        if(strlen($photo->manufacturer)>0)
        {
          $boitier=$photo->manufacturer;
          if(!empty($photo->model))
            $boitier.=" (".$photo->model.")";
        }
        else
          $boitier=$photo->model;
        $exif.="<span class=\"exiftitle\">Boitier</span> : ".$boitier."<br />\n";
      }
      if($photo->exposuretime!=0)
      {
        $et=explode("/",$photo->exposuretime);
        if(count($et)==2)
        {
          $et=((int)$et[0]/(int)$et[1]);
          $et=" (".$et." s)";
        }
        else
          $et="";
        $exif.="<span class=\"exiftitle\">Vitesse</span> : ".$photo->exposuretime." ".$et."<br />\n";
      }
      if($photo->aperture!=0)
        $exif.="<span class=\"exiftitle\">Ouverture</span> : ".$photo->aperture."<br />\n";
      if(!empty($photo->focale))
        $exif.="<span class=\"exiftitle\">Focale</span> : ".$photo->focale." mm<br />\n";
      if(!empty($photo->ouverture))
      {
        $ouv=explode("/",$photo->ouverture);
        if(count($ouv)==2)
        {
          $ouv=((int)$ouv[0]/(int)$ouv[1]);
          $ouv="(f/".sprintf("%.2f",$ouv).")";
        }
        else
          $ouv="";
        $exif.="<span class=\"exiftitle\">Ouverture</span> : ".$photo->ouverture." ".$ouv."<br />\n";
      }
      if($photo->iso!=0)
        $exif.="<span class=\"exiftitle\">Iso</span> : ".$photo->iso."<br />\n";
      if($photo->flash==1)
        $exif.="<span class=\"exiftitle\">Flash</span> : oui<br />\n";
      elseif($photo->flash==0)
        $exif.="<span class=\"exiftitle\">Flash</span> : non<br />\n";
    }

    if(!empty($exif))
    {
      $imgcts->puts($_exif);
      $imgcts->puts($exif);
      $imgcts->puts("</div>\n");
    }
    $this->add($imgcts,false,true,"sasimg");

    if ( $idx != 0 || $idx != $count-1 )
    {
      $subcts->puts("<div id=\"sasnav\">");
    
      if ( $idx != 0 )
      {
        $subcts->puts("<div id=\"back\">");
        $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx-1]."\" onclick=\"return !openInContents( 'cts1', '".$page."', '".$exdata."id_photo=".$photos[$idx-1]."&fetch=photocts');\">");
        $subcts->puts("<img src=\"images.php?/".$photos[$idx-1].".vignette.jpg\" alt=\"Precedent\" class=\"mininav\" />");
        $subcts->puts("<img src=\"".$wwwtopdir."images/to_prev.png\" alt=\"Precedent\" class=\"mininavbtn\" />");
        $subcts->puts("</a>");
        $subcts->puts("</div>");
      }
    
      if ( $idx != $count-1 )
      {
        $subcts->puts("<div id=\"next\">");
        $subcts->puts("<a href=\"".$self."id_photo=".$photos[$idx+1]."\" onclick=\"return !openInContents( 'cts1', '".$page."', '".$exdata."id_photo=".$photos[$idx+1]."&fetch=photocts');\">");
        $subcts->puts("<img src=\"images.php?/".$photos[$idx+1].".vignette.jpg\" alt=\"Suivant\" class=\"mininav\" />");
        $subcts->puts("<img src=\"".$wwwtopdir."images/to_next.png\" alt=\"Suivant\" class=\"mininavbtn\" />");
        $subcts->puts("</a>");
        $subcts->puts("</div>");
      }
    
      $subcts->puts("</div>");
    }
    
    if ( $Message )
    {
      $subcts->add_title(2,"Opération réussie");
      $subcts->add_paragraph($Message);
    }
  
    $subcts->add_title(2,"Informations");
  
    if ( !is_null($photo->date_prise_vue) && $photo->date_prise_vue > 3600 )
      $subcts->add_paragraph(date("d/m/Y H:i:s",$photo->date_prise_vue));
  
    $asso = new asso($photo->db);
    if ( $photo->meta_id_asso )
    {
      $asso->load_by_id($photo->meta_id_asso);    
      $subcts->add_paragraph(classlink($asso));
    }

    $userinfo = new utilisateur($photo->db);
      
    if ( $photo->id_utilisateur_photographe && $photo->id_asso_photographe )
    {
      $userinfo->load_by_id($photo->id_utilisateur_photographe);
      $asso->load_by_id($photo->id_asso_photographe);    
      if ( $photo->type_media == MEDIA_VIDEOFLV )
        $subcts->add_paragraph("Réalisé par ".classlink($asso).", ".classlink($userinfo));
      else
        $subcts->add_paragraph("Photographie par ".classlink($asso).", ".classlink($userinfo));
    }
    elseif ( $photo->id_asso_photographe )
    {
      $asso->load_by_id($photo->id_asso_photographe);    
      if ( $photo->type_media == MEDIA_VIDEOFLV )
        $subcts->add_paragraph("Réalisé par ".classlink($asso));
      else
        $subcts->add_paragraph("Photographie par ".classlink($asso));
    }
    elseif ( $photo->id_utilisateur_photographe )
    {
      $userinfo->load_by_id($photo->id_utilisateur_photographe);
      $subcts->add_paragraph("Photographe: ".classlink($userinfo));  
    }
    
    
    if ( $photo->is_admin($user) )
    {
      if ( $photo->id_utilisateur_moderateur )
      {
        $userinfo->load_by_id($photo->id_utilisateur_moderateur);
        $subcts->add_paragraph("Modéré par: ".classlink($userinfo));  
      }
      if ( $photo->id_utilisateur )
      {
        $userinfo->load_by_id($photo->id_utilisateur);
        $subcts->add_paragraph("Proposé par: ".classlink($userinfo));  
      }
    }

    $photo->set_seen_photo ($user);
    
    $subcts->add(new taglist($photo));
    
    $req = new requete($photo->db,
      "SELECT `utilisateurs`.`id_utilisateur`, " .
      "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
      "FROM `sas_personnes_photos` " .
      "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`sas_personnes_photos`.`id_utilisateur` " .
      "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
      "WHERE `sas_personnes_photos`.`id_photo`='".$photo->id."' " .
      "ORDER BY `nom_utilisateur`");
  
  
    if ( $can_write )
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
          $frm = new form("setfull",$page."?id_photo=".$photo->id,false,"POST","Liste complète");
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
      
      if ( $photo->type_media == MEDIA_PHOTO )
      {
        $subcts->add_paragraph("<a href=\"".$self."id_photo=".$photo->id."&amp;action=rotate90\">Rotation +90°</a>");
        $subcts->add_paragraph("<a href=\"".$self."id_photo=".$photo->id."&amp;action=rotate-90\">Rotation -90°</a>");
      }
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
    
    if ( $photo->type_media == MEDIA_PHOTO )
      $subcts->add_paragraph("<a href=\"images.php?/".$photo->id.".jpg\">Version HD</a>");  
    else if ( $photo->type_media == MEDIA_VIDEOFLV )
      $subcts->add_paragraph("<a href=\"images.php?/".$photo->id.".flv\">Télécharger la vidéo (format FLV)</a>");
      
    $subcts->add_paragraph("<a href=\"".$page."?id_photo=".$photo->id."&amp;page=askdelete\">Demander le retrait</a>");
    
    if ( $photo->type_media == MEDIA_PHOTO && $user->is_in_group ("moderateur_site") && $wwwtopdir == $topdir )
      $subcts->add_paragraph("<a href=\"".$page."?id_photo=".$photo->id."&amp;action=setweekly\">Mettre en photo de la semaine</a>");    

    $this->add($subcts,false,true,"photoinfo");
    $this->puts("<div class=\"clearboth\"></div>");
    
  }

}

?>
