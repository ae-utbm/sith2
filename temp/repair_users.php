<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
$site = new site();

$site->start_page("","Réparation de la base utilisateur");


if(isset($_POST["action"]) && $_POST["action"]=="merge")
{
  $_id=0;
  if(isset($_POST["ids"]))
    $ids=$_POST["ids"];
  elseif(isset($_POST["magicform"]))
    $ids=$_POST["magicform"]["boolean"];
  else
    $ids=null;
  if(count($ids) >1)
  {
    foreach($ids as $id => $value)
    {
      if($_id==0)
      {
        $user = new utilisateur($site->db,$site->dbrw);
        $user->load_by_id($id);
        $_id=$id;
      }
      else
      {
        /* on merge tout vers $_id */
        $user2 = new utilisateur($site->db,$site->dbrw);
        $user2->load_by_id($id);
        /* on merge les infos utilisateur */

        /* on déplace les photos matmat */

        /* avatar forum */
        $photo  = "/var/www/ae/www/var/matmatronch/" . $_id . ".jpg";
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".jpg";
        if(file_exists($photo) && file_exists($_photo))
        {
          if( filemtime($photo) > filemtime($photo_) )
            @unlink($_photo);
          else
          {
            @copy($_photo, $photo);
            @unlink($_photo);
          }
        }
        elseif(file_exists($_photo))
        {
          @copy($_photo, $photo);
          @unlink($_photo);
        }
        
        /* photo mmt */
        $photo  = "/var/www/ae/www/var/matmatronch/" . $_id . ".identity.jpg";
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".identity.jpg";
        $identityi  = "/var/www/ae/www/var/matmatronch/" . $_id . ".identity.i.jpg";
        $_identityi =  "/var/www/ae/www/var/matmatronch/" . $id . ".identity.i.jpg";
        if(file_exists($photo) && file_exists($_photo))
        {
          if( filemtime($photo) > filemtime($photo_) )
          {
            @unlink($_photo);
            @unlink($_identityi);
          }
          else
          {
            @copy($_photo, $photo);
            @copy($_identityi, $identityi);
            @unlink($_photo);
            @unlink($_identityi);
          }
        }
        elseif(file_exists($_photo))
        {
          @copy($_photo, $photo);
          @copy($_identityi, $identityi);
          @unlink($_photo);
          @unlink($_identityi);
        }
        
        /* blouse */
        $photo  = "/var/www/ae/www/var/matmatronch/" . $_id . ".blouse.jpg";
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".blouse.jpg";
        $blousemini  = "/var/www/ae/www/var/matmatronch/" . $_id . ".blouse.mini.jpg";
        $_blousemini =  "/var/www/ae/www/var/matmatronch/" . $id . ".blouse.mini.jpg";
        if(file_exists($photo) && file_exists($_photo))
        {
          if( filemtime($photo) > filemtime($photo_) )
          {
            @unlink($_photo);
            @unlink($_blousemini);
          }
          else
          {
            @copy($_photo, $photo);
            @copy($_blousemini, $blousemini);
            @unlink($_photo);
            @unlink($_blousemini);
          }
        }
        elseif(file_exists($_photo))
        {
          @copy($_photo, $photo);
          @copy($_blousemini, $blousemini);
          @unlink($_photo);
          @unlink($_blousemini);
        }

        /* on vérifie les cotises */
        new update($site->dbrw, 
                   "ae_cotisations", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);

        /* on vérifie les photos */

        /* on vérifie les messages forum */
        new update($site->dbrw, 
                   "frm_message", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);
        /* TODO : propositions de sujets sur le forum ? */
  


        /* on vérifie les emprunts matériel */
        new update($site->dbrw, 
                   "inv_emprunt", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);

        /* on vérifie les réservations de salles */
        new update($site->dbrw, 
                   "sl_reservation", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);
  
        /* on vérifie les asso */
        new update($site->dbrw,
                   "asso_membre",
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur' => $id));
  
        /* on vérifie les groupes */
        /*
          is_in_group_id();
          add_to_group();
        */


        /* on vérifie les planings */

        /* on vérifie les sondages */

        /* on vérifie les votes */

        /* on vérifie les factures */

        /* on vérifie les cartes ae et la lettre clé */

        /* on supprimme le doublon */

        /* on reset le mot de passe */
      }
    }
  }
}

function get_user_bordel($id, $nomtable, $champtable = "id_utilisateur")
{
  $sql = new requete($site->db,
         "SELECT * FROM $nomtable WHERE $champtable = $id LIMIT 1");
  while ($res = $sql->get_row())
    $ret[] = $res;
  return $ret;
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
          $frm->add_checkbox( $ids[$i],"<a href='../user.php?id_utilisateur=".$ids[$i]."'>".$ids[$i]."</a>");
        $frm->add_hidden("action","merge");
        $frm->add_submit("save","Merger");
        $cts->add($frm,true);
      }
    }
}
$site->add_contents($cts);
$site->end_page ();
?>
