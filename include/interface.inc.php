<?php

/** @file
 *
 *
 */
/* Copyright 2005,2006
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

setlocale(LC_ALL,"fr_FR.UTF8"); 

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");
require_once($topdir . "include/entities/std.inc.php");
require_once($topdir . "include/entities/utilisateur.inc.php");
require_once($topdir . "include/cts/standart.inc.php");

if ( !isset($wwwtopdir) )
  $wwwtopdir = $topdir;


/** Classe générant l'interface
 * @see site
 */
class interfaceweb
{
  var $db;
  var $dbrw;
  var $user;
  
  var $contents;
  var $sides;
  var $sides_ref;
  var $boxes;

  var $section;
  var $title;  

  var $extracss;
  var $rss;
  var $extrajs;
  
  var $compact;
  
  var $params; // cache des paramètres
  
  var $tab_array = array (array ("accueil", "index.php", "Accueil"),
        array ("presentation", "article.php?name=presentation", "Présentation",
           array (
              array ("article.php?name=presentation", "L'AE" ),
              array ("asso.php", "Associations et clubs" ),
              array ("article.php?name=presentation:services", "Services quotidiens" )
             ) ),
        array ("services", "article.php?name=services", "Services",
           array ( 
              array("e-boutic/","E-Boutic"),
              array("emprunt.php","Pret de matériel"),
              array("jobetu/","AE Job-étu"),
              array("article.php?name=weekmail","Weekmail"),
              array("article.php?name=covoiturage","Co-voiturage")
            ) ),
        array ("pg", "pgae.php", "Petit géni"),
        array ("matmatronch", "matmatronch/", "Matmatronch"),
        array ("sas", "sas2/", "SAS"),
        array ("forum", "forum2/", "Forum"),
        array ("fichiers", "d.php", "Fichiers",
           array (
              array ("d.php", "Fichiers de l'AE" ),
              array ("asso.php", "Fichiers des associations et des clubs" )
             ) ),
        array ("liens","article.php?name=liens","Liens"));
        
  /** Constructeur
   * @param $db instance de la base de donnée pour la lecture
   * @param $dbrw instance de la base de donéne pour l'écriture (+lecture)
   */
  function interfaceweb ( $db, $dbrw = false )
  {
    $this->db = $db;
    $this->dbrw = $dbrw;
    
    $this->sides["left"] = array("connexion");
    $this->sides["right"] = array();
    
    $this->user = new utilisateur( $db, $dbrw );
    $this->extracss = array();
    $this->extrajs = array();
    $this->rss = array();
    $this->contents=array();
  }

  /** Défini les boites à afficher sur un coté
   * @param $side Coté (left ou right)
   * @param $boxes Array des nom des boites à afficher
   */
  function set_side_boxes ( $side, $boxes, $ref=null )
  {
    if ( $side != "left" && $side != "right" ) return;
    $this->sides[$side] = $boxes;
    
    if ( $ref == null )
    {
      if ( isset($this->sides_ref[$side]) )
        unset($this->sides_ref[$side]);
    }
    else
      $this->sides_ref[$side] = $ref;
  }
  
  /** Ajoute une boite affichable sur le coté
   * $name Nom de la boite
   * $contents Instance de stdcontents à afficher
   */
  function add_box ( $name, $contents )
  {
    if ( is_null($contents) )
      return;
    $this->boxes[$name] = $contents;
  }

  /** Ajoute une boite de contenu (dans le centre).
   * Si un titre est défini, alors il sera affiché.
   * @param $contents Instance de stdcontents à afficher.
   */
  function add_contents ( $contents )
  {
    $this->contents[] = $contents;
  }

  /** Initlialise la page
   * @param $section Nom de la section
   * @param $title Titre de la page
   */
  function start_page ( $section, $title, $compact=false ) // <=> page
  {  
    $this->section = $section;
    $this->title = $title;
    $this->compact = $compact;
  }

  /** Obtient un contenu paramétré
   * @param $nom_boite Nom du contenu
   * @param $form Forme à renvoyer (0 pour du wiki, 1 pour la version brute)
   * @return Le texte correpondant (à placer dans un stdcontents pour être utilisable)
   */
  function get_textbox ($nom_boite,$form = 1)
  {
    $req = "SELECT `contenu_boite` FROM `site_boites` WHERE `nom_boite`='".$nom_boite."'";
    $rs = new requete ($this->db, $req);
    $ret = $rs->get_row();
    if ( $form == 0 )
    {
      $text = doku2xhtml($ret[0]);
      return $text;
    }
    return $ret[0];
  }

  function add_css ( $url )
  {
    $this->extracss[] = $url;  
  }
  
  function add_js ( $url )
  {
    $this->extrajs[] = $url;  
  }

  function add_rss ( $title, $url )
  {
    $this->rss[$title]=$url;
  }
  
  /** Termine et affiche la page
   */
  function end_page () // <=> html_render
  {
    global $wwwtopdir ;
    
    header("Content-Type: text/html; charset=utf-8");
    
    //echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";
    
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "<title>".$this->title." - association des etudiants de l'utbm</title>\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "themes/default/css/site.css\" title=\"AE2-NEW Base\" />\n";
    foreach ( $this->extracss as $url ) 
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . htmlentities($wwwtopdir . $url,ENT_NOQUOTES,"UTF-8"). "\" />\n";
    
    foreach ( $this->rss as $title => $url ) 
      echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".htmlentities($title,ENT_NOQUOTES,"UTF-8")."\" href=\"".htmlentities($url,ENT_NOQUOTES,"UTF-8")."\" />";
    
    echo "<link rel=\"SHORTCUT ICON\" href=\"" . $wwwtopdir . "favicon.ico\" />\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/site.js\">var site_topdir='$wwwtopdir';</script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/ajax.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/dnds.js\"></script>\n";
    
    foreach ( $this->extrajs as $url ) 
      echo "<script type=\"text/javascript\" src=\"".htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8")."\"></script>\n";
        
    echo "</head>\n";
    
    echo "<body>\n";
    /* Generate the logo */
        
    echo "<div id=\"site\">";    
        
    if (!$this->compact )
    {
      echo "<div class=\"box\" id=\"important\">\n";
      echo "<div class=\"body\">\n";
      echo $this->get_textbox('Important',0). "\n";
      echo "</div>\n";
      
      echo "</div>\n";
      
      echo "<div id=\"fsearchbox\">\n";
      echo "<form action=\"".$wwwtopdir."fsearch.php\" method=\"POST\">";
      echo "<input type=\"text\" id=\"fsearchpattern\" name=\"pattern\" onblur=\"fsearch_stop_delayed();\" onkeyup=\"fsearch_keyup(event,'$wwwtopdir');\" value=\"\" />\n";
      echo "</form>";
      echo "<div class=\"fend\"></div></div>\n";
    
      echo "<div id=\"fsearchres\"></div>\n";
      
      echo "<div id=\"logo\"><a href=\"http://ae.utbm.fr\"><img src=\"" . $wwwtopdir ."images/Ae.jpg\" height=\"60\" width=\"218\" alt=\"Logo AE\"/></a></div>";          

    }
    echo "<div class=\"tabsv2\">\n";
    $links=null;
    
    foreach ($this->tab_array as $entry)
    {
      
      echo "<span";
      if ($this->section == $entry[0])
      {
        echo " class=\"selected tab".$entry[0]."\"";
        $links=$entry[3];
      }
      else
        echo " class=\"tab".$entry[0]."\"";
        
      echo "><a id=\"tab_".$entry[0]."\" href=\"" . $wwwtopdir . $entry[1] . "\"";
      echo " title=\"" . $entry[2] . "\">".$entry[2] . "</a></span>\n";
    }
    
    echo "</div>\n"; // /tabs
    
    if ( $links )
    {
      echo "<div class=\"sectionlinks\">\n";  
      
      foreach ( $links as $entry )
      {
        if ( ereg("http://(.*)",$entry[0]) )
          echo "<a href=\"".$entry[0]."\">".$entry[1]."</a>\n";
        else
          echo "<a href=\"".$wwwtopdir.$entry[0]."\">".$entry[1]."</a>\n";
      }
      
      echo "</div>\n";
    }
    else
      echo "<div class=\"emptysectionlinks\"></div>\n";  
    
    echo "<div class=\"contents\">\n";
    $idpage = "";
    
    $mode = $this->user->id > 0 ? "c" : "nc";
    
    foreach ( $this->sides as $side => $names )
    {
      if ( count($names) ) 
      {
        $idpage .= substr($side,0,1);
        
        if ( isset($this->sides_ref[$side]) )
        {
          $ref = "dnds_".$this->sides_ref[$side];
          if ( isset($_SESSION["usersession"][$ref]) )
          {
            $n_names = array();
            $elts =   explode(",",$_SESSION["usersession"][$ref]);
            foreach ( $elts as $elt )
            {
              $name = substr($elt,5);  
              if ( in_array($name,$names) )
                $n_names[] = $name;
            }
            foreach ( $names as $name )
            {
              if ( !in_array($name,$n_names) )
                $n_names = array_merge( array($name), $n_names );
            }
            $names = $n_names;
          }        
        }
        else
          $ref = null;
          
        echo "<div id=\"$side\">\n";
        foreach ( $names as $name )
        {
        
          if ( $cts = $this->boxes[$name] )
          {
            echo "<div class=\"box\" id=\"sbox_$name\">\n";
            if ( $cts->title && ($ref != null) )
              echo "<h1><a onmousedown=\"dnds_startdrag(event,'sbox_$name','$ref');\" class=\"dragstartzone\">".$cts->title."</a></h1>\n";
            elseif ( $cts->title )
              echo "<h1>".$cts->title."</h1>\n";
            
            echo "<div class=\"body\" id=\"sbox_body_$name\">\n";      
          
            echo $cts->html_render();
          
            echo "</div>\n";
            echo "</div>\n";
          }
        
        }
        echo "</div>\n";
      }
    }
    
    if ( $idpage == "" ) $idpage = "n";
    
    echo "\n<!-- page -->\n";
    echo "<div class=\"page\" id=\"$idpage\">\n";
    
    $i=0;
    foreach ( $this->contents as $cts )
    {
      $cssclass = "article";
      
      if ( !is_null($cts->cssclass) )
        $cssclass = $cts->cssclass;      
      
      $i++;
      echo "<div class=\"$cssclass\"";
      if ( $cts->divid )
        echo " id=\"".$cts->divid."\"";
      else
        echo " id=\"cts$i\"";
      echo ">\n";
      
      if ( $cts->toolbox )
      {
        echo "<div class=\"toolbox\">\n";    
        echo $cts->toolbox->html_render()."\n";
        echo "</div>\n";  
      }        
      
      if ( $cts->title )
        echo "<h1>".$cts->title."</h1>\n";

      echo $cts->html_render();
      echo "</div>\n";
    }
    
    echo "<p class=\"text-footer\">";
    echo "<a href=\"". $wwwtopdir ."article.php?name=legals\">AE UTBM</a>";
    echo " - <a href=\"". $wwwtopdir ."article.php?name=docs:index\">Aide et documentation</a>";
    echo " - <a href=\"". $wwwtopdir ."article.php?name=rd\">R&D</a>";
    echo " - <a href=\"". $wwwtopdir ."equipeinfo\">Equipe info</a>";
    echo "<br/>\n"; 
    
    echo "Icones par <a href=\"http://www.everaldo.com/\">Everaldo.com</a></p>\n";
    
    echo "</div>\n"; // /page
    echo "<!-- end of page -->\n\n";
    
    echo "</div>\n"; // /contents
    echo "<div id=\"endsite\"></div></div>\n";    
    
    if ( $this->user->is_valid() && !ereg("majprofil\.php$",$_SERVER['SCRIPT_FILENAME']) )
    {
      $lastforce = strtotime("2007-09-20 21:00:00"); // TODO:algo de génération
      if ( is_null($this->user->date_maj) || $this->user->date_maj < $lastforce )
      {
        //print_r($this->user);
        echo "<div id=\"hugealert\">";    
        echo "<p>Merci de mettre à jour votre profil : ".
             "<a href=\"". $wwwtopdir ."majprofil.php\">le mettre à jour</a></p>";    
        echo "</div>";    
      }
    }    
    
    echo "</body>\n";
    echo "</html>\n";
      
  }
  
  /**
   * Rendu de la page en mode popup (sans header, sans boites laterales)
   */
  function popup_end_page ()
  {
    global $wwwtopdir ;
    
    header("Content-Type: text/html; charset=utf-8");
    
    //echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";
    
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "<title>".$this->title." - association des etudiants de l'utbm</title>\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "themes/default/css/site.css\" title=\"AE2-NEW Base\" />\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "css/popup.css\" title=\"AE2-NEW Base\" />\n";
    foreach ( $this->extracss as $url ) 
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . htmlentities($wwwtopdir . $url,ENT_NOQUOTES,"UTF-8"). "\" />\n";
    
    foreach ( $this->rss as $title => $url ) 
      echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".htmlentities($title,ENT_NOQUOTES,"UTF-8")."\" href=\"".htmlentities($url,ENT_NOQUOTES,"UTF-8")."\" />";
    
    echo "<link rel=\"SHORTCUT ICON\" href=\"" . $wwwtopdir . "favicon.ico\" />\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/site.js\">var site_topdir='$wwwtopdir';</script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/ajax.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/dnds.js\"></script>\n";
    
    foreach ( $this->extrajs as $url ) 
      echo "<script type=\"text/javascript\" src=\"".htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8")."\"></script>\n";
        
    echo "</head>\n";
    
    echo "<body>\n";
    /* Generate the logo */
        
    echo "<div id=\"popup\">";    
    
    $i=0;
    foreach ( $this->contents as $cts )
    {
      $cssclass = "article";
      
      if ( !is_null($cts->cssclass) )
        $cssclass = $cts->cssclass;      
      
      $i++;
      echo "<div class=\"$cssclass\"";
      if ( $cts->divid )
        echo " id=\"".$cts->divid."\"";
      else
        echo " id=\"cts$i\"";
      echo ">\n";
      
      if ( $cts->toolbox )
      {
        echo "<div class=\"toolbox\">\n";    
        echo $cts->toolbox->html_render()."\n";
        echo "</div>\n";  
      }        
      
      if ( $cts->title )
        echo "<h1>".$cts->title."</h1>\n";

      echo $cts->html_render();
      echo "</div>\n";
    }
    
    echo "</div>\n";
    echo "</body>\n";
    echo "</html>\n";
  }

  /** Charge tous les paramètres du site.
   * ATTENTION: ceci est UNIQUEMENT concu pour stocker des paramètres.
   * @private
   */
  function load_params()
  {
    $this->params = array();
    
    $req = new requete($this->db, "SELECT `nom_param`,`valeur_param` " .
        "FROM `site_parametres`");
            
    while ( list($id,$name) = $req->get_row() )
      $this->params[$id] = $name;
      
  }
  
  /**
   * Obtient un paramètre du site.
   * @param $name Nom du paramètre
   * @param $value $default par défaut retrouné si il n'est pas définit
   */
  function get_param ( $name, $default=null )
  {
    if ( !$this->params )  
      $this->load_params();
      
    if ( !isset($this->params[$name]) )  
      return $default;
    
    return unserialize($this->params[$name]);
  }

  /**
   * Définit un paramètre du site.
   * @param $name Nom du paramètre
   * @param $value Valeur du paramètre.
   */
  function set_param ( $name, $value )
  {
    if ( !$this->params )  
      $this->load_params();
    
    $value = serialize($value);
    
    if ( !isset($this->params[$name]) )
    {
      $sql = new insert($this->dbrw,"site_parametres",
        array ( 
          "nom_param" => $name,
          "valeur_param" => $value
        ));
    }
    elseif ( $this->params[$name] !== $value ) 
    {
      $sql = new update($this->dbrw,"site_parametres",
        array( "valeur_param" => $value),
        array( "nom_param" => $name));
    }
  }

  /**
   * Vérifie que l'utilisateur est vraiment sûre de procéder à une opération.
   * Certifié "boulet proof(tm)".
   * Remarque: ne fonctionne pas dans le cas de passage de tableaux en GET/POST
   * @param $section Section de la page de confirmation
   * @param $message Message à afficher
   * @param $uid identifiant unique de la question
   * @param $level niveau d'incidence (0:pas grave, 1:peu risqué, 2:trés risqué)
   */
  function is_sure ( $section,$message,$uid=null,$level=0 )
  {
    if ( isset($_POST["___i_am_really_sure"]) )
    {
      if ( $GLOBALS["svalid_call"] )
        return true;
      return false;
    }
    elseif ( isset($_POST["___finally_i_want_to_cancel"]) )
      return false;
    
    if ( !$uid ) $uid=$section.md5($message);
    
    $this->start_page($section,"Êtes vous sûre ?");
    
    $cts = new contents("Confirmation");
    
    if ( $level == 2 )
      $cts->add_paragraph("ATTENTION","huge");
          
    $cts->add_paragraph($message);
    
    if ( $level == 2 )
      $cts->add_paragraph("Cette opération <b>pourrais avoir de lourdes conséquences</b> sur le <b>bon fonctionnement des services</b> si elle été appliqué sur un élément critique. <b>Contactez un administrateur en cas de doute</b>.");
    
    $cts->add_paragraph("Êtes vous sûre ?");
    
    $frm = new form("suretobesurefor".$uid,"?");
    $frm->allow_only_one_usage();
    
    foreach ( $_POST as $key => $val )
      $frm->add_hidden($key,$val);
    foreach ( $_GET as $key => $val )
      $frm->add_hidden($key,$val);
      
    $frm->add_submit("___i_am_really_sure","OUI");  
    $frm->add_submit("___finally_i_want_to_cancel","NON");  
      
    $cts->add($frm);
      
    $this->add_contents($cts);
    
    $this->end_page();
    exit();
  }
}

?>
