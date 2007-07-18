<?php


class ficheproduit extends stdcontents
{
  
  function ficheproduit( &$typeproduit, &$produit, &$venteprod, &$user )
  {
    global $topdir;
    
    $this->title = 
        "<a href=\"index.php\">E-Boutic</a>".
        " / ".$typeproduit->get_html_link().
        " / ".$produit->get_html_link();
        
    $this->buffer .= "<div class=\"ebficheprod\">";
    
    // Nom et prix de ref
    $this->buffer .= "<h2>".$produit->nom."</h2>";
    $this->buffer .= "<p class=\"prix\">".($produit->prix_vente/100)." &euro;</p>";
    
    // Image
    if ( is_null($produit->id_file) )
    {
      $this->buffer .= 
      "<p class=\"image\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</p>\n";      
    }
    else
    {
      $this->buffer .= 
      "<p class=\"image\">".
      "<img src=\"".$topdir."d.php?id_file=".$produit->id_file."&amp;action=download&amp;download=preview\" alt=\"\" />".
      "</p>\n";      
    }
    
    // Description
    if ( !$produit->description_longue )
      $this->buffer .= "<div class=\"description\">".$produit->description."</div>";
    else
      $this->buffer .= "<div class=\"description\">".$produit->description_longue."</div>";
    
    // Informations sur le retrait
    if ( $produit->a_retirer )
    {
      $req = new requete($produit->db,
          "SELECT `cpt_comptoir`.`nom_cpt` 
          FROM `cpt_mise_en_vente` 
          INNER JOIN `cpt_comptoir` ON `cpt_comptoir`.`id_comptoir` = `cpt_mise_en_vente`.`id_comptoir`
          WHERE `cpt_mise_en_vente`.`id_produit` = '".$produit->id."' AND `cpt_comptoir`.`type_cpt`!=1");

      $this->buffer .= "<div class=\"retrait\">";
      if ( $req->lines != 0 )
      {
        $this->buffer .= "<h3>Produit à venir retirer :</h3>";
        $this->buffer .= "<ul>";
        while ( list($nom) = $req->get_row() )
          $this->buffer .= "<li>".$nom."</li>";
        $this->buffer .= "</ul>";
      }
      else
        $this->buffer .= "<h3>Produit à venir retirer </h3>";
      $this->buffer .= "</div>";
    }
    
    $req = new requete($produit->db,"SELECT `cpt_mise_en_vente`.*, `cpt_produits`.* ".
            "FROM     `cpt_mise_en_vente` ".
            "INNER JOIN `cpt_produits` USING (`id_produit`) ".
            "WHERE `cpt_mise_en_vente`.`id_comptoir` = '".$venteprod->comptoir->id."' ".
            "AND `cpt_produits`.`prod_archive` = 0 ".
            "AND `cpt_produits`.`id_produit_parent`='".$produit->id."'");
            
    $this->buffer .= "<div class=\"achat\">";
    $this->buffer .= "<h3>Acheter le produit</h3>";
    if ( $req->lines > 0 )
    {
      $this->buffer .= "<ul>";
      while ( $row = $req->get_row() )
      {
        $stock = $row["stock_local_prod"] == -1 ? $row["stock_global_prod"] : $row["stock_local_prod"];
        
        if ( $stock == 0 )
          $this->buffer .= 
            "<li>".$row["nom_prod"]." : épuisé</li>";
        else
        {
          if ( $stock != -1 )
            $info_stock=" ($info_stock en stock)";
          else
            $info_stock="";
          
          if ( $row["prix_vente_prod"] != $produit->prix_vente )
            $this->buffer .= 
              "<li><a href=\"./?act=add&amp;id_produit=".$row["id_produit"]."\">".
              $row["nom_prod"]." <b>".($row["prix_vente_prod"]/100).
              "&euro;</b> : Ajouter au panier</a>$info_stock</li>";          
          else
            $this->buffer .= 
              "<li><a href=\"./?act=add&amp;id_produit=".$row["id_produit"]."\">".
              $row["nom_prod"]." : Ajouter au panier</a>$info_stock</li>";
        }    
      }      
      $this->buffer .= "</ul>";
    }
    else
    {
      $stock = $venteprod->stock_local == -1 ? $produit->stock_global : $venteprod->stock_local;
      
      if ( $stock == 0 )
        $this->buffer .= 
          "<ul><li>Produit épuisé</li></ul>";
      else
      {
        if ( $stock != -1 )
          $info_stock=" ($stock en stock)";
        else
          $info_stock="";
          
        $this->buffer .= 
          "<ul>".
          "<li><a href=\"./?act=add&amp;id_produit=".$produit->id."\">Ajouter au panier</a>$info_stock</li>".
          "</ul>";
      }
    }
    $this->buffer .= "</div>";
    $this->buffer .= "</div>";
  }  
}


class vigproduit extends stdcontents
{
  function vigproduit ( $row )
  {
    global $topdir;
    
    $this->buffer .= "<div class=\"ebv\">\n";
    $this->buffer .= "<h2><a href=\"?id_produit=".$row["id_produit"]."\">".$row["nom_prod"]."</a></h2>\n";
    
    if ( !is_null($row["id_file"]) )
      $this->buffer .= 
      "<p class=\"image\"><a href=\"?id_produit=".$row["id_produit"]."\">".
      "<img src=\"".$topdir."d.php?id_file=".$row["id_file"]."&amp;action=download&amp;download=thumb\" alt=\"\" />".
      "</a></p>\n";
    else
      $this->buffer .= 
      "<p class=\"image\"><a href=\"?id_produit=".$row["id_produit"]."\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</a></p>\n";
	
    $this->buffer .= "<p class=\"description\">".$row["description_prod"]."</p>\n";
    $this->buffer .= "<p class=\"prixunit\">".($row["prix_vente_prod"]/100)." &euro;</p>\n";
    
    
    $stock = $row["stock_local_prod"] == -1 ? $row["stock_global_prod"] : $row["stock_local_prod"];
    if ( $stock == 0 )
      $this->buffer .= "<p class=\"details\"><a href=\"./?id_produit=".$row["id_produit"]."\">Details / Acheter</a></p>\n";
    else
      $this->buffer .= "<p class=\"details\"><a href=\"./?id_produit=".$row["id_produit"]."\">Details</a> - <a href=\"./?act=add&amp;id_produit=".$row["id_produit"]."\">Ajout panier</a></p>\n";
    $this->buffer .= "</div>\n";
  }
}

class vigtypeproduit extends stdcontents
{
  function vigtypeproduit ( $row )
  {
    global $topdir;
    
    $this->buffer .= "<div class=\"ebv\">\n";
    $this->buffer .= "<h2><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".$row["nom_typeprod"]."</a></h2>\n";
    
    if ( !is_null($row["id_file"]) )
      $this->buffer .= 
      "<p class=\"image\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".
      "<img src=\"".$topdir."d.php?id_file=".$row["id_file"]."&amp;action=download&amp;download=thumb\" alt=\"\" />".
      "</a></p>\n";
    else
      $this->buffer .= 
      "<p class=\"image\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">".
      "<img src=\"".$topdir."images/comptoir/eboutic/prod-unknown.png\" alt=\"\" />".
      "</a></p>\n";
	
    $this->buffer .= "<p class=\"description\">".$row["description_typeprod"]."</p>\n";
    $this->buffer .= "<p class=\"details\"><a href=\"?id_typeprod=".$row["id_typeprod"]."\">Produits</a></p>\n";
    $this->buffer .= "</div>\n";
  
  }
}



?>
