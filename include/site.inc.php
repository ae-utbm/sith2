<?php
/** @file
 *
 * @brief Fonctions générales du site.
 *
 */
/* Copyright 2004,2005,2006
 * - Alexandre Belloni <alexandre POINT belloni CHEZ utbm POINT fr>
 * - Thomas Petazzoni <thomas POINT petazzoni CHEZ enix POINT org>
 * - Maxime Petazzoni <maxime POINT petazzoni CHEZ bulix POINT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des 0tudiants de
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
session_start();

if ( $_SERVER["REMOTE_ADDR"] == "127.0.1.1" )
  $GLOBALS["is_using_ssl"] = true;
else
  $GLOBALS["is_using_ssl"] = false;

require_once($topdir . "include/interface.inc.php");
require_once($topdir . "include/globals.inc.php");
require_once($topdir . "include/cts/calendar.inc.php");
require_once($topdir . "include/entities/sondage.inc.php");
require_once($topdir . "include/entities/campagne.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");

/** La classe principale du site */
class site extends interfaceweb
{
  /** Constructeur de la classe */
  function site ()
  {
    $dbro = new mysqlae ();
    if (!$dbro)
      $this->fatal("no db");

    $dbrw = new mysqlae ("rw");
    if (!$dbrw)
			$this->fatal("no dbrw");

		$this->interfaceweb($dbro, $dbrw);
		
		if( !preg_match('/taiste/', $_SERVER['SCRIPT_NAME']) )
		  $this->stats();

    if($_COOKIE['AE2_SESS_ID'])
      $this->load_session($_COOKIE['AE2_SESS_ID']);

		if ( $this->get_param("closed",false) && !$this->user->is_in_group("root") )
		  $this->fatal("closed");

    $this->set_side_boxes("left",array("calendrier","alerts","connexion"),"default_left");
    $this->add_box("calendrier",new calendar($this->db));
    $this->add_box("connexion", $this->get_connection_contents());

    $this->add_css("themes/weekair/css/site.css");
    
  }

  function stats()
  {
    $page=$_SERVER['SCRIPT_NAME'];
		$req = new requete($this->db, "SELECT * FROM `stats_page` WHERE `page` = '".mysql_escape_string($page)."'");
    if ( $req->lines != 0 )
      $stats = new requete($this->dbrw, "UPDATE `stats_page` SET `visites`=`visites`+1 WHERE `page`='".mysql_escape_string($page)."'");
    else
      $stats = new insert($this->dbrw, "stats_page", array("page"=>mysql_escape_string($page), "visites"=>1));

    $this->stats_os();
    $this->stats_browser();
  }

  function stats_os()
  {
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (eregi('(win|windows) ?(9x ?4\.90|Me)', $agent))
      $os="Windows ME";
    elseif (eregi('(win|windows) ?(98)', $agent))
      $os="Windows 98";
    elseif (eregi('(win|windows) ?(2000)', $agent))
      $os="Windows 2000";
    elseif (eregi('(win|windows) ?(95)', $agent))
      $os="Windows 95";
    elseif (eregi('(win|windows) ?(NT)', $agent))
    {
      if (eregi('(win|windows) ?NT ?(5\.1|5\.2?)', $agent))
        $os="Windows XP";
      elseif (eregi('(win|windows) ?NT ?(5(\.0)?)', $agent))
        $os="Windows 2000";
      elseif (eregi('(win|windows) ?NT ?(6(\.0)?)', $agent))
        $os="Windows Vista";
    }
    elseif (eregi('(win|windows) ?XP', $agent))
      $os="Windows XP";
    elseif (eregi('(win|windows)', $agent))
      $os="Windows";
    elseif (eregi('(linux)', $agent))
      $os="Linux";
    elseif (eregi('(freebsd|openbsd|netbsd)', $agent))
      $os="BSD";
    elseif (eregi('(unix|x11)', $agent))
      $os="Unix";
    elseif (eregi('mac', $agent))
      $os="Mac OS X";
    elseif (eregi('(mac|ppc)', $agent))
      $os="Mac OS";
    elseif (eregi('(bot|google|slurp|scooter|spider|infoseek|arachnoidea|altavista)', $agent))
      $os="Bot";
    else
      $os="Autre";

    $req = new requete($this->db, "SELECT * FROM `stats_os` WHERE `os` = '".mysql_escape_string($os)."'");
    if ( $req->lines != 0 )
      $stats = new requete($this->dbrw, "UPDATE `stats_os` SET `visites`=`visites`+1 WHERE `os`='".mysql_escape_string($os)."'");
    else
      $stats = new insert($this->dbrw, "stats_os", array("os"=>mysql_escape_string($os), "visites"=>1));
  }

  function stats_browser()
  {
    $agent=$_SERVER['HTTP_USER_AGENT'];

    if (eregi('MSIE[ \/]([0-9\.]+)', $agent, $version))
      $browser="MSIE".$version[1];
    elseif (eregi('OPERA[ \/]([0-9\.]+)', $agent, $version))
      $browser="OPERA".$version[1];
    elseif (eregi('MOZILLA/([0-9.]+)', $agent, $version))
      $browser="MOZILLA".$version[1];
    else
      $browser="AUTRE";
  
    $req = new requete($this->db, "SELECT * FROM `stats_browser` WHERE `browser` LIKE '".mysql_escape_string($browser)."'");
    if ( $req->lines != 0 )
      $stats = new requete($this->dbrw, "UPDATE `stats_browser` SET `visites`=`visites`+1 WHERE `browser`='".mysql_escape_string($browser)."'");
    else
      $stats = new insert($this->dbrw, "stats_browser", array("browser"=>mysql_escape_string($browser), "visites"=>1));
  }

  function fatal ($debug="fatal")
  {
    echo "<?xml version=\"1.0\"?>\n";
    echo "<!DOCTYPE html PUBLIC \"--//W3C//DTD XHTML 1.1//EN\" ";
    echo "\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n\n";
    echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\">\n";
    echo " <head>\n";
    echo "  <title>AE UTBM</title>\n";
    echo "  <link rel=\"stylesheet\" href=\"css/fatal.css\" title=\"fatal\" />\n";
    echo " </head>\n\n";
    echo " <body><!-- DEBUG INFO: $debug -->\n";
    echo "  <p><img src=\"images/fatalerror.jpg\" alt=\"Site en maintenance\" /></p>\n";
    echo " </body>\n";
    echo "</html>\n";
    exit(); 
  }
  
  function load_session ( $sid )
  {
    $req = new requete($this->db, "SELECT `id_utilisateur`, `connecte_sess`, `expire_sess` FROM `site_sessions` WHERE `id_session` = '" .
                       mysql_escape_string($sid) . "'");
    list($uid,$connecte,$expire) = $req->get_row();

    if ( !is_null($expire) )
    {
      if ( strtotime($expire) < time() ) // Session expirée, fait le ménage
      {
        $req = new delete($this->dbrw, "site_sessions", array("id_session"=>$sid) );
        
        if ( isset($_COOKIE['AE2_SESS_ID']) )
        {
      $domain = ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != '127.0.0.1') ? $_SERVER['HTTP_HOST'] : false;
          setcookie ("AE2_SESS_ID", "", time() - 3600, "/", $domain, 0);
          unset($_COOKIE['AE2_SESS_ID']);
        }
        
        if ( isset($_SESSION['session_redirect']) )
          unset($_SESSION['session_redirect']);        
          
        return;
      }
      $expire = date("Y-m-d H:i:s",time()+(15*60)); // Session expire dans 15 minutes
    }
    
    $req = new update($this->dbrw, "site_sessions",
          array(
            "derniere_visite"  => date("Y-m-d H:i:s"),
            "expire_sess"=>$expire
            ),array("id_session" => $sid)); 
            
    $this->user->load_by_id($uid);
    
    if ($this->user->hash != "valid")
      $this->user->id = -1;
    else
    {
      $this->user->visite();
      
      if ( !isset($_SESSION["usersession"]) ) // restore le usersession
        $_SESSION["usersession"] = $this->user->get_param("usersession",null);
    }   
  }

  function start_page ( $section, $title,$compact=false )
  {
    global $topdir;
    $this->add_box("alerts",$this->get_alerts());

    parent::start_page($section,$title,$compact);

    if ( $section == "bdf" )
    {
        $this->set_side_boxes("right",array());
        $this->set_side_boxes("left",array("bdf"));
        $this->add_css("css/bdf.css");
        $this->add_box("bdf",$this->get_bdf_box());
    }
    elseif ( $section == "accueil" )
    {
      if (!$this->user->is_valid())
        $this->set_side_boxes("right",array("planning", "search","anniv","photo"),"accueil_nc_right");
      else
        $this->set_side_boxes("right",array("planning", "weekly_photo","anniv","sondage","photo","comptoirs","forum"),"accueil_c_right");

      $this->add_box("anniv", $this->get_anniv_contents());
      $this->add_box("planning", $this->get_planning_contents());
      $this->add_box("weekly_photo",$this->get_weekly_photo_contents());
      $this->add_box("sondage",$this->get_sondage());
      
      $this->add_box("comptoirs",$this->get_comptoirs_box());
      if ($this->user->is_valid())
        $this->add_box("forum",$this->get_forum_box());
      //
    }
    elseif ( $section == "pg" )
    {
      $this->set_side_boxes("left",array("pg","connexion"),"pg_left");
      $this->add_box("pg", $this->get_petit_geni());
    }
    elseif ( $section == "matmatronch" )
    {
      require_once($topdir . "include/cts/newsflow.inc.php");
      
      $this->set_side_boxes("left",array("mmt","lastnews","connexion"),"mmt_left");
      $this->add_box("mmt", $this->get_mmt_box());
      $this->add_box("lastnews",new newslist ( "Denières nouvelles", $this->db ) );

    }
    elseif ( $section == "forum" )
    {
      $this->set_side_boxes("left",array());
      $this->set_side_boxes("right",array());
    }
  }

  function connect_user ($forever=true)
  {
    if ( $forever )
      $expire = null;
    else
      $expire = date("Y-m-d H:i:s",time()+(15*60)); // Session expire dans 15 minutes
  
    $sid = md5(rand(0,32000) . $_SERVER['REMOTE_ADDR'] . rand(0,32000));

    $req = new insert($this->dbrw, "site_sessions",
            array(
              "id_session"      => $sid,
              "id_utilisateur"    => $this->user->id,
              "date_debut_sess"  => date("Y-m-d H:i:s"),
              "derniere_visite"  => date("Y-m-d H:i:s"),
              "expire_sess" => $expire
              ));
    $domain = ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != '127.0.0.1') ? $_SERVER['HTTP_HOST'] : false;
    setcookie ("AE2_SESS_ID", $sid, time() + 31536000, "/", $domain, 0);

    $this->user->visite();

    return $sid;
  }
  
  function create_session ($forever=true)
  {
    if ( $forever )
      $expire = null;
    else
      $expire = date("Y-m-d H:i:s",time()+(15*60)); // Session expire dans 15 minutes
  
    $sid = md5(rand(0,32000) . $_SERVER['REMOTE_ADDR'] . rand(0,32000));

    $req = new insert($this->dbrw, "site_sessions",
            array(
              "id_session"      => $sid,
              "id_utilisateur"    => $this->user->id,
              "date_debut_sess"  => date("Y-m-d H:i:s"),
              "derniere_visite"  => date("Y-m-d H:i:s"),
              "expire_sess" => $expire
              ));

    return $sid;
  }

  function get_alerts()
  {
    global $topdir;

		if ( !$this->user->is_valid() ) return null;

    $cts = new contents("Attention");

    $carte = new carteae($this->db);
    $carte->load_by_utilisateur($this->user->id);

    $elements = array();

    $cpg = new campagne($this->db,$this->dbrw);
    if($cpg->load_lastest() && $this->user->is_in_group_id($cpg->group))
    {
      $elements[] = "<a href=\"".$topdir."campagne.php?id_campagne=".$cpg->id."\"><b>Campagne en cours : ".$cpg->nom."</b>.</a>";
		}

    if ( $carte->is_valid() )
    {
      if ( $carte->etat_vie_carte == CETAT_ATTENTE &&
        !file_exists("/var/www/ae/www/ae2/var/img/matmatronch/" . $this->user->id .".identity.jpg") )
      {
        $elements[] = "<a href=\"user.php?page=edit&open=photo#setphotos\"><b>Vous devez ajouter une photo</b> pour que votre carte AE soit imprimée.</a>";
      }
      elseif ($carte->etat_vie_carte < CETAT_CIRCULATION )
      {
        $lieu = "Belfort";
        $this->user->load_all_extra();
        if ( $this->user->branche == "TC" || $this->user->branche == "GMC" )
          $lieu = "Sévenans";

        if ( $carte->etat_vie_carte == CETAT_AU_BUREAU_AE )
          $elements[] = "<b>Votre carte AE est prête</b>. Elle vous attends au bureau de l'AE de $lieu.";
        else
          $elements[] = "Votre carte AE est en cours de préparation, elle sera prochainement disponible au bureau de l'AE de $lieu.";
      }
    }

    if( $this->user->is_in_group("moderateur_site") )
    {
      $req = new requete($this->db,"SELECT COUNT(*) FROM `nvl_nouvelles`  WHERE `modere_nvl`='0' ");
      list($nbnews) = $req->get_row();

      $req = new requete($this->db,"SELECT COUNT(*) FROM `d_file`  WHERE `modere_file`='0' ");
      list($nbfichiers) = $req->get_row();
      $req = new requete($this->db,"SELECT COUNT(*) FROM `d_folder`  WHERE `modere_folder`='0' ");
      list($nbdossiers) = $req->get_row();
      
      $nbfichiers+=$nbdossiers;

      if ( $nbnews > 0 )
        $elements[] = "<a href=\"".$topdir."ae/moderenews.php\"><b>$nbnews nouvelle(s)</b> à modérer</b></a>";

      if ( $nbfichiers > 0 )
        $elements[] = "<a href=\"".$topdir."ae/moderedrive.php\"><b>$nbfichiers fichier(s) et dossier(s)</b> à modérer</a>";
    }
    
    if ( $this->user->is_in_group("gestion_salles") )
    {
      $req = new requete($this->db,"SELECT COUNT(*) ".
        "FROM sl_reservation " .
        "INNER JOIN sl_salle ON sl_salle.id_salle=sl_reservation.id_salle " .
        "WHERE ((sl_reservation.date_accord_res IS NULL) OR " .
        "(sl_salle.convention_salle=1 AND sl_reservation.convention_salres=0)) " .
        "AND sl_reservation.date_debut_salres > NOW()");
      list($count) = $req->get_row();

      if ( $count > 0 )
      $elements[] = "<a href=\"".$topdir."ae/modereres.php\"><b>$count reservation(s) de salles</b> à modérer</a>";
    }
    
    if ( $this->user->is_in_group("gestion_emprunts") )
    {
      $req = new requete($this->db,"SELECT COUNT(*) " .
        "FROM inv_emprunt WHERE etat_emprunt=0 ");
      list($count) = $req->get_row();

      if ( $count > 0 )
      $elements[] = "<a href=\"".$topdir."ae/modereemp.php\"><b>$count emprunts(s) de matériel</b> à modérer</a>";
    }

    $req = new requete($this->db, "SELECT COUNT(*) " .
      "FROM `cpt_vendu` " .
      "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
      "WHERE `id_utilisateur_client`='".$this->user->id."' AND a_retirer_vente='1'");

    list($nb) = $req->get_row();

    if ( $nb > 0 )
      $elements[] = "<a href=\"".$topdir."comptoir/encours.php\">Vous avez des commandes à venir retirer</a>";
      
    $sql = new requete($this->db,"SELECT `vt_election`.id_election, `vt_election`.nom_elec " .
        "FROM `vt_election` " .
        "LEFT JOIN vt_a_vote ON (`vt_a_vote`.`id_election`=`vt_election`.`id_election` AND vt_a_vote.id_utilisateur='".$this->user->id."')  " .
        "WHERE `date_debut`<= NOW() " .
        "AND `date_fin` >= NOW() " .
        "AND `id_groupe` IN (".$this->user->get_groups_csv().") " .
        "AND vt_a_vote.id_utilisateur IS NULL");

    if ( $sql->lines != 0 )
    {
      while ( list($id,$nom) = $sql->get_row() )
      {
        $elements[] = "<a href=\"".$topdir."elections.php?id_election=$id\"><b>Votez pour les élections : $nom</b></a>";
      }
    }

    if (  is_null($this->user->date_maj) )
        $elements[] = "<b>Vous n'avez pas r&eacute;cemment mis &agrave; jour votre fiche Matmatronch</b> : <a href=\"".$topdir."user.php?page=edit\">La mettre &agrave; jour</a>";
    elseif ( (time() - $this->user->date_maj) > (6*30*24*60*60) )
        $elements[] = "<b>Vous n'avez pas mis &agrave; jour votre fiche Matmatronch depuis ".round((time() - $this->user->date_maj)/(24*60*60))." jours !</b> : <a href=\"".$topdir."user.php?page=edit\">La mettre &agrave; jour</a>";

    if( $this->user->is_in_group("sas_admin") )
    {
      $req = new requete($this->db, "SELECT COUNT(*) FROM `sas_cat_photos` WHERE `modere_catph`='0' ");
      list($ncat) = $req->get_row();
      $req = new requete($this->db, "SELECT COUNT(*) FROM `sas_photos` WHERE `modere_ph`='0'");
      list($nphoto) = $req->get_row();
      if ( $ncat > 0 || $nphoto > 0 )
      {
        $msg = "";
        if ( $ncat > 0 )
          $msg .= $ncat." catégorie(s)";  
        if ( $ncat > 0 && $nphoto > 0 )
          $msg .= " et ";
        if ($nphoto > 0 )
          $msg .= $nphoto." photo(s)";
        $elements[] = "<a href=\"".$topdir."sas2/modere.php\">$msg &agrave; moderer dans le SAS</a>";
      }
    }
    
    $cotiz = new cotisation($this->db);
    $cotiz->load_lastest_by_user ( $this->user->id );

    if ( ($cotiz->is_valid()) && ($cotiz->date_fin_cotis < time()) && (time()-$cotiz->date_fin_cotis < (30*24*60*60)) )
    {
      $elements[] = "<a href=\"".$topdir."e-boutic/?cat=23\"><b>Votre cotisation &agrave; l'AE est expir&eacute;e !</b> Renouvelez l&agrave; en ligne avec E-boutic.</a>";
    }

    if ( count($elements) == 0 ) return null;

    if ( count($elements) == 1 )
      $cts->add_paragraph("Nous attirons votre attention sur l'&eacute;l&eacute;ment suivant:");
    else
      $cts->add_paragraph("Nous attirons votre attention sur les &eacute;l&eacute;ments suivants:");

    $cts->add(new itemlist(false,false,$elements));
    return $cts;
  }

  function get_bdf_box()
  {
    $cts = new contents("");    
    $cts->add_paragraph("<b>Bureau des<br/>festivités</b>");
    $cts->add_paragraph("&nbsp;");
    $cts->add_paragraph("&nbsp;");
    $cts->add_paragraph("<a href=\"\">Les lieux: la MDE, le foyer</a>");
    $cts->add_paragraph("<a href=\"\">Les services</a>");
    $cts->add_paragraph("<a href=\"\">Les permanences</a>");
    $cts->add_paragraph("<a href=\"\">Boite à idées</a>");
    $cts->add_paragraph("<a href=\"\">L'équipe</a>");
    $cts->add_paragraph("<a href=\"\">Les status</a>");
    $cts->add_paragraph("<a href=\"\">Téléchargements</a>");
    return $cts;
  }

  function get_sondage()
  {
    $sdn = new sondage($this->db,$this->dbrw);

    $sdn->load_lastest();
    if ( !$sdn->is_valid() )
      return NULL;

    require_once($topdir."include/cts/react.inc.php");

    $react = new reactonforum ( $this->db, $this->user, $sdn->question, array("id_sondage"=>$sdn->id), null, false );

    if ( $sdn->a_repondu($this->user->id) )
    {
      $cts = new contents("Sondage");

      $cts->add_paragraph("<b>".$sdn->question."</b>");

      $cts->puts("<p>");

      $res = $sdn->get_results();

      foreach ( $res as $re )
      {
        $cumul+=$re[1];
        $pc = $re[1]*100/$sdn->total;

        $cts->puts($re[0]."<br/>");

        $wpx = floor($pc);
        if ( $wpx != 0 )
          $cts->puts("<div class=\"activebar\" style=\"width: ".$wpx."px\"></div>");
        if ( $wpx != 100 )
          $cts->puts("<div class=\"inactivebar\" style=\"width: ".(100-$wpx)."px\"></div>");

        $cts->puts("<div class=\"percentbar\">".round($pc,1)."%</div>");
        $cts->puts("<div class=\"clearboth\"></div>\n");

      }

      if ( $cumul < $sdn->total )
      {
        $pc = ( $sdn->total-$cumul)*100/$sdn->total;
        $cts->puts("<br/>Blanc ou nul : ".round($pc,1)."%");
      }

      $cts->puts("</p>");

      $cts->add_paragraph("(".$sdn->total." réponses)","nbvotes");
      
      $cts->add($react);
      
      $cts->add_paragraph("<a href=\"sondage.php\">Archives</a>","nbvotes");
      
      return $cts;
    }

    $cts = new contents("Sondage");
    $cts->add_paragraph("<b>".$sdn->question."</b>");

    
    $frm = new form("sondage","sondage.php");
    $frm->add_hidden("id_sondage",$sdn->id);

		$reps = $sdn->get_reponses();
		foreach( $reps as $num => $rep )
      $resp_[$num] = "$rep<br/>";

    $frm->add_radiobox_field ( "numrep", "", $resp_ );

    $frm->add_submit("answord","Repondre");
    $cts->add($frm);
    
    $cts->add($react);

    $cts->add_paragraph("<a href=\"sondage.php\">Archives</a>","nbvotes");
    
    return $cts;
  }


  function get_petit_geni ()
  {
    global $topdir;

    $frm = new form("pgae",$topdir."pgae.php",true,"POST","Petit géni");
      $frm->add_suggested_text_field("recherche","Faites un voeu :","pg");
      $frm->add_submit("btnpgae","Exaucer!");
      return $frm;
  }

  function get_mmt_box ()
  {
    global $topdir;
    $frm_mmt_simplebox = new contents("Mat'Matronch");
    $frm = new form("mmtsimple",$topdir."user.php",true,"POST","Mat'Matronch Rapide");
    $frm->add_user_fieldV2("id_utilisateur","Nom/Surnom");
    $frm->add_submit("go","Voir");
    $frm_mmt_simplebox->add($frm,false,true,"mmt_simple_box","fastsearch");
    return $frm_mmt_simplebox;
  }

  /** La boite de connexion */
  function get_connection_contents ()
  {
    global $topdir;

    if ( !$this->user->is_valid() )
    {
      $frm = new form("connect",$topdir."connect.php",true,"POST","Connexion");
      $frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"));
      $frm->add_text_field("username","Utilisateur","prenom.nom","",20,true);
      $frm->add_password_field("password","Mot de passe","","",20);
      $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
      $frm->add_submit("connectbtn","Se connecter","Veillez a bien sélectionner votre type d'utilisateur UTBM / ASSIDU");
      return $frm;
    }


    $cts = new contents("L'AE et Moi");
    $cts->add_paragraph($this->get_textbox('Welcome')." <b>".$this->user->prenom." ".$this->user->nom."</b>");

    $cts->add_paragraph("<br><a href=\"".$topdir."user/compteae.php\">Compte AE : ".($this->user->montant_compte/100)." Euros</a>");

    $sublist = new itemlist("Mon Compte","boxlist");
    $sublist->add("<a href=\"".$topdir."user.php?id_utilisateur=".$this->user->id."\">Informations personnelles</a>");
    $sublist->add("<a href=\"".$topdir."user.php?view=assos\">Associations et clubs</a>");
    $sublist->add("<a href=\"".$topdir."user.php?view=parrain\">Parrains et fillots</a>");
    $sublist->add("<a href=\"".$topdir."user/compteae.php\">Compte AE</a>");

    $req = new requete($this->db,"SELECT  " .
      "COUNT(*) " .
      "FROM sl_reservation " .
      "INNER JOIN sl_salle ON sl_salle.id_salle=sl_reservation.id_salle " .
      "WHERE sl_reservation.id_utilisateur='".$this->user->id."' AND " .
      "sl_reservation.date_debut_salres > NOW() AND " .
      "((sl_reservation.date_accord_res IS NULL) OR " .
      "(sl_salle.convention_salle=1 AND sl_reservation.convention_salres=0)) " );
    list($nb) = $req->get_row();

    if ( $nb )
      $sublist->add("<a href=\"".$topdir."user/reservations.php\"><b>Mes reservations de salles : $nb en attente</b></a>");
    else
      $sublist->add("<a href=\"".$topdir."user/reservations.php\">Mes reservations de salles</a>");
      
    $req = new requete($this->db,"SELECT COUNT(*) " .
    		"FROM inv_emprunt " .
    		"WHERE id_utilisateur='".$this->user->id."' AND etat_emprunt<=1");	
    list($nb) = $req->get_row();

    if ( $nb )
      $sublist->add("<a href=\"".$topdir."user/emprunts.php\"><b>Mes emprunts de matériel : $nb en attente</b></a>");
    else
      $sublist->add("<a href=\"".$topdir."user/emprunts.php\">Mes emprunts de matériel</a>");

    $cts->add($sublist,true, true, "accountbox", "boxlist", true, true);

    $sublist = new itemlist("Infos et r&eacute;servations","boxlist");
    $sublist->add("<a href=\"".$topdir."news.php\">Proposer une nouvelle</a>");
    $sublist->add("<a href=\"".$topdir."salle.php?page=reservation\">Reserver une salle</a>");
    $sublist->add("<a href=\"".$topdir."emprunt.php\">Reserver du matériel</a>");

    $cts->add($sublist,true, true, "infobox", "boxlist", true, true);

    if( $this->user->is_in_group("moderateur_site") )
    {
      $req = new requete($this->db,"SELECT COUNT(*) FROM `nvl_nouvelles`  WHERE `modere_nvl`='0' ");
      list($nbnews) = $req->get_row();

      $req = new requete($this->db,"SELECT COUNT(*) FROM `d_file`  WHERE `modere_file`='0' ");
      list($nbfichiers) = $req->get_row();
      $req = new requete($this->db,"SELECT COUNT(*) FROM `d_folder`  WHERE `modere_folder`='0' ");
      list($nbdossiers) = $req->get_row();
      
      $nbfichiers+=$nbdossiers;
      
      $sublist = new itemlist("Equipe com'","boxlist");

      $sublist->add("<a href=\"".$topdir."ae/site.php\">Textes paramètrables</a>");
      $sublist->add("<a href=\"".$topdir."ae/sondage.php\">Sondages</a>");
      $sublist->add("<a href=\"".$topdir."ae/weekly_upload.php\">Planning/Photo de la semaine</a>");

      if ( $nbnews > 0 )
        $sublist->add("<a href=\"".$topdir."ae/moderenews.php\"><b>Modération des nouvelles ($nbnews)</b></a>");
      else
        $sublist->add("<a href=\"".$topdir."ae/moderenews.php\">Modération des nouvelles (Aucune)</a>");

      if ( $nbfichiers > 0 )
        $sublist->add("<a href=\"".$topdir."ae/moderedrive.php\"><b>Modération des fichiers et dossiers ($nbfichiers)</b></a>");
      else
        $sublist->add("<a href=\"".$topdir."ae/moderedrive.php\">Modération des fichiers et dossiers (Aucun)</a>");

      $cts->add($sublist,true, true, "siteadminbox", "boxlist", true, false);
    }


    if( $this->user->is_in_group("gestion_ae") )
    {
       $sublist = new itemlist("AE : Administration","boxlist");
       $sublist->add("<a href=\"".$topdir."ae/\">Tâches usuelles</a>");
      $sublist->add("<a href=\"".$topdir."asso.php\">Associations et clubs</a>");
      $sublist->add("<a href=\"".$topdir."ae/cotisations.php\">Cotisations</a>");
      $sublist->add("<a href=\"".$topdir."ae/cartesae.php\">Cartes AE</a>");
      $sublist->add("<a href=\"".$topdir."ae/elections.php\">Elections</a>");
      $cts->add($sublist,true, true, "aeadminbox", "boxlist", true, false);
    
      if( $this->user->is_in_group("compta_admin") )
      {
        $sublist = new itemlist("Comptabilité de l'AE","boxlist");
        $sublist->add("<a href=\"".$topdir."entreprise.php\">Carnet d'adresses</a>");
        $sublist->add("<a href=\"".$topdir."compta/\">Comptabilitée</a>");
        $sublist->add("<a href=\"".$topdir."comptoir/admin.php\">Comptoirs AE</a>");   
        $cts->add($sublist,true, true, "comptaadminbox", "boxlist", true, false);
      }
      
      $sublist = new itemlist("Salles, inventaire","boxlist");
      $sublist->add("<a href=\"".$topdir."sitebat.php\">Batiments, salles</a>");
      $sublist->add("<a href=\"".$topdir."objtype.php\">Type d'objets (inventaire)</a>");

      $req = new requete($this->db,"SELECT COUNT(*) ".
        "FROM sl_reservation " .
        "INNER JOIN sl_salle ON sl_salle.id_salle=sl_reservation.id_salle " .
        "WHERE ((sl_reservation.date_accord_res IS NULL) OR " .
        "(sl_salle.convention_salle=1 AND sl_reservation.convention_salres=0)) " .
        "AND sl_reservation.date_debut_salres > NOW()");

      list($count) = $req->get_row();
      $sublist->add("<a href=\"".$topdir."ae/modereres.php\">Reservation salles (".$count.")</a>");

      $req = new requete($this->db,"SELECT COUNT(*) " .
        "FROM inv_emprunt WHERE etat_emprunt=0 ");
      list($count) = $req->get_row();
      $sublist->add("<a href=\"".$topdir."ae/modereemp.php\">Emprunts de matériel (".$count.")</a>");


      $cts->add($sublist,true, true, "sainvadminbox", "boxlist", true, false);
    }

    if( $this->user->is_in_group("root") )
    {
       $sublist = new itemlist("Equipe info","boxlist");
       $sublist->add("<a href=\"".$topdir."group.php\">Gestion des groupes</a>");
       $sublist->add("<a href=\"".$topdir."ae/pollcoti.php\">Expiration des cotisations (sem.)</a>");
      $cts->add($sublist,true, true, "riitadminbox", "boxlist", true, false);


    }


    $req = new requete($this->db,
        "SELECT `asso`.`id_asso`, " .
        "`asso`.`nom_asso`, ".
        "`asso_membre`.`role` " .
        "FROM `asso_membre` " .
        "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
        "WHERE `asso_membre`.`role` > 1 AND `asso_membre`.`date_fin` IS NULL " .
        "AND `asso_membre`.`id_utilisateur`='".$this->user->id."' " .
        "AND `asso`.`id_asso` != '1' " .
        "ORDER BY asso.`nom_asso`");

    if ( $req->lines > 0 )
    {
      while ( list($id,$nom,$role) = $req->get_row() )
      {
        $sublist = new itemlist("$nom","boxlist");
        $sublist->add("<a href=\"".$topdir."asso/index.php?id_asso=$id\">Outils</a>");
        $sublist->add("<a href=\"".$topdir."asso/inventaire.php?id_asso=$id\">Inventaire</a>");
        $sublist->add("<a href=\"".$topdir."asso/reservations.php?id_asso=$id\">Reservations</a>");
        $sublist->add("<a href=\"".$topdir."asso/membres.php?id_asso=$id\">Membres</a>");
        $sublist->add("<a href=\"".$topdir."asso/mailing.php?id_asso=$id\">Mailing</a>");
        $sublist->add("<a href=\"".$topdir."asso/ventes.php?id_asso=$id\">Ventes</a>");
        $sublist->add("<a href=\"".$topdir."d.php?id_asso=$id\">Fichiers</a>");
        
        if ( $role >= ROLEASSO_TRESORIER )
        {
          $reqa = new requete ($this->db,
            "SELECT id_classeur,nom_classeur " .
            "FROM `cpta_classeur` " .
            "INNER JOIN `cpta_cpasso` ON `cpta_cpasso`.`id_cptasso`=`cpta_classeur`.`id_cptasso` " .
            "INNER JOIN cpta_cpbancaire ON cpta_cpbancaire.id_cptbc=cpta_cpasso.id_cptbc " .
            "WHERE cpta_cpasso.id_asso='".$id."' AND `cpta_classeur`.`ferme`='0'" .
            "ORDER BY `cpta_classeur`.`date_debut_classeur` DESC");    
            
          if ( $reqa->lines == 1 )
          {
            list($id,$nom) = $reqa->get_row();
            $sublist->add("<a href=\"".$topdir."compta/classeur.php?id_classeur=$id\">Compta $nom</a>");
          }
        }
        $cts->add($sublist,true, true, "asso".$id."box", "boxlist", true, true);
      }
    }

    $req = new requete($this->db,"SELECT id_comptoir,nom_cpt " .
        "FROM cpt_comptoir " .
        "WHERE `type_cpt`='2' " .
        "AND id_groupe_vendeur IN (".$this->user->get_groups_csv().") " .
        "ORDER BY nom_cpt");

    if ( $req->lines > 0 )
    {

      $sublist = new itemlist("Actions bureaux AE","boxlist");

       while ( list($id,$nom) = $req->get_row() )
        $sublist->add("<a href=\"".$topdir."comptoir/bureau.php?id_comptoir=$id\">$nom</a>");

      $cts->add($sublist,true, true, "cptbureau", "boxlist", true, true);

    }


    if ( $this->user->is_in_group("root") && $this->user->is_in_group("gestion_ae") )
      $req = new requete($this->db,"SELECT cpt_comptoir.id_comptoir,cpt_comptoir.nom_cpt " .
        "FROM cpt_comptoir " .
        "ORDER BY cpt_comptoir.nom_cpt");
    elseif ( $this->user->is_in_group("gestion_ae") )
      $req = new requete($this->db,"SELECT cpt_comptoir.id_comptoir,cpt_comptoir.nom_cpt " .
        "FROM cpt_comptoir WHERE cpt_comptoir.nom_cpt != 'test' " .
        "ORDER BY cpt_comptoir.nom_cpt");
    else
      $req = new requete($this->db,"SELECT id_comptoir,nom_cpt " .
        "FROM cpt_comptoir " .
        "WHERE ( id_groupe IN (".$this->user->get_groups_csv().") OR `id_assocpt` IN (".$this->user->get_assos_csv(4).") ) AND nom_cpt != 'test' " .
        "ORDER BY nom_cpt");

    if ( $req->lines > 0 )
    {
      $sublist = new itemlist("Admin comptoirs","boxlist");

       while ( list($id,$nom) = $req->get_row() )
        $sublist->add("<a href=\"".$topdir."comptoir/admin.php?id_comptoir=$id\">$nom</a>");

      $cts->add($sublist,true, true, "cptadmin", "boxlist", true, false);
    }

    if ( $this->user->is_asso_role ( 27, 1 ) )
    {
      $sublist = new itemlist("Staff Mat'Matronch","boxlist");
       $sublist->add("<a href=\"".$topdir."mmt/wiki/\">Wiki Mat'Matronch</a>");
      //$sublist->add("<a href=\"".$topdir."mmt/cotiz-mmt/\">Gestion des cotisations</a>");
      $sublist->add("<a href=\"".$topdir."matmatronch/upload_photo_user.php\">Upload des Photos</a>");
      $sublist->add("<a href=\"".$topdir."matmatronch/inscriptions.php\">Ajout utilisateur</a>");
      $cts->add($sublist,true, true, "matmatronchbox", "boxlist", true, false);
    }
    elseif ( $this->user->is_in_group("matmatronch") )
    {
      $sublist = new itemlist("Staff Mat'Matronch","boxlist");
      $sublist->add("<a href=\"".$topdir."mmt/cotiz-mmt/\">Gestion des cotisations</a>");
      $cts->add($sublist,true, true, "matmatronchbox", "boxlist", true, false);
    }

    /* Bouton de Deconnexion */
    $frm = new form("disconnect",$topdir."disconnect.php",false,"POST","Deconnexion");
    $frm->add_submit("disconnect","Se déconnecter");
    $cts->add($frm);

    return $cts;
  }

  /** Génre la boite qui affiche les anniversaires */
  function get_anniv_contents ()
  {
    global $topdir;
    $cts = new contents("Anniversaire");

    $req = new requete ($this->db, "SELECT `utilisateurs`.`id_utilisateur`,
                                            `utilisateurs`.`nom_utl`,
                                            `utilisateurs`.`prenom_utl`,
                                            `utl_etu_utbm`.`surnom_utbm`,
                                            `utilisateurs`.`date_naissance_utl`
                                     FROM `utilisateurs`
                                     INNER JOIN `utl_etu_utbm` ON `utilisateurs`.`id_utilisateur` = `utl_etu_utbm`.`id_utilisateur`
                                     WHERE `utilisateurs`.`date_naissance_utl` LIKE '%-" . date("m-d") . "'
                                     AND (`utilisateurs`.`ancien_etudiant_utl` = '0' OR `utilisateurs`.`ae_utl` = '1')
                                     ORDER BY `utilisateurs`.`date_naissance_utl` DESC");

    if ($req->lines > 0)
    {
      $cts->puts($this->get_textbox('Anniversaire'));
      //$cts->puts ("<ul class=\"boxlist annif\">", 1);

      $old_age = 0;
      $count   = 0;

      while ($res = $req->get_row())
      {
        /*preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $res['date_naissance_utl'], $naissance);
        $age =  date("Y") - $naissance[1];
        */
        $age=date("Y")-date("Y",strtotime($res['date_naissance_utl']));
        if (!$count || ($old_age != $age))
        {
          //$cts->puts("<li class=\"title\">" . $age . " ans</li>");
          if ( $count )
            $cts->puts("</ul>\n");
          
          $cts->puts("<h2 class=\"epure\">" . $age . " ans</h2>\n");
          $cts->puts("<ul>\n");
          $old_age = $age;
        }

        if (empty($res['surnom_utbm']))
          $nom = $res['prenom_utl'] . " " . $res['nom_utl'];
        else
          $nom = $res['surnom_utbm'];

        $ref = "anniv". $res['id_utilisateur'];
        
        $count++;
        $cts->puts ("<li><a id=\"$ref\" onmouseover=\"show_tooltip('$ref','$topdir','utilisateur','".$res['id_utilisateur']."');\" onmouseout=\"hide_tooltip('$ref');\" href=\"". $topdir ."user.php?id_utilisateur=". $res['id_utilisateur'] .
         "\">" . $nom . "</a></li>\n");
      }
      $cts->puts("</ul>\n");
      //$cts->puts("</ul>\n", -1);
    }
    else
    {
      $cts->puts("L'AE est triste de vous annoncer qu'il n'y a pas d'anniversaire aujourd'hui.\n");
    }

    return $cts;
  }

  /** Fonction qui génére le contents du dernier planning de l'AE */
  function get_planning_contents ()
  {
    global $topdir;
    if ( !file_exists($topdir."var/img/com/planning.jpg"))
      return null;
      
    $planning_valid = filemtime($topdir."var/img/com/planning.jpg") + (7 * 24 * 60 * 60);
    if ( time() <= $planning_valid )
    {
      $cts = new contents("Planning");
      $cts->puts("<center><a href=\"".$topdir."article.php?name=planning\"><img src=\"".$topdir."var/img/com/planning-small.jpg?".$planning_valid."\" alt=\"Planning\" /></a></center>");
      return $cts;
    }
  }

  function get_weekly_photo_contents ()
  {
    global $topdir;
    if ( !file_exists($topdir."var/img/com/weekly_photo.jpg"))
      return null;
    $weekly_photo_valid = filemtime($topdir."var/img/com/weekly_photo.jpg") + (7 * 24 * 60 * 60);
    if ( time() <= $weekly_photo_valid )
    {
    $cts = new contents("Photo de la semaine");
    $cts->puts("<center><a href=\"".$topdir."article.php?name=weekly_photo\"><img src=\"".$topdir."var/img/com/weekly_photo-small.jpg?".$weekly_photo_valid."\" style=\"margin-bottom:0.5em;\" alt=\"Photo de la semaine\" /></a><br/>");
    $cts->puts($this->get_textbox('Weekly_photo'));
    $cts->puts("</center>");
    return $cts;
    }
  }

  /** Modifie une boite du site
   * @param nom_boite le nom de la boite
   * @param content le nouveau contenu
   */
  function modify_textbox($nom_boite, $new_content)
  {
    //Protection
    $new_content = mysql_real_escape_string($new_content);
    $req = new update (new mysqlae("rw"),"ae_box",
           array ("content" => $new_content),
           array("name" => $nom_boite));
    if ((!$req) || ($req->lines < 0)) {
      $this->errmsg = $req->errmsg;
      return -1;
    }

    else return 0;
  }
  
  
  function get_comptoirs_box ()
  {
    global $topdir;
    // 1- On ferme les sessions expirés
    $req = new requete ($this->dbrw,
           "UPDATE `cpt_tracking` SET `closed_time`='".date("Y-m-d H:i:s")."'
            WHERE `activity_time` <= '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
            AND `closed_time` IS NULL");
  
    // 2- On récupère les infos sur les bars ouverts
    $req = new requete ($this->dbrw,
           "SELECT MAX(activity_time),id_comptoir
            FROM `cpt_tracking`
            WHERE `activity_time` > '".date("Y-m-d H:i:s",time()-intval(ini_get("session.gc_maxlifetime")))."'
            AND `closed_time` IS NULL
            GROUP BY id_comptoir");
            
    while ( list($act,$id) = $req->get_row() )
      $activity[$id]=strtotime($act);
  
    // 3- On récupère les infos sur tous les bars 
    $req = new requete ($this->dbrw,
           "SELECT id_comptoir, nom_cpt
            FROM cpt_comptoir 
            WHERE type_cpt='0' AND id_comptoir != '4' AND id_comptoir != '8'
            ORDER BY nom_cpt");
            
    $list = new itemlist("Comptoirs <i>(beta)</i>");


    while ( list($id,$nom) = $req->get_row() )
    {
      $led = "green";
      $descled = "ouvert";
  
      if ( !isset($activity[$id]) )
      {
        $led = "red";
        $descled = "fermé (ou pas d'activité depuis plus de ".(intval(ini_get("session.gc_maxlifetime"))/60)." minutes)";
      }
      elseif ( time()-$activity[$id] > 600 )
      {
        $led = "yellow";
        $descled = "ouvert (mais pas d'activité depuis plus de 10 minutes)";

      }
        $list->add("<a href=\"comptoir/activity.php?id_comptoir=$id\"><img src=\"".$topdir."images/leds/".$led."led.png\" class=\"icon\" alt=\"".htmlentities($descled,ENT_NOQUOTES,"UTF-8")."\" title=\"".htmlentities($descled,ENT_NOQUOTES,"UTF-8")."\" /> $nom</a>");
    
    }
  
    return $list;
  
  }
  
  function get_forum_box ()
  {  
    global $wwwtopdir;
    require_once($topdir . "include/entities/forum.inc.php");
    $forum = new forum($this->db);
    $forum->load_by_id(1);

    $cts = new contents("Forum");
  
    $query = "SELECT frm_sujet.*, ".
        "frm_message.date_message, " .
        "frm_message.id_message, " .
        "dernier_auteur.alias_utl AS `nom_utilisateur_dernier_auteur`, " .
        "dernier_auteur.id_utilisateur AS `id_utilisateur_dernier`, " .
        "premier_auteur.alias_utl AS `nom_utilisateur_premier_auteur`, " .
        "premier_auteur.id_utilisateur AS `id_utilisateur_premier`, " .
        "1 AS `nonlu`, " .
        "titre_forum AS `soustitre_sujet` " .
        "FROM frm_sujet " .
        "INNER JOIN frm_forum USING(id_forum) ".
        "LEFT JOIN frm_message ON ( frm_message.id_message = frm_sujet.id_message_dernier ) " .
        "LEFT JOIN utilisateurs AS `dernier_auteur` ON ( dernier_auteur.id_utilisateur=frm_message.id_utilisateur ) " .
        "LEFT JOIN utilisateurs AS `premier_auteur` ON ( premier_auteur.id_utilisateur=frm_sujet.id_utilisateur ) ".
        "LEFT JOIN frm_sujet_utilisateur ".
          "ON ( frm_sujet_utilisateur.id_sujet=frm_sujet.id_sujet ".
          "AND frm_sujet_utilisateur.id_utilisateur='".$this->user->id."' ) ".
        "WHERE ";
              
    if( is_null($this->user->tout_lu_avant))
      $query .= "(frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ";    
    else
      $query .= "((frm_sujet_utilisateur.id_message_dernier_lu<frm_sujet.id_message_dernier ".
                "OR frm_sujet_utilisateur.id_message_dernier_lu IS NULL) ".
                "AND frm_message.date_message > '".date("Y-m-d H:i:s",$this->user->tout_lu_avant)."') ";  
  
    if ( !$forum->is_admin( $this->user ) )
    {
      $grps = $this->user->get_groups_csv();
      $query .= "AND ((droits_acces_forum & 0x1) OR " .
        "((droits_acces_forum & 0x10) AND id_groupe IN ($grps)) OR " .
        "(id_groupe_admin IN ($grps)) OR " .
        "((droits_acces_forum & 0x100) AND frm_forum.id_utilisateur='".$this->user->id."')) ";
    }
  
    $query .= "ORDER BY frm_message.date_message DESC ";
    $query .= "LIMIT 5 ";
  
    $req = new requete($this->db,$query);
    
    
    if ( $req->lines > 0 )
    {
      $cts->add_title(2,"<a href=\"".$wwwtopdir."forum2/search.php?page=unread\">Derniers messages non lus</a>");
      $list = new itemlist();
      while ( $row = $req->get_row() )
      {
        $list->add("<a href=\"".$wwwtopdir."forum2/?id_sujet=".$row['id_sujet']."&amp;spage=firstunread#firstunread\"\">".
        htmlentities($row['titre_sujet'], ENT_NOQUOTES, "UTF-8").
        "</a>");
      }
      $cts->add($list);
      if ( $req->lines == 5 )
        $cts->add_paragraph("<a href=\"".$wwwtopdir."forum2/search.php?page=unread\">suite...</a>");
    }
    else
      $cts->add_paragraph("pas de messages non lus");
    
    return $cts;
  }
  
  function allow_only_logged_users($section="none")
  {
    global $topdir;
    if ( $this->user->is_valid() )
      return;  
      
    require_once($topdir."include/cts/login.inc.php");
    
    $this->start_page($section,"Identification requise");
    $this->add_contents(new loginerror());
    $this->end_page(); 
    exit();
  }
  
  function fatal_partial($section="none")
  {
    global $wwwtopdir;
    
    $this->set_side_boxes("right",array());
    $this->set_side_boxes("left",array());
    
    $this->start_page($section,"En maintenance");
    $cts = new image ( "En maintenance", $wwwtopdir."images/partialclose.jpg" );
    $cts->cssclass = "partialclose";
    $cts->title = null;
    $this->add_contents($cts);
    $this->end_page(); 
    exit();
  }
  
  
  
  function error_forbidden($section="none",$reason=null,$id_group=null)
  {
    $this->start_page($section,"Accés refusé");
    
    if ( $reason == "group" )
    {
      $who = "aux administrateurs";
      
      if ( $id_group == 10000 )
        $who = "aux membres de l'AE";
      elseif ( $id_group == 10001 )
        $who = "aux membres de l'UTBM";
      elseif ( $id_group >= 40000 )
        $who = "aux membres de la promo ".($id_group-40000);
      elseif ( $id_group >= 30000 )
        $who = "aux membres de l'activité";
      elseif ( $id_group >= 20000 )
        $who = "au bureau de l'activité";
      elseif ( $id_group >= 10000 )
        $who = "";
        
      $this->add_contents(new contents("Accés refusé","<p>Accès réservé $who.</p>")); 
    }
    else
      $this->add_contents(new contents("Accés refusé","<p>Vos droits sont insuffisent pour accéder à cette page.</p>"));
    $this->end_page();     
    exit();
  }
  
  function error_not_found($section="none", $redirect=null)
  {
    global $wwwtopdir;
    
    if ( !is_null($redirect) )
    {
      header("Location: $redirect");
      exit();
    }
    
		if (!empty($this->tab_array))
		{
		  foreach ($this->tab_array as $entry)
		  {
			  if ( $section == $entry[0] )
			    $redirect = $wwwtopdir . $entry[1];
			}
		}
		
    if ( !is_null($redirect) )
    {
      header("Location: $redirect");
      exit();
    }
		
    $this->start_page($section,"Non trouvé");
    $this->add_contents(new contents("Non trouvé","<p>L'élément demandé n'a pas été trouvé</p>"));
    $this->end_page(); 
    
    exit();
  }
}
?>
