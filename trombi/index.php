<?php

/* Copyright 2007
 *
 * - Sebastien WATTIEZ < webast2 at gmail dot com >
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants
 * de l'UTBM, http://ae.utbm.fr.
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

$topdir = "../";

include($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "sas2/include/photo.inc.php");
require_once($topdir. "include/cts/gallery.inc.php");
require_once($topdir. "include/cts/special.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir. "include/entities/ville.inc.php");
require_once($topdir. "include/entities/pays.inc.php");
require_once($topdir. "include/graph.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once("include/entities/commentaire.inc.php");
require_once("include/cts/commentaire.inc.php");

$site = new site();
$cmt = new commentaire($site->db, $site->dbrw);

$site->add_css("css/userfullinfo.css");

if (!$site->user->id)
  error_403();
  

if (isset($_REQUEST['id_utilisateur']))
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  
  if (!$user->is_valid())
    $site->error_not_found("matmatronch");
    
  $is_user_page = (!$user->id==$site->user->id);
  $can_edit = ($user->id==$site->user->id || $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));
  
  if ($user->id != $site->user->id && !$site->user->utbm && !$site->user->ae)
    $site->error_forbidden("matmatronch","group",10001);
 
  if (!$user->publique && !$can_edit)
    $site->error_forbidden("matmatronch","private");
  if(!$user->promo_utbm != $site->user->promo_utbm && $site->user->is_in_group("gestion_ae"))
  {
    $user = &$site->user;
    $can_edit = true;
  }
}
else
{
  $user = &$site->user;
  $is_user_page = false;
  $can_edit = true;
}

  
if ( $_REQUEST["page"]  == "edit" && $can_edit )
{
  if ( isset($_REQUEST["id_commentaire"]) )
  {
    $cmt->load_by_id($_REQUEST["id_commentaire"]);
    if ( $cmt->id < 1 )
    {
      header("Location: 404.php");
      exit();
    }
    
    $site->start_page ("services", "Edition d'un commentaire");
    $cts = new contents("Editer");

    $frm = new form("editcomment", "index.php", false, "POST", "Edition d'un commentaire");
    $frm->add_hidden("action","edit");
    $frm->add_hidden("id_commentaire",$cmt->id);
    $frm->add_info("<b>ATTENTION</b>Votre commentaire peut &ecirc;tre mod&eacute;r&eacute;");
    $frm->add_dokuwiki_toolbar('comment');
    $frm->add_text_area ("comment","Commentaire",$cmt->commentaire);
    $frm->add_submit("valid","Enregistrer");

    $site->add_contents ($frm);

    $site->add_contents (new wikihelp());
    $site->end_page ();
    exit();
  }
}


if ( ($_REQUEST["action"] == "create") && (!$is_user_page || $can_edit) )
{
  //add_comment
}
elseif ( ($_REQUEST["action"] == "edit") && (!$is_user_page || $can_edit) )
{
  
}


$site->start_page ("none", "Trombi Promo ".sprintf("%02d",$site->user->promo_utbm));

$tabs = array(array("","trombi/index.php", "Informations"),
              //array("board","trombi/index.php?view=board", "Messages"),
              array("listing","trombi/index.php?view=listing", "La promo"),
              array("stats","trombi/index.php?view=stats", "Des chiffres")
             );
$cts = new contents("Trombinoscope, Promo ".sprintf("%02d",$site->user->promo_utbm));
$cts->add(new tabshead($tabs,$_REQUEST["view"]));


if(isset($_REQUEST["stats"]))
{
  if($_REQUEST["stats"]=="sexe")
  {
    $req = new requete($site->db,
                       "SELECT `utilisateurs`.`sexe_utl`, COUNT(`utilisateurs`.`sexe_utl`) ".
                       "FROM `utl_etu_utbm` ".
                       "LEFT JOIN `utilisateurs` USING (`id_utilisateur`) ".
                       "WHERE `promo_utbm`='" . $site->user->promo_utbm . "' ".
                       "GROUP BY `utilisateurs`.`sexe_utl`");
    $cam=new camembert(600,400,array(),2,0,0,0,0,0,0,10,150);
    while(list($sexe,$nb)=$req->get_row())
    {
      if($sexe==1)
        $cam->data($nb, "Homme");
      elseif($sexe=="2")
        $cam->data($nb, "Femme");
    }
    $cam->png_render();
    exit();
  }
  elseif($_REQUEST["stats"]=="departements")
  {
    $cam=new camembert(600,400,array(),2,0,0,0,0,0,0,10,150);
    $req = new requete($site->db,
                       "SELECT `branche_utbm` , COUNT( `branche_utbm` ) ".
                       "FROM `utl_etu_utbm` ".
                       "WHERE `promo_utbm` = '" . $site->user->promo_utbm . "'".
                       "GROUP BY `branche_utbm`");
    while(list($branche,$nb)=$req->get_row())
      $cam->data($nb, $branche);
    $cam->png_render();
    exit();
  }
  elseif($_REQUEST["stats"]=="naissances")
  {
    $req = new requete($site->db,
                       "SELECT substring(`utilisateurs`.`date_naissance_utl`,1,7) AS date, ".
                       "COUNT(substring(`utilisateurs`.`date_naissance_utl`,1,7)) as num ".
                       "FROM `utilisateurs` ".
                       "INNER JOIN `utl_etu_utbm` USING(`id_utilisateur`) ".
                       "WHERE `utl_etu_utbm`.`promo_utbm`='" . $site->user->promo_utbm . "' ".
                       "AND `utilisateurs`.`date_naissance_utl` IS NOT NULL ".
                       "AND `utilisateurs`.`date_naissance_utl` != '1970-01-01' ".
                       "AND `utilisateurs`.`date_naissance_utl` != '0000-00-00' ".
                       "GROUP BY substring(`utilisateurs`.`date_naissance_utl`,1,7) ".
                       "ORDER BY substring(`utilisateurs`.`date_naissance_utl`,1,7) ASC");
    $coords = array();
    $xtics = array();
    $i = 0;
    $step = (int) ($req->lines / 5);
    while(list($date,$nb)=$req->get_row())
    {
      if (($i % $step) == 0)
        $xtics[$i]=$date;
      $coords[] = array('x' => $i,'y' => $nb);
      $i++;
    }
    $graph = new graphic("", "Date de naissance des membres",$coords,false,$xtics);
    $graph->png_render();
    $graph->destroy_graph();
    exit();
  }
  elseif($_REQUEST["stats"]=="france")
  {
    $img = new imgcarto(800, 10);
    $img->addcolor('pblue_dark', 51, 102, 153);
    $img->addcolor('pblue', 222, 235, 245);

    $nbpaliers = 5;

    $img->addcolor('l0', 255, 255, 255);
    $img->addcolor('l1', 255, 220, 0);
    $img->addcolor('l2', 255, 198, 0);
    $img->addcolor('l3', 255, 176, 0);
    $img->addcolor('l4', 255, 154, 0);
    $img->addcolor('l5', 255, 143, 0);
    $img->addcolor('l6', 255, 121, 0);
    $img->addcolor('l7', 255, 114, 0);
    $img->addcolor('l8', 255, 101, 0);
    $img->addcolor('l9', 255, 68, 0);
    $img->addcolor('l10', 255, 0, 0);
    $img->addcolor('lsux', 255, 255, 0);

    $pgconn = new pgsqlae();

    $statscotis = new requete($site->db, "SELECT  
                                          COUNT(`utl_etu`.`id_utilisateur`) AS num  
                                          , substring(cpostal_ville,1,2) AS cpostal 
                                          FROM `utl_etu`
                                          INNER JOIN `loc_ville` ON `loc_ville`.`id_ville` = `utl_etu`.`id_ville`
                                          INNER JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur` = `utl_etu`.`id_utilisateur`
                                          WHERE `utl_etu`.`id_ville` IS NOT NULL AND `utl_etu_utbm`.`promo_utbm`='" . $site->user->promo_utbm . "'
                                          GROUP BY substring(cpostal_ville,1,2)");
    $max=0;
    while ($rs = $statscotis->get_row())
    {
      $statsdep[$rs['cpostal']] = $rs['num'];
      if($max<$rs['num'])
        $max=$rs['num'];
    }
    $pgreq = new pgrequete($pgconn, "SELECT code_dept, nom_dept, asText(simplify(the_geom, 2000)) AS points FROM deptfr");
    $rs = $pgreq->get_all_rows();
    $numdept = 0;
    $dept=array();
    foreach($rs as $result)
    {
      $astext = $result['points'];
      $matched = array();
      preg_match_all("/\(([^)]*)\)/", $astext, $matched);
      $i = 0;
      foreach ($matched[1] as $polygon)
      {
        $polygon = str_replace("(", "", $polygon);
        $points = explode(",", $polygon);
        foreach ($points as $point)
        {
          $coord = explode(" ", $point);
          $dept[$numdept]['plgs'][$i][] = $coord[0];
          $dept[$numdept]['plgs'][$i][] = $coord[1];
        }
        $i++;
      }
      $dept[$numdept]['name'] = $result['nom_dept'];
      $dept[$numdept]['iddept'] = $result['code_dept'];

      $numdept++;
    }
    foreach($dept as $departement)
    {
      foreach($departement['plgs'] as $plg)
      {
        if ($statsdep[$departement['iddept']] == 0)
          $img->addpolygon($plg, 'l0', true,
                           array('id' =>$departement['gid'],
                                 'url' => "".
                                 $departement['iddept']. ")"));
        else
        {
          $color=(int)(($statsdep[$departement['iddept']]*10)/$max);
          if($color==0)
            $color="sux";
          $img->addpolygon($plg, 'l' . $color, true,
                           array('id' =>$departement['gid'],
                                 'url' => "".
                                 $departement['iddept']. ")"));
        }
        $img->addpolygon($plg, 'black', false);
      }
    }

    $img->draw();
    $wm_img = new img_watermark ($img->imgres);
    $wm_img->output();
    exit();
  }
}


if($_REQUEST["view"] == "listing")
{
  $site->add_css("css/mmt.css");
  $npp=18;
  $page = intval($_REQUEST["page"]);

  if ( $page)
    $st=$page*$npp;
  else
    $st=0;
  $reqnb = new requete($site->db,
                       "SELECT COUNT(`utilisateurs`.`id_utilisateur`) "
                       ."FROM `utl_etu_utbm` "
                       ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                       ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                       ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                       ."AND `publique_utl`='1'");
  list($nb) = $reqnb->get_row();

  $req = new requete($site->db,
                     "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.*, "
                    ."`utilisateurs`.`id_ville` AS `id_ville`, `utl_etu`.`id_ville` AS `ville_parents`, "
                    ."`utilisateurs`.`id_pays` AS `id_pays`, `utl_etu`.`id_pays` AS `pays_parents` "
                    ."FROM `utl_etu_utbm` "
                    ."LEFT JOIN `utilisateurs` USING (`id_utilisateur`) "
                    ."LEFT JOIN `utl_etu` USING (`id_utilisateur`) "
                    ."WHERE `promo_utbm`='" . $site->user->promo_utbm . "' "
                    ."AND `publique_utl`='1' "
                    ."ORDER BY `nom_utl`, `prenom_utl` ASC "
                    ."LIMIT ".$st." , ".$npp."");
  if ($req->lines == 0)
    $tbl = new error("Aucun resultat","");
  else
  {
    $gal = new gallery();
    $tmpuser = new utilisateur($site->db);
    while ( $row = $req->get_row() )
    {
      $tmpuser->_load_all($row);
      $gal->add_item(new userinfov2($tmpuser, "small", false, "trombi/index.php"));
    }
    $cts->add($gal);
    if ( $nb > $npp )
    {
      $tabs = array();
      $i=0;
      while ( $i < $nb )
      {
        $n = $i/$npp;
        $url = "";
        $ar = array_merge($_GET,$_POST);
        $ar["page"] = $n;
        foreach ( $ar as $key => $value )
        {
          if( $key != "magicform" && $value && $key != "mmtsubmit" )
          {
            if ( $url )
              $url .= "&";
            else
              $url = "trombi/index.php?";
            if ( !is_array($value) )
              $url .= $key."=".rawurlencode($value);
          }
        }
        $tabs[]=array($n,$url,$n+1 );
        $i+=$npp;
      }
      $cts->add(new tabshead($tabs, $page, "_bottom"));
    }
  }
}
elseif($_REQUEST["view"]=="stats")
{
  $cts->add_paragraph("Des stats, des stats, oui mais des panzani ! (&copy;Ayolo)");
  $site->add_contents($cts);
  $cts = new contents("Répartition Homme/Femme dans la promo");
  $cts->add_paragraph("<center><img src=\"index.php?stats=sexe\" alt=\"répartition Homme/Femme\" /></center>\n");
  $site->add_contents($cts);
  $cts = new contents("Carte de la natalité");
  $cts->add_paragraph("<center><img src=\"index.php?stats=naissances\" alt=\"graph des naissances\" /></center>\n");
  $site->add_contents($cts);
  $cts = new contents("Répartition par départements");
  $cts->add_paragraph("<center><img src=\"index.php?stats=departements\" alt=\"répartition par départements\" /></center>\n");
  $site->add_contents($cts);
  $cts = new contents("Carte de france de la promo");
  $cts->add_paragraph("<center><img src=\"index.php?stats=france\" alt=\"carte de france de la promo\" /></center>\n");
}
else
{

  $cts->add_title(2, "Informations personnelles");
  $info = new userinfov2($user,"full",$site->user->is_in_group("gestion_ae"), "trombi/index.php");
  $cts->add($info);
  
  /* renvois plus bas */
  $cts->add_paragraph("<a href=\"comments\">Voir les commentaires</a>");

  /* photos */
  $grps = $site->user->get_groups_csv();
  $req = new requete($site->db,"SELECT sas_photos.* " .
                               "FROM sas_personnes_photos AS `p2` " .
                               "INNER JOIN sas_photos ON p2.id_photo=sas_photos.id_photo " .
                               "INNER JOIN sas_cat_photos ON sas_cat_photos.id_catph=sas_photos.id_catph " .
                               "LEFT JOIN sas_personnes_photos AS `p1` ON " .
                               "(p1.id_photo=sas_photos.id_photo " .
                               "AND p1.id_utilisateur='". $site->user->id."' " .//$site->user->id
                               "AND p1.modere_phutl='1') " .
                               "WHERE " .
                               "p2.id_utilisateur='". $user->id."' AND " .
                               "((((droits_acces_ph & 0x1) OR " .
                               "((droits_acces_ph & 0x10) AND sas_photos.id_groupe IN ($grps))) " .
                               "AND droits_acquis='1') OR " .
                               "(sas_photos.id_groupe_admin IN ($grps)) OR " .
                               "((droits_acces_ph & 0x100) AND sas_photos.id_utilisateur='". $site->user->id."') OR " .
                               "((droits_acces_ph & 0x100) AND p1.id_utilisateur IS NOT NULL) ) " .
                               "ORDER BY RAND() ".
                               "LIMIT 5"
                     );
  if($req->lines>0)
  {
    $site->add_css("css/sas.css");
    $site->add_contents($cts);
    $cts = new contents("Photos");
    $gal = new gallery("Photos aléatoires","photos");
    while ( $row = $req->get_row())
    {
      $img = $topdir."sas2/images.php?/".$row['id_photo'].".vignette.jpg";
      $gal->add_item("<a href=\"".$topdir."sas2/?id_photo=".$row['id_photo']."\"><img src=\"$img\" alt=\"Photo\"></a>");
    }
    $cts->add($gal,true);
    $cts->add_paragraph("<a href=\"".$topdir."user/photos.php?id_utilisateur=".$user->id."\">Toutes les photos</a>.");
  }

  /* genealogie */
  $genea=false;
  $req = new requete($site->db,
                "SELECT `utilisateurs`.`id_utilisateur` AS `id_utilisateur2`, " .
                "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur2` " .
                "FROM `parrains` " .
                "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`parrains`.`id_utilisateur` " .
                "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
                "WHERE `parrains`.`id_utilisateur_fillot`='".$user->id."'");
  if ( $req->lines > 0 )
  {
    $site->add_contents($cts);
    $cts = new contents("Généalogie");
    $cts->add_paragraph("<a href=\"".$topdir."family.php?id_utilisateur=".$user->id."\">Arbre généalogique parrains/fillots</a>.");
    $genea=true;
    $tbl = new sqltable("listasso",
                        "Parrain(s)/Marraine(s)", $req, "user.php?view=parrain&mode=parrain&id_utilisateur=".$user->id,
                        "id_utilisateur2",
                        array("nom_utilisateur2"=>"Parrain/Marraine"),
                        array("delete"=>"Enlever"), array(), array( )
                      );
    $cts->add($tbl,true);
  }
  $req = new requete($site->db,
                "SELECT `utilisateurs`.`id_utilisateur` AS `id_utilisateur2`, " .
                "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur2` " .
                "FROM `parrains` " .
                "INNER JOIN `utilisateurs` ON `utilisateurs`.`id_utilisateur`=`parrains`.`id_utilisateur_fillot` " .
                "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
                "WHERE `parrains`.`id_utilisateur`='".$user->id."'");
  if ( $req->lines > 0 )
  {
    if(!$genea)
    {
      $site->add_contents($cts);
      $cts = new contents("Généalogie");
      $cts->add_paragraph("<a href=\"".$topdir."family.php?id_utilisateur=".$user->id."\">Arbre généalogique parrains/fillots</a>.");
    }
    $tbl = new sqltable("listasso",
                      "Fillot(s)/Fillote(s)", $req, "user.php?view=parrain&mode=fillot&id_utilisateur=".$user->id,
                      "id_utilisateur2",
                      array("nom_utilisateur2"=>"Fillot/Fillote"),
                      array("delete"=>"Enlever"), array(), array( )
                    );
    $cts->add($tbl,true);
  }

  $asso=false;
  /* Associations en cours */
  $req = new requete($site->db,
                     "SELECT `asso`.`id_asso`, `asso`.`nom_asso`, " .
                     "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`, ".
                     "`asso_membre`.`date_debut`, `asso_membre`.`desc_role`, " .
                     "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                     "FROM `asso_membre` " .
                     "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                     "WHERE `asso_membre`.`id_utilisateur`='".$user->id."' " .
                     "AND `asso_membre`.`date_fin` is NULL " .
                     "ORDER BY `asso`.`nom_asso`");
  if ( $req->lines > 0 )
  {
    $site->add_contents($cts);
    $cts = new contents("Associations");
    $asso=true;
    $tbl = new sqltable("listasso",
                        "Associations et clubs actuels",
                        $req,
                        "user.php?id_utilisateur=".$user->id,
                        "id_membership",
                          array("nom_asso"=>"Association","role"=>"Role","desc_role"=>"","date_debut"=>"Depuis"),
                          $can_edit?array("delete"=>"Supprimer","stop"=>"Arreter à la date de ce jour"):array(),
                          array(),
                          array("role"=>$GLOBALS['ROLEASSO100'])
                        );
    $cts->add($tbl,true);
  }
  /* Anciennes assos */
  $req = new requete($site->db,
                     "SELECT `asso`.`id_asso`, `asso`.`nom_asso`, " .
                     "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`, ".
                     "`asso_membre`.`date_debut`, `asso_membre`.`desc_role`, `asso_membre`.`date_fin`, " .
                     "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                     "FROM `asso_membre` " .
                     "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                     "WHERE `asso_membre`.`id_utilisateur`='".$user->id."' " .
                     "AND `asso_membre`.`date_fin` is NOT NULL " .
                     "ORDER BY `asso`.`nom_asso`,`asso_membre`.`date_debut`");
  if ( $req->lines > 0 )
  {
    if(!$asso)
    {
      $site->add_contents($cts);
      $cts = new contents("Associations");
    }
    $tbl = new sqltable("listassoformer",
                        "Associations et clubs (anciennes participations)", $req, "user.php?id_utilisateur=".$user->id,
                        "id_membership",
                        array("nom_asso"=>"Association","role"=>"Role","desc_role"=>"","date_debut"=>"Date de début","date_fin"=>"Date de fin"),
                        $can_edit?array("delete"=>"Supprimer"):array(), array(), array("role"=>$GLOBALS['ROLEASSO100'] )
                       );
    $cts->add($tbl,true);
  }
  
  /* Commentaires */
  $site->add_contents($cts);
  $cts = new contents("Commentaires");
  $cts->add_paragraph("<a name=\"comments\"></a>");
  
  $req = new requete($site->db,
           "SELECT * FROM `trombi_commentaire`
             WHERE `id_commente` = '" .
		    mysql_real_escape_string($user->id) . "'
             AND `id_commentateur` = '" .
            mysql_real_escape_string($site->user->id) . "'
             LIMIT 1"
         );
                     
  $cmt_exists = false;
  
  if ( $req->lines == 0 )
  {
    $cts->add_paragraph( ($is_user_page ? "Vous n'avez" : "Cet utilisateur n'a") . " encore aucun commentaire.");
  }
  else
  {
    if ( !$is_user_page )
    {
      $cmt_exists = $cmt->comment_exists ( $user->id, $site->user->id );
      
      if ( $cmt_exists )
      {
        $cts->add_paragraph("<a href=\"#my_comment\">Aller à mon commentaire</a>");
      }
    }
    
    while ( $row = $req->get_row() )
    {
      $cts->add(new comment_contents(&$row, $user->id, $can_edit));
    }
    
  }
  
  if ( !$is_user_page && !$cmt_exists )
  {    
    $frm = new form("createcomment", "index.php", false, "POST", "Ajouter mon commentaire");
    $frm->add_hidden("action","create");
    $frm->add_info("<b>ATTENTION</b>Votre commentaire peut &ecirc;tre mod&eacute;r&eacute;");
    $frm->add_dokuwiki_toolbar('comment');
    $frm->add_text_area ("comment","Commentaire");
    $frm->add_submit("valid","Enregistrer");
    
    $cts->add($frm);
    $cts->add(new wikihelp());
  }
}


$site->add_contents($cts);


$site->end_page ();

?>

