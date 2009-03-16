<?php
/**
 * Import de l'ancienne base vers la nouvelle
 */
$topdir="../";
require_once($topdir."include/site.inc.php");
/* on inclu tout pour voir deja si ya pas d erreur syntaxique */
/**
require_once("include/uv.inc.php");
require_once("include/uv_comment.inc.php");
require_once("include/cursus.inc.php");
require_once("include/pedag_user.inc.php");
*/

require_once("temp/pedagogie2.inc.php");
require_once("temp/uv2.inc.php");
require_once("include/uv_comment.inc.php");
require_once("include/pedag_user.inc.php");


/* ancien systeme */
require_once($topdir."include/entities/uv.inc.php");

/* conversion P09 en P2009 */
function PXX($value){
  return $value[0].'20'.$value[1].$value[2];
}

$site = new site();

if ( !$site->user->is_in_group("taiste") )
  $site->error_forbidden();

$sql = new requete($site->db, "SELECT COUNT('id_uv') FROM `edu_uv`");
$n = $sql->get_row();
echo $n[0]." UV enregistrées dans `edu_uv` <br />";

$sql = new requete($site->db, "SELECT COUNT('id_uv') FROM `pedag_uv`");
$n = $sql->get_row();
echo $n[0]." UV enregistrées dans `pedag_uv` <br /><br />";

$sql = new requete($site->db, "SELECT COUNT('id_comment') FROM `edu_uv_comments`");
$n = $sql->get_row();
echo $n[0]." commentaires enregistrés dans `edu_uv_comments` <br />";

$sql = new requete($site->db, "SELECT COUNT('id_commentaire') FROM `pedag_uv_commentaire`");
$n = $sql->get_row();
echo $n[0]." commentaires enregistrés dans `pedag_uv_commentaire` <br />";





if($_REQUEST['cleanup']){

}

if($_REQUEST['uv_diff']){
  $sql = new requete($site->db, "SELECT *
                                  FROM `edu_uv`
                                  WHERE `code_uv` NOT
                                  IN (
                                    SELECT `code`
                                    FROM `pedag_uv`
                                  )");
  while($row = $sql->get_row()){
    echo "- ".implode(" : ", array($row['id_uv'], $row['code_uv'], $row['intitule_uv']))." <br />";
  }
}

exit;



/**** on touche plus ***************************************************/


if($_REQUEST['test_add_uv']){
  $uv = new uv2($site->db, $site->dbrw);
  $uv->add("TE00", "Test UV", TYPE_CS, null, SEMESTER_AP, true);
  print_r($uv);
}

if($_REQUEST['test_add_comment']){
}

if($_REQUEST['merge_uv']){
  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`");
  while($row = $sql->get_row()){
    if($row['code_uv'] && check_uv_format($row['code_uv'])){
      if(uv2::exists($site->db, $row['code_uv']))
        echo "- ".$row[0]." existe deja dans la nouvelle base <br />";
      else{
        /* chargement UV schema precedent */
        $uv_old = new uv($site->db, $site->dbrw);
        $uv_old->load_by_code($row['code_uv']);
        $uv_old->load_depts();
        if(!$uv_old){
          echo "*** ".$row[0]." n'a pu etre chargee depuis l'ancienne base ***<br />";
          continue;
        }

        $uv_new = new uv2($site->db, $site->dbrw);
        $uv_new->add($uv_old->code, $uv_old->intitule, $uv_old->cat_by_depts[$uv_old->depts[0]], null, SEMESTER_AP, true);
        /* plouf dans le nouveau */
        if($uv_new->id < 0){
          echo "*** ".$uv_old->code." n'a pu etre ajoutee à nouvelle base ***<br />";
          continue;
        }
        echo "+ ".$row[0]." a ete ajoutee à la nouvelle base<br />";
      }
    }
    else{
      echo "*** ".$row[0]." n'a pu etre validee ***<br />";
    }
  }
}


if($_REQUEST['merge_comment']){
  exit;

  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`", true);
  $c = 0;

  while(list($code) = $sql->get_row()){
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_code($code);
    if(!$uv->is_valid()){
      echo "- ".$code." : probleme base 1 <br />\n";
      continue;
    }

    $uv2 = new uv2($site->db, $site->dbrw);
    $uv2->load_by_code($code);
    if(!$uv2->is_valid()){
      echo "- ".$code." : probleme base 2 <br />\n";
      continue;
    }

    $uv->load_comments();
    $n = count($uv->comments);
    echo "- ".$code." possede $n commentaires (b.1)<br />";
    $c += $n;

    if($n < 1)
      continue;

    foreach($uv->comments as $cmt){
      if(!($cmt instanceof uvcomment)){
        echo "* pb de classe : $cmt <br />\n";
        continue;
      }

      if(!$cmt->id_commentateur){
        echo "* pb de contenu : $cmt->id ".print_r($cmt)."<br />\n";
      }

      $nc = new uv_comment($site->db, $site->dbrw );
      $r = $nc->add($uv2->id, $cmt->id_commentateur, $cmt->note, $cmt->utilite, $cmt->interet, $cmt->qualite_ens, $cmt->charge_travail, $cmt->comment, $cmt->date);

      if($r === false)
        echo "* le commentaire ".$cmt->id." n'a pas pu etre importe <br />\n";
    }

  }

  echo "+ nombre total de commentaires réel : $c <br />\n";
}

if($_REQUEST['correct_comment']){
  $sql = new requete($site->db, "SELECT `code_uv` , `id_utilisateur` , `qualite_uv`
                                  FROM `edu_uv_comments`
                                  LEFT JOIN `edu_uv`
                                    ON `edu_uv`.`id_uv`=`edu_uv_comments`.`id_uv`", true);
  while($row = $sql->get_row()){
    $sql2 = new requete($site->dbrw, "UPDATE `pedag_uv_commentaire`, `pedag_uv`
                                        SET `pedag_uv_commentaire`.`note_enseignement` = '".$row['qualite_uv']."'
                                      WHERE `pedag_uv`.`id_uv` = `pedag_uv_commentaire`.`id_uv`
                                        AND `pedag_uv`.`code` = '".$row['code_uv']."'
                                        AND `pedag_uv_commentaire`.`id_utilisateur` = ".$row['id_utilisateur']);
    if($sql2->is_success())
      echo "- commentaire sur ".$row['code_uv']." modifie avec succes <br />\n";
    else
      print_r($sql2);
  }
}

if($_REQUEST['merge_guide_info']){
  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`", true);
  while(list($code) = $sql->get_row()){
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_code($code);

    $uv2 = new uv2($site->db, $site->dbrw);
    $uv2->load_by_code($code);

    if(!$uv->is_valid()){
      echo "- ".$code." : probleme base 1 <br />\n";
      continue;
    }

    if(!$uv2->is_valid())
      echo "- ".$code." : probleme base 2 <br />\n";
    else{
      $r = $uv2->update_guide_infos($uv->objectifs, $uv->programme, $uv->cours, $uv->td, $uv->tp, null, $uv->ects);
      echo "- ".$code." : $r<br />\n";
    }
  }
}

if($_REQUEST['merge_groups']){
  exit;
  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`", true);
  $c = 0;

  while(list($code) = $sql->get_row()){
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_code($code);
    if(!$uv->is_valid()){
      echo "- ".$code." : probleme base 1 <br />\n";
      continue;
    }

    $uv2 = new uv2($site->db, $site->dbrw);
    $uv2->load_by_code($code);
    if(!$uv2->is_valid()){
      echo "- ".$code." : probleme base 2 <br />\n";
      continue;
    }

    $sql2 = new requete($site->db, "SELECT * FROM `edu_uv_groupe` WHERE `id_uv` = ".$uv->id);
    while(list($id_groupe, ,$type, $numero, $hdebut, $hfin, $jour, $freq, $semestre, $salle) = $sql2->get_row()){
      /* on la jour bourrin pour conserver les id des groupes sinon ca
       * va etre le bordel pour retrouver les correspondances */

      $data = array("id_groupe" => $id_groupe,
                  "id_uv" => $uv2->id,
                  "type" => $type,
                  "num_groupe" => $numero,
                  "freq" => $freq,
                  "semestre" => PXX($semestre),
                  "debut" => $hdebut,
                  "fin" => $hfin,
                  "jour" => $jour,
                  "salle" => $salle);

      $sql3 = new insert($site->dbrw, "pedag_groupe", $data);

      if(!$sql3->is_success())
        print_r($sql3);
      else{
        echo "- groupe n°".$id_groupe." de ".$uv2->code." ajoute <br />\n";
        $c++;
      }
    }

    echo "+ ".$c." groupes ajoutes";
  }
}

if($_REQUEST['merge_groups_utl']){
  /* du coup les id des groupes sont les memes */
  $sql = new requete($site->db, "SELECT * FROM `edu_uv_groupe_etudiant`", true);

  while(list($groupe, $utl, $semaine) = $sql->get_row()){
    if($semaine == 'AB')
      $semaine = NULL;

    $data = array("id_groupe" => $groupe,
                  "id_utilisateur" => $utl,
                  "semaine" => $semaine);
    $sql2 = new insert($site->dbrw, "pedag_groupe_utl", $data);

    if(!$sql2->is_success())
      print_r($sql3);
    else
      echo "- inscription ".$groupe." <=> ".$utl." : OK <br />\n";

  }
}

if($_REQUEST['merge_results']){
  exit;

  /* stockes dans deux endroits :(
   *  => edu_uv_comments.note_obtention_uv !pas de semestre
   *  => edu_uv_obtention.note_obtention
   */

  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`", true);
  $c = 0;

  /**
   * $obt[$utl][$uv][$semestre] = $result
   */
  $obt = array(array(array()));

  while(list($code) = $sql->get_row()){
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_code($code);
    if(!$uv->is_valid()){
      echo "- ".$code." : probleme base 1 <br />\n";
      continue;
    }

    $uv2 = new uv2($site->db, $site->dbrw);
    $uv2->load_by_code($code);
    if(!$uv2->is_valid()){
      echo "- ".$code." : probleme base 2 <br />\n";
      continue;
    }

    /** premiere tournee */
    $sql2 = new requete($site->db, "SELECT * FROM `edu_uv_obtention` WHERE `id_uv` = ".$uv->id);
    if(!$sql2->is_success())
      continue;

    while(list(, $utl, $result, $semestre) = $sql2->get_row()){
      $obt[$utl][$uv2->id][$semestre] = $result;
      $c++;
    }

    /** deuxieme tournee
     * on annule la deuxieme tournee => juste une dizaine de diffs sur lesquels on peut pas revenir car pas de semestre
    $sql3  = new requete($site->db, "SELECT `id_utilisateur`, `note_obtention_uv` FROM `edu_uv_comments` WHERE `id_uv` = ".$uv->id);
    if(!$sql3->is_success())
      print_r($sql3);

    while(list($utl, $result) = $sql3->get_row()){
      if(!in_array($result, $obt[$utl][$uv2->id]))
        echo "- ".$code." : resultat manquant pour ".$utl." : ".$result." <br />\n";
    }
    */
  }
  echo "+ nombre de resultats comptes : $c <br />\n";
  $c = 0;

  foreach($obt as $utl=>$uvs){
    if(empty($uvs))
      continue;

    $usr = new pedag_user($site->db, $site->dbrw);
    $usr->load_by_id($utl);
    if(!$usr->is_valid())
      continue;

    foreach($uvs as $uv=>$sems){
      if(empty($sems))
        continue;

      foreach($sems as $sem=>$result){
        try{
          $r = $usr->add_uv_result($uv, PXX($sem), strtoupper($result));
        }catch(Exception $e){
          echo "! ".$e->getMessage()."<br />\n";
        }
        if($r === false)
          echo "- resultat (".$uv.", ".PXX($sem).", ".strtoupper($result).") n'a pas ete ajoute <br />\n";
        else{
          $c++;
          echo "+ resultat (".$uv.", ".PXX($sem).", ".strtoupper($result).") ajout OK <br />\n";
        }
      }
    }
  }

  echo "+ nombre de resultats ajoutes : $c <br />\n";
}

if($_REQUEST['merge_dept']){
  /** merde on peut pas le faire deux fois pour l instant */
  exit;

  $sql = new requete($site->db, "SELECT `code_uv` FROM `edu_uv`", true);
  /* noms utilisés dans la premiere base */
  $noms = array(
    "Humanites" => DPT_HUMA,
    "TC" => DPT_TC,
    "GMC" => DPT_GMC,
    "EDIM" => DPT_EDIM,
    "GESC" => DPT_GESC,
    "GI" => DPT_GI,
    "IMAP" => DPT_IMAP
  );

  while(list($code) = $sql->get_row()){
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_code($code);

    $uv2 = new uv2($site->db, $site->dbrw);
    $uv2->load_by_code($code);

    $uv->load_depts();
    foreach($uv->depts as $dept){
      echo "- ".$code.": ajout en ".$dept.": ".$uv2->add_to_dept($noms[$dept])."<br />\n";
    }
  }
}


?>
