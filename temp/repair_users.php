<?php

$topdir="../";

require_once($topdir. "include/site.inc.php");
$site = new site();

$site->start_page("","Réparation de la base utilisateur");

$echec="";
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
    $ae=false;
    $utbm=false;
    $user = new utilisateur($site->db,$site->dbrw);
    foreach($ids as $id => $value)
    {
      $user->load_by_id($id);
      if($user->ae && $user->utbm)
      {
        $_id=$user->id;
        break;
      }
      elseif($user->ae)
        $ae=true;
      elseif($user->utbm)
        $utbm=true;
    }
    if($_id==0 && ($ae||$utbm))
    {
      foreach($ids as $id => $value)
      {
        $user->load_by_id($id);
        if($ae && !$utbm && $user->ae)
        {
          $_id=$user->id;
          break;
        }
        elseif(!$ae && $utbm && $user->utbm)
        {
          $_id=$user->id;
          break;
        }
      }
    }
    foreach($ids as $id => $value)
    {
      if($_id==0)
      {
        $user->load_by_id($id);
        $_id=$id;
      }
      elseif($_id==$id)
        continue;
      else
      {
        /* on merge tout vers $_id */
        $user2 = new utilisateur($site->db,$site->dbrw);
        $user2->load_by_id($id);
        /* on merge les infos utilisateur */
        //email
        //$user->set_email($email);
        //$user->set_email_utbm($email);
        if(!$user->utbm && $user2->utbm)
        {
          $user->became_utbm($user2->email_utbm,true);
          $user->etudiant( $user2->nom_ecole_etudiant, $user2->ancien_etudiant, true);
        }
        if(!$user->etudiant && $user2->etudiant)
          $user->etudiant( $ecole, false, true);
        //sexe
        if($user->sexe < $user2->sexe)
          $user->sexe=2;
        //date_naissance
        if($user->date_naissance == strtotime("1970-01-01"))
          $user->date_naissance=$user2->date_naissance;
        // else comment je fais moi ???
        //addresse
        $user->addresse = "";
        //id_ville
        $user->id_ville=null;
        //id_pays
        $user->id_pays=null;
        //tel_maison
        $user->tel_maison=null;
        //tel_portable
        $user->tel_portable=null;
        //alias
        $user->alias=null;
        //droit_image(true or false)
        $user->droit_image=true;
        //site_web ???
        $user->site_web=null;
        //publique
        $user->publique=true; //nazi :P
        //publique_mmtpapier
        $user->publique_mmtpapier=true; //nazi aussi :P
        //signature_utl
        $user->signalure_utl="l'AE c'est bien";;
        //citation
        $user->citation="";
        //adresse_parents
        $user->adresse_parents="";
        //id_ville_parents
        $user->id_ville_parents=null;
        //id_pays_parents
        $user->id_pays_parents=null;
        //tel_parents
        $user->tel_parents="";

        // on sauvegarde
        $user->saveinfos();

        /* on déplace les photos matmat */

        /* avatar forum */
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".jpg";
        if(file_exists($_photo))
          @unlink($_photo);
        
        /* photo mmt */
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".identity.jpg";
        $_identityi =  "/var/www/ae/www/var/matmatronch/" . $id . ".identity.i.jpg";
        if(file_exists($_photo))
        {
          @unlink($_photo);
          @unlink($_identityi);
        }
        
        /* blouse */
        $_photo =  "/var/www/ae/www/var/matmatronch/" . $id . ".blouse.jpg";
        $_blousemini =  "/var/www/ae/www/var/matmatronch/" . $id . ".blouse.mini.jpg";
        if(file_exists($_photo))
        {
          @unlink($_photo);
          @unlink($_blousemini);
        }

        /* on vérifie les cotises */
        /* en bougeant les "cotises" on bouge les carte */
        /* TODO : vérifier qu'il n'existe qu'une carte */
        if($user2->ae)
          $echec.="$user2->id => vérifier les cotisations\n";
        /*new update($site->dbrw, 
                   "ae_cotisations", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);*/

        /* on vérifie les photos */

        /* on vérifie le forum */
        new update($site->dbrw, 
                   "frm_message", 
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));//, true);
        new update($site->dbrw,
                   "frm_forum",
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));
        new update($site->dbrw,
                   "frm_sujet",
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));
        new update($site->dbrw,
                   "frm_sujet",
                   array('id_utilisateur_moderateur' => $_id),
                   array('id_utilisateur_moderateur'  => $id));
        new update($site->dbrw,
                   "frm_sujet_utilisateur",
                   array('id_utilisateur' => $_id),
                   array('id_utilisateur'  => $id));
        new delete($site->dbrw,
                   "frm_sujet_utilisateur",
                   array('id_utilisateur'  => $id));
  


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

        /* on vérifie les edt */

        /* on supprimme le doublon */
        new delete($site->dbrw,"utilisateurs",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_etu",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_etu_utbm",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_extra",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_groupe",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_joue_instru",array("id_utilisateur" => $id));
        new delete($site->dbrw,"utl_parametres",array("id_utilisateur" => $id));

        /* on reset le mot de passe */

      }
    }
    $pass = genere_pass(10);
    $user->send_autopassword_email($_REQUEST["email"],$pass);
    $user->invalidate();
    $user->change_password($pass);
  }
}

if(!empty($echec))
{
  $cts = new contents("y'a eu des merdes");
  $cts->add_paragraph($echec);
  $site->add_contents($cts);
}

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
