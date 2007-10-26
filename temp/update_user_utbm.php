<?php
// ce fichier permet de mettre à jour les fiches matmatronch

// tout d'abord, tout ceux qui ne sont pas dans le xml sont
// ancien étudiants s'ils sont étudiants
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
      fclose($fp);
      $i=0;
      $user = new utilisateur($site->db,$site->dbrw);

      $xml = simplexml_load_file($src);

      foreach($xml->Etudiant as $student)
      {
        if($user->load_by_email($student->email))
        {

	  $user->load_all_extra();

          $cts = new contents($user->prenom." ".$user->nom);
          $cts->add_paragraph("<a href='".$topdir."user.php?id_utilisateur=".
			      $user->id."'>fiche matmat</a>");
	  
	  $error = 0;
	  /* date naissance ? */
	  if ($student->DateNaissance != date("d/m/Y", $user->date_naissance))
	    {
	      $cts->add_paragraph("<b>date de naissance non concordante</b> : <br/>".
				  $student->DateNaissance . "(CRI) /  ".
				  date("d/m/Y", $user->date_naissance) . " (NOUS)");
	      $error++;
	    }

	  /* branche ? */
	  if ($student->CodeDepartement != strtoupper($user->departement))
	    {
	      $cts->add_paragraph("<b>departement non concordant</b> : ".
				  $student->CodeDepartement . " (CRI) / " . $strtoupper($user->departement) . " (NOUS)");
	      $error++;
	    }
	    
	  /* filière ? */
	  if ($student->CodeFiliere != strtoupper($user->filiere))
	    {
	      $cts->add_paragraph("<b>filiere non concordante</b> : <br/>".
				  $student->CodeFiliere . " (CRI) / " . strtoupper($user->filiere) . " (NOUS)");
	      $error++;
	    }

	  /* semestre ? */
	  if ($student->Semestre != $user->semestre)
	    {
	      $cts->add_paragraph("<b>semestre non concordant</b> : <br/>".
				  $student->Semestre . " (CRI) / " . $user->semestre . " (NOUS)");
	      $error++;
	    }
	  
	  if ($error == 0)
	    $cts->add_paragraph("L'utilisateur semble être à jour.");
	  else
	    $cts->add_paragraph("<b>$error erreurs.</b>");

          $site->add_contents($cts);
        }
	else
	  {
	    $cts = new contents($student->email);
	    $cts->add_paragraph("<b>NON TROUVE</b>");
	    $site->add_contents($cts);
	  }

        $i++;
	if($i == 50)
          break;
      }
      $site->end_page();
      exit();
    }
  }
}



$frm = new form("upload","update_user_utbm.php"."#upload",true,"POST","Envoi d'un fichier XML");
$frm->add_hidden("action","upload");
$frm->add_file_field ( "xmlfile", "Fichier" );
$frm->add_submit("save","UPLOAD");

$cts->add($frm,true);

$frm = new form("frompath","update_user_utbm.php"."#frompath",true,"POST","Chargement du XML depuis un fichier sur le serveur");
$frm->add_hidden("action","frompath");
$frm->add_text_field("path","Path : (ex : /tmp/truc.xml)");
$frm->add_submit("save","Executer");
$cts->add($frm,true);

$site->add_contents($cts);
$site->end_page();

?>
