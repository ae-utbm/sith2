<?php
/**
 * Import de l'ancienne base vers la nouvelle
 */
$topdir="../";
require_once($topdir."include/site.inc.php");
/* on inclu tout pour voir deja si ya pas d erreur syntaxique */

require_once("include/pedagogie.inc.php");
require_once("include/uv.inc.php");
require_once("include/uv_comment.inc.php");
require_once("include/cursus.inc.php");
require_once("include/pedag_user.inc.php");
require_once("include/edt.inc.php");


/* ancien systeme */
require_once($topdir."include/entities/uv.inc.php");

$site = new site();

if ( !$site->user->is_in_group("taiste") )
  $site->error_forbidden();

$sql = new requete($site->db, "SELECT COUNT('id_uv') FROM `edu_uv`");
$n = $sql->get_row();
echo $n[0]." UV enregistrées dans `edu_uv` <br />";

$sql = new requete($site->db, "SELECT COUNT('id_uv') FROM `pedag_uv`");
$n = $sql->get_row();
echo $n[0]." UV enregistrées dans `pedag_uv` <br />";  

$sql = new requete($site->db, "SELECT COUNT('id_comment') FROM `edu_uv_comments`");
$n = $sql->get_row();
echo $n[0]." commentaires enregistrés dans `edu_uv_comments` <br />";  

$sql = new requete($site->db, "SELECT COUNT('id_commentaire') FROM `pedag_uv_commentaire`");
$n = $sql->get_row();
echo $n[0]." UV enregistrés dans `pedag_uv_commentaire` <br />";  

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


if($_REQUEST['merge_comment']){
}

if($_REQUEST['merge_guide_info']){
}

if($_REQUEST['merge_groups']){
}

?>
