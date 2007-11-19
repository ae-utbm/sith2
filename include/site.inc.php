<?php
/** @file
 *
 * @brief Fonctions générales du site.
 *
 */
/* Copyright 2004,2005,2006,2007
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
  
$timing["site"] -= microtime(true);
require_once($topdir . "include/interface.inc.php");
require_once($topdir . "include/globals.inc.php");
require_once($topdir . "include/cts/calendar.inc.php");
require_once($topdir . "include/entities/sondage.inc.php");
require_once($topdir . "include/entities/campagne.inc.php");
require_once($topdir . "include/entities/cotisation.inc.php");
require_once($topdir . "jobetu/include/jobuser_etu.inc.php");

/** La classe principale du site */
class site extends interfaceweb
{
  /** Constructeur de la classe */
  function site ($stats=true)
  {
    global $init;
    $timing["init"] -= microtime(true);

    $dbro = new mysqlae ();
    
    if (!$dbro->dbh)
      $this->fatal("no db");

    $dbrw = new mysqlae ("rw");
    if (!$dbrw->dbh)
      $this->fatal("no dbrw");

    $this->interfaceweb($dbro, $dbrw);
    
    if( $stats && !preg_match('/taiste/', $_SERVER['SCRIPT_NAME']) && !preg_match('/ae/', $_SERVER['SCRIPT_NAME']))
      $this->stats();

    if($_COOKIE['AE2_SESS_ID'])
      $this->load_session($_COOKIE['AE2_SESS_ID']);

    if ( $this->get_param("closed",false) && !$this->user->is_in_group("root") )
      $this->fatal("closed");

    $this->set_side_boxes("left",array("calendrier","alerts","connexion"),"default_left");
    $this->add_box("calendrier",new calendar($this->db));
    $this->add_box("connexion", $this->get_connection_contents());

    //$this->add_css("themes/gala/css/site.css");
    $timing["init"] += microtime(true);
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
  

  /**
   * Charge une session en fonction de son identidiant.
   * @param $sid Identifiant de la session
   */
  function load_session ( $sid )
  {
    $req = new requete($this->db, "SELECT `id_utilisateur`, `connecte_sess`, `expire_sess` FROM `site_sessions` WHERE `id_session` = '" .
                       mysql_escape_string($sid) . "'");
    list($uid,$connecte,$expire) = $req->get_row();

    if ($req->lines < 1 )
    {
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
    elseif ( !is_null($expire) )
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
    {
      $this->user->id = null;
      new delete($this->dbrw,"site_sessions",array("id_session" => $sid));
    }
    else
    {
      $this->user->visite();
      
      if ( !isset($_SESSION["usersession"]) ) // restore le usersession
        $_SESSION["usersession"] = $this->user->get_param("usersession",null);
    }   
  }

  /**
   * Connecte l'utilisateur chargé dans le champ user ($this->user) pour 
   * 15 minutes, ou permanente, en créant une sessions et en envoyant un cookie.
   * @param $forever Precise si la session doit être permanente
   * @return l'identifiant de la session
   */
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

    $this->add_box("connexion", $this->get_connection_contents());

    return $sid;
  }
  
  /**
   * Crée un identifiant unique pour connecter ultérieurement un utilisateur.
   * Utile pour envoyer un lien par e-mail avec authentification automatique.
   * Le "token" est en fait un identifiant de session, il expire au bout de 60 jours.
   * @param $id_utilisateur Id de l'utilisateur pour qui le "token" doit être généré
   * @return le "token" (identifiant de session)
   * @see load_token
   */
  function create_token_for_user ( $id_utilisateur )
  {
    $sid = "T".$id_utilisateur."$".md5(rand(0,32000) . "TOKEN" . $id_utilisateur . rand(0,32000));

    $req = new insert($this->dbrw, "site_sessions",
            array(
              "id_session"      => $sid,
              "id_utilisateur"    => $id_utilisateur,
              "date_debut_sess"  => date("Y-m-d H:i:s"),
              "derniere_visite"  => date("Y-m-d H:i:s"),
              "expire_sess" => date("Y-m-d H:i:s",time()+(24*60*60*60))
              ));

    return $sid;
  }
  
  /**
   * Ouvre une session pour l'utilisateur associé à un token donné.
   * La session est ouverte par le biai de connect_user(). Le token n'est plus
   * valable après l'appel à cette fonction.
   * @param $token le "token"
   * @return null en cas d'echec, ou l'identifiant de la session ouverte
   * @see connect_user
   * @see create_token_for_user
   */
  function load_token ( $token )
  {
    $this->user->id=null;
    $this->load_session($token);  
    if ( $this->user->is_valid() )
    {
      new delete($this->dbrw, "site_sessions", array("id_session"=>$token) );
      return $this->connect_user();
    }
    return null;
  }

  /**
   * Crée une session pour  l'utilisateur chargé dans le champ user ($this->user)  
   * pour 15 minutes, ou permanente.
   * @param $forever Precise si la session doit être permanente
   * @return l'identifiant de la session
   */
  function create_session ($forever=true)
  {
    if ( $forever )
      $expire = null;
    else
      $expire = date("Y-m-d H:i:s",time()+(15*60)); // Session expire dans 15 minutes
  
    $sid = md5(rand(0,32000) . $_SERVER['REMOTE_ADDR'] . rand(0,32000));

    $req = new insert($this->dbrw, "site_sessions",
                      array("id_session"      => $sid,
                            "id_utilisateur"  => $this->user->id,
                            "date_debut_sess" => date("Y-m-d H:i:s"),
                            "derniere_visite" => date("Y-m-d H:i:s"),
                            "expire_sess"     => $expire
                           ));

    return $sid;
  }

  /**
   * Demarre la page à rendre en spécifiant quelques informations clefs.
   * Aucune donnée ne sera envoyé au client avant l'appel de end_page.
   * Gènère la liste des boites en fonction de la section.
   * @param $section Nom de la section
   * @param $title Titre de la page
   * @param $compact Cache le logo et la boite informations (utile pour augmenter la taille de contenu visisble sans scroll)
   */
  function start_page ( $section, $title,$compact=false )
  {
    global $topdir;

    parent::start_page($section,$title,$compact);
    
    if ( $section != "pg" && $section != "matmatronch" && $section != "forum"  )
      $this->add_box("alerts",$this->get_alerts());

    if ( $section == "accueil" )
    {
      $this->add_box("photo",$this->get_weekly_photo_contents());
      $this->add_box("anniv", $this->get_anniv_contents());
      $this->add_box("planning", $this->get_planning_contents());
      
      if ( preg_match('/^\/var\/www\/ae\/www\/(taiste|taiste21)\//', $_SERVER['SCRIPT_FILENAME']) )
        $this->add_box("stream",$this->get_stream_box());
      
      if ($this->user->is_valid())
      {
        $this->add_box("forum",$this->get_forum_box());
        $this->add_box("comptoirs",$this->get_comptoirs_box());        
        $this->add_box("sondage",$this->get_sondage());
        $this->set_side_boxes("right",
          array("planning","photo","anniv","stream",
                "sondage","comptoirs","forum"),"accueil_c_right");
      } 
      else
        $this->set_side_boxes("right",
          array("planning","photo","anniv","stream"),"accueil_nc_right");
      
    }
    elseif ( $section == "pg" )
    {
      $this->set_side_boxes("left",array("pg","connexion"),"pg_left");
      $this->add_box("pg", $this->get_petit_geni());
    }
    elseif ( $section == "matmatronch" )
    {
      require_once($topdir . "include/cts/newsflow.inc.php");
      
      $this->set_side_boxes("left",array("lastnews","connexion"),"mmt_left");
      $this->add_box("lastnews",new newslist ( "Denières nouvelles", $this->db ) );
    }
    elseif ( $section == "forum" )
    {
      $this->set_side_boxes("left",array());
      $this->set_side_boxes("right",array());
    }
  }

  /**
   * Gènère la boite "Attention".
   * @param renvoie un stdcontents, ou null (si vide)
   */
  function get_alerts()
  {
    global $topdir;

    if ( !$this->user->is_valid() ) return null;

    $carte = new carteae($this->db);
    $carte->load_by_utilisateur($this->user->id);

    $elements = array();

    $cpg = new campagne($this->db,$this->dbrw);
    $req = new requete($this->db, "SELECT `id_campagne` FROM `cpg_campagne` WHERE `date_fin_campagne`>=NOW() ORDER BY date_debut_campagne DESC");
    while(list($id)=$req->get_row())
      if($cpg->load_by_id($id) && $this->user->is_in_group_id($cpg->group) && !$cpg->a_repondu($this->user->id))
        $elements[] = "<a href=\"".$topdir."campagne.php?id_campagne=".$cpg->id."\"><b>Campagne en cours : ".$cpg->nom."</b>.</a>";

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
        if ( $this->user->departement == "tc" || 
          $this->user->departement == "gmc" || 
          $this->user->departement == "edim" )
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

      $req = new requete($this->db,"SELECT COUNT(*) FROM `planet_flux`  WHERE `modere`='0' ");
      list($nbflux) = $req->get_row();
      $req = new requete($this->db,"SELECT COUNT(*) FROM `planet_tags`  WHERE `modere`='0' ");
      list($nbtags) = $req->get_row();
      $nbflux+=$nbtags;

      if ( $nbnews > 0 )
        $elements[] = "<a href=\"".$topdir."ae/moderenews.php\"><b>$nbnews nouvelle(s)</b> à modérer</b></a>";

      if ( $nbfichiers > 0 )
        $elements[] = "<a href=\"".$topdir."ae/moderedrive.php\"><b>$nbfichiers fichier(s) et dossier(s)</b> à modérer</a>";

      if ( $nbflux > 0 )
        $elements[] = "<a href=\"".$topdir."planet/index.php?view=modere\"><b>$nbflux flux</b> à modérer</b></a>";
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
        $elements[] = "<b>Vous n'avez pas r&eacute;cemment mis &agrave; jour votre fiche Matmatronch</b> : <a href=\"".$topdir."majprofil.php\">La mettre &agrave; jour</a>";
    elseif ( (time() - $this->user->date_maj) > (6*30*24*60*60) )
        $elements[] = "<b>Vous n'avez pas mis &agrave; jour votre fiche Matmatronch depuis ".round((time() - $this->user->date_maj)/(24*60*60))." jours !</b> : <a href=\"".$topdir."majprofil.php\">La mettre &agrave; jour</a>";

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

    if ( !$this->user->droit_image )
    {
      $sql = new requete($this->db,
        "SELECT COUNT(*) " .
        "FROM sas_personnes_photos " .
        "INNER JOIN sas_photos ON (sas_photos.id_photo=sas_personnes_photos.id_photo) " .
        "WHERE sas_personnes_photos.id_utilisateur=".$this->user->id." " .
        "AND sas_personnes_photos.accord_phutl='0' " .
        "AND (droits_acces_ph & 0x100)");
      list($count) = $sql->get_row();

      if ( $count > 0 )
        $elements[] ="<a href=\"".$topdir."sas2/droitimage.php?page=process\"><b>$count photo(s)</b> nécessitent votre accord</a>";
      
    }

    if ( count($elements) == 0 ) return null;
    
    $cts = new contents("Attention");

    if ( count($elements) == 1 )
      $cts->add_paragraph("Nous attirons votre attention sur l'&eacute;l&eacute;ment suivant:");
    else
      $cts->add_paragraph("Nous attirons votre attention sur les &eacute;l&eacute;ments suivants:");

    $cts->add(new itemlist(false,false,$elements));
    return $cts;
  }

  /**
   * Gènère la boite sondage.
   * @param renvoie un stdcontents, ou null (si vide)
   */
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

  /**
   * Gènère la boite petit geni.
   * @param renvoie un stdcontents
   */
  function get_petit_geni ()
  {
    global $topdir;
    $frm = new form("pgae",$topdir."pgae.php",true,"POST","Petit géni");
    $frm->add_suggested_text_field("recherche","Faites un voeu :","pg");
    $frm->add_submit("btnpgae","Exaucer!");
    return $frm;
  }

  /**
   * Gènère la boite de "Connexion" / "L'AE et Moi".
   * @param renvoie un stdcontents, ou null (si vide)
   */
  function get_connection_contents ()
  {
    global $topdir;
    global $wwwtopdir;

    if ( !$this->user->is_valid() )
    {
      $cts = new contents("Connexion");
      $frm = new form("connect",$topdir."connect.php",true,"POST","Connexion");
      $frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"));
      $frm->add_text_field("username","Utilisateur","prenom.nom","",20,true);
      $frm->add_password_field("password","Mot de passe","","",20);
      $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
      $frm->add_submit("connectbtn","Se connecter");
      $cts->add($frm);
      
      $cts->add_paragraph("<a href=\"".$wwwtopdir."article.php?name=docs:connexion\">Aide</a> - <a href=\"".$wwwtopdir."password.php\">Mot de passe perdu</a>");
      
      return $cts;
    }

    $cts = new contents("L'AE et Moi");
    $cts->add_paragraph($this->get_textbox('Welcome')." <b>".$this->user->prenom." ".$this->user->nom."</b>");

    $cts->add_paragraph("<br><a href=\"".$topdir."user/compteae.php\">Compte AE : ".(sprintf("%.2f", $this->user->montant_compte/100))." Euros</a>");

    $sublist = new itemlist("Mon Compte","boxlist");
    $sublist->add("<a href=\"".$topdir."user.php?id_utilisateur=".$this->user->id."\">Informations personnelles</a>");
    $sublist->add("<a href=\"".$topdir."uvs/edt.php\">Mes emplois du temps</a>");
    if($this->user->utbm)
      $sublist->add("<a href=\"".$topdir."trombi/index.php\">Trombinoscope</a>");
    $sublist->add("<a href=\"".$topdir."user.php?view=assos\">Associations et clubs</a>");
    if( $this->user->is_in_group("jobetu_etu") )
    {
      $jobuser = new jobuser_etu($this->db);
      $jobuser->load_by_id($this->user->id);
      $jobuser->load_annonces();
      $sublist->add("<a href=\"".$topdir."jobetu/board_etu.php\">Mon compte JobEtu (".count($jobuser->annonces).")</a>");
    }
    else if( $this->user->is_in_group("jobetu_client") )
      $sublist->add("<a href=\"".$topdir."jobetu/board_client.php\">AE JobEtu</a>");
    else
      $sublist->add("<a href=\"".$topdir."jobetu/index.php\">AE JobEtu</a>");
    $sublist->add("<a href=\"".$topdir."user.php?view=parrain\">Parrains et fillots</a>");
    $sublist->add("<a href=\"".$topdir."user/compteae.php\">Compte AE</a>");

    $cts->add($sublist,true, true, "accountbox", "boxlist", true, true);

    $sublist = new itemlist("Infos et r&eacute;servations","boxlist");
    
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

    $sublist->add("<a href=\"".$topdir."news.php\">Proposer une nouvelle</a>");
    $sublist->add("<a href=\"".$topdir."salle.php?page=reservation\">Reserver une salle</a>");
    $sublist->add("<a href=\"".$topdir."emprunt.php\">Reserver du matériel</a>");

    $cts->add($sublist,true, true, "infobox", "boxlist", true, true);

    if( $this->user->is_in_group("moderateur_site") )
    {
      $sublist = new itemlist("Equipe com'","boxlist");

      $sublist->add("<a href=\"".$topdir."ae/com.php\">Tâches usuelles</a>");

      $cts->add($sublist,true, true, "siteadminbox", "boxlist", true, false);
    }


    if( $this->user->is_in_group("gestion_ae") )
    {
      $sublist = new itemlist("AE : Administration","boxlist");
      $sublist->add("<a href=\"".$topdir."ae/\">Tâches usuelles</a>");
      $sublist->add("<a href=\"".$topdir."ae/cartesae.php\">Cartes AE</a>");
      $sublist->add("<a href=\"".$topdir."asso.php\">Associations et clubs</a>");
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
    
    if( $this->user->is_in_group("gestion_syscarteae") )
    {
      $sublist = new itemlist("Système Carte AE","boxlist");
      $sublist->add("<a href=\"".$topdir."ae/syscarteae.php?view=factures\">Appels à facture</a>");
      $sublist->add("<a href=\"".$topdir."ae/syscarteae.php?view=comptes\">Comptes</a>");
      $sublist->add("<a href=\"".$topdir."ae/syscarteae.php?view=retrait\">Produits non retirés</a>");
      $sublist->add("<a href=\"".$topdir."ae/syscarteae.php?view=remb\">Rembourser</a>");
      $cts->add($sublist,true, true, "syscarteaebox", "boxlist", true, false);
    }
    
    if( $this->user->is_in_group("root") )
    {
      $sublist = new itemlist("Equipe info","boxlist");
      $sublist->add("<a href=\"".$topdir."rootplace/index.php\">Rootplace</a>");
      $cts->add($sublist,true, true, "rootadminbox", "boxlist", true, false);
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


    if ( $this->user->is_in_group("root") && $this->user->is_in_group("gestion_syscarteae") )
      $req = new requete($this->db,"SELECT cpt_comptoir.id_comptoir,cpt_comptoir.nom_cpt " .
        "FROM cpt_comptoir " .
        "ORDER BY cpt_comptoir.nom_cpt");
    elseif ( $this->user->is_in_group("gestion_syscarteae") )
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
      $sublist->add("<a href=\"".$topdir."matmatronch/upload_photo_user.php\">Upload des Photos</a>");
      $sublist->add("<a href=\"".$topdir."matmatronch/inscriptions.php\">Ajout utilisateur</a>");
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
  
  /**
   * Gènère la boite contenant la photo de la semaine.
   * @param renvoie un stdcontents
   */
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

  /**
   * Gènère la boite d'information sur les comptoirs.
   * @param renvoie un stdcontents
   */
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
  
  /**
   * Gènère la boite d'information sur le forum
   * @param renvoie un stdcontents
   */
  function get_forum_box ()
  {  
    global $wwwtopdir, $topdir;
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
  
    $query_fav = $query."AND frm_sujet_utilisateur.etoile_sujet='1' ";
    $query_fav .= "ORDER BY frm_message.date_message DESC ";
    $query_fav .= "LIMIT 4 ";
    
    $query .= "AND ( frm_sujet_utilisateur.etoile_sujet IS NULL OR frm_sujet_utilisateur.etoile_sujet!='1' ) ";
    $query .= "ORDER BY frm_message.date_message DESC ";
    $query .= "LIMIT 4 ";
  
    $req = new requete($this->db,$query_fav);
  
    if ( $req->lines > 0 )
    {
      $cts->add_title(2,"<a href=\"".$wwwtopdir."forum2/search.php?page=unread\">Favoris non lus</a>");
      $list = new itemlist();
      while ( $row = $req->get_row() )
      {
        $list->add("<a href=\"".$wwwtopdir."forum2/?id_sujet=".$row['id_sujet']."&amp;spage=firstunread#firstunread\"\">".
        htmlentities($row['titre_sujet'], ENT_NOQUOTES, "UTF-8").
        "</a>");
      }
      $cts->add($list);
      if ( $req->lines == 4 )
        $cts->add_paragraph("<a href=\"".$wwwtopdir."forum2/search.php?page=unread\">suite...</a>");
    }
    else
      $cts->add_paragraph("pas de favoris non lus");
  
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
      if ( $req->lines == 4 )
        $cts->add_paragraph("<a href=\"".$wwwtopdir."forum2/search.php?page=unread\">suite...</a>");
    }
    else
      $cts->add_paragraph("pas d'autres messages non lus");
    
    return $cts;
  }
  
  /**
   * Gènère la boite d'information Superflux
   * @param renvoie un stdcontents
   */
  function get_stream_box()
  {
    $cts = new contents("Superflux");
    
    $cts->add_paragraph("La webradio de l'AE");
    
    if ( preg_match('/^\/var\/www\/ae\/www\/(taiste|taiste21)\//', $_SERVER['SCRIPT_FILENAME']) )
      $infofile = $topdir."var/cache/stream";
    else
      $infofile = $topdir."var/cache/stream-prod";
  
    if ( file_exists($infofile) )
      $GLOBALS["streaminfo"] = unserialize(file_get_contents($infofile));
    
    if ( $GLOBALS["streaminfo"]["ogg"] || $GLOBALS["streaminfo"]["mp3"] ) 
    {
      if ( $GLOBALS["streaminfo"]["title"] || $GLOBALS["streaminfo"]["artist"] )
      {
        $cts->add_title(2,"Actuellement");
        
        $cts->add_paragraph("<span id=\"streaminfo\">".
          htmlentities($GLOBALS["streaminfo"]["title"], ENT_NOQUOTES, "UTF-8").
          " - ".
          htmlentities($GLOBALS["streaminfo"]["artist"], ENT_NOQUOTES, "UTF-8")."</span>");
      }
      
      if ( $GLOBALS["streaminfo"]["message"] ) 
      {
        $cts->add_title(2,"Information");
        $cts->add_paragraph($GLOBALS["streaminfo"]["message"]);
      }
      
      $cts->add_title(2,"Ecouter");
      $list = new itemlist();
      
      if ( $GLOBALS["streaminfo"]["mp3"] )
      {
        $list->add("<a href=\"".$wwwtopdir."stream.php\" onclick=\"return popUpStream('".$wwwtopdir."');\">Lecteur web</a>");
        $list->add("<a href=\"".$GLOBALS["streaminfo"]["mp3"]."\">Flux MP3</a>");
      }
      
      if ( $GLOBALS["streaminfo"]["ogg"] )
        $list->add("<a href=\"".$GLOBALS["streaminfo"]["ogg"]."\">Flux Ogg</a>");
        
      $cts->add($list);
    }
    else
      $cts->add_paragraph("Indisponible");
    
    return $cts;  
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
  
  /**
   * S'assure qu'a partir de ce point, seul les utilisateur connecté peuvent 
   * accèder à la suite. Dans le cas d'un utilisateur connecté, affiche une 
   * erreur avec la section précisé active, propose aussi de se connecter et/ou
   * de créer un compte et arrête l'execution du script.
   * @param $section Section à activer en cas d'utilisateur non connecté.
   */
  function allow_only_logged_users($section="none")
  {
    global $topdir;
    
    if ( $this->user->is_valid() )
      return;  
      
    require_once($topdir."include/cts/login.inc.php");
    
    $this->start_page($section,"Identification requise");
    $this->add_contents(new loginerror($section));
    $this->end_page(); 
    exit();
  }
  
  /**
   * Erreur "Fatale" (ensemble du site) : Arrête l'execution du script et
   * affiche un message de maintenance.
   * @param $debug Texte inséré en comentaire dans le message de maintenance. Utile pour déterminer la raison du problème.
   */
  function fatal ($debug="fatal")
  {
    global $wwwtopdir;
    echo "<?xml version=\"1.0\"?>\n";
    echo "<!DOCTYPE html PUBLIC \"--//W3C//DTD XHTML 1.1//EN\" ";
    echo "\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n\n";
    echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\">\n";
    echo " <head>\n";
    echo "  <title>AE UTBM</title>\n";
    echo "  <link rel=\"stylesheet\" href=\"".$wwwtopdir."css/fatal.css\" title=\"fatal\" />\n";
    echo " </head>\n\n";
    echo " <body><!-- DEBUG INFO: $debug -->\n";
    echo "  <p><img src=\"".$wwwtopdir."images/fatalerror.jpg\" alt=\"Site en maintenance\" /></p>\n";
    echo " </body>\n";
    echo "</html>\n";
    exit(); 
  }
  
  /**
   * Erreur "fatale" dans une section : Arrête l'execution du script et affiche 
   * un message de maintenance dans l'interface du site avec la section précisé
   * active.
   * @param $section Section où se produit l'erreur.
   */
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
  
  /**
   * Affiche une erreur "Accès refusé", en explique la raison si précisé et
   * arrête l'execution du script.
   * @param $section Section où s'est produite l'erreur
   * @param $reason Raison du refus d'accés ("group","private")
   * @param $id_group Si la raison est "group", groupe dont il aurai fallut faire partie pour accèder au contenu. (Utilisé par expliciter l'erreur)
   */
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
    elseif ( $reason == "private" && $section =="matmatronch" )
      $this->add_contents(new contents("Accés refusé","<p>Cette fiche est privée, la personne concernée a souhaité que les informations la concernant ne soit pas rendues publiques.</p>"));
    elseif ( $reason == "blacklist_machines" )
      $this->add_contents(new contents("Accès refusé","<p>Vous n'avez pas le droit d'utiliser les machines à laver de l'AE, car vous n'avez pas respecté les confitions d'utilisations.</p>"));
    else
      $this->add_contents(new contents("Accés refusé","<p>Vos droits sont insuffisant pour accéder à cette page.</p>"));
    $this->end_page();     
    exit();
  }
  
  /**
   * Affiche une erreur "non trouvé", ou si possible redirige l'utilisateur, 
   * arrête l'execution du script dans tous les cas. La redirection est soit 
   * celle précisé, soit vers la page principale de la section.
   * @param $section Section où s'est produite l'erreur.
   * @param $redirect Redirection à faire.
   */
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
$timing["site"] += microtime(true);
?>