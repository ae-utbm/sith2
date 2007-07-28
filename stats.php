<?php

/* Copyright 2007
 * - Julien Etelain < julien dot etelain at gmail dot com >
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Simon Lopez < simon DOT lopez AT ayolo DOT org >
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
 
$topdir = "./";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir . "include/graph.inc.php");
$site = new site ();

function dec2hex($val)
{
  $hex="";
  for($i=0; $i<3; $i++)
  {
    $temp = dechex($val[$i]);
    if(strlen($temp) < 2)
      $hex .= "0". $temp;
    else
      $hex .= $temp;
  }
  return $hex;
}

if ( $_REQUEST["action"] == "os" )
{
  $color=array(255=>0,1=>0,2=>0);
  $_color="#FF0000";
  $inc=50;
  $req = new requete($site->db,"SELECT * FROM `stats_os`  ORDER BY `visites` DESC");
  $cam=new camembert(600,500,array(),2,20,0,0,0,0,0,10,150);
  $i=1;
  while($row=$req->get_row())
  {
    $cam->data($row['visites'], $_color, $row['os']);
    if($i==1)
    {
      if($color[0]!=0)
      {
        $color[0]=$color[0]-$inc;
        if($color[0]<0)
          $color[0]=0;
      }
      elseif($color[1]!=0)
      {
        $color[1]=$color[1]-$inc;
        if($color[1]<0)
          $color[1]=0;
      }
      elseif($color[2]!=0)
      {
        $color[2]=$color[2]-$inc;
        if($color[2]<0)
          $color[2]=0;
      }
      else
        $i=0;
      $_color=dec2hex($color);
    }
    if($i==0)
    {
      if($color[2]!=255)
      {
        $color[2]=$color[2]+$inc;
        if($color[2]>255)
          $color[2]=255;
      }
      elseif($color[0]!=255)
      {
        $color[0]=$color[0]+$inc;
        if($color[0]>255)
          $color[0]=255;
      }
      elseif($color[1]!=255)
      {
        $color[1]=$color[1]+$inc;
        if($color[1]>255)
          $color[1]=255;
      }
      else
      {
        $color[0]=$color[0]-$inc;
        $i=1;
      }
      $_color=dec2hex($color);
    }
  }
  $cam->png_render();
  exit();
}

if ( $_REQUEST["action"] == "browser" )
{
  $color=array(255=>0,1=>0,2=>0);
  $_color="#FF0000";
  $inc=50;
  $req = new requete($site->db,"SELECT * FROM `stats_browser`  ORDER BY `visites` DESC");
  $cam=new camembert(600,500,array(),2,20,0,0,0,0,0,10,150);
  $i=1;
  while($row=$req->get_row())
  {
    $cam->data($row['visites'], $_color, $row['browser']);
    if($i==1)
    {
      if($color[0]!=0)
      {
        $color[0]=$color[0]-$inc;
        if($color[0]<0)
          $color[0]=0;
      }
      elseif($color[1]!=0)
      {
        $color[1]=$color[1]-$inc;
        if($color[1]<0)
          $color[1]=0;
      }
      elseif($color[2]!=0)
      {
        $color[2]=$color[2]-$inc;
        if($color[2]<0)
          $color[2]=0;
      }
      else
        $i=0;
      $_color=dec2hex($color);
    }
    if($i==0)
    {
      if($color[2]!=255)
      {
        $color[2]=$color[2]+$inc;
        if($color[2]>255)
          $color[2]=255;
      }
      elseif($color[0]!=255)
      {
        $color[0]=$color[0]+$inc;
        if($color[0]>255)
          $color[0]=255;
      }
      elseif($color[1]!=255)
      {
        $color[1]=$color[1]+$inc;
        if($color[1]>255)
          $color[1]=255;
      }
      else
      {
        $color[0]=$color[0]-$inc;
        $i=1;
      }
      $_color=dec2hex($color);
    }
  }
  $cam->png_render();
  exit();
}

if (isset($_REQUEST['stats_site_start']))
{
  $start = mysql_real_escape_string($_REQUEST['stats_site_start']);
  $req = new requete($site->db,"SELECT * FROM `stats_page`  ORDER BY `visites` DESC LIMIT ".$start.",20");

  if ($req->lines < 20)
  {
    $txt = "retour au d&eacute;but  ...";
    $start=-21;
  }
  else
    $txt = "Voir les 20 suivants ...";
  if ($req->lines <= 0)
  {
    $req = new requete($site->db,"SELECT * FROM `stats_page`  ORDER BY `visites` DESC LIMIT 20");
    $start=-21;
  }
  echo "<h1>Pages visit&eacute;es visit&eacute;s</h1>\n";
  echo "<center>\n";
  $sqlt = new sqltable("top_full",
                       "Pages visit&eacute;es visit&eacute;s", $req, "stats.php",
                       "page",
                       array("page"=>"page",
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
                      );

  echo $sqlt->html_render();
  $start = $start+21;
  echo "\n<a href=\"javascript:next(this, $start)\">".$txt."</a>";
  echo "</center>";

  exit();
}

$site->start_page("none","Statistiques");
$cts = new contents("Statistiques");


$tabs = array(array("","stats.php", "Informations"),
              array("cotisants","stats.php?view=cotisants", "Cotisants"),
              array("utilisateurs","stats.php?view=utilisateurs", "Utilisateurs"),
              array("site","stats.php?view=site", "Statistiques du site"),
              array("sas","stats.php?view=sas", "SAS"),
              array("forum","stats.php?view=forum", "Forum"),
              array("comptoirs","stats.php?view=comptoirs", "Comptoirs")
              );
              
$cts->add(new tabshead($tabs,$_REQUEST["view"]));

if ( $_REQUEST["view"] == "cotisants" )
{
  if (!$site->user->is_in_group ("gestion_ae"))
  {
    error_403();
  }
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0'");
  list($total) = $req->get_row();
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW()");
  list($cotisants) = $req->get_row();
  
  if ( $site->user->is_in_group("gestion_ae") )
    $cts->add_paragraph("Cotisants : $cotisants, ".round($cotisants*100/$total,1)." % des inscrits hors anciens");
  
  $req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$cotisants,1) AS `count`, `mode_paiement_cotis` FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW() GROUP BY `mode_paiement_cotis` ORDER BY `count` DESC");
  
  $tbl = new sqltable(
    "paie", 
    "Mode de paiement des cotisations", $req, "", 
    "", 
    array("count"=>"%","mode_paiement_cotis"=>"Mode"), 
    array(), array(),
    array("mode_paiement_cotis"=> array(1=>"Espèces",2=>"Carte bleu",3=>"Chèque",4=>"Administration",5=>"E-Boutic"))
    );  
  $cts->add($tbl,true);
  
  $req = new requete($site->db,"SELECT SUM(a_pris_carte),SUM(a_pris_cadeau)  FROM `ae_cotisations` WHERE `date_fin_cotis` > NOW()");
  
  list($nbcarte,$nbcadeau) = $req->get_row();
  
  $cts->add_title(2,"Retraits");
  $cts->add_paragraph("".round($nbcarte*100/$cotisants,1)."% ont pris leur carte de membre");
  $cts->add_paragraph("".round($nbcadeau*100/$cotisants,1)."% ont pris leur cadeau");
  
 
  $req = new requete($site->db,"SELECT ROUND(SUM(`ae_utl`='1')*100/$cotisants,1) AS `count`, ROUND(SUM(`ae_utl`='1')*100/SUM(IF (ancien_etudiant_utl='0' OR ae_utl='1',1,0)),1) AS `taux`,IF(`utl_etu_utbm`.`promo_utbm` IS NULL,0,`utl_etu_utbm`.`promo_utbm`)AS `promo` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) GROUP BY `promo` ORDER BY `count` DESC");
  
  $tbl = new sqltable(
    "paie", 
    "Distribution par promo", $req, "", 
    "", 
    array("count"=>"%","taux"=>"Taux de cotisants","promo"=>"Promo"), 
    array(), array(),
    array()
    );  
  $cts->add($tbl,true);
  
/*
  $req = new requete($site->db,"SELECT ROUND(SUM(`ae_utl`='1')*100/$cotisants,1) AS `count`, ROUND(SUM(`ae_utl`='1')*100/SUM(IF (ancien_etudiant_utl='0' OR ae_utl='1',1,0)),1) AS `taux`,IF(`utl_etu_utbm`.`branche_utbm` IS NULL,'Autre',`utl_etu_utbm`.`branche_utbm`) AS `branche` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) GROUP BY `branche` ORDER BY `count` DESC");
  
  $tbl = new sqltable(
    "paie", 
    "Distribution par departement", $req, "", 
    "", 
    array("count"=>"%","taux"=>"Taux de cotisants","branche"=>"Branche"), 
    array(), array(),
    array("branche"=>$UserBranches)
    );  
  $cts->add($tbl,true);
*/
  
  $month = date("m");
  
  if ( $month >= 2 && $month < 9 )
    $debut_semestre = date("Y")."-02-01";
  else if ( $month >= 9 )
    $debut_semestre = date("Y")."-09-01";
  else
    $debut_semestre = (date("Y")-1)."-09-01";
  
  $cts->add_title(2,"Carte AE");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur)  FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `mode_paiement`='AE' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
  
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont utilisé le paiement par carte AE ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='3' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
  
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont utilisé l'e-boutic ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='1' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé à la kfet ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='7' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé à la MDE ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN cpt_debitfacture ON(ae_cotisations.`id_utilisateur`=cpt_debitfacture.`id_utilisateur_client`) WHERE `id_comptoir`='2' AND `date_fin_cotis` > NOW() AND `date_facture`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consomé au foyer ce semestre");
  
  
  $cts->add_title(2,"Matmatronch");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_maj_utl`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont mis à jour leur fiche matmatronch ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `hash_utl`='valid'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont bien activé leur compte");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN asso_membre USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND ( `date_fin` IS NULL OR `date_fin`>'$debut_semestre' ) ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% sont inscrits à une activité (sur le site)");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` LEFT JOIN parrains AS p1 USING(`id_utilisateur`) LEFT JOIN parrains AS p2 ON(ae_cotisations.`id_utilisateur`=p2.`id_utilisateur_fillot`) WHERE `date_fin_cotis` > NOW() AND (p2.`id_utilisateur` IS NOT NULL OR p1.`id_utilisateur` IS NOT NULL )");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont un parrain ou un fillot renseigné sur le site");
  
  
  $cts->add_title(2,"Site ae.utbm.fr");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `droit_image_utl`='1'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont accordé leur droit à l'image de façon systèmatique");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN sdn_a_repondu USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_reponse`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont répondu à un sondage ce semestre sur le site");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `derniere_visite_utl`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND DATEDIFF(NOW(),`derniere_visite_utl`) < 30 ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site dans les 30 derniers jours");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN utilisateurs USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND DATEDIFF(NOW(),`derniere_visite_utl`) < 7 ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont consulté le site dans les 7 derniers jours");
  
  $cts->add_title(2,"Vrac");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT ae_cotisations.id_utilisateur) FROM `ae_cotisations` INNER JOIN inv_emprunt USING(`id_utilisateur`) WHERE `date_fin_cotis` > NOW() AND `date_demande_emp`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$cotisants,1)."% ont fait un emprunt de matériel ce semestre (livres, video projecteur, tables...)");

}
elseif ( $_REQUEST["view"] == "utilisateurs" )
{
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0'");
  list($total) = $req->get_row(); 
  
  if ( $site->user->is_in_group("gestion_ae") )
    $cts->add_paragraph("Total : $total");
  
  $req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$total,1) AS `count`, IF(`utl_etu_utbm`.`promo_utbm` IS NULL,0,`utl_etu_utbm`.`promo_utbm`)AS `promo` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0'  GROUP BY `promo` ORDER BY `count` DESC");
  
  $tbl = new sqltable(
    "paie", 
    "Distribution par promo", $req, "", 
    "", 
    array("count"=>"%","promo"=>"Promo"), 
    array(), array(),
    array()
    );  
  $cts->add($tbl,true);
  /*
  $req = new requete($site->db,"SELECT ROUND(COUNT(*)*100/$total,1) AS `count`, IF(`utl_etu_utbm`.`branche_utbm` IS NULL,'Autre',`utl_etu_utbm`.`branche_utbm`) AS `branche` FROM `utilisateurs` LEFT JOIN `utl_etu_utbm` USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0'  GROUP BY `branche` ORDER BY `count` DESC");
  
  $tbl = new sqltable(
    "paie", 
    "Distribution par departement", $req, "", 
    "", 
    array("count"=>"%","branche"=>"Branche"), 
    array(), array(),
    array("branche"=>$UserBranches)
    );  
  $cts->add($tbl,true);
  */
  $month = date("m");
  
  if ( $month >= 2 && $month < 9 )
    $debut_semestre = date("Y")."-02-01";
  else if ( $month >= 9 )
    $debut_semestre = date("Y")."-09-01";
  else
    $debut_semestre = (date("Y")-1)."-09-01";
  
  
  $cts->add_title(2,"Matmatronch");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `date_maj_utl`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont mis à jour leur fiche matmatronch ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `hash_utl`='valid'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont bien activé leur compte");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` INNER JOIN asso_membre USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0' AND ( `date_fin` IS NULL OR `date_fin`>'$debut_semestre' ) ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% sont inscrits à une activité (sur le site)");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` LEFT JOIN parrains AS p1 USING(`id_utilisateur`) LEFT JOIN parrains AS p2 ON(utilisateurs.`id_utilisateur`=p2.`id_utilisateur_fillot`) WHERE `ancien_etudiant_utl`='0' AND (p2.`id_utilisateur` IS NOT NULL OR p1.`id_utilisateur` IS NOT NULL )");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont un parrain ou un fillot renseigné sur le site");
  
  
  $cts->add_title(2,"Site ae.utbm.fr");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `droit_image_utl`='1'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont accordé leur droit à l'image de façon systèmatique");
  
  $req = new requete($site->db,"SELECT COUNT(DISTINCT utilisateurs.id_utilisateur) FROM `utilisateurs` INNER JOIN sdn_a_repondu USING(`id_utilisateur`) WHERE `ancien_etudiant_utl`='0' AND `date_reponse`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont répondu à un sondage ce semestre sur le site");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND `derniere_visite_utl`>'$debut_semestre'");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site ce semestre");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND DATEDIFF(NOW(),`derniere_visite_utl`) < 30 ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site dans les 30 derniers jours");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `utilisateurs` WHERE `ancien_etudiant_utl`='0' AND DATEDIFF(NOW(),`derniere_visite_utl`) < 7 ");
  list($nbutil) = $req->get_row();
  $cts->add_paragraph("".round($nbutil*100/$total,1)."% ont consulté le site dans les 7 derniers jours");
}
elseif ( $_REQUEST["view"] == "site" )
{
  if ($site->user->is_in_group ("gestion_ae"))
  {
    if ( $_REQUEST["action"] == "reset" )
    {
      $req = new requete($site->dbrw, "DELETE FROM `stats_page` WHERE `page`!=''");
      $req = new requete($site->dbrw, "DELETE FROM `stats_os` WHERE `os`!=''");
      $req = new requete($site->dbrw, "DELETE FROM `stats_browser` WHERE `browser`!=''");
      $cts->add_title(2, "Reset");
      $cts->add_paragraph("Le reset des stats a &eacute;t&eacute; effectu&eacute; avec succ&egrave;s");
    }

    $cts->add_title(2, "Administration");
    $cts->add_paragraph("Remettre &agrave; z&eacute;ro les stats du site ae.".
			"<br /><img src=\"".$topdir."images/actions/delete.png\"><b>ATTENTION CECI EST IRREVERSIBLE</b> : ".
			"<a href=\"stats_site.php?view=site&action=reset\" onClick= \"if (confirm('Voulez vous vraiment remettre les statistiques à zéro ? ')) { return true; } else { return false; }}\">Reset !</a>");
	}
  $cts->add_paragraph("<script language=\"javascript\">
  function next(obj, start)
  {
    openInContents('cts2', './stats.php', 'stats_site_start='+start);
  }
	</script>\n");
  $site->add_contents($cts);

  $cts = new contents("Pages visit&eacute;es visit&eacute;s");
  $req = new requete($site->db,"SELECT * FROM `stats_page`  ORDER BY `visites` DESC LIMIT 20");
  if($req->lines<20)
    $less=true;
  else
    $less=false;
  $sqlt = new sqltable("top_full",
                       "", $req, "stats.php",
                       "page",
                       array("page"=>"page",
                             "visites"=>"Visites"),
                       array(),
                       array(),
                       array()
                      );
  $cts->add_paragraph("<center>".$sqlt->html_render()."</center>");
  if(!$less)
    $cts->add_paragraph("<center><a href=\"javascript:next(this, 21)\">Voir les 20 suivants ...</a></center>");

  $site->add_contents($cts);

  $cts = new contents("Navigateurs utilis&eacute;s");
  $cts->add_paragraph("<center><img src=\"stats.php?action=browser\" alt=\"navigateurs utilis&eacute;s\" /></center>\n");
  $site->add_contents($cts);

  $cts = new contents("Syst&egrave;mes d'exploitation utilis&eacute;s");
  $cts->add_paragraph("<center><img src=\"stats.php?action=os\" alt=\"syst&egrave;mes d'exploitation utilis&eacute;s\" /></center>\n");
}
elseif ( $_REQUEST["view"] == "sas" )
{
  
  $cts->add_title(2,"Photos");

  $req = new requete($site->db,"SELECT COUNT(*) FROM `sas_photos`");
  list($total) = $req->get_row();
  
  $cts->add_paragraph("".$total." photos (et vidéos) dans le SAS");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `sas_photos` WHERE incomplet=1");
  list($nbphotos) = $req->get_row();
  
  $cts->add_paragraph("".round($nbphotos*100/$total,1)."% photos incomplètes");
  
  $req = new requete($site->db,"SELECT COUNT(*) FROM `sas_photos` WHERE droits_acquis=1");
  list($nbphotos) = $req->get_row();
  
  $cts->add_paragraph("".round($nbphotos*100/$total,1)."% photos dont tous les droits sont acquis");
  
  
  $req = new requete ($site->db, "SELECT COUNT(sas_photos.id_photo) as `count`, `utilisateurs`.`id_utilisateur`, " .
          "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
          "FROM sas_photos " .
          "INNER JOIN utilisateurs ON sas_photos.id_utilisateur=utilisateurs.id_utilisateur " .
          "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
          "GROUP BY sas_photos.id_utilisateur " .
          "ORDER BY count DESC LIMIT 30");
  
  $lst = new itemlist("Les meilleurs contributeurs (30)");
  $n=1;
  while ( $row = $req->get_row() )
    {
      $lst->add("$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] )." (".$row['count']." photos)");
      $n++;
    }
  
  $cts->add($lst,true);
  
  $req = new requete ($site->db, "SELECT COUNT(sas_personnes_photos.id_photo) as `count`, `utilisateurs`.`id_utilisateur`, " .
          "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur` " .
          "FROM sas_personnes_photos " .
          "INNER JOIN utilisateurs ON sas_personnes_photos.id_utilisateur=utilisateurs.id_utilisateur " .
          "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
          "GROUP BY sas_personnes_photos.id_utilisateur " .
          "ORDER BY count DESC LIMIT 30");
  
  $lst = new itemlist("Les plus photographi&eacute;s (30)");
  $n=1;
  while ( $row = $req->get_row() )
    {
      $lst->add("$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] )." (".$row['count']." photos)");
      $n++;
    }
  
  $cts->add($lst,true);
  
}
elseif ( $_REQUEST["view"] == "forum" )
{
  if (!$site->user->is_in_group ("gestion_ae"))
    { 
      error_403();
    }   

  if (isset($_REQUEST['toptenimg']))
    {
      require_once($topdir. "include/graph.inc.php");
      $req = "SELECT 
                COUNT(`id_message`) as totmesg
              , `utilisateurs`.`alias_utl`
          FROM 
                `frm_message`
          INNER JOIN 
                `utilisateurs`
          USING (`id_utilisateur`)
          GROUP BY 
                 `id_utilisateur`
          ORDER BY 
                 COUNT(`id_message`) DESC LIMIT 10";

      $rs = new requete($site->db, $req);


      $datas = array("utilisateur" => "Nbmessages");
  

      while ($plouf = $rs->get_row())
  {
    $plouf['alias_utl'] = explode(' ', $plouf['alias_utl']);
    $plouf['alias_utl'] = $plouf['alias_utl'][0];
      
    $datas[utf8_decode($plouf['alias_utl'])] = $plouf['totmesg'];
  }
  
  
      $hist = new histogram($datas, "Top 10");
  
      $hist->png_render();
  
      $hist->destroy();
  
      exit();

    }

  if (isset($_REQUEST['mesgbyday']))
    {
      if (!isset($_REQUEST['db']))
  $db = date("Y")."-01-01";
      else
  $db = $_REQUEST['db'];

      if (!isset($_REQUEST['de']))
  $de = date("Y-m-d");
      else
  $de = $_REQUEST['de'];

      $db = mysql_real_escape_string($db);
      $de = mysql_real_escape_string($de);


      require_once($topdir. "include/graph.inc.php");
      $query =
  "SELECT 
            DATE_FORMAT(date_message,'%Y-%m-%d') AS `datemesg`
            , COUNT(id_message) AS `nbmesg` 
     FROM 
            `frm_message` 
     WHERE 
            `date_message` >= '".$db."'
     AND
            `date_message` <= '".$de."'
     GROUP BY 
            `datemesg`";

      $req = new requete($site->db, $query);

      $i = 0;

      $step = (int) ($req->lines / 5);

      while ($rs = $req->get_row())
  {
    if (($i % $step) == 0)
      $xtics[$i]  = $rs['datemesg'];
    $coords[] = array('x' => $i,
          'y' => $rs['nbmesg']);
    $i++;
  }
  

      $grp = new graphic("",
       "messages par jour",
       $coords,
       false,
       $xtics);

      $grp->png_render();

      $grp->destroy_graph();

      exit();
    }



  $fcts = new contents("Statistiques du forum");
  $fcts->add_title(1, "Top 10 des posteurs");
  $fcts->add_paragraph("<center><img src=\"./stats.php?view=forum&toptenimg\" alt=\"top10\" /></center>");
  
  $fcts->add_title(1, "Messages postés depuis le début de l'année");
  $fcts->add_paragraph("<center><img src=\"./stats.php?view=forum&mesgbyday\" alt=\"Messages par jour\" /></center>");
  
  $fcts->add_title(1, "Messages postés les 30 derniers jours");
  
  /* statistiques sur 30 jours */
  $db = date("Y-m-d", time() - (30 * 24 * 3600));
  
  $fcts->add_paragraph("<center><img src=\"./stats.php?view=forum&mesgbyday&db=".$db."&de=".date("Y-m-d").
          "\" alt=\"Messages par jour\" /></center>");
  
  $cts->add($fcts);

}

elseif ( $_REQUEST["view"] == "comptoirs" )
{
  $site->add_css("css/comptoirs.css");
  
  $month = date("m");
  
  if ( $month >= 2 && $month < 9 )
    $debut_semestre = date("Y")."-02-01";
  else if ( $month >= 9 )
    $debut_semestre = date("Y")."-09-01";
  else
    $debut_semestre = (date("Y")-1)."-09-01";
  if ($site->user->is_in_group ("gestion_ae") || $site->user->is_in_group ("kfet_admin") || $site->user->is_in_group ("foyer_admin"))
  {
    $req = new requete ($site->db, "SELECT `utilisateurs`.`id_utilisateur`, " .
        "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur`, " .
        "ROUND(SUM( UNIX_TIMESTAMP( `activity_time` ) - UNIX_TIMESTAMP( `logged_time` ) ) /60/60,1) as total " .
        "FROM cpt_tracking " .
        "INNER JOIN utilisateurs ON cpt_tracking.id_utilisateur=utilisateurs.id_utilisateur " .
        "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
        "WHERE logged_time > '$debut_semestre' " .
        "GROUP BY utilisateurs.id_utilisateur " .
        "ORDER BY total DESC LIMIT 30");
      
    $cts->add(new sqltable(
      "barmens", 
      "Barmens : Permanences cumulées (ce semestre)", $req, "", 
      "", 
      array("nom_utilisateur"=>"Barmen","total"=>"Heures cumulées"), 
      array(), array(),
      array()
      ),true);  
  }
  $cts->add_title(2,"Consomateurs : Top 10 (+ 90 premiers) (ce semestre)");
  
  
  $req = new requete ($site->db, "SELECT `utilisateurs`.`id_utilisateur`, " .
      "IF(utl_etu_utbm.surnom_utbm!='' AND utl_etu_utbm.surnom_utbm IS NOT NULL,utl_etu_utbm.surnom_utbm, CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`)) as `nom_utilisateur`, " .
      "sum(`cpt_vendu`.`quantite`*`cpt_vendu`.prix_unit) as total " .
      "FROM cpt_vendu " .
      "INNER JOIN cpt_debitfacture ON cpt_debitfacture.id_facture=cpt_vendu.id_facture " .
      "INNER JOIN utilisateurs ON cpt_debitfacture.id_utilisateur_client=utilisateurs.id_utilisateur " .
      "LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` ".
      "WHERE cpt_debitfacture.mode_paiement='AE' AND date_facture > '$debut_semestre' " .
      "GROUP BY utilisateurs.id_utilisateur " .
      "ORDER BY total DESC LIMIT 100");
  
  $lst = new itemlist(false,"top10");
  
  $n=1;
  
  while ( $row = $req->get_row() )
  {
    $class = $n<=10?"top":false;
    
    if ( $row["id_utilisateur"] == $site->user->id )
      $class = $class?"$class me":"me";
          if ( !$site->user->is_in_group("gestion_ae") && !$site->user->is_in_group("foyer_admin") && !$site->user->is_in_group("kfet_admin"))
      $lst->add("N°$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"]),$class);
          else
      $lst->add("N°$n : ".entitylink ("utilisateur", $row["id_utilisateur"], $row["nom_utilisateur"] ).(isset($_REQUEST["fcsoldes"])?" ".($row["total"]/100):""),$class);
    $n++;
  }
  
  $cts->add($lst);
}



$site->add_contents($cts);
$site->end_page();  

?>
