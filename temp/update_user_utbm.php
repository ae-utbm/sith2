<?php
// ce fichier permet de mettre à jour les fiches matmatronch
// tout d'abord, tout ceux qui ne sont pas dans le xml sont
// ancien étudiant s'ils sont étudiants
//

$topdir = "../";
include($topdir. "include/site.inc.php");


$site = new site ();

$cts = new contents();

if( isset($_REQUEST["action"]) )
{
  if ( $_REQUEST["action"]=="upload" )
    if ( is_uploaded_file($_FILES['xmlfile']['tmp_name']) )
      $src=$_FILES['xmlfile']['tmp_name'];

  if ( $_REQUEST["action"]=="frompath" )
    if (file_exists($_REQUEST["path"]))
      $src=$_REQUEST["path"];


  if (isset($src))
  {
    $fp = fopen($src, "r");
    if (!$fp)
      $cts->add_paragraph("Impossible d'ouvrir le fichier XML");
    else
    {
      $i=0;
      fclose($fp);
      $xml = simplexml_load_file($src);
      foreach($xml->Etudiant as $student)
      {
        $user = new utilisateur($site->db,$site->dbrw);
        $cts = new contents($student->email);
        if($user->load_by_email($student->email))
        {
          $subcts = new contents($user->prenom." ".$user->nom);
          $subcts->add_paragraph("<a href='".$topdir."user.php?id_utilisateur=".$user->id."'>fiche matmat</a>");
          $cts->add($subcts);
        }
        $site->add_contents($cts);
        $i++;
        if($i==10)
          break;
      }
      $site->end_page();
      exit();
    }
  }
}



$frm = new form("upload","update_user_utbm.php"."#upload",true,"POST","Changer mes photos persos");
$frm->add_hidden("action","upload");
$frm->add_file_field ( "xmlfile", "Fichier" );
$frm->add_submit("save","UPLOAD");

$cts->add($frm,true);

$frm = new form("frompath","update_user_utbm.php"."#frompath",true,"POST","Changer mes photos persos");
$frm->add_hidden("action","frompath");
$frm->add_text_field("path","Path : (ex : /tmp/truc.xml)");
$frm->add_submit("save","Executer");
$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();

?>
