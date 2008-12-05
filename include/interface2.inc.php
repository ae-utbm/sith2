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

$timing["all"] -= microtime(true);

setlocale(LC_ALL,"fr_FR.UTF8");

if( !strncmp('/var/www/ae/www/taiste', $_SERVER['SCRIPT_FILENAME'], 22) )
  $GLOBALS["taiste"] = true;
else
  $GLOBALS["taiste"] = false;

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");
require_once($topdir . "include/entities/std.inc.php");
require_once($topdir . "include/entities/utilisateur.inc.php");
require_once($topdir . "include/cts/standart.inc.php");

if ( !isset($wwwtopdir) )
  $wwwtopdir = $topdir;


/** Classe générant l'interface
 * @see site
 * @ingroup display
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

  protected $buffer="";

  var $section;
  var $title;

  var $extracss;
  var $rss;
  var $extrajs;

  var $compact;

  var $params; // cache des paramètres

  var $meta_keywords;
  var $meta_description;
  var $alternate;

  var $tab_array = array (array ("accueil", "index.php", "Accueil"),
        array ("presentation", "article.php?name=presentation", "Présentation",
           array (
              array ("article.php?name=presentation", "L'AE" ),
              array ("asso.php", "Associations et clubs" ),
              array ("article.php?name=presentation:services", "Services quotidiens" )
             ) ),
        array ("services", "article.php?name=services", "Services" ),
        //array ("pg", "pgae.php", "Petit géni"),
        array ("eboutic", "e-boutic/", "E-boutic"),
        array ("matmatronch", "matmatronch/", "Matmatronch"),
        array ("sas", "sas2/", "SAS"),
        array ("forum", "forum2/", "Forum"),
        array ("wiki", "wiki2/", "Wiki"),
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

    $this->sides["left"] = array();
    $this->sides["right"] = array();

    $this->user = new utilisateur( $db, $dbrw );
    $this->extracss = array();
    $this->extrajs = array();
    $this->rss = array();
    $this->contents=array();
    $this->alternate=array();
  }

  /** Défini les boites à afficher sur un coté
   * @param $side Coté (left ou right)
   * @param $boxes Array des nom des boites à afficher
   */
  function set_side_boxes ( $side, $boxes, $ref=null )
  {
/*
    if ( $side != "left" && $side != "right" ) return;
    $this->sides[$side] = $boxes;

    if ( $ref == null )
    {
      if ( isset($this->sides_ref[$side]) )
        unset($this->sides_ref[$side]);
    }
    else
      $this->sides_ref[$side] = $ref;
*/
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
    $this->add_alternate ( "application/rss+xml", $title, $url );
  }

  /** Termine et affiche la page
   */
  function end_page () // <=> html_render
  {
    global $wwwtopdir,$timing ;
    $timing["render"] -= microtime(true);

    header("Content-Type: text/html; charset=utf-8");

    $this->buffer .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
    $this->buffer .= "<head>\n";

    $this->buffer .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n"; // (IE6 Legacy support)
    if(!defined('NOTAE'))
      echo "<title>".htmlentities($this->title,ENT_COMPAT,"UTF-8")." - association des etudiants de l'utbm</title>\n";
    else
      echo "<title>".htmlentities($this->title,ENT_COMPAT,"UTF-8")."</title>\n";

    $this->buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "themes/default2/css/site2.css?".filemtime($wwwtopdir . "themes/default/css/site2.css")."\" title=\"AE2-NEW2\" />\n";
    foreach ( $this->extracss as $url )
      if(file_exists(htmlentities($wwwtopdir . $url,ENT_COMPAT,"UTF-8")))
        $this->buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".
             htmlentities($wwwtopdir . $url,ENT_COMPAT,"UTF-8")."?".
             filemtime(htmlentities($wwwtopdir . $url,ENT_COMPAT,"UTF-8"))."\" />\n";

    foreach ( $this->alternate as $row )
    {
      $this->buffer .= "<link rel=\"alternate\" ".
        "type=\"".htmlentities($row[0],ENT_COMPAT,"UTF-8")."\" ".
        "title=\"".htmlentities($row[1],ENT_COMPAT,"UTF-8")."\" ".
        "href=\"".htmlentities($row[2],ENT_COMPAT,"UTF-8")."\" />\n";
    }

    if ( !empty($this->meta_keywords) )
      $this->buffer .= "<meta name=\"keywords\" content=\"".htmlentities($this->meta_keywords,ENT_COMPAT,"UTF-8")."\" />\n";

    if ( !empty($this->meta_description) )
      $this->buffer .= "<meta name=\"description\" content=\"".htmlentities($this->meta_description,ENT_COMPAT,"UTF-8")."\" />\n";

    $this->buffer .= "<link rel=\"SHORTCUT ICON\" href=\"" . $wwwtopdir . "favicon.ico\" />\n";
    $this->buffer .= "<script type=\"text/javascript\">var site_topdir='".$wwwtopdir."';</script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/site.js?".filemtime($wwwtopdir . "js/site.js")."\"></script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/ajax.js?".filemtime($wwwtopdir . "js/ajax.js")."\"></script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/dnds.js?".filemtime($wwwtopdir . "js/dnds.js")."\"></script>\n";

    foreach ( $this->extrajs as $url )
      $this->buffer .= "<script type=\"text/javascript\" src=\"".htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8")."?".filemtime(htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8"))."\"></script>\n";

    $this->buffer .= "</head>\n";

    $this->buffer .= "<body>\n";
    /* Generate the logo */
    $this->buffer .= "<div id=\"site\">\n";

/* header */
    $this->buffer .= "<div id='header'>\n";
    $this->buffer .= "<div id=\"logo\"><a href=\"http://ae.utbm.fr\"><img src=\"" . $wwwtopdir ."images/Ae.jpg\" height=\"60\" width=\"218\" alt=\"Logo AE\"/></a></div>\n";

    $this->buffer .= "<div id='headermenu'>\n";
    if ( !$this->user->is_valid() )
    {
      $this->buffer .= "<div id=\"overlay\" onclick=\"hideConnexionBox()\" style=\"display:none\"></div>\n";
      $this->buffer .= '<div id="passwordbox" style="display:none">';
      $this->buffer .= '<img id="close" src="'.$topdir.'images/actions/delete.png" onClick="hideConnexionBox()" alt="Fermer" ';
      $this->buffer .= 'title="Fermer" />';
      $frm = new form("connect",$topdir."connect.php",true,"POST","Connexion");
      $jsoch = "javascript:switchSelConnection(this);";
      $frm->add_select_field("domain",
           "Connexion",
           array("utbm"=>"UTBM / Assidu",
           "id"=>"ID",
           "autre"=>"E-mail",
           "alias"=>"Alias"),
           false,
           "",
           false,
           true,
           $jsoch);
      $frm->add_text_field("username","Utilisateur","prenom.nom","",20,true);
      $frm->add_password_field("password","Mot de passe","","",20);
      $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
      $frm->add_submit("connectbtn","Se connecter");
      $this->buffer .= $frm->html_render();
      unset($frm);
      $this->buffer .= "</div>\n";

      $this->buffer .= "<script type=\"text/javascript\">\n";
      $this->buffer .= "var menu_utilisateur=new Array();";
      $this->buffer .= "menu_utilisateur[0]='<a href=\"".$topdir."index.php\" onClick=\"return showConnexionBox()\">Connexion</a>';";
      $this->buffer .= "menu_utilisateur[1]='<a href=\"".$topdir."password.php\">Mot de passe perdu</a>';";
      $this->buffer .= "menu_utilisateur[2]='<a href=\"".$topdir."newaccount.php\">Créer un compte</a>';";
      $this->buffer .= "</script>";
      $this->buffer .= "<div id='login' onMouseover=\"dropdownmenu(this, event, menu_utilisateur, '150px')\" onMouseout=\"delayhidemenu()\">\n";
        $this->buffer .= "Identification\n";
    }
    elseif($this->user->type=="srv" )
    {
      $this->buffer .= "<div id='login'>\n";
      $this->buffer .= "<a href=\"".$topdir."user/compteae.php\">Factures en attente de paiement : ".(sprintf("%.2f", $this->user->montant_compte/-100))." Euros</a>\n";
    }
    else
    {
      $this->buffer .= "<script type=\"text/javascript\">\n";
      $this->buffer .= "var menu_utilisateur=new Array();";
      $i=0;
      if($this->user>-ae)
      {
        $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."user/compteae.php\">Compte AE : ".(sprintf("%.2f", $this->user->montant_compte/100))." Euros</a>';";
        $i++;
      }
      $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."user.php?id_utilisateur=".$this->user->id."\">Informations personnelles</a>';";
      $i++;
      if($this->user->utbm)
      {
        $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."trombi/index.php\">Trombinoscope</a>';";
        $i++;
      }
      if( $this->user->is_in_group("jobetu_etu") )
      {
        $jobuser = new jobuser_etu($this->db);
        $jobuser->load_by_id($this->user->id);
        $jobuser->load_annonces();
        $this->buffer .= "menu_utilisateur[$i]='<a href=\"".
                         $topdir."jobetu/board_etu.php\">Mon compte JobEtu (".count($jobuser->annonces).")</a>';";
        unset($jobuser);
      }
      elseif( $this->user->is_in_group("jobetu_client") )
        $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."jobetu/board_client.php\">AE JobEtu</a>';";
      else
        $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."jobetu/index.php\">AE JobEtu</a>';";
      $i++;
      $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."user/outils.php\">Mes outils</a>';";
      $i++;
      $this->buffer .= "menu_utilisateur[$i]='<a href=\"".$topdir."disconnect.php\">Déconnexion</a>';";
      $this->buffer .= "</script>";
      $this->buffer .= "<div id='login' onMouseover=\"dropdownmenu(this, event, menu_utilisateur, '150px')\" onMouseout=\"delayhidemenu()\">\n";
      $this->buffer .= $this->user->prenom." ".$this->user->nom;
    }
    $this->buffer .= "</div>\n";

    $req = new requete($this->db,
        "SELECT `asso`.`id_asso`, " .
        "`asso`.`nom_asso` ".
        "FROM `asso_membre` " .
        "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
        "WHERE `asso_membre`.`role` > 1 AND `asso_membre`.`date_fin` IS NULL " .
        "AND `asso_membre`.`id_utilisateur`='".$this->user->id."' " .
        "AND `asso`.`id_asso` != '1' " .
        "ORDER BY asso.`nom_asso`");
    if ( $req->lines > 0 || $this->user->is_in_group("root") || $this->user->is_in_group("moderateur_site") )
    {
      $this->buffer .= "<script type=\"text/javascript\">\n";
      $this->buffer .= "var menu_assos=new Array();";
      $i=0;
      if( $this->user->is_in_group("root") )
      {
        $this->buffer .= "menu_assos[".$i."]='<a href=\"".$topdir."rootplace/index.php\">Équipe informatique</a>';";
        $i++;
      }
      if($this->user->is_in_group("moderateur_site"))
      {
        $this->buffer .= "menu_assos[".$i."]='<a href=\"".$topdir."ae/com.php\">Équipe com</a>';";
        $i++;
      }
      while(list($id,$nom)=$req->get_row())
      {
        $this->buffer .= "menu_assos[".$i."]='<a href=\"".$topdir."asso/index.php?id_asso=$id\">$nom</a>';";
        $i++;
      }
      $this->buffer .= "</script>";
      $this->buffer .= "<div id='assos' onMouseover=\"dropdownmenu(this, event, menu_assos, '150px')\" onMouseout='delayhidemenu()'>\n";
      $this->buffer .= "Gestion assos/clubs";
      $this->buffer .= "</div>\n";
    }


    $this->buffer .= "<div id=\"fsearchbox\">\n";
    $this->buffer .= "<form action=\"".$wwwtopdir."fsearch.php\" method=\"post\">";
    $this->buffer .= "<input type=\"text\" id=\"fsearchpattern\" name=\"pattern\" onblur=\"fsearch_stop_delayed();\" onkeyup=\"fsearch_keyup(event);\" value=\"\" />\n";
    $this->buffer .= "</form>";
    $this->buffer .= "<div class=\"fend\"></div></div>\n";

    $this->buffer .= "<div id=\"fsearchres\"></div>\n";
    $this->buffer .= "</div>\n";
    $this->buffer .= "</div>\n";
/* fin header */

    $this->buffer .= "<div class=\"tabsv2\">\n";
    $links=null;

    foreach ($this->tab_array as $entry)
    {

      $this->buffer .= "<span";
      if ($this->section == $entry[0])
      {
        $this->buffer .= " class=\"selected tab".$entry[0]."\"";
        $links=$entry[3];
      }
      else
        $this->buffer .= " class=\"tab".$entry[0]."\"";

      $this->buffer .= "><a id=\"tab_".$entry[0]."\" href=\"" . $wwwtopdir . $entry[1] . "\"";
      $this->buffer .= " title=\"" . $entry[2] . "\">".$entry[2] . "</a></span>";
    }

    $this->buffer .= "</div>\n"; // /tabs

    if ( $links )
    {
      $this->buffer .= "<div class=\"sectionlinks\">";

      foreach ( $links as $entry )
      {
        if ( !strncmp("http://",$entry[0],7) )
          $this->buffer .= "<a href=\"".$entry[0]."\">".$entry[1]."</a>";
        else
          $this->buffer .= "<a href=\"".$wwwtopdir.$entry[0]."\">".$entry[1]."</a>";
      }

      $this->buffer .= "</div>\n";
    }
    else
      $this->buffer .= "<div class=\"emptysectionlinks\"></div>\n";

    $this->buffer .= "<div class=\"contents\">\n";
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

        $this->buffer .= "<div id=\"".$side."\" class=\"clearfix\">\n";
        foreach ( $names as $name )
        {

          if ( $cts = $this->boxes[$name] )
          {
            $this->buffer .= "<div class=\"box\" id=\"sbox_".$name."\">\n";
            if ( $cts->title && ($ref != null) )
              $this->buffer .= "<h1><a onmousedown=\"dnds_startdrag(event,'sbox_".$name."','".$ref."');\" class=\"dragstartzone\">".$cts->title."</a></h1>\n";
            elseif ( $cts->title )
              $this->buffer .= "<h1>".$cts->title."</h1>\n";

            $this->buffer .= "<div class=\"body\" id=\"sbox_body_".$name."\">\n";

            $this->buffer .= $cts->html_render();

            $this->buffer .= "</div>\n";
            $this->buffer .= "</div>\n";
          }

        }
        $this->buffer .= "</div>\n";
      }
    }

    if ( $idpage == "" ) $idpage = "n";

    $this->buffer .= "\n<!-- page -->\n";
    $this->buffer .= "<div class=\"page\" id=\"".$idpage."\">\n";

    $i=0;
    foreach ( $this->contents as $cts )
    {
      $cssclass = "article";

      if ( !is_null($cts->cssclass) )
        $cssclass = $cts->cssclass;

      $i++;


      $this->buffer .= "<div class=\"".$cssclass."\"";
      if ( $cts->divid )
        $this->buffer .= " id=\"".$cts->divid."\"";
      else
        $this->buffer .= " id=\"cts".$i."\"";
      $this->buffer .= ">\n";

      if ( $cts->toolbox )
      {
        $this->buffer .= "<div class=\"toolbox\">\n";
        $this->buffer .= $cts->toolbox->html_render()."\n";
        $this->buffer .= "</div>\n";
      }

      if ( $cts->title )
        $this->buffer .= "<h1>".$cts->title."</h1>\n";

      $this->buffer .= $cts->html_render();

      $this->buffer .= "</div>\n";
    }

    $this->buffer .= "<p class=\"text-footer\">";
    $this->buffer .= "<a href=\"". $wwwtopdir ."article.php?name=legals\">AE UTBM</a>";
    $this->buffer .= " - <a href=\"". $wwwtopdir ."article.php?name=docs:index\">Aide et documentation</a>";
    $this->buffer .= " - <a href=\"". $wwwtopdir ."article.php?name=rd\">R&amp;D</a>";
    $this->buffer .= " - <a href=\"". $wwwtopdir ."wiki2/?name=ae:info\">Equipe info</a>";
    $this->buffer .= "<br/>\n";

    $this->buffer .= "Icones par <a href=\"http://www.everaldo.com/\">Everaldo.com</a></p>\n";

    $this->buffer .= "</div>\n"; // /page
    $this->buffer .= "<!-- end of page -->\n\n";

    $this->buffer .= "</div>\n"; // /contents
    $this->buffer .= "<div id=\"endsite\">&nbsp;</div></div>\n";

    if ( $this->get_param("backup_server",true) )
    {
      $this->buffer .= "<div id=\"topalert\">";
      $this->buffer .= "<img width=\"16\" height=\"16\" src=\"".$wwwtopdir."themes/default/images/exclamation.png\" />";
      $this->buffer .= "Le système fonctionne actuellement sur le serveur de secours, ".
           "veuillez limiter vos actions au strict minimum.";
      $this->buffer .= "</div>";
    }
    elseif ( $this->get_param("warning_enabled",true) )
    {
      $this->buffer .= "<div id=\"topalert\">";
      $this->buffer .= "<img width=\"16\" height=\"16\" src=\"".$wwwtopdir."themes/default/images/exclamation.png\" />";
      $this->buffer .= $this->get_param("warning_message");
      $this->buffer .= "</div>";
    }
    $this->buffer .= "</body>\n";
    $this->buffer .= "</html>\n";
    echo $this->buffer;
    $timing["render"] += microtime(true);
    $timing["all"] += microtime(true);
    echo "<!-- ";
    print_r($timing);
    if ( $GLOBALS["taiste"] )
      echo "\non est en taiste\n";
    echo " -->";
  }

  /**
   * Rendu de la page en mode popup (sans header, sans boites laterales)
   */
  function popup_end_page ()
  {
    global $wwwtopdir ;

    header("Content-Type: text/html; charset=utf-8");

    //$this->buffer .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";

    $this->buffer .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
    $this->buffer .= "<head>\n";
    $this->buffer .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
    $this->buffer .= "<title>".htmlentities($this->title,ENT_COMPAT,"UTF-8")." - association des etudiants de l'utbm</title>\n";
    $this->buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "themes/default/css/site.css?".filemtime($wwwtopdir . "themes/default/css/site.css")."\" title=\"AE2-NEW2\" />\n";
    $this->buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wwwtopdir . "css/popup.css?".filemtime($wwwtopdir ."css/popup.css")."\" />\n";
    foreach ( $this->extracss as $url )
      $this->buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . htmlentities($wwwtopdir . $url,ENT_COMPAT,"UTF-8"). "\" />\n";

    foreach ( $this->rss as $title => $url )
      $this->buffer .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".htmlentities($title,ENT_COMPAT,"UTF-8")."\" href=\"".htmlentities($url,ENT_COMPAT,"UTF-8")."\" />";

    $this->buffer .= "<link rel=\"SHORTCUT ICON\" href=\"" . $wwwtopdir . "favicon.ico\" />\n";
    $this->buffer .= "<script type=\"text/javascript\">var site_topdir='".$wwwtopdir."';</script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/site.js\"></script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/ajax.js\"></script>\n";
    $this->buffer .= "<script type=\"text/javascript\" src=\"" . $wwwtopdir . "js/dnds.js\"></script>\n";

    foreach ( $this->extrajs as $url )
      $this->buffer .= "<script type=\"text/javascript\" src=\"".htmlentities($wwwtopdir.$url,ENT_QUOTES,"UTF-8")."\"></script>\n";

    $this->buffer .= "</head>\n";

    $this->buffer .= "<body>\n";
    /* Generate the logo */

    $this->buffer .= "<div id=\"popup\">";

    $i=0;
    foreach ( $this->contents as $cts )
    {
      $cssclass = "article";

      if ( !is_null($cts->cssclass) )
        $cssclass = $cts->cssclass;

      $i++;
      $this->buffer .= "<div class=\"".$cssclass."\"";
      if ( $cts->divid )
        $this->buffer .= " id=\"".$cts->divid."\"";
      else
        $this->buffer .= " id=\"cts".$i."\"";
      $this->buffer .= ">\n";

      if ( $cts->toolbox )
      {
        $this->buffer .= "<div class=\"toolbox\">\n";
        $this->buffer .= $cts->toolbox->html_render()."\n";
        $this->buffer .= "</div>\n";
      }

      if ( $cts->title )
        $this->buffer .= "<h1>".$cts->title."</h1>\n";

      $this->buffer .= $cts->html_render();
      $this->buffer .= "</div>\n";
    }

    $this->buffer .= "</div>\n";
    $this->buffer .= "</body>\n";
    $this->buffer .= "</html>\n";
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

    $this->params["backup_server"] = serialize($_SERVER["BACKUP_AE_SERVER"]);
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
      $this->params[$name]=$value;
    }
    elseif ( $this->params[$name] !== $value )
    {
      $sql = new update($this->dbrw,"site_parametres",
        array( "valeur_param" => $value),
        array( "nom_param" => $name));      //$this->buffer .= " onmouseover=\"tabsection('".$entry[0]."', 'hoversectionlinks');\"";
      $this->params[$name]=$value;
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

    $cts->add_paragraph("Êtes vous sûr ?");

    $frm = new form("suretobesurefor".$uid,"?");
    $frm->allow_only_one_usage();

    foreach ( $_POST as $key => $val )
      if ( $key != "magicform" )
      {
        if($key=="__script__")
          $frm->add_hidden($key,htmlspecialchars($val));
        else
          $frm->add_hidden($key,$val);
      }
    foreach ( $_GET as $key => $val )
      if ( $key != "magicform" )
        $frm->add_hidden($key,$val);

    $frm->add_submit("___i_am_really_sure","OUI");
    $frm->add_submit("___finally_i_want_to_cancel","NON");

    $cts->add($frm);

    $this->add_contents($cts);

    $this->end_page();
    exit();
  }

  function set_meta_information( $keywords, $description )
  {
    $this->meta_keywords = $keywords;
    $this->meta_description = $description;
  }

  function add_alternate ( $type, $title, $href )
  {
    $this->alternate[]=array($type,$title,$href);
  }

  function add_alternate_geopoint ( &$geopoint )
  {
    global $wwwtopdir;
    $this->add_alternate("application/vnd.google-earth.kml+xml","KML",$wwwtopdir."loc.php?id_geopoint=".$geopoint->id."&action=kml");
  }


}

?>
