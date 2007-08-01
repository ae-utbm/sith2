<?php

/* Copyright 2007
 *
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
define('MAGPIE_CACHE_DIR', '/var/www/ae/www/var/cache/planet/');
define('MAGPIE_CACHE_ON', true);
define('MAGPIE_CACHE_AGE', 60*60); //une heure
define('MAGPIE_OUTPUT_ENCODING', "UTF-8");
define('MAX_NUM',20);
define('MAX_SUM_LENGHT',200);


include($topdir. "include/site.inc.php");
require_once($topdir. "include/lib/magpierss/rss_fetch.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir . "include/graph.inc.php");

$site = new site();

if (!$site->user->id)
  error_403();

$site->start_page ("none", "Planet AE ");

$tabs = array(array("","planet/index.php", "Planet"),
              array("perso","planet/index.php?view=perso", "Personnaliser"),
              array("add","planet/index.php?view=add", "Proposer")
             );
$cts = new contents("Planet AE ");
$cts->add(new tabshead($tabs,$_REQUEST["view"]));

if($_REQUEST["action"]=="delete")
{
  $sql="";
  if(!$site->user->is_in_group("gestion_ae"))
  {
    $sql="AND `id_utilisateur`='".$site->user->id."'";
  }
  $req = new requete($site->db,"SELECT `id_flux` FROM `planet_flux` WHERE `id_flux`='".$_REQUEST["id_flux"]."' ".$sql." LIMIT 1");
  if($req->lines==1)
  {
    $_req = new delete($site->dbrw, "planet_flux", array("id_flux" => $_REQUEST["id_flux"]));
    $_req = new delete($site->dbrw, "planet_flux_tags", array("id_flux" => $_REQUEST["id_flux"]));
    $_req = new delete($site->dbrw, "planet_user_flux", array("id_flux" => $_REQUEST["id_flux"]));
  }
}
elseif($_REQUEST["action"]=="addflux" && !empty($_REQUEST["url"]) && !empty($_REQUEST["nom"]))
{
  $req = new requete($site->db,"SELECT `id_flux` FROM `planet_flux` WHERE `url`='".$_REQUEST["url"]."' LIMIT 1");
  if($req->lines==1)
  {
    $add="Le flux ".$_REQUEST["url"]." est déjà présent.";
  }
  else
  {
    if($site->user->is_in_group("gestion_ae"))
      $_req = new insert($site->dbrw,"planet_flux", array('url'=>$_REQUEST["url"],'nom'=>$_REQUEST["nom"],'id_utilisateur' => $site->user->id,'modere'=>1));
    else
      $_req = new insert($site->dbrw,"planet_flux", array('url'=>$_REQUEST["url"],'nom'=>$_REQUEST["nom"],'id_utilisateur' => $site->user->id,'modere'=>0));
    $add="Le flux ".$_REQUEST["nom"]." (".$_REQUEST["url"].") a bien été ajouté.";
    if(isset($_REQUEST['tags_']))
    {
      $fluxid=$_req->get_id();
      $req = new requete($site->db,"SELECT `id_tag` FROM `planet_tags`");
      while ( list($id) = $req->get_row() )
        if(isset($_REQUEST['tags'][$id]))
          $_req = new insert($site->dbrw,"planet_flux_tags", array('id_flux'=>$fluxid,'id_tag'=>$id));
    }
  }
}
elseif($_REQUEST["action"]=="addtag" && !empty($_REQUEST["tag"]))
{
  $req = new requete($site->db,"SELECT `id_tag` FROM `planet_tags` WHERE `tag`='".strtoupper($_REQUEST["tag"])."' LIMIT 1");
  if($req->lines==1)
    $add="Le tag ".strtoupper($_REQUEST["tag"])." existe déjà.";
  else
  {
    if($site->user->is_in_group("gestion_ae"))
      $_req = new insert($site->dbrw,"planet_tags", array('tag'=>strtoupper($_REQUEST["tag"]),'modere'=>1));
    else
      $_req = new insert($site->dbrw,"planet_tags", array('tag'=>strtoupper($_REQUEST["tag"]),'modere'=>0));
    $add="Le tag ".strtoupper($_REQUEST["tag"])." a été ajouté.";
  }
}
elseif($_REQUEST["action"]=="tagperso")
{
  $req = new requete($site->db,"SELECT `planet_tags`.`id_tag`, `planet_tags`.`tag`, `planet_user_tags`.`id_utilisateur` ".
                               "FROM `planet_tags` ".
                               "LEFT JOIN `planet_user_tags` ".
                               "ON (`planet_user_tags`.`id_tag`=`planet_tags`.`id_tag` ".
                               "AND `planet_user_tags`.`id_utilisateur` = '".$site->user->id."') ".
                               "WHERE `planet_tags`.`modere`='1'");
  if($req->lines>0)
  {
    while($row=$req->get_row())
    {
      if(isset($_REQUEST['tags'][$row['id_tag']]) && is_null($row['id_utilisateur']))
      {
        $_req = new insert($site->dbrw,"planet_user_tags", array('id_tag'=>$row['id_tag'],'id_utilisateur'=>$site->user->id));
        $subreq = new requete($site->db,"SELECT `planet_flux_tag`.`id_flux` ".
                                        "`planet_flux_tag` ".
                                        "INNER JOIN `planet_user_flux` USING(`id_flux`) ".
                                        "WHERE `planet_flux_tag`.`id_tag` = '".$row['id_tag']."'");
        while($_row=$subreq->get_row())
          $_req = new delete($site->dbrw, "planet_user_flux", array("id_flux" => $_row["id_flux"],"id_utilisateur"=>$site->user->id));
      }
      elseif(!isset($_REQUEST['tags'][$row['id_tag']]) && !is_null($row['id_utilisateur']))
      {
        $_req = new delete($site->dbrw, "planet_user_tags", array("id_tag" => $row['id_tag'], "id_utilisateur"=>$site->user->id));
        $subreq = new requete($site->db,"SELECT `planet_flux_tag`.`id_flux` ".
                                        "`planet_flux_tag` ".
                                        "INNER JOIN `planet_user_flux` USING(`id_flux`) ".
                                        "WHERE `planet_flux_tag`.`id_tag` = '".$row['id_tag']."'");
        while($_row=$subreq->get_row())
          $_req = new delete($site->dbrw, "planet_user_flux", array("id_flux" => $_row["id_flux"],"id_utilisateur"=>$site->user->id));
      }
    }
  }
}
elseif($_REQUEST["action"]=="fluxperso")
{
  $req = new requete($site->db,"SELECT `planet_flux`.`id_flux`, `planet_user_flux`.`id_utilisateur`, `planet_user_flux`.`view` ".
                               "FROM `planet_flux` ".
                               "LEFT JOIN `planet_user_flux` ".
                               "ON (`planet_user_flux`.`id_flux`=`planet_flux`.`id_flux` ".
                               "AND `planet_user_flux`.`id_utilisateur` = '".$site->user->id."') ".
                               "WHERE (`planet_flux`.`id_utilisateur`='".$site->user->id."' OR `planet_flux`.`modere`= '1')");
  if($req->lines>0)
  {
    while($row=$req->get_row())
    {
      if(isset($_REQUEST['flux'][$row['id_flux']]) && is_null($row['id_utilisateur']))
        $_req = new insert($site->dbrw,"planet_user_flux", array('id_flux'=>$row['id_flux'],'id_utilisateur'=>$site->user->id, 'view'=>1));
      elseif(!isset($_REQUEST['flux'][$row['id_flux']]) && !is_null($row['id_utilisateur']) && $row['view']==1)
        $_req = new delete($site->dbrw, "planet_user_flux", array("id_flux" => $row['id_flux'], "id_utilisateur"=>$site->user->id));
      elseif(isset($_REQUEST['flux'][$row['id_flux']]) && !is_null($row['id_utilisateur']) && $row['view']==0)
        $_req = new requete($site->dbrw, "UPDATE `planet_user_flux` SET `view`='1' ".
                                         "WHERE `id_flux` = '".$row['id_flux']."' AND `id_utilisateur`='".$site->user->id."'");
    }
  }
}


if($_REQUEST["view"]=="add")
{
  $cts->add_paragraph("Gestion du contenu");
  if(isset($add))
    $cts->add_paragraph($add);
  $site->add_contents($cts);
  $cts = new contents("Proposer un nouveau flux");
  $frm = new form("addflux","index.php?view=add&action=addtag",false,"POST","");
  $frm->add_text_field("tag","Tag",false,true);
  $frm->add_submit("save","Envoyer");
  $cts->add($frm,false);
  $site->add_contents($cts);
  $cts = new contents("Proposer un nouveau flux");
  $frm = new form("addflux","index.php?view=add&action=addflux",false,"POST","");
  $frm->add_text_field("nom","Nom",false,true);
  $frm->add_text_field("url","URL",false,true);
  $subfrm = new form("tags_",null,null,null,"Tags associés");
  $req = new requete($site->db,"SELECT `id_tag`, `tag` FROM `planet_tags` ORDER BY `tag` ASC");
  while ( $row = $req->get_row() )
    $subfrm->add_checkbox("tags[".$row['id_tag']."]",$row['tag'], false);
  $frm->add ( $subfrm, true, false, false, false, false, true );
  $frm->add_submit("save","Envoyer");
  $cts->add($frm,false);
  $site->add_contents($cts);
  $cts = new contents("Mes propositions");
  $cts->add_paragraph("Liste des flux déjà proposés\n");
  $req = new requete($site->db,"SELECT * FROM `planet_flux` WHERE `id_utilisateur`='".$site->user->id."'");
  $tbl = new sqltable("listasso","",$req, "index.php?view=add","id_flux",array("nom"=>"Nom","url"=>"Url"),array("delete"=>"Supprimer"), array(), array());
  $cts->add($tbl,false);
}
elseif($_REQUEST["view"]=="perso")
{
  $cts->add_paragraph("Personalisez votre planet");

  $req = new requete($site->db,"SELECT `planet_tags`.`id_tag`, `planet_tags`.`tag`, `planet_user_tags`.`id_utilisateur` ".
                               "FROM `planet_tags` ".
                               "LEFT JOIN `planet_user_tags` ".
                               "ON (`planet_user_tags`.`id_tag`=`planet_tags`.`id_tag` ".
                               "AND `planet_user_tags`.`id_utilisateur` = '".$site->user->id."') ".
                               "WHERE `planet_tags`.`modere`='1'");
  $abotags=array();
  if($req->lines>0)
  {
    $site->add_contents($cts);
    $cts = new contents("Vos abonnements aux tags");
    $frm = new form("tags_","index.php?view=perso&action=tagperso","false","POST","");
    while ( $row = $req->get_row() )
    {
      if(!is_null($row['id_utilisateur']))
        $abotags[]=$row['id_tag'];
      $frm->add_checkbox("tags[".$row['id_tag']."]","<a href=index.php?view=perso&tagid=".$row['id_tag'].">".$row['tag']."</a>", !is_null($row['id_utilisateur']));
    }
    $frm->add_submit("save","Modifier");
    $cts->add ( $frm, false, false, true, false, false, true );
  }
  $req = new requete($site->db,"SELECT `planet_flux`.`id_flux`, `planet_flux`.`nom`, `planet_user_flux`.`id_utilisateur` ".
                               "FROM `planet_flux` ".
                               "LEFT JOIN `planet_user_flux` ".
                               "ON (`planet_user_flux`.`id_flux`=`planet_flux`.`id_flux` ".
                               "AND `planet_user_flux`.`id_utilisateur` = '".$site->user->id."') ".
                               "WHERE (`planet_flux`.`id_utilisateur`='".$site->user->id."' OR `planet_flux`.`modere`= '1')");
  $aboflux=false;
  if($req->lines>0)
  {
    while ( $row = $req->get_row() )
    {
      $_req = new requete($site->db,"SELECT `id_tag` ".
                                    "FROM `planet_flux_tags` ".
                                    "WHERE `id_flux`='".$row['id_flux']."'");
      $abotag=false;
      while ( $_row = $_req->get_row() )
      {
        if(in_array($_row['id_tag'],$abotags))
        {
          $abotag=true;
          break;
        }
      }
      if(!$abotag)
      {
        if(!$aboflux)
        {
          $site->add_contents($cts);
          $cts = new contents("Vos abonnements aux autres flux");
          $aboflux=true;
          $frm = new form("flux_","index.php?view=perso&action=fluxperso","false","POST","");
        }

        $frm->add_checkbox("flux[".$row['id_flux']."]",$row['nom'], !is_null($row['id_utilisateur']));
      }
    }
    if($aboflux)
    {
      $frm->add_submit("save","Modifier");
      $cts->add ( $frm, false, false, true, false, false, true );
    }
  }
}
else
{
  $cts->add_title(2, "Mon planet à moi");
  $_tags = new requete($site->db,
                       "SELECT `planet_tags`.`tag`, `planet_flux_tags`.`id_flux`, `planet_flux`.`nom`, `planet_flux`.`url` ".
                       "FROM `planet_user_tags` ".
                       "INNER JOIN `planet_flux_tags` USING(`id_tag`) ".
                       "INNER JOIN `planet_tags` ON `planet_user_tags`.`id_tag` = `planet_tags`.`id_tag` ".
                       "INNER JOIN `planet_flux` ON `planet_flux_tags`.`id_flux`=`planet_flux`.`id_flux` ".
                       "LEFT JOIN `planet_user_flux` ON `planet_flux_tags`.`id_flux`=`planet_user_flux`.`id_flux` ".
                       "WHERE `planet_user_tags`.`id_utilisateur`='".$site->user->id."' ".
                       "AND (`planet_user_flux`.`view` IS NULL OR `planet_user_flux`.`view`!='0') ".
                       "AND `planet_flux`.`modere`='1' ".
                       "GROUP BY `planet_flux_tags`.`id_flux`");
  $totflux=$_tags->lines;
  $tags=array();
  $flux=array();
  $j=0;
  while(list($tag,$id_flux,$nom_flux,$url_flux)=$_tags->get_row())
  {
    if(!isset($tags[$tag]))
      $tags[$tag]=array();
    $tags[$tag][]=$id_flux;
    $flux[$id_flux]['nom_flux']=$nom_flux;
    $flux[$id_flux]['url_flux']=$url_flux;
  }
  $_flux = new requete($site->db,
                       "SELECT `planet_flux`.`id_flux`, `planet_flux`.`nom`, `planet_flux`.`url` ".
                       "FROM `planet_user_flux` ".
                       "INNER JOIN `planet_flux` ON `planet_user_flux`.`id_flux`=`planet_flux`.`id_flux` ".
                       "WHERE `planet_user_flux`.`view`='1' AND `planet_flux`.`modere`='1'");
  $totflux=$totflux+$_flux->lines;
  while(list($id_flux,$nom_flux,$url_flux)=$_flux->get_row())
  {
    if(!isset($flux[$id_flux]))
    {
      if(!isset($tags['Reste']))
        $tags['Reste']=array();
      $tags['Reste'][]=$id_flux;
      $flux[$id_flux]['nom_flux']=$nom_flux;
      $flux[$id_flux]['url_flux']=$url_flux;
    }
  }
  if(count($totflux)==0)
  {
    $cts->add_paragraph("Vous n'êtes inscrits à aucun flux (actifs) pour le moment.");
  }
  else
  {
    $cts->add_paragraph("L'AE n'est pas responsable du contenu de cette page. Les contenus proposés  ".
                        "restent la propriété de leur(s) auteur(s) respectif(s).");
    foreach($tags AS $tag => $_flux)
    {
      $content=array();
      $num=0;
      foreach($_flux AS $id_flux)
      {
        if($num==MAX_NUM)
          break;
        if($rs=fetch_rss($flux[$id_flux]['url_flux']))
        {
          if(count($rs->items)>0)
          {
            foreach($rs->items as $item)
            {
              if($num==MAX_NUM)
                break;
              if(!isset($content[$item['date_timestamp']]))
                $content[$item['date_timestamp']]=array();

              if(!empty($item['description']))
              {
                $sumup=$item['description'];
                $sumup = preg_replace('/<(.+?)>/s','',$sumup);
                if (strlen($sumup) > MAX_SUM_LENGHT)
                {
                  $sumup = substr($sumup, 0, MAX_SUM_LENGHT);
                  $espace = strrpos($sumup, " ");
                  $sumup = substr($sumup, 0, $espace)."...";
                }
              }
              else
                $sumup="Pas de résumé désolé.";

              if(isset($item['dc']['creator']))
                $auteur="par ".$item['dc']['creator']." ";
              else
                $auteur="";

              if(empty($item['content']['encoded']))
                $full=str_replace('&lt;','<',str_replace('&gt;','>',$item['description']));
              else
                $full=$item['content']['encoded'];

              $content[$item['date_timestamp']][]=array('title'=>$item['title'],'sumup'=>$sumup,'full'=>$full,'link'=>$item['link'], 'auteur'=>$auteur);

              $num++;
            }
          }
        }
      }
      if(count($content)>0)
      {
        krsort($content);
        $site->add_contents($cts);
        $cts = new contents("Tag : ".$tag);
        $cts->add_paragraph("<br />");
        foreach($content AS $date => $items)
        {
          foreach($items AS $item)
          {
            $cts->add_title(1,$item['title']." (".$item['auteur']."le ".date("d/m/Y h:i:s", $date).")");
            $cts->add_paragraph($item['sumup']);
            $_cts = new contents("Version complète");
            $_cts->puts($item['full']);
            $_cts->add_paragraph('<p align="right"><a href="'.$item['link'].'">Lien vers l\'article</a></p>');
            $cts->add($_cts,true,false,$date.$tag.$i,false,true,false);
            $_cts->add_paragraph('<br />');
          }
        }
      }
    }
  }
}

$site->add_contents($cts);

$site->end_page ();

?>

