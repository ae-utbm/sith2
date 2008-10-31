<?

$topdir = "../";

require_once($topdir . "include/mysql.inc.php");
require_once($topdir . "include/mysqlae.inc.php");

$db = new mysqlae ();

header("Content-Type: text/plain");

$idusr = intval($_REQUEST['id']);

function dump_table($nom)
{
  global $idusr, $db;

  echo "in table $nom\n\n";

  $sql = new requete ($db, "SELECT * FROM $nom WHERE id_utilisateur = $idusr");

  while ($res = $sql->get_row())
    print_r($res);

  echo "\n\n\n";
}


dump_table("ae_cotisations");
dump_table("asso_membre");
dump_table("commentaire_entreprise");
dump_table("cpg_participe");
dump_table("cpg_reponse");
dump_table("cpt_debitfacture");
dump_table("cpt_rechargements");
dump_table("cpt_tracking");
dump_table("cpt_verrou");
dump_table("cpta_operation");
dump_table("cv_trajet");
dump_table("cv_trajet_etape");
dump_table("d_file");
dump_table("d_folder");
dump_table("edu_uv_groupe_etudiant");
dump_table("fax_fbx");
dump_table("fimu_inscr");
dump_table("frm_forum");
dump_table("frm_message");
dump_table("frm_sujet");
dump_table("frm_sujet_utilisateur");
dump_table("inv_emprunt");
dump_table("nvl_nouvelles");
dump_table("parrains");
dump_table("sas_photos");
dump_table("sdn_a_repondu");
dump_table("site_sessions");
dump_table("utilisateurs");
dump_table("utl_etu");
dump_table("utl_etu_utbm");
dump_table("utl_extra");
dump_table("utl_groupe");
dump_table("utl_joue_instr");
dump_table("utl_parametres");
dump_table("vt_a_vote");
dump_table("vt_candidat");
dump_table("vt_liste_candidat");
dump_table("utl_joue_instr");



?>
