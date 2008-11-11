<?php
// ce fichier permet de mettre à jour les fiches matmatronch

// tout d'abord, tout ceux qui ne sont pas dans le xml sont
// ancien étudiants s'ils sont étudiants
//

$topdir = "../";
include($topdir. "include/site.inc.php");


$site = new site ();

$cts = new contents();


  function regaccent ($mot)
  {
    $pattern2 = str_replace("e","(e|é|è|ê|ë|É|È|Ê|Ë)",$mot);
    $pattern2 = str_replace("a","(a|à|â|ä|À|Â|Ä)",$pattern2);
    $pattern2 = str_replace("i","(i|ï|î|Ï|Î)",$pattern2);
    $pattern2 = str_replace("c","(c|ç|Ç)",$pattern2);
    $pattern2 = str_replace("u","(u|ù|ü|û|Ü|Û|Ù)",$pattern2);
    $pattern2 = str_replace("n","(n|ñ|Ñ)",$pattern2);
    return $pattern2;
  }

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

      $notfound = array();
      $updated = array();


      foreach($xml->Etudiant as $student)
      {
        $user->load_by_email($student->email);

        if ( !$user->is_valid() ) // Non trouvé... essayons par nom/prenom/date de naissance
        {
          $naissance = datetime_to_timestamp($student->DateNaissance);
          $nom = $student->Nom;
          $prenom = $student->Prenom;

          if ( !is_null($naissance) )
          {
            $req = new requete($site->db,
              "SELECT * FROM `utilisateurs`
              WHERE `nom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($nom)) . "$'
              AND `prenom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($prenom)) . "$'
              AND `date_naissance_utl` = '".date("Y-m-d",$naissance)."'");
          }
          /*
          if ( is_null($naissance) || $req->lines == 0 )
          {
            $req = new requete($site->db,
              "SELECT * FROM `utilisateurs`
              WHERE `nom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($nom)) . "$'
              AND `prenom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($prenom)) . "$'");
          }
          */
          if ( $req->lines == 1 )
            $user->_load($req->get_row());

        }

        if($user->is_valid())
        {
          $updated[] = $user->id;

          $user->load_all_extra();

          $cts = new contents($user->prenom." ".$user->nom." ".$student->email);
          $cts->add_paragraph("<a href='".$topdir."user.php?id_utilisateur=".
			      $user->id."'>fiche matmat</a>");

      	  $error = 0;

          if ( !$user->became_utbm($student->email, true) )
          {
            $cts->add_paragraph("Erreur passage UTBM");
            $error++;
          }
          $user->became_etudiant("UTBM", false, true);



      	  /* date naissance ? */
      	  if ($student->DateNaissance != date("d/m/Y", $user->date_naissance))
          {
            $cts->add_paragraph("<b>date de naissance non concordante</b> : <br/>".
      			  $student->DateNaissance . "(CRI) /  ".
      			  date("d/m/Y", $user->date_naissance) . " (NOUS)");
            $error++;
            change_birthdate($user->id, $student->DateNaissance);
          }

          if ( !trim($student->CodeDepartement) )
          {
            $cts->add_paragraph("<b>cri=boulets?</b> : departement selon le CRI ".$student->CodeDepartement);
            $error++;
          }

      	  /* branche ? */
      	  if ($student->CodeDepartement != strtoupper($user->departement))
          {
            $cts->add_paragraph("<b>departement non concordant</b> : ".
      			  $student->CodeDepartement . " (CRI) / " . strtoupper($user->departement) . " (NOUS)");
            $error++;

            move_to_branche($user->id, $student->CodeDepartement);
          }

      	  /* filière ? */
      	  if ($student->CodeFiliere != strtoupper($user->filiere))
          {
            $cts->add_paragraph("<b>filiere non concordante</b> : <br/>".
      			  $student->CodeFiliere . " (CRI) / " . strtoupper($user->filiere) . " (NOUS)");
            $error++;

            move_to_filiere($user->id, $student->CodeFiliere);
          }

      	  /* semestre ? */
      	  if ($student->Semestre != $user->semestre)
          {
            $cts->add_paragraph("<b>semestre non concordant</b> : <br/>".
      			  $student->Semestre . " (CRI) / " . $user->semestre . " (NOUS)");
            $error++;
            move_to_semester($user->id, $student->Semestre);
          }


      	  if ($error > 0)
          {
            $cts->add_paragraph("$error erreurs.","error");

            $site->add_contents($cts);
          }
        }
        else
        {
          $cts = new contents($student->Prenom." ".$student->Nom);
          $cts->add_paragraph($req->lines." trouvé(s)","error");
          $cts->add_paragraph(print_r($student,true));
          $site->add_contents($cts);
          $notfound[] = $student;
        }
      }

      // Passe en ancien les autres
      $req = new requete($site->dbrw, "UPDATE utilisateurs SET
      `ancien_etudiant_utl` = '1', `etudiant_utl` = '0'
      WHERE id_utilisateur NOT IN (".implode(",",$updated).") AND `utbm_utl` = '1' AND `etudiant_utl` = '1'");

      $cts = new contents("Passage en ancien");
      $cts->add_paragraph($req->lines." utilisateur(s) affecté(s)");
      $site->add_contents($cts);

      $cts = new contents("Stats");
      $cts->add_paragraph(count($updated)." etudiants mise à jour");
      $cts->add_paragraph(count($notfound)." inconnus");
      $site->add_contents($cts);

      $site->end_page();
      exit();
    }
  }
}

function move_to_semester($iduser, $sem)
{
  global $site;

  return new update($site->dbrw,
		    "utl_etu_utbm",
		    array("semestre_utbm" => strtolower($sem)),
		    array("id_utilisateur" => $iduser));
}

function move_to_branche($iduser, $branche)
{
  global $site;

  $branche = strtolower($branche);

  return new update($site->dbrw,
		    "utl_etu_utbm",
		    array("departement_utbm" => $branche),
		    array("id_utilisateur" => $iduser));
}
function move_to_filiere($iduser, $filiere)
{
  global $site;

  $filiere = strtolower($filiere);

  return new update($site->dbrw,
		    "utl_etu_utbm",
		    array("filiere_utbm" => $filiere),
		    array("id_utilisateur" => $iduser));
}


function change_birthdate($iduser, $date)
{
  global $site;

  $timestp = datetime_to_timestamp($date);
  return new update($site->dbrw,
		    "utilisateurs",
		    array("date_naissance_utl" => date("Y-m-d", $timestp)),
		    array("id_utilisateur" => $iduser));
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
