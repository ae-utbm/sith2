<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
$site = new site();

$site->start_page("","Réparation de la base utilisateur");


if(isset($_POST["action"]) && $_POST["action"]=="merge")
{
  $_id=0;
  $ids=$_POST["ids"];
  if(count($ids) >1)
  {
    foreach($ids as $id => $value)
    {
      if($_id==0)
      {
        $_id=$id;
      }
      else
      {
        /* on merge tout vers $_id */
        print_r("merging ".$id." on ".$_id." \n");
      }
    }
  }
}

/**
 * Script de vérification des catégories des doublons utilisateurs
 */


$sql = new requete($site->db,"SELECT * FROM `utilisateurs`");

while ( $row = $sql->get_row() )
{
  if(!isset($names[$row['nom_utl']]))
  {
    $names[$row['nom_utl']]=array();
    $names[$row['nom_utl']][$row['prenom_utl']]=array();
    $names[$row['nom_utl']][$row['prenom_utl']][]=$row['id_utilisateur'];
  }
  else
  {
    if(!isset($names[$row['nom_utl']][$row['prenom_utl']]))
    {
      $names[$row['nom_utl']][$row['prenom_utl']]=array();
      $names[$row['nom_utl']][$row['prenom_utl']][]=$row['id_utilisateur'];
    }
    else
      $names[$row['nom_utl']][$row['prenom_utl']][]=$row['id_utilisateur'];
  }
}

$cts = new contents("Gestion des doublons");
foreach($names as $name => $firstnames)
{
  foreach($firstnames as $firstname => $ids)
    if(count($ids)>1)
    {
      if(count($ids)==2)
      {
        $frm = new form("discard","repair_users.php",true,"POST",$name." ".$firstname);
        $frm->add_info("Faut il merger les fiches suivantes :<br />");
        $frm->add_info("<ul>");
        $frm->add_info("<li><a href='../user.php?id_utilisateur=".$ids[0]."'>".$ids[0]."</a></li>");
        $frm->add_info("<li><a href='../user.php?id_utilisateur=".$ids[1]."'>".$ids[1]."</a></li>");
        $frm->add_info("</ul>");
        $frm->add_info("");
        $frm->add_hidden("action","merge");
        $frm->add_hidden("ids[".$ids[0]."]",$ids[0]);
        $frm->add_hidden("ids[".$ids[1]."]",$ids[1]);
        $frm->add_submit("save","Merger");
        $cts->add($frm,true);
      }
      else
      {
        $frm = new form("discard","repair_users.php",true,"POST",$name." ".$firstname);
        $frm->add_info("Faut il merger les fiches suivantes :<br />");
        for ($i=0; $i<count($ids); $i++)
          $frm->add_checkbox( "ids[".$ids[$i]."]","<a href='../user.php?id_utilisateur=".$ids[$i]."'>".$ids[$i]."</a>");
        $frm->add_hidden("action","merge");
        $frm->add_submit("save","Merger");
        $cts->add($frm,true);
      }
    }
}
$site->add_contents($cts);
$site->end_page ();
?>
