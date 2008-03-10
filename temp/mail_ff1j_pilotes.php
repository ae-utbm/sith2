<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");


$req = new requete (new mysqlae(),
        "SELECT utilisateurs.id_utilisateur, 
            CONCAT(nom_utl,' ',prenom_utl) as nom,
            email_utl 
         FROM utilisateurs
         INNER JOIN utl_extra on utilisateurs.id_utilisateur = utl_extra.id_utilisateur
         WHERE (datediff(NOW(),date_naissance_utl))/365 >= '21'
            AND ae_utl='1' 
            AND utbm_utl='1'
            AND etudiant_utl='1'
            AND permis_conduire_utl='1'
        ORDER BY id_utilisateur");

while($res = $req->get_row())
{
  echo $res['nom'] . " &lt;" . $res['email_utl'] . "&gt;<br />";
}

?>
