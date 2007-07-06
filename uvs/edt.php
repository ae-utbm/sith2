<?php

$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$site = new site();

$site->start_page("services", "Emploi du temps");

if (!$site->user->utbm)
{
	error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
	error_403();
}

$edt = new edt($site->db, $site->dbrw);


if ($_REQUEST['render'] == 1)
{
  isset($_REQUEST['id']) ? $id = intval($_REQUEST['id']) : $id = $site->user->id;

  $sem = (date("m") > 6 ? "A" : "P") . date("y");

  isset($_REQUEST['semestre']) ? $semestre = $_REQUEST['semestre'] : $semestre = $sem;
 
  $user = new utilisateur($site->db);
  $user->load_by_id($id);


  $edt->load($id, $semestre);

  $edtimg = new edt_img($user->prenom . " ". $user->nom . "(".$user->alias.") ",  $edt->edt_arr);
  $edtimg->generate ();
  exit();

}

$cts = new contents("Emploi du temps", "");

$cts->add_paragraph("<h2>Vos emplois du temps disponibles</h2><br/>");

$req = new requete($site->db, "SELECT 
                                        `semestre_grp`
                                        , `edu_uv_groupe_etudiant`.`id_utilisateur` 
                               FROM 
                                        `edu_uv_groupe` 
                               INNER JOIN 
                                        `edu_uv_groupe_etudiant` 
                               USING(`id_uv_groupe`) 
                               WHERE 
                                        `id_utilisateur` = ".$site->user->id." 
                               GROUP BY 
                                        `id_utilisateur`");
if ($req->lines <= 0)
{
  $cts->add_paragraph("Vous n'avez pas enregistré d'emploi du temps.");
} 

else
{
  while ($rs = $req->get_row())
    $tab[] = "<a href=\"./edt.php?render=1&id=".$site->user->id."&semestre=".$rs['semestre_grp']."\">".
      "Emploi du temps du semestre ".$rs['semestre_grp']."</a>";

  $itemlst = new itemlist("Liste des emploi du temps", false, $tab);
  $cts->add($itemlst);


}

$site->add_contents($cts);


$site->end_page();

?>

