<?php
/*
 * AECMS : CMS pour les clubs et activités de l'AE UTBM
 *
 * Copyright 2007
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

/**
 * @file
 */

/**
 * @defgroup aecms AECMS
 * Des sites en kit pour les activités de l'AE.
 *
 * Organisation d'un AECMS, la fonction install_aecms s'en charge trés bien :
 * <pre>
 * club/
 *   specific
 *     aecms.conf.php
 *     custom.css
 *   aecms --> /var/www/ae/www/taiste/aecms
 *   .htaccess
 *     RewriteEngine On
 *     RewriteRule ^([a-z]*)\.php(.*)$  aecms/$1.php$2 [L]
 *     RewriteRule ^$  aecms/index.php [L]
 *     RewriteRule ^images/(.*)$  aecms/images/$1 [L]
 *     RewriteRule ^css/(.*)$  aecms/css/$1 [L]
 * </pre>
 *
 */

$basedir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));

// Chargement de la configuration statique
if ( !file_exists($basedir."/specific/aecms.conf.php") ) // COnfiguration par défaut, pour les tests
{
  // Configuration par défaut
  define("CMS_ID_ASSO",1);
  define("CMS_PREFIX","cms:".CMS_ID_ASSO.":");
  $topdir = "../";
}
else
{
  include($basedir."/specific/aecms.conf.php");
  $topdir = dirname(readlink($basedir."/aecms"))."/";
}

// Verification de sécu
if ( CMS_ID_ASSO != intval(CMS_ID_ASSO) )
{
  header("Content-Type: text/html; charset=utf-8");
  echo "<p>Site actuellement en maintenance. Merci de votre compréhension.</p>";
  exit();
}

// Configuration générale (en BETA)
$wwwtopdir = "./";
/**
 * Repertoire de stockage des fichiers de configurations des AEMCS
 * @ingroup aecms
 */
define("CMS_CONFIGPATH","/var/www/ae/www/taiste/var/aecms");
define("CMS_CONFIGFILE",CMS_CONFIGPATH."/cms".CMS_ID_ASSO.".conf.php");

// Inclusion des classes AE2
require_once($topdir."include/site.inc.php");
require_once($topdir."include/entities/asso.inc.php");
require_once($topdir."include/entities/page.inc.php");

// Met à jour le catalogue pour AECMS
$GLOBALS["entitiescatalog"]["catphoto"][3]="photos.php";
$GLOBALS["entitiescatalog"]["photo"][3]="photos.php";
$GLOBALS["entitiescatalog"]["utilisateur"][3]=null;
$GLOBALS["entitiescatalog"]["asso"][3]=null;
$GLOBALS["entitiescatalog"]["page"][3]="index.php";

/*
 * NOTE : Il faudra modifier mysqlae.inc.php pour accepter les inclusions d'autres emplacements...
 * ou trouver une solution moins risquée. En aucun cas les fichiers du CMS et leur configuration
 * ne devront être accessible depuis le WEBDAV. L'idéal serait de désactiver les fichiers PHP autres que
 * ceux du CMS dans les webdav où sera exploité AECMS
 */



/**
 * Classe de gestion de site AECMS
 * @ingroup aecms
 * @author Julien Etelain
 */
class aecms extends site
{
  /** Association/activité dont c'est le site*/
  var $asso;
  /** URL publique du site */
  var $pubUrl;
  /** Paramétres du site */
  var $config;

  /**
   * Construteur de site
   * Utilise les constantes CMS_ID_ASSO, CMS_CONFIGFILE et CMS_PREFIX
   */
  function aecms()
  {
    $this->site(false);

    if ( ereg("^/var/www/ae/accounts/([a-z0-9]*)/aecms",$_SERVER['SCRIPT_FILENAME'],$match) )
      $this->pubUrl = "http://ae.utbm.fr/".$match[1]."/";
    else
      $this->pubUrl = "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["SCRIPT_NAME"])."/";

    $this->tab_array = array (array(CMS_PREFIX."accueil", "index.php", "Accueil"));
    $this->config = array(
      "membres.allowjoinus"=>1,
      "membres.upto"=>ROLEASSO_TRESORIER,
      "boxes.sections"=> CMS_PREFIX."accueil",
      "boxes.names"=>"calendrier",
      "home.news"=>1,
      "home.excludenewssiteae"=>0,
      "css.base"=>"base.css",
      "footer"=>false
    );

    $this->asso = new asso($this->db,$this->dbrw);
    $this->asso->load_by_id(CMS_ID_ASSO);

    $this->set_side_boxes("left",array());
    $this->set_side_boxes("right",array());

    if ( file_exists(CMS_CONFIGFILE) && !isset($_GET["aecms_admin_ignoreconf"]) )
      include(CMS_CONFIGFILE);

    if ($this->is_user_admin())
      $this->tab_array[] = array(CMS_PREFIX."config", "configurecms.php", "Administration");

  }

  function start_page ( $section, $title,$compact=false )
  {
    $sections = explode(",",$this->config["boxes.sections"]);
    if ( in_array($section,$sections) )
    {
      $boxes = explode(",",$this->config["boxes.names"]);
      $this->set_side_boxes("right",$boxes,"aecms");

      foreach( $boxes as $name )
      {
        if ( $name == "calendrier" )
          $this->add_box("calendrier",new calendar($this->db,$this->asso->id));
        else
          $this->add_box($name,$this->get_box($name));
      }
    }

    interfaceweb::start_page($section,$title,$compact);
  }

  /**
   * Enregistre la configuration acteulle ( tab_array et config )
   * Ecrit le fichier CMS_CONFIGFILE, et si nécessaire creer le dossier CMS_CONFIGPATH
   */
  function save_conf()
  {
    if ( !$this->is_user_admin() )
      return;

    if ( !file_exists(CMS_CONFIGPATH) )
      mkdir(CMS_CONFIGPATH);

    $f = fopen(CMS_CONFIGFILE,"wt");

    if ( !$f )
      return;

    fwrite($f,"<?php\n");


    fwrite($f,'$'."this->config = array(\n");

    $n=0;
    $cnt=count($this->config);

    if ( $cnt == 0 )
      fwrite($f,");\n");
    else
    {
      foreach ( $this->config as $key => $value )
      {
        if ( is_numeric($value) || is_bool($value) )
          fwrite($f,' \''.addcslashes($key,'\'\\').'\' => '.str_replace(",",".",$value).'');
        else
          fwrite($f,' \''.addcslashes($key,'\'\\').'\' => \''.addcslashes($value,'\'\\').'\'');

        $n++;
        if ( $n == $cnt )
          fwrite($f,");\n");
        else
          fwrite($f,",\n");
      }
    }

    fwrite($f,'$'."this->tab_array = array(\n");

    $n=0;
    $cnt=count($this->tab_array)-1;

    if ( $cnt == 0 )
      fwrite($f,");\n");
    else
    {
      global $_REQUEST;
      foreach ( $this->tab_array as $row )
      {
        if ( $row[0] != CMS_PREFIX."config" )
        {
	  if($_REQUEST["action"] != "addonglet")
	    fwrite($f," array(\"".$row[0]."\",\"".$row[1]."\",\"".$row[2]."\"");
          else
            fwrite($f," array(\"".addslashes($row[0])."\",\"".addslashes($row[1])."\",\"".addslashes($row[2])."\"");
          $n++;
          if ( $n == $cnt )
            fwrite($f,"));\n");
          else
            fwrite($f,"),\n");
        }
      }
    }
    fwrite($f,"\n?>");

    fclose($f);
  }

  /**
   * Determine si l'utilisateur connecté est administrateur du AECMS.
   * @return true si l'utilisateur est administrateur, false sinon.
   */
  function is_user_admin()
  {
    if ( !$this->user->is_valid() )
      return false;

    if ( !$this->asso->is_member_role($this->user->id,ROLEASSO_MEMBREBUREAU)
         && !$this->user->is_in_group("root") )
      return false;

    return true;
  }

  /**
   * Renvoie le stdcontents pour la boite demandée, si la boite en gestion est une "page"
   * @return une instance de stdcontents ou NULL si la boite demandée n'existe pas
   */
  function get_box ( $name )
  {
    $page = new page ($this->db);
    $page->load_by_pagename(CMS_PREFIX."boxes:".$name);

    if ( !$page->is_valid() || !$page->is_right($this->user,DROIT_LECTURE) )
      return null;

    return $page->get_contents();
  }


  function end_page () // <=> html_render
  {
    global $wwwtopdir, $basedir ;

    header("Content-Type: text/html; charset=utf-8");

    //echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";

    echo "<html>\n";
    echo "<head>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "<title>".$this->title." - ".htmlentities($this->asso->nom,ENT_NOQUOTES,"UTF-8")."</title>\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "css/doku.css\" />\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "css/".$this->config["css.base"]."\" />\n";

    foreach ( $this->extracss as $url )
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . htmlentities($wwwtopdir . $url,ENT_NOQUOTES,"UTF-8"). "\" />\n";

    if ( file_exists($basedir."/specific/custom.css") )
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "specific/custom.css\" />\n";

    foreach ( $this->rss as $title => $url )
      echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".htmlentities($title,ENT_NOQUOTES,"UTF-8")."\" href=\"".htmlentities($url,ENT_NOQUOTES,"UTF-8")."\" />";

    foreach ( $this->extrajs as $url )
      echo "<script type=\"text/javascript\" src=\"".htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8")."\"></script>\n";

    echo "<script type=\"text/javascript\" src=\"/js/site.js\">var site_topdir='$wwwtopdir';</script>\n";
    echo "<script type=\"text/javascript\" src=\"/js/ajax.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"/js/dnds.js\"></script>\n";
    echo "</head>\n";

    echo "<body>\n";
        /* Generate the logo */
    echo "<div id=\"site\">";

    if (!$this->compact )
    {
      echo "<div id=\"logo\"><a href=\"".htmlentities($this->pubUrl,ENT_QUOTES,"UTF-8")."\">";
      echo htmlentities($this->asso->nom,ENT_QUOTES,"UTF-8");
      echo "</a></div>\n";
    }

    echo "<div class=\"tabsv2\">\n";
    $links=null;

    foreach ($this->tab_array as $entry)
    {
      echo "<span";
      if ($this->section == $entry[0])
      {
        echo " class=\"selected\"";
        $links=$entry[3];
      }
      echo "><a id=\"tab_".$entry[0]."\" href=\"" . $wwwtopdir . $entry[1] . "\"";
      echo " title=\"" . stripslashes($entry[2]) . "\">".
        stripslashes($entry[2]) . "</a></span>\n";
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

    foreach ( $this->sides as $side => $names )
    {
      if ( count($names) )
      {
        $idpage .= substr($side,0,1);
        echo "<div id=\"$side\">\n";
        foreach ( $names as $name )
        {
          if ( $cts = $this->boxes[$name] )
          {
            echo "<div class=\"box\" id=\"sbox_$name\">\n";
            if ( !empty($cts->title) )
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

    foreach ( $this->contents as $cts )
    {
      $cssclass = "article";

      if ( !is_null($cts->cssclass) )
        $cssclass = $cts->cssclass;

      echo "<div class=\"$cssclass\"";
      if ( $cts->divid )
        echo " id=\"".$cts->divid."\"";
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
    echo "<!-- end of page -->\n\n";

    echo "<p class=\"footer\">\n";

    if ( !is_null($this->asso->id_parent) && !$this->config["footer"] )
    {
      echo "<a href=\"/\">association des etudiants de l'utbm</a>";
      echo " - <a href=\"index.php?name=:legals\">informations légales</a>";
      echo " - <a href=\"contact.php\">contact</a>";
    }
    elseif ( !is_null($this->asso->id_parent) )
    {
      echo stripslashes($this->config["footer"]);
    }
    else
    {
      echo "<a href=\"index.php?name=legals\">informations légales</a>";
      echo " - <a href=\"contact.php\">contact</a>";
    }

    echo "</p>\n";

    echo "</div>\n"; // /contents
    echo "<div id=\"endsite\"></div></div>\n";
    echo "</body>\n";
    echo "</html>\n";

  }


}

$site = new aecms();

?>
