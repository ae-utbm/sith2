<?php

/**
 * Index des sous-catégories d'une catégorie du SAS
 *
 * Permet de remplacer le découpage manuel par semestre, par un découpe automatisé si celui-ci est possible.
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
          $this->write_simple_gallery( $scat->nom, $scat->get_all_categories($user), $page, $scat, $user, $sscat );
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


?>